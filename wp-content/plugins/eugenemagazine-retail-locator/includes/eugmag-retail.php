<?php


// load template for the retail store locator
function retail_store_template( $single ) {
	global $post;
	if ( $post->ID == "122" && file_exists( EUGMAG_RS_PATH . '/assets/page-retail-stores.php' ) ) {
		return EUGMAG_RS_PATH . '/assets/page-retail-stores.php';
	}

	return $single;
}

add_filter( 'page_template', 'retail_store_template' );


// shortcode to output retail store locator

function retail_store_shortcode( $atts, $content = '' ) {

	$doingSearch = false;
	$retailPinsOutput = '';
	$args = array(
		'post_type'      => 'retail_store',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'orderby'        => array( 'title' => 'DESC' ),
	);

	if ( isset( $_GET['address'] ) && is_numeric( $_GET['radius'] ) ) {
		$doingSearch = true;
		$address = gmaps_get_latlng( $_GET['address'] );
		$args['geo_query'] = array(
			'lat'           => $address['lat'],
			'lng'           => $address['lng'],
			'lat_meta_key'  => 'retail_latitude',
			'lng_meta_key'  => 'retail_longitude',
			'radius'        => (int)$_GET['radius'],
			'order'         => 'ASC',
			'distance_unit' => 69.0,
		);
		global $post;
		$retailListOutput = '';
	}else{
		// if not searching, try to get the transient that stores all pins
		$retailPinsOutput = get_transient( 'retailPins' );
	}

	// if we are doing a search, or if there is no transient, do query to get pins
	if ( $doingSearch || !$retailPinsOutput ) {

		$retailPins = new WP_Query( $args );
		if ( $retailPins ) {
			while ( $retailPins->have_posts() ) {
				$retailPins->the_post();
				if ( $map = get_field( 'retail_gmap' ) ) {
					$retailPinsOutput .= '<div class="marker" title="' . esc_attr( get_the_title() ) . '" data-id="' . get_the_ID() . '" data-lat="' . $map['lat'] . '" data-lng="' . $map['lng'] . '">';
					$retailPinsOutput .= '<div class="marker-title">' . esc_attr( get_the_title() ) . '</div>';
					$retailPinsOutput .= '<p class="marker-desc">' . esc_attr( get_field( 'retail_address' ) ) . ', ' . esc_attr( get_field( 'retail_city' ) ) . ', ' . esc_attr( get_field( 'retail_state' ) ) . ' ' . esc_attr( get_field( 'retail_zip' ) ) . '</p>';
					$retailPinsOutput .= '</div>';

					if ( $doingSearch ) {
						$retailListOutput .= '<li><strong>' . get_the_title() . '</strong><br />';
						$retailListOutput .= esc_attr( get_field( 'retail_address', get_the_ID() ) ) . ', ' . esc_attr( get_field( 'retail_city', get_the_ID() ) ) . ', ' . esc_attr( get_field( 'retail_state', get_the_ID() ) ) . ' ' . esc_attr( get_field( 'retail_zip', get_the_ID() ) ) . '<br />';
						$retailListOutput .= '<span class="distance">' . round( $post->distance_value, 2 ) . ' miles</span></li>';
					}
				}
			}
		}

		// if we are not doing a search, we just found all pins, so save them to the transient
		if ( !$doingSearch ) {
			set_transient( 'retailPins', $retailPinsOutput, 60 * 60 * 24 );
		}
	}

	echo '<h2>Search Results</h2><div class="retail_map">', $retailPinsOutput, '</div>';

	if ( $doingSearch ) {
		if ( $retailListOutput ) {
			echo '<ul class="retail-search-results">', $retailListOutput, '</ul>';
		}else{
			echo '<p>No results found.</p><p><a href="' . get_page_link() . '">Show all locations</a></p>';
		}
	}
}

add_shortcode( 'retail_store', 'retail_store_shortcode' );


// save lat and long as separate meta keys so can use them in geo_query
function save_lat_long( $post_id ) {
	if ( get_post_type( $post_id ) != "retail_store" ) {
		return;
	}
	if ( $gmap = get_field( "retail_gmap", $post_id ) ) {
		update_post_meta( $post_id, 'retail_latitude', $gmap['lat'] );
		update_post_meta( $post_id, 'retail_longitude', $gmap['lng'] );
		delete_transient( 'retailPins' );
	}
}

add_action( 'save_post', 'save_lat_long' );


function clear_retail_transient( $post_id ) {
	if ( get_post_type( $post_id ) != "retail_store" ) {
		return;
	}
	delete_transient( 'retailPins' );
}

add_action( 'wp_trash_post', 'clear_retail_transient' );


function gmaps_get_latlng( $address ) {
	// http://stackoverflow.com/a/8633623/470480
	$address = urlencode( $address ); // Spaces as + signs
	
	$apiKey = 'AIzaSyDV43uZacJPgWA12ncyfSo5p0e4HymFNF8';
	$result = wp_remote_get( "https://maps.google.com/maps/api/geocode/json?address=".$address."&key=".$apiKey );

	if ( !$result ) {
		echo 'Could not connect to the Google Maps API';
		return false;
	}

	if ( is_wp_error($result ) ) {
		echo '<h3>Error: ' . $result->get_error_code() .'</h3>';
		echo wpautop( $result->get_error_message() );
		return false;
	}

	if ( $result['response']['code'] !== 200 ) {
		echo '<h3>Error: Google Maps API returned an invalid response:</h3>';
		echo '<pre>';
		echo '<strong>Response:</strong>'. "\n";
		var_dump($result['response']);
		echo '<strong>Body:</strong>'. "\n";
		var_dump($result['body']);
		echo '</pre>';
		return false;
	}

	$data = json_decode( $result['body'], true );
	if ( !$data ) {
		echo '<h2>ERROR! Google Maps returned an invalid response, expected JSON data:</h2>';
		echo esc_html( print_r( $result['body'], true ) );
		exit;
	}

	if ( isset( $data["error_message"] ) ) {
		echo '<h2>ERROR! Google Maps API returned an error:</h2>';
		echo '<strong>' . esc_html( $data["status"] ) . '</strong> ' . esc_html( $data["error_message"] ) . '<br>';
		
		exit;
	}

	if ( empty( $data["results"][0]["geometry"]["location"]["lat"] ) || empty( $data["results"][0]["geometry"]["location"]["lng"] ) ) {
		echo '<h2>ERROR! Latitude/Longitude could not be found:</h2>';
		echo esc_html( print_r( $data, true ) );
		exit;
	}

	$lat = $data["results"][0]["geometry"]["location"]["lat"];
	$lng = $data["results"][0]["geometry"]["location"]["lng"];

	// Value can be negative, so check for specifically 0
	if ( floatval( $lat ) === 0 || floatval( $lng ) === 0 ) {
		echo '<h2>ERROR! Latitude/Longitude is invalid (exactly zero):</h2>';
		var_dump( 'Latitude:', $lat );
		var_dump( 'Longitude:', $lng );
		var_dump( 'Result:', $data["results"][0] );
		exit;
	}

	return array(
		'lat' => $lat,
		'lng' => $lng,
	);
}