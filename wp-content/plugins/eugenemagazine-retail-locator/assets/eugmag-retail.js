(function ( $ ) {
	$(document).ready(function () {
		if ( $(".retail_map").length ) {
			init_restaurant_map();
		}
	});

	function init_restaurant_map() {

		// load Google Map
		var map = new_map($('.retail_map'));
		var markerNo = 1;

		// close active info window
		function closeInfoWindow() {
			if ( activeWindow != null ) {
				activeWindow.close();
			}
		}


		// render a Google Map onto the selected jQuery element
		function new_map( $el ) {
			var $markers = $el.find('.marker');
			var args = {
				zoom: 11,
				center: new google.maps.LatLng(44.044129, -123.0946347),
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				scrollwheel: false
			};

			// create map
			var map = new google.maps.Map($el[0], args);

			// add a markers reference
			map.markers = [];

			// add markers
			$markers.each(function () {
				add_marker($(this), map);
			});

			// center map (if it won't be centered after the filters are applied)
			if ( !$('.neighborhoods').length ) {
				center_map(map);
			}
			// return
			return map;
		}


		// add a marker to the selected Google Map
		function add_marker( $marker, map ) {
			var varneighborhoods = $marker.attr('data-neighborhoods') ? $marker.attr('data-neighborhoods').toString().split(' ') : '';
			var varfoodTypes = $marker.attr('data-foodTypes') ? $marker.attr('data-foodTypes').toString().split(' ') : '';

			// create marker
			var marker = new google.maps.Marker({
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
			if ( $marker.html() ) {
				// create info window
				var infowindow = new google.maps.InfoWindow({
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
		function center_map( map ) {

			// vars
			var bounds = new google.maps.LatLngBounds();

			var visiblemarkers = 0;

			// loop through all markers and create bounds
			$.each(map.markers, function ( i, marker ) {
				if ( marker.getVisible() ) {
					var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
					bounds.extend(latlng);
					visiblemarkers++;
				}
			});

			if ( visiblemarkers == 0 ) {
				var latlng = new google.maps.LatLng(44.044129, -123.0946347);
				map.setCenter(latlng);
				map.setZoom(12);
			} else if ( visiblemarkers == 1 ) {
				map.setCenter(bounds.getCenter());
				map.setZoom(14);
			} else {
				map.fitBounds(bounds);
			}
		}


		// MAP FILTERING

		if ( $('.neighborhoods').length ) {
			// filter markers now
			filterMarkers(map);

			// filter markers on select change
			$('.neighborhoods, .food_types').change(function () {
				filterMarkers(map);
			});
		}


		// filter markers
		function filterMarkers( map ) {
			var neighborhoods = $(".neighborhoods").val().toString();
			var food_types = $(".food_types").val().toString();
			for ( var i = 0; i < map.markers.length; i++ ) {
				var marker = map.markers[i];
				marker.setVisible(false);
				if ( marker.filter['neighborhoods'].indexOf(neighborhoods) >= 0 || !neighborhoods ) {
					if ( marker.filter['food_types'].indexOf(food_types) >= 0 || !food_types ) {
						// park has matching neighborhood and food type (or these weren't specified)
						marker.setVisible(true);
					}
				}
			}
			center_map(map);
		}
	}
})(jQuery);