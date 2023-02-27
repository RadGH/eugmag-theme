<?php

// Warn if guest checkout is enabled, as users must be logged in to buy ads
function ldadstore_warn_guest_checkout() {
	if ( !is_admin() ) return;
	
	if ( get_option('woocommerce_enable_guest_checkout') == "yes" ) {
		if ( isset($_REQUEST['save']) ) return;

		?>
		<div class="error">
			<p><strong>Limelight Advertisements Store &ndash; Warning:</strong> The option "Guest Checkout" is currently enabled.</p>
			
			<p>Users must be registered to purchaes ad locations. Please <a href="<?php echo esc_attr( admin_url('admin.php?page=wc-settings&tab=checkout') ); ?>">disable guest checkout</a> to correct this issue.</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ldadstore_warn_guest_checkout' );

// Gets the ad store product. If it doesn't exist yet, it will be created.
function ldadstore_get_ad_product_id() {
	static $stored_id = null;
	if ( $stored_id !== null ) return $stored_id;

	$ad_product_id = get_option( 'ldadstore_product' );

	if ( $ad_product_id && get_post_type($ad_product_id) == "product" ) {
		$stored_id = (int) $ad_product_id;
		return $stored_id;
	}

	$args = array(
		'post_type' => 'product',
		'post_title' => 'Advertisement',
		'post_status' => 'publish',
		'post_content' => '',
	);

	$ad_product_id = wp_insert_post( $args );

	if ( $ad_product_id ) {
		$stored_id = (int) $ad_product_id;
		update_option( 'ldadstore_product', $ad_product_id, false );
		return $stored_id;
	}else{
		$stored_id = false;
		return false;
	}
}

// When accessing the advertisement product on the store, redirect to the advertisement purchase page instead.
function ldadstore_redirect_advertisement_product_to_page() {
	if ( is_singular('product') && get_the_ID() && get_the_ID() === ldadstore_get_ad_product_id() ) {
		wp_redirect( get_permalink( ldadstore_get_store_product_id() ) );
		exit;
	}
}
add_action( 'template_redirect', 'ldadstore_redirect_advertisement_product_to_page' );

// When editing the advertisement product, show a message explaining what it is for.
function ldadstore_explain_ad_product() {
	$screen = get_current_screen();
	if ( $screen->id != "product"  ) return;

	global $post;
	if ( $post->id != ldadstore_get_ad_product_id() ) return;

	?>
	<div class="updated">
		<p><strong>Advertisement Product:</strong></p>

		<p>This product is used for purchasing advertisement locations. You do not need to modify this product or set up pricing here. You may, however, rename this product or upload a photo.</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'ldadstore_explain_ad_product' );

// When trying to delete the advertisement product, stop them.
function ldadstore_redirect_on_deleting_ad_product( $post_id ) {
	if ( $post_id != ldadstore_get_ad_product_id() ) return;
	if ( get_post_type($post_id) != 'product' ) return;

	$args = array(
		'ID' => $post_id,
		'post_status' => 'publish',
	);
	wp_update_post( $args );

	$url = get_edit_post_link($post_id);

	wp_die(
		'<h2>Cannot delete product</h2>'.
		'<p>This product is required to make the plugin Limelight - Advertisements Store function properly. You may not delete it.</p>'.
		'<p><a href="'. esc_attr($url) .'" class="button button-secondary">&laquo; Go Back</a></p>'
	);
	exit;
}
add_action( 'wp_trash_post', 'ldadstore_redirect_on_deleting_ad_product', 1 );
add_action( 'before_delete_post', 'ldadstore_redirect_on_deleting_ad_product', 1 );


// Ensure our ad product is purchasable
function ldadstore_make_ad_product_purchasable( $purchasable, $product ) {
	if ( !$purchasable && $product->id === ldadstore_get_ad_product_id() ) {
		return true;
	}

	return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'ldadstore_make_ad_product_purchasable', 10, 2 );

// Set the price of the advertisement product
function ldadstore_calculate_product_totals( $cart_object ) {
	if ( is_admin() ) return $cart_object;

	foreach ( $cart_object->cart_contents as $key => $cart_item ) {
		if ( $cart_item['product_id'] !== ldadstore_get_ad_product_id() ) continue;

		if ( $cart_item['quantity'] > 1 ) {
			remove_filter( 'woocommerce_before_calculate_totals', 'ldadstore_calculate_product_totals' );
			WC()->cart->set_quantity( $key, 1 );
			add_filter( 'woocommerce_before_calculate_totals', 'ldadstore_calculate_product_totals', 10, 2 );
		}

		$cart_item['data']->price = ldadstore_get_ad_cart_price( $cart_item['_ad_location'], $cart_item['_ad_time_slots'] );
	}

	return $cart_object;
}
add_filter( 'woocommerce_before_calculate_totals', 'ldadstore_calculate_product_totals', 10, 2 );

function ldadstore_get_ad_cart_price( $selected_location, $selected_time_slots ) {
	$location_pricing = get_field( 'ldadstore_location_prices', 'options' );
	$price = null;

	foreach( $location_pricing as $k => $v ) {
		if ( $v['location'] == $selected_location ) {
			$price = $v['price'];
			break;
		}
	}

	if ( $price === null ) return false;

	$time_slots = ldadstore_get_time_slots();

	return round( count($selected_time_slots) * $price, 2 );
}

// Gets an array of all timeslots, sorted by date (earliest first). Expired time slots are not returned.
function ldadstore_get_time_slots() {
	static $slots = null;

	if ( $slots === null ) {
		$system = get_field( 'ldadstore_duration_system', 'options' );

		switch( $system ) {
			case "Custom":
				$slots = __ldadstore_get_custom_time_slots();
				break;

			default:
				$slots = __ldadstore_get_monthly_time_slots();
				break;
		}
	}

	return $slots;
}

// Gets time slots by month
function __ldadstore_get_monthly_time_slots() {
	$slots = array();

	$months_in_advance = get_field('ldadstore_months_in_advance', 'options');
	if ( !$months_in_advance ) $months_in_advance = 12;

	// May 1st, 2016
	$current_year = (int) date('Y'); // 2016
	$current_month = (int) date('n'); // 5

	for( $i = 0; $i < $months_in_advance; $i++ ) {
		// $i = 0: May 1st, 2016
		// $i = 1; June 1st, 2016
		// ...
		// $i = 12; May 1st, 2017
		// $i = 13; June 1st, 2017
		$this_year = $current_year + floor(($current_month + $i) / 12);
		$this_month = ($current_month + $i) % 12;
		$start_timestamp = mktime(0, 0, 0, $this_month, 1, $this_year);

		$next_year = $this_year + floor(($this_month + 1) / 12);
		$next_month = ($this_month + 1) % 12;
		$end_timestamp = mktime(0, 0, 0, $next_month, 1, $next_year) - 1; // Take one second off to make it the end of the previous month.

		$slots[] = array(
			'name' => date('M Y', $start_timestamp),
			'start_date' => date('Ymd', $start_timestamp), // Probably don't need this, but it makes it consistent with the ACF start date
		    'start_timestamp' => $start_timestamp,
		    'end_timestamp' => $end_timestamp,
			'date_range' => date('F j', $start_timestamp) .'&ndash;' . date('j, Y', $end_timestamp)
		);
	}

	return $slots;
}

// Gets time slots of a custom defined list. Time slots are manually defined by the admin in Advertisements / Advertisement Store
function __ldadstore_get_custom_time_slots() {
	$acf_times = get_field( 'ldadstore_time_slots', 'options' );
	$slots = array();

	// Get timestamp for each slot
	foreach( $acf_times as $k => $slot ) {
		$slot['start_timestamp'] = strtotime( $slot['start_date'] );

		$slots[] = $slot;
	}

	// Sort by start timestamps
	usort( $slots, '__ldadstore_sort_time_slots' );

	// Get the end date, which is just before the next slot
	foreach( $slots as $k => $slot ) {
		$next_slot = isset($slots[$k+1]) ? $slots[$k+1] : false;

		if ( $next_slot ) {
			// One second before the next ad activates. So precise.
			$slots[$k]['end_timestamp'] = $next_slot['start_timestamp'] - 1;
		}else{
			// The last ad slot has an undefined length, so it will not be used.
			$slots[$k]['end_timestamp'] = null;
		}

		$slots[$k]['date_range'] = date( 'F j, Y', $slots[$k]['start_timestamp'] ) . ' &ndash; ' . date( 'F j, Y', $slots[$k]['end_timestamp'] );

		if ( $slots[$k]['end_timestamp'] < time() ) unset($slots[$k]);
	}

	// Re-index the array so we start from 0
	return array_values($slots);
}

// Sorts timestamps from ldadstore_get_time_slots(), smallest first.
function __ldadstore_sort_time_slots( $a, $b ) {
	if ( $a['start_timestamp'] < $b['start_timestamp'] ) return -1;
	else if ( $a['start_timestamp'] > $b['start_timestamp'] ) return 1;
	return 0;
}

// Get an array of locations, time slots, and availability for ads.
function ld_get_store_location_settings() {
	$locations = get_field( 'ldadstore_location_prices', 'options' );

	$theme_locations = ld_ads_get_locations();

	// Merge ad pricing from the backend with the location settings defined in the theme
	foreach( $locations as $k => $loc ) {
		$locations[$k]["key"] = sanitize_title_with_dashes( $k );

		if ( isset($theme_locations[$loc['location']]) ) {
			$locations[$k]["width"]   = $theme_locations[$loc['location']]["width"];
			$locations[$k]["height"]  = $theme_locations[$loc['location']]["height"];
			$locations[$k]["desktop"] = $theme_locations[$loc['location']]["desktop"];
			$locations[$k]["mobile"]  = $theme_locations[$loc['location']]["mobile"];
			$locations[$k]["desc"]    = $theme_locations[$loc['location']]["desc"];
		}else{
			$locations[$k]['width']   = false;
			$locations[$k]['height']  = false;
			$locations[$k]['desktop'] = false;
			$locations[$k]['mobile']  = false;
			$locations[$k]['desc']    = false;
		}
	}

	// Get all ads that haven't expired. These will be used to mark ad locations as occupied.
	$args = array(
		'post_type' => 'ld_ad',
		'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
		'nopaging' => true,
		'meta_query' => array(
			array(
				'key' => 'end_date_timestamp',
				'value' => time(),
				'compare' => '>',
			),
		),
	);

	$ads = get_posts( $args );

	$time_slots = ldadstore_get_time_slots();

	foreach( $locations as $key => $location ) {
		$locations[$key]['price'] = (float) $location['price'];
		unset( $locations[$key]['availability'] );

		$locations[$key]['slots'] = array();

		foreach( $time_slots as $slot_key => $slot ) {
			$locations[$key]['slots'][$slot_key] = $slot;

			$slot["key"] = sanitize_title_with_dashes( $slot["name"] );

			$slot['available'] = true;
			$slot['existing_ad'] = false;

			// If an ad exists that is active for this location, mark the location as unavailable for this time slot.
			foreach( $ads as $ad_key => $ad ) {
				$start_date = get_field( 'start_date_timestamp', $ad->ID );
				$end_date = get_field( 'end_date_timestamp', $ad->ID );
				$ad_locations = get_field( 'ad-locations', $ad->ID );

				if (
					in_array($location['location'], $ad_locations) // Ad occupies this location
					&& $start_date < $slot['end_timestamp'] // Ad hasn't ended yet
					&& $end_date > $slot['start_timestamp'] // Ad has started
				) {
					// This ad is currently running.
					$slot['available'] = false;
					$slot['existing_ad'] = $ad->ID;
					break;
				}
			}


			$locations[$key]['slots'][$slot_key] = $slot;
		}
	}

	return $locations;
}

function ldadstore_get_time_slot_by_name( $time_slot_name ) {
	$slots = ldadstore_get_time_slots();

	foreach( $slots as $the_slot ) {
		if ( $the_slot['name'] == $time_slot_name ) {
			return $the_slot;
			break;
		}
	}

	return false;
}

function ldadstore_get_month_index( $timestamp = null ) {
	if ( $timestamp === null ) $timestamp = time();

	$year_since_epoch = (int) date('Y', $timestamp) - 1970;
	$month_index = (int) date('n', $timestamp);

	return ($year_since_epoch * 12) + $month_index;
}


// When the add to cart button is clicked, create an instace of the ad product and add it to the cart.
function ldadstore_add_to_cart_event() {
	if ( !isset($_REQUEST['ldadstore']) ) return;

	$_data = stripslashes_deep($_REQUEST['ldadstore']);

	$ad_location = $_data['location'];
	$time_slots = (array) $_data['time_slot'];
	$nonce = $_data['nonce'];

	if ( !wp_verify_nonce( $nonce, 'ldadstore-add-to-cart' ) ) {
		wc_add_notice( 'Your ad product could not be added to the cart: Your session has expired.', 'message' );
		return;
	}

	if ( empty( $ad_location ) ) {
		wc_add_notice( 'Your ad product could not be added to the cart: The ad location was not provided.', 'error' );
		return;
	}

	if ( empty( $time_slots ) ) {
		wc_add_notice( 'Your ad product could not be added to the cart: You did not provide any valid time slots.', 'error' );
		return;
	}

	ldadstore_add_advertisement_to_cart( $ad_location, $time_slots );
}
add_action( 'wp_loaded', 'ldadstore_add_to_cart_event' );


// Adds an ad location and specified time slots to the cart for purchase
function ldadstore_add_advertisement_to_cart( $location, $time_slots ) {
	$product_id = ldadstore_get_ad_product_id();
	$quantity = 1;
	$cart_item_data = array();
	$removed_ads = array();

	// Loop through each location/slot and find any that are no longer available, and remove them.
	foreach( $time_slots as $key => $slot ) {
		$existing_ad = ldadstore_is_ad_available( $location, $slot );

		// If an ad spot is not open or gives an error, remove the time slot and remember that it was removed to show an error later.
		if ( is_numeric($existing_ad) || is_wp_error($existing_ad) ) {
			unset($time_slots[$key]);
			$removed_ads[] = $slot;
		}
	}

	// Re-index the array in case some indexes were removed.
	$time_slots = array_values($time_slots);

	// Show an error if the item was removed
	if ( empty($time_slots) ) {
		wc_add_notice( "This ad location and time slot configuration is no longer available." ,'error' );
		return;
	}elseif ( !empty($removed_ads) ) {
		wc_add_notice( "The following ad time slots are no longer available and have been removed: " . implode( ", ", $removed_ads ) ,'notice' );
	}

	$cart_item_data['_ad_location'] = $location;
	$cart_item_data['_ad_time_slots'] = $time_slots;

	WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

	wp_redirect( add_query_arg( 'adstore_redirect', '1', wc_get_cart_url()) );
	exit;
}

function ldadstore_detect_ad_redirect() {
	if ( !empty($_REQUEST['adstore_redirect']) ) {
		// An ad was added to the cart. After we display the page, we'll clear notices that may have been generated.
		add_action( 'wp_footer', 'ldadstore_clear_cart_notices', 2 );
	}
}
function ldadstore_clear_cart_notices() {
	// clear notices that may have been generated.
	wc_clear_notices();
}
add_action( 'woocommerce_init', 'ldadstore_detect_ad_redirect', 2 );

// Displays a list of time periods for an ad within your cart
function ldadstore_display_time_slots_in_cart( $cart_data, $cart_item = null ) {
	$custom_items = array();

	/* Woo 2.4.2 updates */
	if( !empty( $cart_data ) ) $custom_items = $cart_data;

	if( isset( $cart_item['_ad_location'] ) && isset( $cart_item['_ad_time_slots'] ) ) {
		$custom_items[] = array(
			'name' => "Time Periods",
			'value' =>implode( "<br />\n", $cart_item['_ad_time_slots'] ),
		);
	}

	return $custom_items;
}
add_filter( 'woocommerce_get_item_data', 'ldadstore_display_time_slots_in_cart', 10, 2 );

// Displays the ad location in the title of the ad product
function ldadstore_customize_advertisement_product_title( $title, $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['_ad_location'] ) ) {
		$title = $title . ' &ndash; '. $cart_item['_ad_location'];
	}

	return $title;
}
add_filter( 'woocommerce_cart_item_name', 'ldadstore_customize_advertisement_product_title', 10, 3 );

