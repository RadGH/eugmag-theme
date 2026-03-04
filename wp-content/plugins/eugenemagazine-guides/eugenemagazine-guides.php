<?php
/*
Plugin Name: Eugene Magazine Guides
Version:     1.0.0
Description: Extends various guides (Recreation, Restaurants, Services, Retail) to have a more extensive template.
Author:      Radley Sustaire
Author URI:  https://radleysustaire.com/
*/

defined( 'ABSPATH' ) || exit;

define( 'EUGMAG_GUIDES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'EUGMAG_GUIDES_PATH', dirname( __FILE__ ) );
define( 'EUGMAG_GUIDES_VERSION', '1.0.0' );

add_action( 'plugins_loaded', 'initialize_eugmag_guides_plugin' );

function initialize_eugmag_guides_plugin() {
	include( EUGMAG_GUIDES_PATH . '/includes/template.php' );
}
