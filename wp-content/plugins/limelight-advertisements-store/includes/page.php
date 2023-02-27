<?php
// Gets the ad store page, or creates it if not set already. It should include the shortcode [advertisement_store].
function ldadstore_get_store_page_id() {
	static $stored_id = null;
	if ( $stored_id !== null ) return $stored_id;

	$ad_page_id = get_option( 'ldadstore_page' );

	if ( $ad_page_id && get_post_type($ad_page_id) == "page" ) {
		$stored_id = (int) $ad_page_id;
		return $stored_id;
	}

	$args = array(
		'post_type' => 'page',
		'post_title' => 'Advertisement Store',
		'post_name' => 'advertise',
		'post_status' => 'publish',
		'post_content' => '[advertisement_store]',
	);

	$ad_page_id = wp_insert_post( $args );

	if ( $ad_page_id ) {
		$stored_id = (int) $ad_page_id;
		update_option( 'ldadstore_page', $ad_page_id, false );
		return $stored_id;
	}else{
		$stored_id = false;
		return false;
	}
}

// When editing the advertisement product, show a message explaining what it is for.
function ldadstore_explain_ad_page() {
	$screen = get_current_screen();
	if ( $screen->id != "page"  ) return;

	global $post;
	if ( $post->id != ldadstore_get_store_page_id() ) return;
	
	if ( !strstr($post->post_content, '') ) {
		?>
		<div class="error">
			<p><strong>Advertisement Page: Shortcode Missing</strong></p>

			<p>This page requires the <code>[advertisement_store]</code> shortcode to be placed in the body, or else you may run into issues.</p>
		</div>
		<?php
	}else{
		?>
		<div class="updated">
			<p><strong>Advertisement Page:</strong></p>
	
			<p>This page is used to display the advertisement purchasing form. You may rename this page or modify the content, but you must keep the <code>[advertisement_store]</code> shortcode.</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ldadstore_explain_ad_page' );

// When trying to delete the advertisement product, stop them.
function ldadstore_redirect_on_deleting_ad_page( $post_id ) {
	if ( $post_id != ldadstore_get_store_page_id() ) return;
	if ( get_post_type($post_id) != 'page' ) return;

	$args = array(
		'ID' => $post_id,
		'post_status' => 'publish',
	);
	wp_update_post( $args );

	$url = get_edit_post_link($post_id);

	wp_die(
		'<h2>Cannot delete page</h2>'.
		'<p>This page is required to make the plugin Limelight - Advertisements Store function properly. You may not delete it.</p>'.
		'<p><a href="'. esc_attr($url) .'" class="button button-secondary">&laquo; Go Back</a></p>'
	);
	exit;
}
add_action( 'wp_trash_post', 'ldadstore_redirect_on_deleting_ad_page', 1 );
add_action( 'before_delete_post', 'ldadstore_redirect_on_deleting_ad_page', 1 );