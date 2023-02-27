<?php
add_action( 'after_setup_theme', 'theme_setup_settings' );

function theme_setup_settings() {
	add_image_size('thumbnail-uncropped', 340, 250, false);
	add_image_size('rssfeed-landscape', 560, 280, false);

	add_image_size('mobile-alt', 600, 415, true);

	// Enable RSS feed channels in the document header
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress handle the title tag
	add_theme_support( 'title-tag' );

	// Enable featured images for posts and pages
	add_theme_support( 'post-thumbnails' );

	// Enable HTML5 for the specified templates
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
}

function ld_get_attachment_mobile( $attachment_id ) {
	// Check for a mobile version of the photo. We need to make sure it matches the size above.
	if ( $m = wp_get_attachment_image_src( $attachment_id, 'mobile-alt' ) ) {
		if ( $m[1] == 600 && $m[2] == 415 ) {
			return $m;
		}
	}
	return false;
}