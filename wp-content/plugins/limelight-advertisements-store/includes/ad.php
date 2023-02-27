<?php

function ldadstore_is_ad_available( $location, $time_slot ) {
	// 1. Check if time slot exists
	$slots = ldadstore_get_time_slots();

	$slot_exists = false;

	foreach( $slots as $s ) {
		if ( $s['name'] == $time_slot ) {
			$slot_exists = true;
			break;
		}
	}

	if ( !$slot_exists ) {
		return new WP_Error( 'time_slot_not_found', "Time slot not found" );
	}

	// 2. Check if location exists
	$theme_locations = ld_ads_get_locations();

	$location_exists = false;

	foreach( $theme_locations as $loc_name => $loc_settings ) {
		if ( $loc_name == $location ) {
			$location_exists = true;
			break;
		}
	}

	if ( !$location_exists ) {
		return new WP_Error( 'location_not_found', "Advertisement location not found" );
	}

	// 3. Check if the location/time slot is occupied
	$existing_ad = ldadstore_get_ad_occupying_slot( $location, $time_slot );

	if ( $existing_ad ) {
		if ( is_wp_error($existing_ad) ) return $existing_ad;
		else return new WP_Error( 'ad_slot_occupied', "The ad location for this time period is already occupied." );
	}else{
		return true;
	}
}

function ldadstore_create_ad_from_purchase( $order_id,  $location, $time_slot_name ) {
	$order = new WC_Order( $order_id );
	$customer_id = $order->get_user_id();

	// Do not create an ad if the location is occupied
	$time_slot = ldadstore_get_time_slot_by_name( $time_slot_name );
	if ( !$time_slot ) return false;

	// Ensure there is not a conflicting ad (we already did this, but let's double check)
	$occupying_ad = ldadstore_get_ad_occupying_slot( $location, $time_slot_name );
	if ( $occupying_ad ) return false;

	// Get the start and end date for the ad. Ensure it has not already expired.
	$slot_start_time = $time_slot['start_timestamp']; // The ad being tested
	$slot_end_time = $time_slot['end_timestamp'];
	if ( $slot_end_time < time() ) return false;

	// Create a new ad
	$args = array(
		'post_type' => 'ld_ad',
		'post_status' => 'draft',
		'post_author' => $customer_id,
		'post_title' => 'Customer Ad (' . $location . ', ' . $time_slot_name . ')',
	);

	$post_id = wp_insert_post( $args );
	if ( !$post_id ) return false;

	// Fields: Ad Settings
	update_field( 'ad-locations', array( $location ), $post_id );
	// update_field( 'ad-type', 'image', $post_id );

	// Fields: Customer Ad Settings
	update_field( 'time_slot_name', $time_slot_name, $post_id );
	update_field( 'start_date_timestamp', $slot_start_time, $post_id );
	update_field( 'end_date_timestamp', $slot_end_time, $post_id );
	update_field( 'customer', $customer_id, $post_id );
	update_field( 'order', $order_id, $post_id );

	do_action( 'ldadstore_new_ad_setup', $post_id, $order_id, $customer_id, $location, $time_slot_name, $time_slot );

	return $post_id;
}

function ldadstore_get_ad_occupying_slot( $location, $time_slot_name ) {
	$time_slot = ldadstore_get_time_slot_by_name( $time_slot_name );
	if ( !$time_slot ) return false;

	// Get ads for a location. These will be used to mark ad locations as occupied.
	$args = array(
		'post_type' => 'ld_ad',
		'post_status' => 'publish', 'future', 'draft', 'pending', 'private',
		'nopaging' => true,
		'meta_query' => array(
			array(
				'key' => 'ld-ad-location',
				'value' => $location,
				'compare' => '=',
			),
		),
	);

	$ads = get_posts( $args );

	// Loop through all ads to see if any occupy the same slot.
	$slot_start_time = $time_slot['start_timestamp']; // The ad being tested
	$slot_end_time = $time_slot['end_timestamp'];

	foreach( $ads as $ad_key => $ad ) {
		$target_start_time = get_field( 'start_date_timestamp', $ad->ID ); // An existing ad
		$target_end_time = get_field( 'end_date_timestamp', $ad->ID );

		if ( $slot_start_time >= $target_end_time ) continue; // The ad starts after the existing ad has ended, and does not conflict
		if ( $slot_end_time <= $target_start_time ) continue; // The ad ends before the existing ad begins, and does not conflict

		// The ad has a conflicting timestamp, so we return the existing ad.
		return $ad->ID;
	}

	// The ad has no conflicts
	return false;
}

