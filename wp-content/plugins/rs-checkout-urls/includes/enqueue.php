<?php

class RSCU_Enqueue {
	
	public function __construct() {
		
		// Enqueue front-end assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
		// Enqueue admin assets (backend)
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		
		// Enqueue login page assets
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_assets' ) );
		
		// Enqueue block editor assets
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_editor_assets' ) );
		
	}
	
	// Singleton instance
	protected static $instance = null;
	
	public static function get_instance() {
		if ( !isset( self::$instance ) ) self::$instance = new static();
		return self::$instance;
	}
	
	// Utilities
	
	// Hooks
	/**
	 * Enqueue front-end assets
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		
	}
	
	/**
	 * Enqueue admin assets (backend)
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
	
	}
	
	/**
	 * Enqueue login page assets
	 *
	 * @return void
	 */
	public function enqueue_login_assets() {
	
	}
	
	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
	
	}
	
}

// Initialize the object
RSCU_Enqueue::get_instance();