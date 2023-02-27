<?php

function eugmag_register_retail_post_type() {

	$args = array(
		'labels'            => array(
			'name'                       => 'Retail Types',
			'singular_name'              => 'Retail Type',
			'menu_name'                  => 'Retail Types',
			'all_items'                  => 'All Retail Types',
			'new_item_name'              => 'New Retail Type',
			'add_new_item'               => 'Add New Retail Type',
			'edit_item'                  => 'Edit Retail Type',
			'update_item'                => 'Update Retail Type',
			'view_item'                  => 'View Retail Type',
			'separate_items_with_commas' => 'Separate items with commas',
			'add_or_remove_items'        => 'Add or remove items',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Retail Types',
			'search_items'               => 'Search Retail Types',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No items',
			'items_list'                 => 'Retail type list',
			'items_list_navigation'      => 'Retail type list navigation',
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
	register_taxonomy( 'retail_type', array( 'retailer' ), $args );


	$args = array(
		'label'             => 'Retailer',
		'labels'            => array(
			'name'                  => 'Retailers',
			'singular_name'         => 'Retailer',
			'menu_name'             => 'Retail Guide',
			'name_admin_bar'        => 'Retailers',
			'archives'              => 'Retailer Archives',
			'all_items'             => 'All Retailers',
			'add_new_item'          => 'Add New Retailer',
			'add_new'               => 'Add Retailer',
			'new_item'              => 'New Retailer',
			'edit_item'             => 'Edit Retailer',
			'update_item'           => 'Update Retailer',
			'view_item'             => 'View Retailer',
			'search_items'          => 'Search Retailer',
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into retailer',
			'uploaded_to_this_item' => 'Uploaded to this retailer',
			'items_list'            => 'Retailers list',
			'items_list_navigation' => 'Retailers list navigation',
			'filter_items_list'     => 'Filter retailers list',
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

	register_post_type( 'retailer', $args );
}

add_action( 'init', 'eugmag_register_retail_post_type' );
