(function($) {

	$(document).ready(function () {
		let $gallery = $(".guide_gallery");
		if ( $gallery.length ) init_slider( $gallery );

		let $map = $(".guide_map");
		if ( $map.length ) init_map( $map );
	});

	function init_slider( $slider ) {
		$slider.flickity({
			cellAlign: 'left',
			contain: true
		});
	};

	function init_map( $map_element ) {

		// load Google Map
		let map = new_map( $map_element );
		let markerNo = 1;

		// close active info window
		function closeInfoWindow() {
			if ( activeWindow != null ) {
				activeWindow.close();
			}
		}

		// render a Google Map onto the selected jQuery element
		function new_map( $el ) {
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

			// return
			return map;
		}

		// add a marker to the selected Google Map
		function add_marker( $marker, map ) {
			// create marker
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng')),
				map: map,
				title: $marker.attr('title'),
				filter: {
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
	};

})(jQuery);