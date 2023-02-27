<?php

function eugmag_register_recreation_post_type() {

	$args = array(
		'labels'            => array(
			'name'                       => 'Recreation Activities',
			'singular_name'              => 'Recreation Activity',
			'menu_name'                  => 'Recreation Activities',
			'all_items'                  => 'All Activities',
			'new_item_name'              => 'New Activity',
			'add_new_item'               => 'Add New Activity',
			'edit_item'                  => 'Edit Activity',
			'update_item'                => 'Update Activity',
			'view_item'                  => 'View Activity',
			'popular_items'              => 'Popular Activities',
			'search_items'               => 'Search Activities',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No items',
			'items_list'                 => 'Recreation activity list',
			'items_list_navigation'      => 'Recreation activity list navigation',
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'rewrite'           => false
	);
	register_taxonomy( 'activity', array( 'recreation' ), $args );


	$args = array(
		'label'             => 'Recreation Location',
		'labels'            => array(
			'name'                  => 'Recreation Locations',
			'singular_name'         => 'Recreation Location',
			'menu_name'             => 'Recreation Guide',
			'name_admin_bar'        => 'Locations',
			'archives'              => 'Archives',
			'all_items'             => 'All Locations',
			'add_new_item'          => 'Add New Recreation Location',
			'add_new'               => 'Add Location',
			'new_item'              => 'New Location',
			'edit_item'             => 'Edit Location',
			'update_item'           => 'Update Location',
			'view_item'             => 'View Location',
			'search_items'          => 'Search Locations',
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into location',
			'uploaded_to_this_item' => 'Uploaded to this location',
			'items_list'            => 'Locations',
			'items_list_navigation' => 'Location navigation',
			'filter_items_list'     => 'Filter locations',
		),
		'supports'          => array(
			'title',
			'revisions',
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'menu_position'     => 5,
		'menu_icon'         => 'dashicons-format-aside',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'        => true,
		'has_archive'       => false,
		'capability_type'   => 'page',
	);

	register_post_type( 'recreation', $args );
}

add_action( 'init', 'eugmag_register_recreation_post_type' );
