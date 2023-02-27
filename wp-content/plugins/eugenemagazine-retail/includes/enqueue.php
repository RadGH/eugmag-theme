<?php

function eugmag_retail_guide_enqueue_scripts() {
	$dev = 0;
	$min = $dev ? '' : '.min';
	$key = $dev ? '' : '?key=AIzaSyDV43uZacJPgWA12ncyfSo5p0e4HymFNF8';

	wp_enqueue_script( 'gmaps', 'https://maps.googleapis.com/maps/api/js' . $key, array( 'jquery' ), null, true );
	wp_localize_script( 'gmaps', 'eugmag_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_enqueue_style( 'eugmag-retail-guide', EUGMAG_RG_URL . '/assets/eugmag-retail-guide' . $min . '.css' );
	wp_enqueue_script( 'eugmag-retail-guide', EUGMAG_RG_URL . '/assets/eugmag-retail-guide' . $min . '.js', array( 'jquery', 'gmaps' ), '1.1', true );
}

add_action( 'wp_enqueue_scripts', 'eugmag_retail_guide_enqueue_scripts' );
