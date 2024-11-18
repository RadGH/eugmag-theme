<?php

class RSCU_Redirect {
	
	public function __construct() {
	
		add_action( 'template_redirect', array( $this, 'evaluate_redirects' ), 100 );
		
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
	 * Evaluate the current URL and redirect if necessary
	 *
	 * @return void
	 */
	public function evaluate_redirects() {
		global $post;
		if ( post_password_required( $post ) ) return;
		
		$redirect_urls = RSCU_Settings::get_redirect_urls();
		if ( empty( $redirect_urls ) ) return;
		
		$current_slug = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		$current_slug = trim( $current_slug, '/' );
		if ( empty( $current_slug ) ) return;
		
		foreach( $redirect_urls as $redirect ) {
			if ( $redirect['slug'] == $current_slug ) {
				$this->execute_redirect( $redirect );
			}
		}
	}
	
	/**
	 * Performs a redirect using redirect settings
	 *
	 * @param $redirect
	 *
	 * @return void
	 */
	public function execute_redirect( $redirect ) {
		
		// Initialize the cart
		wc_load_cart();
		
		// Apply products
		if ( $redirect['products'] ) {
			$this->redirect_apply_products($redirect['products']);
		}
		
		// Apply coupons
		if ( $redirect['coupons'] ) {
			$this->redirect_apply_coupons($redirect['coupons']);
		}
		
		// Get redirect url
		$redirect_to = wc_get_checkout_url();
		
		switch( $redirect['redirect'] ) {
			case 'other':
				$redirect_to = $redirect['redirect_other'];
				break;
			case 'cart':
				$redirect_to = wc_get_cart_url();
				break;
		}
		
		$redirect_to = add_query_arg(array('rscu' => RSCU_VERSION), $redirect_to);
		
		// Perform redirect
		wp_redirect( $redirect_to, 302, 'RS Checkout URLs' );
		exit;
	}
	
	public function redirect_apply_products( $products ) {
		if ( $products ) foreach( $products as $p ) {
			$product_id = $p['product_id'] ?? false;
			$action = $p['action'] ?? false;
			$quantity = isset($p['quantity']) ? (int) $p['quantity'] : 1;
			
			// Add or remove product in cart
			if ( $action == 'add' ) {
				$found_in_cart = false;
				
				// Check if in cart and update quantity
				if ( WC()->cart->get_cart_contents() ) {
					foreach( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
						if ( isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id ) {
							$found_in_cart = true;
							WC()->cart->set_quantity( $cart_item_key, $quantity );
							break;
						}
					}
				}
				
				// If not in cart, add it
				if ( ! $found_in_cart ) {
					WC()->cart->add_to_cart( $product_id, $quantity );
				}
				
			} elseif ( $action == 'remove' ) {
				
				// Check if in cart and remove
				if ( WC()->cart->get_cart_contents() ) {
					foreach( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
						if ( isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id ) {
							WC()->cart->remove_cart_item( $cart_item_key );
							break;
						}
					}
				}
				
			}
		}
	}
	
	public function redirect_apply_coupons( $coupons ) {
		if ( $coupons ) foreach( $coupons as $c ) {
			$coupon_code = $c['coupon_code'] ?? false;
			$action = $c['action'] ?? false;
			
			// Apply or remove coupon
			if ( $action == 'add' ) {
				WC()->cart->apply_coupon( $coupon_code );
				wc_clear_notices();
			} elseif ( $action == 'remove' ) {
				WC()->cart->remove_coupon( $coupon_code );
			}
		}
	}
	
}

// Initialize the object
RSCU_Redirect::get_instance();