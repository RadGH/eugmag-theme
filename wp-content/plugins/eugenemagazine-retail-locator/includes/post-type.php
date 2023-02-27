<?php

function as_register_retail_store_post_type() {

	$args = array(
		'label'             => 'Retail Store',
		'description'       => 'Eugene Magazine\'s retail stores',
		'labels'            => array(
			'name'                  => 'Retail Stores',
			'singular_name'         => 'Retail Store',
			'menu_name'             => 'Retail Stores',
			'name_admin_bar'        => 'Retail Stores',
			'archives'              => 'Retail Store Archives',
			'all_items'             => 'All Retail Stores',
			'add_new_item'          => 'Add New Retail Store',
			'add_new'               => 'Add New',
			'new_item'              => 'New Retail Store',
			'edit_item'             => 'Edit Retail Store',
			'update_item'           => 'Update Retail Store',
			'view_item'             => 'View Retail Store',
			'search_items'          => 'Search Retail Store',
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into Retail Store',
			'uploaded_to_this_item' => 'Uploaded to this Retail Store',
			'items_list'            => 'Retail Stores list',
			'items_list_navigation' => 'Retail Stores list navigation',
			'filter_items_list'     => 'Filter Retail Stores list',
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
		'menu_icon'         => 'dashicons-admin-page',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'        => true,
		'has_archive'       => false,
		'capability_type'   => 'page',
	);

	register_post_type( 'retail_store', $args );
}

add_action( 'init', 'as_register_retail_store_post_type' );