// Returns a PHP date format used to format the start/end date for advertisements
function ad_date_format() {
	// return apply_filters( 'lad_ad_store_date_format', "M j, Y" );
	return apply_filters( 'lad_ad_store_date_format', "n/j/Y" );
}

// Gets the status of an ad, but as detailed info for the user (or admin)
function ldadstore_get_status_info( $ad_id = null ) {
	if ( $ad_id === null ) $ad_id = get_the_ID();

	$status = get_post_status($ad_id);
	$start = get_field( 'start_date_timestamp', $ad_id );
	$end = get_field( 'end_date_timestamp', $ad_id );

	if ( $end < time() ) {
		return 'Expired';
	}
	
	switch( $status ) {
		case 'pending':
			return 'Pending review';
			break;

		case 'draft':
			if ( is_admin() && current_user_can('manage_options') ) {
				return 'Incomplete, waiting on customer submission';
			}else{
				return 'Incomplete, waiting for your content';
			}
			break;

		case 'publish':
			if ( $end < time() ) {
				return 'Expired';
			}else if ( $start < time() ) {
				return 'Active';
			}else{
				return 'Approved (Scheduled)';
			}
			break;

		default:
			return 'Unknown status (' . ucwords($status) . ')';
			break;
	}
}

// Validate an ad submitted by a visitor. Returns WP Error on failure. True otherwise.
function ldadstore_validate_ad( $location_key, $post_id = null ) {
	if ( $post_id === null ) $post_id = get_the_ID();

	$type = get_field('ad-type', $post_id);

	if ( $type == 'image' || $type == 'external_image' ) {
		// Validating URL is not necessary, ACF takes care of it

		$image_url = false;

		if ( $type == 'image' ) {
			$image_id = get_field('ad-image', $post_id);
			$attachment = wp_get_attachment_image_src( $image_id, 'full' );
			if ( $attachment ) $image_url = $attachment[0];
		}else{
			$image_url = get_field('ad-external-image', $post_id);
		}

		if ( !$image_url ) {
			return new WP_Error( "invalid_image", "The image URL cannot be determined." );
		}

		// Validate image size
		$locations = ld_ads_get_locations();

		if ( !isset($locations[$location_key]) ) {
			return new WP_Error( "invalid_location", "The location for this advertisement ({$location_key}) is no longer valid. Please contact support." );
		}

		$location = $locations[$location_key];
		$image_size = getimagesize( $image_url );

		if ( !$image_size || empty($image_size[0]) ) {
			return new WP_Error( "no_image_size", "The image size could not be determined. If your image size is correct, please contact support to override this error." );
		}

		if ( $image_size[0] !== $location['width'] || $image_size[1] !== $location['height'] ) {
			return new WP_Error( "invalid_image_size", "The image size does not match this ad space. Your image must fit exactly.<br>Your Image: {$image_size[0]}&times;{$image_size[1]}<br>Ad Size: {$location['width']}&times;{$location['height']}" );
		}
	}elseif ( $type == "embed" ) {
		// Embed codes must be manually inspected, we can't validate it automatically
		return true;
	}elseif ( empty($type) ) {
		return new WP_Error( "no_type", "No ad type selected" );
	}else{
		return new WP_Error( "invalid_type", "Invalid ad type selected (". esc_html($type) .")" );
	}

	return true;
}

// Generate a link to submit ad for approval
function ldadstore_get_ad_approval_submit_link( $ad_id, $location ) {
	$url = get_permalink( ldadstore_get_dashboard_page_id() );

	$params = array(
		'ad' => $ad_id,
		'location' => $location,
		'nonce' => wp_create_nonce( 'submit-approval' )
	);

	return add_query_arg( $params, $url );
}

