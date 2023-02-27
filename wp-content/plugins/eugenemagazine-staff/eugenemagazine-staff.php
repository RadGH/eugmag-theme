<?php
/*
Plugin Name: Eugene Magazine Staff
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LDS_URL', plugin_dir_url( __FILE__ ) );
define( 'LDS_PATH', dirname( __FILE__ ) );


include LDS_PATH . '/enqueue.php';
include LDS_PATH . '/archive-staff.php';
include LDS_PATH . '/single-staff.php';
include LDS_PATH . '/fields/staff.php';

add_shortcode( 'll_staff', 'print_staff_archive' );
add_shortcode( 'll_staff_new', 'print_staff_archive_new' );


function get_custom_post_type_template( $content ) {
	global $post;
	
	if ( $post->post_type == 'staff' ) {
		$content = single_staff( $content );
	}
	
	return $content;
}

add_filter( 'the_content', 'get_custom_post_type_template' );


add_action( 'init', 'ld_staff_register_post_type' );

function ld_staff_register_post_type() {
	$labels = array(
		'name'               => 'Staff',
		'singular_name'      => 'Staff',
		'menu_name'          => 'Staff',
		'name_admin_bar'     => 'Staff',
		'parent_item_colon'  => 'Staff:',
		'all_items'          => 'All Staff',
		'add_new_item'       => 'Add New Staff',
		'add_new'            => 'Add Staff',
		'new_item'           => 'New Staff',
		'edit_item'          => 'Edit Staff',
		'update_item'        => 'Update Staff',
		'view_item'          => 'View Staff',
		'search_items'       => 'Search Staff',
		'not_found'          => 'Not found',
		'not_found_in_trash' => 'Not found in trash',
	);
	
	$args = array(
		'labels' => $labels,
		
		'hierarchical' => false,
		'supports'     => array(
			'title',
			'editor',
			'revisions',
			'thumbnail',
		),
		'menu_icon'    => 'dashicons-groups',
		
		'public'            => true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'        => true,
		
		'has_archive' => false,
		'rewrite'     => true,
		
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
	);
	
	// If changing the slug, be sure to refresh permalinks manually!
	$args = apply_filters( 'staff-post-type-args', $args );
	
	register_post_type( 'staff', $args );
}