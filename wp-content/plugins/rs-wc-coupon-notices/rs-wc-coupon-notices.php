<?php
/*
Plugin Name: RS WooCommerce Coupon Notices
Description: Adds custom notices that appear if you have certain coupons in your cart. Note: There are no settings for this plugin, any new coupon notices must be added directly to the plugin.
Version: 1.0.0
Author: Radley Sustaire
Author URI: https://radleysustaire.com
*/

define( 'RS_WC_COUPON_NOTICES_PATH', __DIR__ );
define( 'RS_WC_COUPON_NOTICES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'RS_WC_COUPON_NOTICES_VERSION', '1.0.0' );

function rs_wc_get_coupon_notices() {
	return array(
		'summer2024' => array(
			'message' => '<strong>Coupon <code>&ldquo;summer2024&rdquo;</code> applied!</strong> During checkout, enter your friend\'s address to receive an additional subscription of Eugene Magazine for FREE.',
		),
	);
}

// Checks if you have applied a certain coupon code to your cart
function rs_wc_has_coupon( $coupon_code ) {
	if ( ! function_exists('WC') ) return false;
	
	$applied_coupons = WC()->cart->get_applied_coupons();
	return in_array( $coupon_code, $applied_coupons );
}

// Add the notice for the coupon
function rs_wc_coupon_notices() {
	if ( ! function_exists('wc_add_notice') ) return;
	
	$any_found = false;
	
	foreach( rs_wc_get_coupon_notices() as $coupon_code => $settings ) {
		if ( ! rs_wc_has_coupon( $coupon_code ) ) continue;
		
		$message = $settings['message'];
		$type = $settings['type'] ?? 'notice';
		
		wc_add_notice( $message, $type );
		
		$any_found = true;
	}
	
	if ( $any_found ) {
		?>
		<style>.woocommerce-form-coupon-toggle { display: none; }</style>
		<?php
	}
}
add_action( 'woocommerce_before_cart', 'rs_wc_coupon_notices' );
add_action( 'woocommerce_before_checkout_form', 'rs_wc_coupon_notices' );