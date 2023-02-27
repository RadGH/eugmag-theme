<?php
/*
Plugin Name: Eugene Magazine Retail Store Locator
Version:     1.0.0
Plugin URI:  http://limelightdept.com/
Description: Provides Eugene Magazine's retail store locator.
Author:      Rosie Leung, Limelight Department
Author URI:  mailto:rosie@limelightdept.com
License:     Copyright 2016 Limelight Department, all rights reserved.
*/

if( !defined( 'ABSPATH' ) ) exit;

define( 'EUGMAG_RS_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'EUGMAG_RS_PATH', dirname(__FILE__) );

add_action( 'plugins_loaded', 'initialize_eugenemagazine_retail_plugin' );

function initialize_eugenemagazine_retail_plugin() {
	include( EUGMAG_RS_PATH . '/includes/eugmag-retail.php' );
	include( EUGMAG_RS_PATH . '/includes/enqueue.php' );
	include( EUGMAG_RS_PATH . '/includes/post-type.php' );
}