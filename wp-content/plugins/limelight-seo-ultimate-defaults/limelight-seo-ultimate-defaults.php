<?php
/*
Plugin Name: Limelight - SEO Ultimate Defaults
Version: 1.0.1
Plugin URI: http://www.limelightdept.com/
Description: Provides sensible defaults for post titles and descriptions, if they aren't entered into the SEO metabox.
Author: Radley Sustaire
Author URI: mailto:radleygh@gmail.com

Copyright 2015 Limelight Department (radley@limelightdept.com)
For use by Limelight Department and affiliates, do not distribute
*/

if( !defined( 'ABSPATH' ) ) exit;

function ldseo_filter_su_metadata( $value, $object_id, $meta_key, $single ) {
	if ( substr($meta_key, 0, 4) === '_su_' ) {

		remove_filter( 'get_post_metadata', 'ldseo_filter_su_metadata', 8 );

		$new_value = false;

		switch( $meta_key ) {
			case '_su_description':
				$new_value = ldseo_default_su_description( $object_id );
				break;

			case '_su_title':
				$new_value = ldseo_default_su_title( $object_id );
				break;
		}

		add_filter( 'get_post_metadata', 'ldseo_filter_su_metadata', 8, 4 );

		if ( $new_value ) return $new_value;
		else return $value;
	}

	return $value;
}
add_filter( 'get_post_metadata', 'ldseo_filter_su_metadata', 8, 4 );


function ldseo_default_su_description( $object_id ) {
	$actual_value = get_post_meta( $object_id, '_su_description', true );

	if ( !$actual_value ) {
		$p = get_post($object_id);
		return wp_trim_words(wp_strip_all_tags( $p->post_content ), 20, '&hellip;' );
	}

	return false;
}

function ldseo_default_su_title( $object_id ) {
	$actual_value = get_post_meta( $object_id, '_su_title', true );

	if ( !$actual_value ) {
		$p = get_post($object_id);
		return wp_trim_words(wp_strip_all_tags( $p->post_title ), 8, '&hellip;' );
	}

	return false;
}