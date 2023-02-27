<?php
/*
Plugin Name: Eugene Magazine Retail Guide
Version:     1.1
Description: Provides Eugene Magazine's retail guide.
Author:      Rosie Leung
Author URI:  https://rosieleung.com/
License:     Copyright 2020 Rosie Leung
*/

defined( 'ABSPATH' ) || exit;

define( 'EUGMAG_RG_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'EUGMAG_RG_PATH', dirname( __FILE__ ) );

add_action( 'plugins_loaded', 'initialize_eugmag_retail_guide_plugin' );

function initialize_eugmag_retail_guide_plugin() {
	include( EUGMAG_RG_PATH . '/includes/eugmag-retail-guide.php' );
	include( EUGMAG_RG_PATH . '/includes/enqueue.php' );
	include( EUGMAG_RG_PATH . '/includes/post-type.php' );
}