// Convert the cart item data into similar order item metadata for the sales log, etc.
function ldadstore_add_order_item_meta( $item_id, $cart_item_data, $cart_item_key ) {
	if( isset( $cart_item_data['_ad_location'] ) && isset( $cart_item_data['_ad_time_slots'] ) ) {
		wc_add_order_item_meta( $item_id, '_ad_location', $cart_item_data['_ad_location'], true );
		wc_add_order_item_meta( $item_id, '_ad_time_slots', $cart_item_data['_ad_time_slots'], true );
	}
}
add_action( 'woocommerce_add_order_item_meta', 'ldadstore_add_order_item_meta', 20, 3 );

// When an order is created, make advertisements out of the purchased items
function ldadstore_create_ads_from_order( $order_id, $postdata ) {
	// Note: Payment has not been completed, but the order has been placed.
	$order = new WC_Order( $order_id );

	foreach( $order->get_items() as $item_id => $item ) {
		if ( $item['product_id'] != ldadstore_get_ad_product_id() ) continue;

		$location = wc_get_order_item_meta( $item_id, '_ad_location', true );
		$slots = wc_get_order_item_meta( $item_id, '_ad_time_slots', false );
		if ( !$location || !$slots ) continue;

		$ad_posts = array();

		// Iterate through each slot. Create an ad and add it to our ad_posts array.
		foreach( $slots[0] as $slot ) {
			$ad_id = ldadstore_create_ad_from_purchase( $order_id, $location, $slot );

			if ( !$ad_id || is_wp_error($ad_id) ) {
				$error_msg = "Failed to create ad for order #" . $order_id . " (location: " . print_r($location, true) . "; slot: " . print_r($slot, true) .")";

				$ad_posts[] = array(
					'location' => $location,
					'slot' => $slot,
					'ad_id' => false,
					'error' => $error_msg,
				);

				wc_add_notice( $error_msg, 'error' );
				$order->add_order_note( $error_msg );
				continue;
			}else{
				$ad_posts[] = array(
					'location' => $location,
					'slot' => $slot,
					'ad_id' => $ad_id,
					'error' => false,
				);
			}
		}

		// Attach all created ads to the order, so we can easily relate an order to the ads and vice versa.
		wc_add_order_item_meta( $item_id, '_ad_posts', $ad_posts );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'ldadstore_create_ads_from_order', 100, 2 );

// Ensure the user is logged in before they may purchase an ad
function ldadstore_require_login_to_buy_ad() {
	global $woocommerce;

	if ( is_user_logged_in() ) return; // User is logged in
	if ( isset($_REQUEST['createaccount']) && absint($_REQUEST['createaccount']) !== 0 ) return; // User is creating an account

	foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
		if ( $cart_item['product_id'] == ldadstore_get_ad_product_id() ) {
			wc_add_notice( 'You must be logged in to purchase an advertisement.', 'error' );
		}
	}
}
add_action( 'woocommerce_checkout_process', 'ldadstore_require_login_to_buy_ad' );

