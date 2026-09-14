<?php

// Exclude inline gforms scripts from Cloudflare's Rocket Loader (otherwise conditional logic fails / hides the whole form)
// https://gravitywiz.com/documentation/gravity-forms-nested-forms/
function exclude_gforms_from_rocket_loader( $attributes, $javascript ) {
    if ( strpos( $javascript, 'gform.' ) ) {
        $attributes['data-cfasync'] = 'false';
    }

    return $attributes;
}

add_filter( 'wp_inline_script_attributes', 'exclude_gforms_from_rocket_loader', 10, 2 );


function eugmag_acf_json_load_point( $paths ) {
	$paths[] = EUGMAG_GUIDES_PATH . '/acf-json';

	return $paths;
}

add_filter( 'acf/settings/load_json', 'eugmag_acf_json_load_point' );


function eugmag_acf_json_save_paths( $paths, $post ) {
	if ( ! in_array( $post['title'], array( 'Guide: Dining', 'Guide: Recreation', 'Guide: Retail', 'Guide: Service' ) ) ) {
		return $paths;
	}

	return array( EUGMAG_GUIDES_PATH . '/acf-json' );
}

add_filter( 'acf/json/save_paths', 'eugmag_acf_json_save_paths', 10, 2 );


function eugmag_acf_json_filename( $filename, $post ) {
	if ( ! in_array( $post['title'], array( 'Guide: Dining', 'Guide: Recreation', 'Guide: Retail', 'Guide: Service' ) ) ) {
		return $filename;
	}

	$filename = str_replace( array( ' ', '_', ), array( '-', '-' ), $post['title'] );

	return strtolower( $filename ) . '.json';
}

add_filter( 'acf/json/save_file_name', 'eugmag_acf_json_filename', 10, 2 );


// https://wordpress.stackexchange.com/a/208939
function eugmag_search_titles_and_meta_keys( $q ) {
	if ( $search = $q->get( '_meta_or_title' ) ) {
		add_filter( 'get_meta_sql', function( $sql ) use ( $search ) {
			global $wpdb;

			// Only run once:
			static $nr = 0;
			if ( 0 != $nr ++ ) {
				return $sql;
			}

			// find the second-to-last close parentheses, and insert metafield search immediately before it
			$found = 0;
			for ( $i = 1; $i < mb_strlen( $sql['where'] ); $i ++ ) {
				$parentheses_search = mb_substr( $sql["where"], - $i, 1 );
				if ( $parentheses_search === ")" ) {
					$found ++;
				}
				if ( $found == 2 ) {
					$insert       = ' OR (' . $wpdb->prepare( "{$wpdb->posts}.post_title like '%%%s%%'", $search ) . ')';
					$sql['where'] = substr_replace( $sql['where'], $insert, - $i - 1, 0 );
					break;
				}
			}

			return $sql;
		} );
	}
}

add_action( 'pre_get_posts', 'eugmag_search_titles_and_meta_keys' );


// allow scheduling guide (un)feature

function save_guide_post( $post_id, $post ) {
	if ( ! in_array( $post->post_type, array( 'restaurant', 'recreation', 'retailer', 'service' ) ) ) {
		return;
	}

	if ( ! get_field( 'featured', $post_id ) ) {
		//return;
	}

	// unhook the scheduled feature/unfeature actions (we'll re-add them if still valid)
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_feature', true ), 'feature_guide', array(
		$post_id,
		'feature',
	) );
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_unfeature', true ), 'feature_guide', array(
		$post_id,
		'unfeature',
	) );
	delete_post_meta( $post_id, 'scheduled_feature' );
	delete_post_meta( $post_id, 'scheduled_unfeature' );

	$field_prefix = match ( $post->post_type ) {
		'restaurant' => 'restaurant',
		'recreation' => 'recreation',
		'retailer' => 'retailer',
		'service' => 'service',
	};

	if ( $start = get_field( $field_prefix . '_feature_start', $post_id ) ) {
		$start = strtotime( $start . ' 00:00:00 ' . wp_timezone_string() );
		if ( $start < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', true );
		} else {
			update_post_meta( $post_id, 'featured', false );
			// schedule the update
			wp_schedule_single_event( $start, 'feature_guide', array(
				$post_id,
				'feature',
			) );
			update_post_meta( $post_id, 'scheduled_feature', $start );
		}
	}

	if ( $end = get_field( $field_prefix . '_feature_end', $post_id ) ) {
		$end = strtotime( $end . ' 23:59:59 ' . wp_timezone_string() );
		if ( $end < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', false );
		} else {
			// schedule the update
			wp_schedule_single_event( $end, 'feature_guide', array(
				$post_id,
				'unfeature',
			) );
			update_post_meta( $post_id, 'scheduled_unfeature', $end );
		}
	}
}

add_action( 'save_post', 'save_guide_post', 10, 2 );


function feature_guide_fun( $post_id, $action ) {
	if ( $action === 'feature' ) {
		update_post_meta( $post_id, 'featured', true );
		delete_post_meta( $post_id, 'scheduled_feature' );
	} else {
		update_post_meta( $post_id, 'featured', false );
		delete_post_meta( $post_id, 'scheduled_unfeature' );
	}
}

add_action( 'feature_guide', 'feature_guide_fun' );
