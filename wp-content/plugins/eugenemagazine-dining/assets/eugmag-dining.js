(function ($) {
	$(document).ready(function () {

		if ($("#dining-guide-list-wrapper").length && $("#rest_map").length) {
			init_dining_guide();
		}

		let $restaurant_gallery = $(".rest_gallery");
		if ($restaurant_gallery.length) {
			$restaurant_gallery.flickity({
				cellAlign: 'left',
				contain: true
			});
		}
	});

	function init_dining_guide() {
		let $neighborhoods = $('.neighborhoods');
		let $food_types = $('.food_types');

		function eugmag_get_paginated_dining_guide_list(page, foodtype, pushState = true) {
			$('#dining-guide-list-wrapper').html('<img src="/wp-content/plugins/eugenemagazine-dining/assets/loader-lg.gif" alt="" />');

			// https://stackoverflow.com/a/42067456/11420510
			window.$_GET = location.search.substr(1).split("&").reduce((o, i) => (u = decodeURIComponent, [k, v] = i.split("="), o[u(k)] = v && u(v), o), {});
			let search = $_GET.search ? $_GET.search : '';

			jQuery.post(eugmag_ajax_object.ajax_url, {
				action: 'paginated_dining_guide',
				page: page,
				foodtype: foodtype,
				search: search
			}, function (response) {
				$('#dining-guide-list-wrapper').html(response);
				if (pushState) {
					let pageurl = page === 1 ? new URL(dining_guide_url) : new URL(dining_guide_url + 'page/' + page);
					if (foodtype) {
						pageurl.searchParams.append('food', foodtype);
					}
					if (search) {
						pageurl.searchParams.append('search', search);
					}
					history.pushState({
						page: window.prevpage,
						foodtype: foodtype
					}, window.title, pageurl);
				}
			});
		}

		// save current page as state
		window.prevpage = 1;
		//let foodtype = parseInt($food_types.val());
		/*
		 history.replaceState({
		 page: window.prevpage,
		 foodtype: foodtype
		 }, window.title, location.pathname);*/

		// update results on click of pagination links
		let $diningGuideWrap = $("#dining-guide-list-wrapper");
		$diningGuideWrap.on('click', 'a.page-numbers', function (e) {
			e.preventDefault();

			let page,
				foodtype,
				href = jQuery(this).attr('href'),
				matches = href.match(/page\/([0-9]+)\/?(\?food=([0-9]+))?/);

			if (matches) {
				page = parseInt(matches[1]);
				foodtype = matches[3] ? parseInt(matches[3]) : 0;
			} else {
				page = 1;
				foodtype = 0;
			}

			eugmag_get_paginated_dining_guide_list(page, foodtype);
			window.prevpage = page;
		});


		// update results on select change
		$food_types.change(function () {
			let foodtype = parseInt($food_types.val());
			eugmag_get_paginated_dining_guide_list(1, foodtype);
			window.prevpage = 1;
		});

		// update results on use of browser's fwd/back button
		window.onpopstate = function (e) {
			let page = e.state ? parseInt(e.state.page) : 1;
			let foodtype = e.state ? parseInt(e.state.foodtype) : 0;
			eugmag_get_paginated_dining_guide_list(page, foodtype, false);
			//$food_types.val(foodtype);
			filterMarkers(map);
		};


		/* GOOGLE MAP */

		// load Google Map
		let markerNo = 1;
		let map = new_map($('#rest_map'));

		// close active info window
		function closeInfoWindow() {
			if (activeWindow != null) {
				activeWindow.close();
			}
		}


		// render a Google Map onto the selected jQuery element
		function new_map($el) {
			let $markers = $el.find('.marker');
			let args = {
				zoom: 11,
				center: new google.maps.LatLng(44.044129, -123.0946347),
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				scrollwheel: false
			};

			// create map
			let map = new google.maps.Map($el[0], args);

			// add a markers reference
			map.markers = [];

			// add markers
			$markers.each(function () {
				add_marker($(this), map);
			});

			// center map (if it won't be centered after the filters are applied)
			if (!$neighborhoods.length) {
				center_map(map);
			}
			// return
			return map;
		}


		// add a marker to the selected Google Map
		function add_marker($marker, map) {
			let varneighborhoods = $marker.attr('data-neighborhoods') ? $marker.attr('data-neighborhoods').toString().split(' ') : '';
			let varfoodTypes = $marker.attr('data-foodTypes') ? $marker.attr('data-foodTypes').toString().split(' ') : '';

			// create marker
			let marker = new google.maps.Marker({
				position: new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng')),
				map: map,
				title: $marker.attr('title'),
				filter: {
					neighborhoods: varneighborhoods,
					food_types: varfoodTypes
				}
			});

			markerNo++;

			// add to array
			map.markers.push(marker);

			// if marker contains HTML, add it to an infoWindow
			if ($marker.html()) {
				// create info window
				let infowindow = new google.maps.InfoWindow({
					content: $marker.html()
				});

				activeWindow = null;

				// show info window when marker is clicked
				google.maps.event.addListener(marker, 'click', function () {
					closeInfoWindow();
					infowindow.open(map, marker);
					activeWindow = infowindow;
					map.panTo(this.getPosition());
				});

				google.maps.event.addListener(map, 'click', closeInfoWindow);
			}
		}

		// center the map, showing all markers attached to this map
		function center_map(map) {

			// vars
			let bounds = new google.maps.LatLngBounds();

			let visiblemarkers = 0;

			// loop through all markers and create bounds
			$.each(map.markers, function (i, marker) {
				if (marker.getVisible()) {
					let latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
					bounds.extend(latlng);
					visiblemarkers++;
				}
			});

			if (visiblemarkers === 0) {
				let latlng = new google.maps.LatLng(44.044129, -123.0946347);
				map.setCenter(latlng);
				map.setZoom(12);
			} else if (visiblemarkers === 1) {
				map.setCenter(bounds.getCenter());
				map.setZoom(14);
			} else {
				map.fitBounds(bounds);
			}
		}


		// MAP FILTERING
		if ($neighborhoods.length && $food_types.length) {
			// filter markers now
			filterMarkers(map);

			// filter markers on select change
			$neighborhoods.add($food_types).change(function () {
				filterMarkers(map);
			});
		}


		// filter markers
		function filterMarkers(map) {
			closeInfoWindow();

			let neighborhoods = parseInt($neighborhoods.val());
			let food_types = parseInt($food_types.val());

			for (let i = 0; i < map.markers.length; i++) {
				let marker = map.markers[i];
				marker.setVisible(false);

				if (!neighborhoods || marker.filter['neighborhoods'].indexOf(neighborhoods.toString()) >= 0) {
					if (!food_types || marker.filter['food_types'].indexOf(food_types.toString()) >= 0) {
						// restaurant has matching neighborhood and food type (or these weren't specified)
						marker.setVisible(true);
					}
				}
			}
			center_map(map);
		}
	}
})(jQuery);
