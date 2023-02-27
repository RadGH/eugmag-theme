<?php

// Remove sorting dropdown from store
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

// Custom ad ajax url
// init array used for ads rendered later
function custom_ad_print_scripts() {
	?>
	<script type="text/javascript">
		var ld_ads_markup = [];
		var ad_ajax_url = '/ad_ajax.php';
	</script>
	<?php
}
remove_action( 'wp_print_scripts', 'ld_ads_print_scripts' );
add_action( 'wp_print_scripts', 'custom_ad_print_scripts' );

// Change woocommerce breadcrumb Home link to Shop home
function woo_custom_breadrumb_home_url() {
	return get_permalink( woocommerce_get_page_id( 'shop' ) );
}

add_filter( 'woocommerce_breadcrumb_home_url', 'woo_custom_breadrumb_home_url' );


// Change woocommerce breadcrumb Home text to Shop
function jk_change_breadcrumb_home_text( $defaults ) {
	$defaults['home'] = 'Shop';

	return $defaults;
}

add_filter( 'woocommerce_breadcrumb_defaults', 'jk_change_breadcrumb_home_text' );


function add_isotope() {
	if ( function_exists( "is_woocommerce" ) && is_woocommerce() ) {
		ld_enqueue_script( '/includes/libraries/isotope/isotope.pkgd.min.js', array( 'jquery' ), "3.0", true );
	}
}

add_action( 'wp_enqueue_scripts', 'add_isotope', 30 );


function add_woocommerce_category_filters() {
	$product_categories = get_terms( 'product_cat', array( "hide_empty" => 1 ) );
	if ( !$product_categories ) return;
	
	$active_obj = get_queried_object();
	$active_term_id = false;
	
	if ( $active_obj instanceof WP_Term ) foreach( $product_categories as $term ) {
		if ( $term->term_id == $active_obj->term_id ) $active_term_id = $active_obj->term_id;
	}
	
	echo '<ul id="woocommerce-category-filter">';
	echo '<li><a href="' . get_permalink( woocommerce_get_page_id( 'shop' ) ) . '" class="'. ($active_term_id === false ? 'active' : '') .'" data-filterclass="product">All</a></li>';
	
	foreach ( $product_categories as $product_category ) {
		$filtername = str_replace(" ","-",strtolower($product_category->name));
		echo '<li><a href="' . get_term_link( $product_category ) . '" class="'. ($active_term_id === $product_category->term_id ? 'active' : '') .'" data-filterclass="product_cat-'. $filtername .'">' . $product_category->name . '</a></li>';
	}
	
	echo "</ul>";
}

add_action( "woocommerce_archive_description", "add_woocommerce_category_filters", 5 );


// Renames "Category" to "Department"
function eugmag_rename_category() {
	global $wp_taxonomies;
	$wp_taxonomies['category']->labels->name = "Departments";
	$wp_taxonomies['category']->labels->singular_name = 'Department';
	$wp_taxonomies['category']->labels->search_items = 'Search Departments';
	$wp_taxonomies['category']->labels->popular_items = 'Popular Departments';
	$wp_taxonomies['category']->labels->all_items = 'All Departments';
	$wp_taxonomies['category']->labels->parent_item = 'Parent Department';
	$wp_taxonomies['category']->labels->parent_item_colon = 'Parent Department:';
	$wp_taxonomies['category']->labels->edit_item = 'Edit Department';
	$wp_taxonomies['category']->labels->view_item = 'View Department';
	$wp_taxonomies['category']->labels->update_item = 'Update Department';
	$wp_taxonomies['category']->labels->add_new_item = 'Add New Department';
	$wp_taxonomies['category']->labels->new_item_name = 'New Department Name';
	$wp_taxonomies['category']->labels->separate_items_with_commas = 'Separate departments with commas';
	$wp_taxonomies['category']->labels->add_or_remove_items = 'Add or remove departments';
	$wp_taxonomies['category']->labels->choose_from_most_used = 'Choose from most used departments';
	$wp_taxonomies['category']->labels->not_found = 'No departments found';
	$wp_taxonomies['category']->labels->no_terms = 'No departments';
	$wp_taxonomies['category']->labels->menu_name = 'Departments';
	$wp_taxonomies['category']->label = 'Departments';
}

add_action( 'init', 'eugmag_rename_category' );


// Disable Authorize.net gateway for ad purchases, and vice versa for other purchases
function eugmag_filter_gateways( $gateways ) {
	$ad_product_id = function_exists('ldadstore_get_ad_product_id') ? ldadstore_get_ad_product_id() : -1;

	if ( empty( WC()->cart->cart_contents ) ) return;
	
	foreach( WC()->cart->cart_contents as $key => $values ) {
		if ( $values['product_id'] === $ad_product_id ) {
			// Do not pay for ads with credit card here, ads use COD.
			unset($gateways['authorize_net_cim_credit_card']);
		}else{
			// Cash On Delivery is not meant for non-ad products.
			unset($gateways['cod']);
		}
	}

	if ( empty($gateways) ) {
		// Prevent this notice from being shown multiple times using a static $once.
		wc_clear_notices();
		$cart_url = wc_get_cart_url();
		wc_add_notice( 'You cannot purchase an advertisement and different product at the same time. Please <a href="'. esc_attr($cart_url) .'">return to your cart</a> and remove one of these items.', "error" );
	}

	return $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'eugmag_filter_gateways', 1);



