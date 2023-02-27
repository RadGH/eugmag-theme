<?php

function eugmag_retail_enqueue_scripts() {
	wp_enqueue_script( 'gmaps', 'https://maps.googleapis.com/maps/api/js?key=' . rs_get_google_maps_api_key(), array( 'jquery' ), null, true );
	wp_enqueue_style( 'eugmag-retail-locator', EUGMAG_RS_URL . '/assets/eugmag-retail.css' );
	wp_enqueue_script( 'eugmag-retail-locator', EUGMAG_RS_URL . '/assets/eugmag-retail.js', array( 'jquery', 'gmaps' ), '1.0.2', true);
}

add_action( 'wp_enqueue_scripts', 'eugmag_retail_enqueue_scripts' );
