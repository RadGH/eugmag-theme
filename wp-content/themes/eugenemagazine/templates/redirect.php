<?php
/*
Template Name: Redirect
*/

global $post;

if ( post_password_required( $post ) ) {
	// hook to remove "Protected:" from the page title
	add_filter( 'protected_title_format', function() { return '%s'; } );
	get_template_part('index');
	return;
}

$redirect_url = get_field( 'redirect_url', get_the_ID() );
if ( ! $redirect_url ) {
	echo 'Invalid redirect specified for post #'. get_the_ID();
	exit;
}

wp_redirect( $redirect_url );
exit;