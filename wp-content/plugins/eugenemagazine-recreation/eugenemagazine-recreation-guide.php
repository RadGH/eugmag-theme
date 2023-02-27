<?php
/*
Plugin Name: Eugene Magazine Recreation Guide
Version:     1.0
Description: Provides Eugene Magazine's recreation guide.
Author:      Rosie Leung
Author URI:  https://rosieleung.com/
License:     Copyright 2021 Rosie Leung
*/

defined( 'ABSPATH' ) || exit;

define( 'EUGMAG_REC_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'EUGMAG_REC_PATH', dirname( __FILE__ ) );

add_action( 'plugins_loaded', 'initialize_eugmag_recreation_guide_plugin' );

function initialize_eugmag_recreation_guide_plugin() {
	include( EUGMAG_REC_PATH . '/includes/eugmag-recreation-guide.php' );
	include( EUGMAG_REC_PATH . '/includes/enqueue.php' );
	include( EUGMAG_REC_PATH . '/includes/post-type.php' );
}