// add google maps API key to ACF backend
add_filter('acf/settings/google_api_key', function () {
	//return 'AIzaSyAJ0dhHd1rkPzg9cxSYz0-mqRyXaKsJZJg';
	return 'AIzaSyDV43uZacJPgWA12ncyfSo5p0e4HymFNF8';
});


/**
 * Get cover image settings for the current page
 *
 * @return mixed
 */
function em_get_cover_header() {
	$cover = array(
		// standard cover.php
		'image'     => get_field( 'cover-image' ),
		'mobile_image' => get_field( 'cover-image-mobile' ),
		
		'iconcolor' => get_field( 'cover-icon-color' ),
		
		'logo' => array(
			'image' => get_field( 'cover-logo' ),
			'align' => get_field( 'logo-position' ),
		),
		
		/*
		// advanced (todo, based on cover-front-page.php)
		'align' => strtolower( get_field( 'cover-position' ) ),
		
		'title' => array(
			'text' => get_field( 'cover-title' ),
			'color' => get_field( 'cover-title-color' ),
			'align' => strtolower( get_field( 'cover-title-align' ) ),
		),
		
		'subtitle' => array(
			'text' => get_field( 'cover-subtitle' ),
			'color' => get_field( 'cover-subtitle-color' ),
			'align' => strtolower( get_field( 'cover-subtitle-align' ) ),
		),
		
		'button' => array(
			'data' => get_field( 'cover-button' ),
			'background' => get_field( 'cover-button-bg-color' ),
			'color' => get_field( 'cover-button-text-color' ),
			'align' => strtolower( get_field( 'cover-button-align' ) ),
		),
		*/
	);
	
	// Default settings
	if ( empty($cover['logo']['image']) ) $cover['logo']['image'] = get_field( 'cover-logo' );
	if ( empty($cover['image']) ) $cover['image'] = get_field( 'cover-image' );
	if ( empty($cover['iconcolor']) ) $cover['iconcolor'] = get_field( 'cover-icon-color' );
	
	// Convert cover image into a mobile image size
	if ( !$cover['mobile_image'] && $cover['image'] ) {
		$i = wp_get_attachment_image_src($cover['image'], 'large');
		$m = $i ? $m = ld_get_attachment_mobile( $cover['image']) : false;
		if ( $m ) {
			$cover['mobile_image'] = $m[0];
		}
	}
	
	// woocommerce pages
	if ( empty( $cover['image'] ) && function_exists( "is_woocommerce" ) && is_woocommerce() ) {
		// note: singular woocommerce pages have already tried to get their specific header image (in the "cover-image" field)
		$cover['image'] = get_post_thumbnail_id( get_option( 'woocommerce_shop_page_id' ) );
	}
	
	// singular pages excluding events pages
	if ( empty( $cover['image'] ) && is_singular() && get_post_type() != "tribe_events" ) {
		// if no image, check for a Featured Image on this page
		$cover['image'] = get_post_thumbnail_id( get_the_ID() );
	}
	
	// events pages
	if ( empty( $cover['image'] ) && get_post_type() == "tribe_events" ) {
		$cover['image'] = get_field( 'events-cover-image', 'options' );
	}
	
	// fallback to cover image off the homepage
	if ( empty( $cover['image'] ) ) {
		$cover['image'] = get_field( 'cover-image', get_option( 'page_on_front' ) );
		$cover['iconcolor'] = get_field( 'cover-icon-color', get_option( 'page_on_front' ) );
	}
	
	// Convert cover image into an inline CSS background property
	/*
	if ( !empty( $cover['image'] ) ) {
		if ( $i = wp_get_attachment_image_src( $cover['image'], 'large' ) ) {
			$cover['image'] = sprintf( 'style="background-image: url(%s);"', esc_attr( $i[0] ) );
		}
	}
	
	// Convert cover logo into html img element
	if ( !empty( $cover['logo']['image'] ) ) {
		if ( $i = wp_get_attachment_image_src( $cover['logo']['image'], 'thumbnail-uncropped' ) ) {
			$cover['logo']['image'] = sprintf( '<img src="%s" alt="%s" width="%s" height="%s" />', esc_attr( $i[0] ), esc_attr( smart_media_alt( $i[0] ) ), (int)$i[1], (int)$i[2] );
		}
	}
	*/
	
	// IDK where this is from
	if ( empty( $cover['iconcolor'] ) ) {
		$cover['iconcolor'] = get_field( 'photo_darkness' );
	}
	
	return $cover;
}