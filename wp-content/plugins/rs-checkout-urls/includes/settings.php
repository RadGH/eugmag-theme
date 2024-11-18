<?php

class RSCU_Settings {
	
	public function __construct() {
	
		// Add a settings page using ACF
		add_action( 'acf/init', array( $this, 'add_settings_page' ) );
		
	}
	
	// Singleton instance
	protected static $instance = null;
	
	public static function get_instance() {
		if ( !isset( self::$instance ) ) self::$instance = new static();
		return self::$instance;
	}
	
	// Utilities
	
	/**
	 * Gets checkout url settings from the settings page and validates them
	 *
	 * @return array[] {
	 * @type string $slug
	 * @type array[] $products {
	 * @type string $product_id
	 * @
	 */
	public static function get_redirect_urls() {
		$redirect_urls = array();
		
		$raw_settings = get_field('checkout_urls', 'rscu-settings');
		if ( $raw_settings ) foreach( $raw_settings as $s ) {
			$setting = array(
				'slug' => '',
				'products' => array(),
				'coupons' => array(),
				'redirect' => 'checkout',
				'redirect_other' => '',
			);
			
			// Add slug
			if ( $s['slug'] ) {
				$setting['slug'] = $s['slug'];
			}
			
			// Prepare products
			if ( $s['products'] ) foreach( $s['products'] as $p ) {
				if ( ! isset($p['product_id']) || ! isset($p['action']) ) continue;
				
				$setting['products'][] = array(
					'product_id' => $p['product_id'],
					'action' => $p['action'],
					'quantity' => $p['quantity'] ?? 1,
				);
			}
			
			// Prepare coupons
			if ( $s['coupons'] ) foreach( $s['coupons'] as $p ) {
				if ( ! isset($p['coupon_code']) || ! isset($p['action']) ) continue;
				
				$setting['coupons'][] = array(
					'coupon_code' => $p['coupon_code'],
					'action' => $p['action'],
				);
			}
			
			// Add redirect
			if ( $s['redirect'] ) {
				$setting['redirect'] = $s['redirect'];
				$setting['redirect_other'] = ($s['redirect'] == 'other') ? $s['redirect_custom_url'] : '';
			}
			
			// Validate
			if ( empty($setting['slug']) ) continue;
			
			// Add setting using slug as unique key
			$redirect_urls[ $setting['slug'] ] = $setting;
		}
		
		return $redirect_urls;
	}
	
	// Hooks
	/**
	 * Add a settings page using ACF
	 *
	 * @return void
	 */
	public function add_settings_page() {
		if ( ! function_exists('acf_add_options_sub_page') ) return;
		
		acf_add_options_sub_page( array(
			'parent' => 'woocommerce',
			'page_title' => 'RS Checkout URLs',
			'menu_title' => 'Checkout URLs',
			'menu_slug' => 'rscu-settings',
			'capability' => 'manage_options',
			'redirect' => false,
			'post_id' => 'rscu-settings', // get_field( 'something', 'rscu-settings' )
		));
	}
	
}

// Initialize the object
RSCU_Settings::get_instance();