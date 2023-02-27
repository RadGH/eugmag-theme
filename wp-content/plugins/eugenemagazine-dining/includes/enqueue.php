<?php

function eugmag_dining_enqueue_scripts() {
	$dev = 0;
	$min = $dev ? '' : '.min';
	$key = $dev ? '' : '?key=' . rs_get_google_maps_api_key();

	wp_enqueue_script( 'gmaps', 'https://maps.googleapis.com/maps/api/js' . $key, array( 'jquery' ), null, true );
	wp_localize_script( 'gmaps', 'eugmag_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_style( 'eugmag-dining', AS_URL . '/assets/eugmag-dining' . $min . '.css' );
	wp_enqueue_script( 'eugmag-dining', AS_URL . '/assets/eugmag-dining' . $min . '.js', array( 'jquery', 'gmaps' ), '1.1', true );
}

add_action( 'wp_enqueue_scripts', 'eugmag_dining_enqueue_scripts' );
