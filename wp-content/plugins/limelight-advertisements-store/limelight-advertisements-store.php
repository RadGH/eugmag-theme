<?php
/*
Plugin Name: Limelight - Advertisements Store
Version: 1.0.0
Plugin URI: http://www.limelightdept.com/
Description: Allows customers to purchase advertisement locations using WooCommerce and submit their own advertisement media for approval.
Author: Radley Sustaire
Author URI: mailto:radleygh@gmail.com

Copyright 2016 Limelight Department (radley@limelightdept.com)
For use by Limelight Department and affiliates, do not distribute
*/

if( !defined( 'ABSPATH' ) ) exit;

define( 'LDAdStore_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'LDAdStore_PATH', dirname(__FILE__) );
define( 'LDAdStore_VERSION', '1.0.0' );

// Ensure ACF and WooCommerce are installed/activated before including further functionality.
function ldadstore_init_plugin() {
	if ( !function_exists('acf') ) {
		add_action( 'admin_notices', 'ldadstore_acf_not_found' );
		return;
	}
	
	if ( !class_exists('WooCommerce') ) {
		add_action( 'admin_notices', 'ldadstore_wc_not_found' );
		return;
	}

	include( LDAdStore_PATH . '/includes/enqueue.php' );
	include( LDAdStore_PATH . '/includes/options.php' );
	include( LDAdStore_PATH . '/includes/users.php' );
	include( LDAdStore_PATH . '/includes/store.php' );
	include( LDAdStore_PATH . '/includes/ad.php' );
	include( LDAdStore_PATH . '/includes/page.php' );
	include( LDAdStore_PATH . '/includes/dashboard.php' );
	include( LDAdStore_PATH . '/advertisements-store/shortcode.php' );
}
add_action( 'plugins_loaded', 'ldadstore_init_plugin', 12 );

if ( function_exists('acf') ) {
	include( LDAdStore_PATH . '/includes/acf-message-field.php' );
}

// Error message for when ACF is not installed.
function ldadstore_acf_not_found() {
	?>
	<div class="error">
		<p><strong>Limelight - Advertisements Store: Error</strong></p>
		<p>The required plugin <strong>Advanced Custom Fields Pro</strong> is not running. Please install or activate ACF Pro.</p>
	</div>
	<?php
}

// Error message for when WooCommerce is not installed.
function ldadstore_wc_not_found() {
	?>
	<div class="error">
		<p><strong>Limelight - Advertisements Store: Error</strong></p>
		<p>The required plugin <strong>WooCommerce</strong> is not running. Please install or activate WooCommerce.</p>
	</div>
	<?php
}