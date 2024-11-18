<?php
/*
Plugin Name: RS Checkout URLs
Description: Allows you to create URLs that automatically add products and/or coupons to cart, and go straight to the checkout page (or the cart page).
Version: 1.0.4
Author: Radley Sustaire
Author URI: https://radleysustaire.com/
*/

define( 'RSCU_PATH', __DIR__ );
define( 'RSCU_URL', untrailingslashit(plugin_dir_url(__FILE__)) );
define( 'RSCU_VERSION', '1.0.4' );

class RS_Checkout_URLs_Plugin {
	
	/**
	 * Checks that required plugins are loaded before continuing
	 *
	 * @return void
	 */
	public static function load_plugin() {
		
		// Check for required plugins
		$missing_plugins = array();
		
		if ( ! class_exists('ACF') ) {
			$missing_plugins[] = 'Advanced Custom Fields Pro';
		}
		
		if ( ! function_exists('WC') ) {
			$missing_plugins[] = 'WooCommerce';
		}
		
		if ( $missing_plugins ) {
			self::add_admin_notice( '<strong>RS Checkout URLs:</strong> The following plugins are required: '. implode(', ', $missing_plugins) . '.', 'error' );
			return;
		}
		
		// Load acf fields
		require_once( RSCU_PATH . '/assets/acf-fields.php' );
		
		// Load plugin files
		require_once( RSCU_PATH . '/includes/enqueue.php' );
		require_once( RSCU_PATH . '/includes/settings.php' );
		require_once( RSCU_PATH . '/includes/redirect.php' );
		
		// After the plugin has been activated, flush rewrite rules, upgrade database, etc.
		add_action( 'admin_init', array( __CLASS__, 'after_plugin_activated' ) );
		
	}
	
	/**
	 * When the plugin is activated, set up the post types and refresh permalinks
	 */
	public static function on_plugin_activation() {
		update_option( 'rscu_plugin_activated', 1, true );
	}
	
	/**
	 * Flush rewrite rules if the option is set
	 *
	 * @return void
	 */
	public static function after_plugin_activated() {
		if ( get_option( 'rscu_plugin_activated' ) ) {
			flush_rewrite_rules();
			update_option( 'rscu_plugin_activated', 0, true );
		}
	}
	
	/**
	 * Adds an admin notice to the dashboard's "admin_notices" hook.
	 *
	 * @param string $message The message to display
	 * @param string $type    The type of notice: info, error, warning, or success. Default is "info"
	 * @param bool $format    Whether to format the message with wpautop()
	 *
	 * @return void
	 */
	public static function add_admin_notice( $message, $type = 'info', $format = true ) {
		add_action( 'admin_notices', function() use ( $message, $type, $format ) {
			?>
			<div class="notice notice-<?php echo $type; ?> bbearg-crm-notice">
				<?php echo $format ? wpautop($message) : $message; ?>
			</div>
			<?php
		});
	}
	
	/**
	 * Add a link to the settings page
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public static function add_settings_link( $links ) {
		 array_unshift( $links, '<a href="'. admin_url('admin.php?page=rscu-settings') .'">Settings</a>' );
		return $links;
	}
	
}

// Add a link to the settings page
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array('RS_Checkout_URLs_Plugin', 'add_settings_link') );

// When the plugin is activated, set up the post types and refresh permalinks
register_activation_hook( __FILE__, array('RS_Checkout_URLs_Plugin', 'on_plugin_activation') );

// Initialize the plugin
add_action( 'plugins_loaded', array('RS_Checkout_URLs_Plugin', 'load_plugin') );