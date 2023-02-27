<?php
/*
Plugin Name: Eugene Magazine Service Guide
Version:     1.0
Description: Provides Eugene Magazine's service guide.
Author:      Rosie Leung
Author URI:  https://rosieleung.com/
License:     Copyright 2021 Rosie Leung
*/

defined( 'ABSPATH' ) || exit;

define( 'EUGMAG_SERVICES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'EUGMAG_SERVICES_PATH', dirname( __FILE__ ) );

add_action( 'plugins_loaded', 'initialize_eugmag_service_guide_plugin' );

function initialize_eugmag_service_guide_plugin() {
	include( EUGMAG_SERVICES_PATH . '/includes/eugmag-service-guide.php' );
	include( EUGMAG_SERVICES_PATH . '/includes/enqueue.php' );
	include( EUGMAG_SERVICES_PATH . '/includes/post-type.php' );
}
