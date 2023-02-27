<?php
/*
Plugin Name: Eugene Magazine Dining Guide
Version:     1.1
Description: Provides Eugene Magazine's dining guide.
Author:      Rosie Leung
Author URI:  https://rosieleung.com/
License:     Copyright 2020 Rosie Leung
*/

defined( 'ABSPATH' ) || exit;

define( 'AS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'AS_PATH', dirname( __FILE__ ) );

add_action( 'plugins_loaded', 'initialize_eugenemagazine_dining_plugin' );

function initialize_eugenemagazine_dining_plugin() {
	include( AS_PATH . '/includes/eugmag-dining.php' );
	include( AS_PATH . '/includes/enqueue.php' );
	include( AS_PATH . '/includes/post-type.php' );
}