// Submit an ad for approval through the front end. Validates the ad settings and marks the ad as pending.
function ldadstore_submit_ad_for_approval() {
	$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : false;
	if ( !$nonce ) return;
	if ( !wp_verify_nonce( $nonce, 'submit-approval' ) ) return;

	$ad_id = isset($_REQUEST['ad']) ? (int) $_REQUEST['ad'] : false;
	$location = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : false;

	$time_slot_name = get_field( 'time_slot_name', $ad_id );

	// Validate the ad to ensure it's OK. Then submit for approval.
	$validation = ldadstore_validate_ad( $location, $ad_id );

	if ( is_wp_error($validation) ) {
		wp_die($validation);
		exit;
	}

	// Set the post status to PENDING
	$args = array(
		'ID' => $ad_id,
		'post_status' => 'pending',
	);
	wp_update_post( $args );

	// Send an email to the notification recipient. Fallback to admin email.
	$notify_email = get_field( 'ld_ad_store_review_email', 'options' );
	if ( !$notify_email ) $notify_email = get_option('admin_email');

	// Generate body content for the email
	ob_start();
	?>
	<p>An advertisement was just submitted for approval on <?php echo site_url(); ?>.</p>

	<h3><strong>Ad Location:</strong> <?php echo $time_slot_name; ?> &ndash; <?php echo $location; ?></h3>

	<p>You should review the content of this advertisement to make sure it meets your website requirements.</p>

	<p><a href="<?php echo esc_attr(get_edit_post_link( $ad_id )); ?>"><strong>Review advertisement &raquo;</strong></a></p>
	<?php
	$body = ob_get_clean();

	wp_mail(
		$notify_email,
		"A user has submitted an advertisement for your approval",
		$body,
		"Content-Type: text/html; charset=UTF-8\r\n"
	);

	wp_redirect( ldadstore_get_ad_link( $ad_id, 'edit' ) );
	exit;
}
add_action( 'init', 'ldadstore_submit_ad_for_approval' );

// 1/2 Show a counter of pending posts in the dashboard
// -> Append a tag to look for in esc_attr hooks
function ldadstore_add_counter_placeholder_to_menu_name( $text ) {
	if ( !is_admin() ) return $text;

	// Hook an event that fires just before displaying the menu, to replace our tag
	add_filter( 'attribute_escape', 'ldadstore_add_pending_counter_to_menu', 20, 2 );

	$text.= " %%LD_AD_STORE_COUNT%%";

	return $text;
}
add_filter( 'ld_ad_menu_name', 'ldadstore_add_counter_placeholder_to_menu_name' );

// 2/2 Show a counter of pending posts in the dashboard
// -> Replace the tag with the number of posts
function ldadstore_add_pending_counter_to_menu( $text ) {
	if ( substr_count($text, '%%LD_AD_STORE_COUNT%%') ) {
		// Remove our hook to prevent all esc_attr calls from using this code
		remove_filter('attribute_escape', 'ldadstore_add_pending_counter_to_menu', 20);

		$text = trim( str_replace('%%LD_AD_STORE_COUNT%%', '', $text) );

		$count = wp_count_posts( 'ld_ad',  'readable' );
		$count_pending = (int) $count->pending;

		if ( $count_pending > 0 ) {
			// we have pending, add the count
			$text = esc_attr($text) . '<span class="awaiting-mod count-' . $count_pending . '"><span class="pending-count">' . $count_pending . '</span></span>';
		}
	}

	return $text;
}


// Filter the results of the action ad loading systems, ensuring that they do not include ads that are not active.
function ldadstore_exclude_expired_and_pending_args( $args, $locations ) {
	if ( !is_array($locations) ) $locations = array($locations);

	$args['meta_query']['relation'] = 'AND';

	$args['meta_query'][] = array(
		'relation' => 'OR',
		array(
			// Ad is purchased by a user, and between the start and end date
			'relation' => 'AND',
			array(
				'key' => 'start_date_timestamp',
				'value' => time(),
				'compare' => '<=',
				'type' => 'NUMERIC',
			),
			array(
				'key' => 'end_date_timestamp',
				'value' => time(),
				'compare' => '>=',
				'type' => 'NUMERIC',
			),
		),
		array(
			// Not a customer's ad
			'relation' => 'OR',
		    array(
			    'key' => 'customer',
			    'value' => '',
			    'compare' => '==',
		    ),
		    array(
			    'key' => 'customer',
			    'value' => 'bug #23268',
			    'compare' => 'NOT EXISTS',
		    )
		),
	);

	return $args;
}
add_filter( 'ld_ad_display_args', 'ldadstore_exclude_expired_and_pending_args', 10, 2 );