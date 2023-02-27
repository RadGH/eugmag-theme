<?php

// Gets the ad store dashboard page, creates it if not set already. It should include the shortcode [advertisement_store page="dashboard"].
function ldadstore_get_dashboard_page_id() {
	static $stored_id = null;
	if ( $stored_id !== null ) return $stored_id;

	$ad_page_id = get_option( 'ldadstore_dashboard' );

	if ( $ad_page_id && get_post_type($ad_page_id) == "page" ) {
		$stored_id = (int) $ad_page_id;
		return $stored_id;
	}

	$args = array(
		'post_type' => 'page',
		'post_title' => 'Dashboard',
		'post_status' => 'publish',
		'post_content' => '[advertisement_store page="dashboard"]',
		'post_name' => 'dashboard',
		'post_parent' => ldadstore_get_store_page_id(),
	);

	$ad_page_id = wp_insert_post( $args );

	if ( $ad_page_id ) {
		$stored_id = (int) $ad_page_id;
		update_option( 'ldadstore_dashboard', $ad_page_id, false );
		return $stored_id;
	}else{
		$stored_id = false;
		return false;
	}
}

function ldadstore_get_ad_link( $id = null, $action = null ) {
	if ( $id === null ) $id = get_the_ID();

	$url = get_permalink( ldadstore_get_dashboard_page_id() );
	
	if ( $action ) {
		$url = add_query_arg( array( 'action' => $action ), $url );
	}else{
		$url = remove_query_arg( 'action', $url );
	}

	return add_query_arg( array( 'ad' => $id ), $url );
}


function ldadstore_include_acf_head() {
	if ( !is_singular() ) return;
	if ( get_the_ID() != ldadstore_get_dashboard_page_id() ) return;
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] == "edit" ) {
		acf_form_head();
	}
}
add_action( 'wp', 'ldadstore_include_acf_head' );



// add pages to the ad store navigation bar, using a custom ACF field from the advertisements store page
function ldadstore_custom_menu( $menu ) {
	$custom_items = get_field( 'ld_ad_store_custom_pages', 'options' );
	if ( empty($custom_items) ) return $menu;


	foreach( $custom_items as $item ) {
		$post_id = $item['page'];
		if ( !$post_id ) continue;

		$title = $item['title'];
		if ( !$title ) $title = get_the_title( $post_id );

		$post = get_post($post_id);
		if ( !$post ) continue;

		$menu[$post->post_name] = array(
			'id' => $post_id,
			'title' => $title,
			'url' => get_permalink( $post_id ),
			'disabled' => false
		);
	}

	return $menu;
}
add_filter( 'ld_ad_store_menu', 'ldadstore_custom_menu' );


// Add the navigation menu to the beginning of content, for pages that appear in the ad store navigation bar
function ldadstore_custom_menu_nav( $content ) {
	global $post;
	if ( !$post || !$post->ID ) return $content;

	$custom_items = get_field( 'ld_ad_store_custom_pages', 'options' );
	if ( empty($custom_items) ) return $content;


	foreach( $custom_items as $item ) {
		$menu_item_id = $item['page'];
		if ( $menu_item_id == $post->ID ) {
			ob_start();
			include( LDAdStore_PATH . '/advertisements-store/templates/parts/navigation.php' );
			$content = ob_get_clean() . "\n\n" . $content;
			break;
		}
	}

	return $content;
}
add_filter( 'the_content', 'ldadstore_custom_menu_nav', 3 );