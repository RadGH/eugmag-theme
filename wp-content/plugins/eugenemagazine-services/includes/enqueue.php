<?php

function eugmag_service_guide_enqueue_scripts() {
	$dev = 1;
	$min = $dev ? '' : '.min';
	$key = $dev ? '' : '?key=AIzaSyDV43uZacJPgWA12ncyfSo5p0e4HymFNF8';

	wp_enqueue_script( 'gmaps', 'https://maps.googleapis.com/maps/api/js' . $key, array( 'jquery' ), null, true );
	wp_localize_script( 'gmaps', 'eugmag_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_style( 'eugmag-service-guide', EUGMAG_SERVICES_URL . '/assets/eugmag-service-guide' . $min . '.css' );
	wp_enqueue_script( 'eugmag-service-guide', EUGMAG_SERVICES_URL . '/assets/eugmag-service-guide' . $min . '.js', array( 'jquery', 'gmaps' ), '1.1', true );
}

add_action( 'wp_enqueue_scripts', 'eugmag_service_guide_enqueue_scripts' );