// Set order meta to be hidden from the order screen in the backend
function ldadstore_hide_meta_keys_from_dashboard( $hidden_keys ) {
	$hidden_keys[] = '_ad_location';
	$hidden_keys[] = '_ad_time_slots';
	$hidden_keys[] = '_ad_posts';
	return $hidden_keys;
}
add_filter( 'woocommerce_hidden_order_itemmeta', 'ldadstore_hide_meta_keys_from_dashboard' );

/*
// Display options on receipt / order received screens
function ldadstore_display_options_on_order_meta( $item_id, $item, $order ) {
	$location = !empty($item['item_meta']['_ad_location'][0]) ? maybe_unserialize( $item['item_meta']['_ad_location'][0] ) : false;
	$time_slots = !empty($item['item_meta']['_ad_time_slots'][0]) ? maybe_unserialize( $item['item_meta']['_ad_time_slots'][0] ) : false;

	$display_items = array();

	if ( $location ) {
		$display_items[] = array(
			'key' => 'ad-location',
			'label' => 'Location',
			'value' => $location
		);
	}

	if ( $time_slots ) {
		$display_items[] = array(
			'key' => 'ad-time-slots',
			'label' => 'Time Slot(s)',
			'value' => implode( "<br />\n", $time_slots )
		);
	}

	if ( !empty($display_items) ) {
		?>
		<div class="ldadstore-order-meta">
			<?php foreach( $display_items as $display ) { ?>
				<dl class="variation">
					<dt class="meta-item meta-<?php echo esc_attr($display['key']); ?>"><?php echo $display['label']; ?>:</dt>
					<dd class="meta-item meta-<?php echo esc_attr($display['key']); ?>"><?php echo $display['value']; ?></dd>
				</dl>
			<?php } ?>
		</div>
		<?php
	}
}
add_action( 'woocommerce_order_item_meta_end', 'ldadstore_display_options_on_order_meta', 30, 3 );
*/


