<?php

function eugmag_recreation_guide_enqueue_scripts() {
	$dev = 1;
	$min = $dev ? '' : '.min';
	$key = $dev ? '' : '?key=' . rs_get_google_maps_api_key();

	wp_enqueue_script( 'gmaps', 'https://maps.googleapis.com/maps/api/js' . $key, array( 'jquery' ), null, true );
	wp_localize_script( 'gmaps', 'eugmag_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_style( 'eugmag-recreation-guide', EUGMAG_REC_URL . '/assets/eugmag-recreation-guide' . $min . '.css' );
	wp_enqueue_script( 'eugmag-recreation-guide', EUGMAG_REC_URL . '/assets/eugmag-recreation-guide' . $min . '.js', array( 'jquery', 'gmaps' ), '1.1', true );
}

add_action( 'wp_enqueue_scripts', 'eugmag_recreation_guide_enqueue_scripts' );
