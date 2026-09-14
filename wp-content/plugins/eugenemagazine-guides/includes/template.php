<?php

class EM_Guides_Template {
	private static $instance = null;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'single_template', array( $this, 'load_single_guide_template' ) );
		add_filter( 'theme_page_templates', array( $this, 'eugmag_guides_add_template_to_select' ) );
		add_filter( 'page_template', array( $this, 'eugmag_guides_archive_template' ) );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// add custom template to Page Attributes > Template dropdown
	public function eugmag_guides_add_template_to_select( $post_templates ) {
		$post_templates['page-service-guide.php']    = 'Services Guide';
		$post_templates['page-retail-guide.php']     = 'Retail Guide';
		$post_templates['page-recreation-guide.php'] = 'Recreation Guide';
		$post_templates['page-dining-guide.php']     = 'Dining Guide';

		return $post_templates;
	}

	// load custom template when selected for a page
	public function eugmag_guides_archive_template( $page_template ) {

		if ( in_array( get_page_template_slug(), array( 'page-service-guide.php', 'page-retail-guide.php', 'page-recreation-guide.php', 'page-dining-guide.php' ) ) ) {
			return EUGMAG_GUIDES_PATH . '/templates/archive-guide.php';
		}

		return $page_template;
	}


	/**
	 * Route guide post types to the shared single guide template.
	 *
	 * @param string $single
	 *
	 * @return string
	 */
	public function load_single_guide_template( $single ) {
		global $post;

		if ( ! $post || ! in_array( $post->post_type, array( 'restaurant', 'recreation', 'retailer', 'service' ) ) ) {
			return $single;
		}

		return EUGMAG_GUIDES_PATH . '/templates/single-guide.php';
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

	public function enqueue_assets() {
		//$version = EUGMAG_GUIDES_VERSION;

		if ( $this->is_singular_guide() ) {
			wp_enqueue_style( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.css' );
			wp_enqueue_script( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.pkgd.min.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'em-single-guide', EUGMAG_GUIDES_URL . '/assets/single-guide.js', array( 'flickity' ), filemtime( EUGMAG_GUIDES_PATH . '/assets/single-guide.js' ), true );
		}

		wp_enqueue_style( 'em-guides', EUGMAG_GUIDES_URL . '/assets/guides.css', array(), filemtime( EUGMAG_GUIDES_PATH . '/assets/guides.css' ) );
	}
}

EM_Guides_Template::get_instance();
