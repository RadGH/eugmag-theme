(function ($) {
	$(document).ready(function () {
		if ($("#recreation-guide-list-wrapper").length && $("#recreation_guide_map").length) {
			init_recreation_guide();
		}

		let $recreation_gallery = $(".recreation_gallery");
		if ($recreation_gallery.length) {
			$recreation_gallery.flickity({
				cellAlign: 'left',
				contain: true
			});
		}
	});

	function init_recreation_guide() {
		// save current page
		window.prevpage = 1;

		let $activity_dropdown = $('#activity');

		// load Google Map
		let map = new_map($('#recreation_guide_map'));

		// filter markers
		if ($activity_dropdown.length) {
			filterMarkers(map);

			$activity_dropdown.change(function () {
				filterMarkers(map);
			});
		}

		// update results on click of pagination links
		$("#recreation-guide-list-wrapper").on('click', 'a.page-numbers', recreation_guide_page_numbers_clicked);

		// update results on filter dropdown change
		$activity_dropdown.change(recreation_guide_select_changed);

		// update results on use of browser's fwd/back button
		window.onpopstate = function (e) {
			let page = e.state ? parseInt(e.state.page) : 1;
			let recreationtype = e.state ? parseInt(e.state.activity) : 0;
			eugmag_get_paginated_recreation_guide_list(page, recreationtype, false);
			filterMarkers(map);
		};

		function recreation_guide_page_numbers_clicked(e) {
			e.preventDefault();

			let page = 1,
				recreationtype = 0,
				href = jQuery(this).attr('href'),
				matches = href.match(/page\/([0-9]+)\/?(\?activities=([0-9]+))?/);

			if (matches) {
				page = parseInt(matches[1]);
				recreationtype = matches[3] ? parseInt(matches[3]) : 0;
			}

			eugmag_get_paginated_recreation_guide_list(page, recreationtype);
			window.prevpage = page;
		}

		function recreation_guide_select_changed() {
			let recreationtype = parseInt($activity_dropdown.val());
			eugmag_get_paginated_recreation_guide_list(1, recreationtype);
			window.prevpage = 1;
		}

		function eugmag_get_paginated_recreation_guide_list(page, recreationtype, pushState = true) {
			$('#recreation-guide-list-wrapper').html('<img src="/wp-content/plugins/eugenemagazine-recreation-guide/assets/loader-lg.gif" alt="" />');

			// https://stackoverflow.com/a/42067456/11420510
			window.$_GET = location.search.substr(1).split("&").reduce((o, i) => (u = decodeURIComponent, [k, v] = i.split("="), o[u(k)] = v && u(v), o), {});
			let search = $_GET.search ? $_GET.search : '';

			jQuery.post(eugmag_ajax_object.ajax_url, {
				action: 'paginated_recreation_guide',
				page: page,
				recreationtype: recreationtype,
				search: search
			}, function (response) {
				$('#recreation-guide-list-wrapper').html(response);
				if (pushState) {
					let pageurl = page === 1 ? new URL(recreation_guide_url) : new URL(recreation_guide_url + 'page/' + page);
					if (recreationtype) {
						pageurl.searchParams.append('activity', recreationtype);
					}
					if (search) {
						pageurl.searchParams.append('search', search);
					}
					history.pushState({
						page: window.prevpage,
						activity: recreationtype
					}, window.title, pageurl);
				}
			});
		}

		// render a Google Map onto the selected jQuery element
		function new_map($el) {
			let map = new google.maps.Map($el[0], {
				zoom: 11,
				center: new google.maps.LatLng(44.044129, -123.0946347),
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				scrollwheel: false
			});

			// add markers
			map.markers = [];
			$("#recreation_map_markers").find('.marker').each(function () {
				add_marker($(this), map);
			});

			google.maps.event.addListener(map, 'click', closeInfoWindow);

			// center map (if it won't be centered after the filters are applied)
			center_map(map);

			return map;
		}


		// add a marker to the selected Google Map
		function add_marker($marker, map) {
			let recreationTypes = $marker.attr('data-recreationTypes') ? $marker.attr('data-recreationTypes').toString().split(' ') : '';

			// create marker
			let marker = new google.maps.Marker({
				position: new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng')),
				map: map,
				title: $marker.attr('title'),
				filter: {
					activities: recreationTypes
				}
			});

			// add to array
			map.markers.push(marker);

			// if marker contains HTML, add it to an infoWindow
			if ($marker.html()) {
				// create info window
				let infowindow = new google.maps.InfoWindow({
					content: $marker.html()
				});

				//window.activeWindow = null;

				// show info window when marker is clicked
				google.maps.event.addListener(marker, 'click', function () {
					closeInfoWindow();
					infowindow.open(map, marker);
					window.activeWindow = infowindow;
					map.panTo(this.getPosition());
				});
			}
		}

		// center the map, showing all markers attached to this map
		function center_map(map) {
			let visiblemarkers = 0,
				bounds = new google.maps.LatLngBounds();

			// loop through all markers and create bounds
			$.each(map.markers, function (i, marker) {
				console.log(marker.getVisible());
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


		// filter markers
		function filterMarkers(map) {
			closeInfoWindow();

			let activities = parseInt($activity_dropdown.val());

			for (let i = 0; i < map.markers.length; i++) {
				let marker = map.markers[i];

				// whether recreation has matching recreation type (or it wasn't specified)
				let visible = !activities || marker.filter['activities'].indexOf(activities.toString()) >= 0;

				marker.setVisible(visible);
			}
			center_map(map);
		}

		// close active info window
		function closeInfoWindow() {
			if (window.activeWindow != null) {
				window.activeWindow.close();
			}
		}
	}
})(jQuery);