function _ldadstore_display_order_ad_details( $ad_posts, $location, $time_slots ) {
	$display_items = array();

	$is_admin = current_user_can('manage_woocommerce');

	if ( $ad_posts ) {

		foreach( $ad_posts as $i => $ad_post ) {
			$location = $ad_post['location'];
			$slot = $ad_post['slot'];
			$ad_id = $ad_post['ad_id'];
			$error = $ad_post['error'];

			$status = 'invalid';
			$status_message = ldadstore_get_status_info( $ad_id );

			if ( get_post_type($ad_id) == "ld_ad" ) {

				if ( $is_admin ) {
					$ad_html = sprintf(
						'<a href="%s" target="_blank" class="ad-link-edit">Edit / Review</a>',
						esc_attr( get_edit_post_link( $ad_id ) )
					);
				}else{
					$ad_html = sprintf(
						'<a href="%s" target="_blank" class="ad-link-edit">Manage Ad</a>',
						esc_attr( ldadstore_get_ad_link( $ad_id, 'edit' ) )
					);
				}

				$start = get_field( 'start_date_timestamp', $ad_id );
				$end = get_field( 'end_date_timestamp', $ad_id );

				if ( $end < time() ) {
					// Ad has expired
					$status_message = 'Expired';
				}else{
					// Ad has not expired, describe the status
					$status = ldadstore_get_status_info($ad_id);
				}
			}else{
				$ad_html = '(Ad ID not available)';
			}

			ob_start();

			?>
			<div class="ad-post-advertisement"><?php echo $ad_html; ?></div>
			<div class="ad-post-status status-<?php echo esc_attr($status); ?>"><span class="label">Status:</span> <?php echo $status_message; ?></div>
			<div class="ad-post-location"><span class="label">Location:</span> <?php echo esc_html($location); ?></div>
			<div class="ad-post-slot"><span class="label">Slot:</span> <?php echo esc_html($slot); ?></div>
			<?php

			if ( $error ) {
				?>
				<div class="ad-post-error"><span class="label">Error:</span> <?php echo $error; ?></div>
				<?php
			}

			$value = ob_get_clean();

			$display_items[] = array(
				'key' => 'ad-posts ad-posts-' . $i,
				'label' => 'Ad #' . $ad_id,
				'value' => $value
			);
		}

	}else{
		// If $ad_posts is empty for some reason... though it should never be empty!

		if ( $location ) {
			$display_items[] = array(
				'key' => 'ad-location',
				'label' => 'Location',
				'value' => $location
			);
		}

		if ( $time_slots ) {
			$display_items[] = array(
				'key' => 'ad-time-slots',
				'label' => 'Time Slot(s)',
				'value' => implode( "<br />\n", $time_slots )
			);
		}
	}

	if ( !empty($display_items) ) {
		?>
		<div class="view ldadstore-view">
			<table cellspacing="0" class="display_meta">
				<?php
				foreach( $display_items as $display ) {
					if ( $display === 'separator' ) {
						echo '<tr class="meta-item meta-sep"><td colspan="2"></td></tr>';
						continue;
					}
					?>
					<tr class="meta-item ldadstore-<?php echo esc_attr($display['key']); ?>">
						<th><?php echo $display['label']; ?></th>
						<td><?php echo $display['value']; ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<?php
	}
}


// Displays links on the order receipt screen detailing the status of the ad
function ldadstore_display_ad_edit_links_on_order_receipt( $item_id, $item, $order ) {
	$location = !empty($item['item_meta']['_ad_location'][0]) ? maybe_unserialize( $item['item_meta']['_ad_location'][0] ) : false;
	$time_slots = !empty($item['item_meta']['_ad_time_slots'][0]) ? maybe_unserialize( $item['item_meta']['_ad_time_slots'][0] ) : false;
	$ad_posts = !empty($item['item_meta']['_ad_posts'][0]) ? maybe_unserialize( $item['item_meta']['_ad_posts'][0] ) : false;
	if ( !$ad_posts && !$location && !$time_slots ) return;

	_ldadstore_display_order_ad_details( $ad_posts, $location, $time_slots );
}
add_action( 'woocommerce_order_item_meta_end', 'ldadstore_display_ad_edit_links_on_order_receipt', 40, 3 );

// Display order meta on the dashboard, formatted properly
function ldadstore_display_custom_meta_on_dashboard( $item_id, $item, $_product) {
	$order_id = isset($_REQUEST['post']) ? intval($_REQUEST['post']) : get_the_ID();
	$order = wc_get_order($order_id);
	if ( !$order || is_wp_error($order) ) return;

	$location = $order->get_item_meta( $item_id, '_ad_location', true );
	$time_slots = $order->get_item_meta( $item_id, '_ad_time_slots', true );
	$ad_posts = $order->get_item_meta( $item_id, '_ad_posts', true );
	if ( !$ad_posts && !$location && !$time_slots ) return;

	_ldadstore_display_order_ad_details( $ad_posts, $location, $time_slots );
}
add_action( 'woocommerce_before_order_itemmeta', 'ldadstore_display_custom_meta_on_dashboard', 7, 3 );