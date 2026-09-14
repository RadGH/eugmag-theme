<?php
/*
Plugin Name: Eugene Magazine Guides
Version:     2.0.2
Description: Adds various guides (Recreation, Restaurants, Services, Retail)
Author: Rosie Leung and Radley Sustaire
Author URI: https://zingmap.com
*/

defined( 'ABSPATH' ) || exit;

define( 'EUGMAG_GUIDES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'EUGMAG_GUIDES_PATH', __DIR__ );
define( 'EUGMAG_GUIDES_VERSION', '2.0.2' );

add_action( 'plugins_loaded', 'initialize_eugmag_guides_plugin' );

function initialize_eugmag_guides_plugin() {
	include( EUGMAG_GUIDES_PATH . '/includes/template.php' );
	include( EUGMAG_GUIDES_PATH . '/includes/register-post-types-and-taxonomies.php' );
	include( EUGMAG_GUIDES_PATH . '/includes/guides.php' );
}
