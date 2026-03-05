<?php

class EM_Guides_Template {
	
	public function __construct() {
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
	}
	
	// Singleton instance
	private static $instance = null;
	
	public static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new self;
		return self::$instance;
	}
	
	// Utilities
	
	/**
	 * Displays a single guide template
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public static function display_single_guide( $args = array() ) {
		$args = wp_parse_args( $args, array(
			// General
			'back_link' => '',
			'back_link_text' => '',
			'ad_sidebar' => '',
			
			// Fields
			'featured' => '',
			'logo_id' => '',
			'gallery_image_ids' => '',
			'address' => '',
			'description' => '',
			'hours' => '',
			'price' => '',
			'category_terms' => '',
			'category_label' => '',
			'meals_served' => '', // Restaurants
			'info' => '',
			'phone' => '',
			'website' => '',
			'facebook' => '',
			'instagram' => '',
			'twitter' => '',
			'gmaps' => '',
			
		) );
		
		include( EUGMAG_GUIDES_PATH . '/templates/single-guide.php' );
	}
	
	/**
	 * Converts a formatted phone number into a tel: link format (e.g. (541) 123-4567 -> 5411234567)
	 *
	 * @param string $phone_number
	 *
	 * @return string
	 */
	public static function get_phone_link( $phone_number, $display = null ) {
		if ( $display === null ) $display = $phone_number;
		
		$digits = preg_replace( '/\D+/', '', $phone_number );
		
		// Add US country code if missing
		if ( strlen( $digits ) === 10 ) {
			$digits = '1' . $digits;
		}
		
		return sprintf(
			'<a href="tel:+%s">%s</a>',
			esc_attr( $digits ),
			esc_html( $display )
		);
	}
	
	/**
	 * Checks if the current page is any of the singular guide pages
	 *
	 * @return bool
	 */
	public static function is_singular_guide() {
		return
			   is_singular( 'restaurant' )
			|| is_singular( 'recreation' )
			|| is_singular( 'retailer' )
		    || is_singular( 'service' );
	}
	
	// Hooks
	public function enqueue_assets() {
		if ( $this->is_singular_guide() ) {
			wp_enqueue_style( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.css' );
			wp_enqueue_script( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.pkgd.min.js', array( 'jquery' ), null, true );
			
			wp_enqueue_style( 'em-single-guide', EUGMAG_GUIDES_URL . '/assets/single-guide.css', array(), EUGMAG_GUIDES_VERSION );
			wp_enqueue_script( 'em-single-guide', EUGMAG_GUIDES_URL . '/assets/single-guide.js', array('jquery', 'flickity'), EUGMAG_GUIDES_VERSION, true );
		}
	}
	
}

EM_Guides_Template::get_instance();