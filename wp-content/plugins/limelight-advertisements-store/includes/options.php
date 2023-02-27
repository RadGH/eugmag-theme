<?php

if( function_exists('acf_add_options_sub_page') ) {
	acf_add_options_sub_page(array(
		'parent' => 'edit.php?post_type=ld_ad',
		'page_title' => 'Advertisement Store',
		'menu_title' => 'Advertisement Store',
		'menu_slug' => 'ld-ad-store',
	));

	 include( LDAdStore_PATH . '/fields/location-pricing.php' );
	 include( LDAdStore_PATH . '/fields/store-settings.php' );
	 include( LDAdStore_PATH . '/fields/customer-ad-settings.php' );
}

function ldadstore_insert_into_repeater( $field ) {
	$locations = ld_ads_get_locations();

	$field['min'] = count($locations);
	$field['max'] = count($locations);

	$field['sub_fields'][0]['readonly'] = 1;

	return $field;
}
add_filter('acf/load_field/key=field_5734d07d4777e', 'ldadstore_insert_into_repeater');

function ldadstore_insert_locations( $value, $post_id, $field ) {
	$locations = ld_ads_get_locations();
	$location_keys = array_keys( $locations );

	// Converts repeater names to keys, and the price to values.
	$value_array = array();
	if ( $value ) foreach( $value as $row ) $value_array[$row['field_5734d0f04777f']] = $row['field_5734d0f647780'];

	// We want to rebuild the values in the same order that locations are defined in ld_ads_get_locations().
	$value = array();
	foreach( $location_keys as $k => $row ) {
		$name = $location_keys[$k];

		$value[$k] = array(
			'field_5734d0f04777f' => $name,
			'field_5734d0f647780' => '',
		);

		if ( isset($value_array[$name]) ) {
			$value[$k]['field_5734d0f647780'] = $value_array[$name];
		}
	}

	return $value;
}
add_filter('acf/load_value/key=field_5734d07d4777e', 'ldadstore_insert_locations', 10, 3);

// Using a custom PHP Message field in ACF, we show the customer who currently occupies an ad location.
function ldadstore_display_customer_message( $message, $field ) {
	if ( !is_admin() ) return $message;
	if ( $field['key'] != 'field_5734dbbb6a3ed' ) return $message;

	static $ad_location_slots = null;

	if ( $ad_location_slots === null ) {
		$ad_location_slots = ld_get_store_location_settings();
	}

	$screen = get_current_screen();
	if ( $screen->id != "ld_ad_page_ld-ad-store" ) return $message;

	// This is a bit confusing. ACF doesn't tell us what repeater index we are in, so we scrape it from the array.

	// A bit of a roundabout way to get the location field, by looking at the prefix for the field.
	$i = str_replace( array('acf[field_5734d07d4777e][', ']'), '', $field['prefix'] );
	$prices = get_field( 'ld_adstore_location_prices', 'options' );
	if ( empty($prices[$i]['location']) ) return '(Error #1: Try saving your changes first)';

	$location = $prices[$i]['location'];

	foreach( $ad_location_slots as $loc_slot ) {
		if ( $loc_slot['location'] != $location ) continue;

		ob_start();

		foreach( $loc_slot['slots'] as $slot ) {
			if ( $slot['available'] ) {
				printf(
					'<span class="dashicons dashicons-marker ldad-slot-indicator slot-available"><span data-title="%s &ndash; Available"></span></span>',
					esc_attr($slot['name'])
				);
			}else{
				printf(
					'<a href="%s" style="text-decoration: none; color: #A00;"><span class="dashicons dashicons-dismiss ldad-slot-indicator slot-reserved"><span data-title="%s &ndash; Occupied"></span></a>',
					esc_attr(get_edit_post_link($slot['existing_ad'])),
					esc_attr($slot['name'])
				);
			}
		}

		return ob_get_clean();
	}

	return '(Error #2: Try saving your changes first)';
}
add_filter('acf_php_message_content', 'ldadstore_display_customer_message', 10, 2);