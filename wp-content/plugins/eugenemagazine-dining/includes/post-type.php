<?php

function as_register_restaurant_post_type() {
	$args = array(
		'labels'            => array(
			'name'                       => 'Neighborhoods',
			'singular_name'              => 'Neighborhood',
			'menu_name'                  => 'Neighborhoods',
			'all_items'                  => 'All Neighborhoods',
			'new_item_name'              => 'New Neighborhood',
			'add_new_item'               => 'Add New Neighborhood',
			'edit_item'                  => 'Edit Neighborhood',
			'update_item'                => 'Update Neighborhood',
			'view_item'                  => 'View Neighborhood',
			'separate_items_with_commas' => 'Separate items with commas',
			'add_or_remove_items'        => 'Add or remove items',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Neighborhoods',
			'search_items'               => 'Search Neighborhoods',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No items',
			'items_list'                 => 'Neighborhood list',
			'items_list_navigation'      => 'Neighborhood list navigation',
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'rewrite'           => false,
	);
	register_taxonomy( 'neighborhood', array( 'restaurant' ), $args );


	$args = array(
		'labels'            => array(
			'name'                       => 'Types of Food',
			'singular_name'              => 'Type of Food',
			'menu_name'                  => 'Types of Food',
			'all_items'                  => 'All Types of Food',
			'new_item_name'              => 'New Type of Food',
			'add_new_item'               => 'Add New Type of Food',
			'edit_item'                  => 'Edit Type of Food',
			'update_item'                => 'Update Type of Food',
			'view_item'                  => 'View Type of Food',
			'separate_items_with_commas' => 'Separate items with commas',
			'add_or_remove_items'        => 'Add or remove items',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Types of Food',
			'search_items'               => 'Search Types of Food',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No items',
			'items_list'                 => 'Food type list',
			'items_list_navigation'      => 'Food type list navigation',
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
	register_taxonomy( 'food_type', array( 'restaurant' ), $args );


	$args = array(
		'label'             => 'Restaurant',
		'description'       => 'Restaurants included in the Eugene Magazine dining guide',
		'labels'            => array(
			'name'                  => 'Restaurants',
			'singular_name'         => 'Restaurant',
			'menu_name'             => 'Dining Guide',
			'name_admin_bar'        => 'Restaurants',
			'archives'              => 'Restaurant Archives',
			'all_items'             => 'All Restaurants',
			'add_new_item'          => 'Add New Restaurant',
			'add_new'               => 'Add Restaurant',
			'new_item'              => 'New Restaurant',
			'edit_item'             => 'Edit Restaurant',
			'update_item'           => 'Update Restaurant',
			'view_item'             => 'View Restaurant',
			'search_items'          => 'Search Restaurant',
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into restaurant',
			'uploaded_to_this_item' => 'Uploaded to this restaurant',
			'items_list'            => 'Restaurants list',
			'items_list_navigation' => 'Restaurants list navigation',
			'filter_items_list'     => 'Filter restaurants list',
		),
		'supports'          => array(
			'title',
			'revisions',
		),
		//'taxonomies'            => array( 'neighborhood', 'food_type' ),
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

	register_post_type( 'restaurant', $args );
}

add_action( 'init', 'as_register_restaurant_post_type' );

/*
function remove_taxonomy_metaboxes() {
	remove_meta_box( 'neighborhooddiv', 'restaurant', 'side' );
	remove_meta_box( 'food_typediv', 'restaurant', 'side' );
}
add_action( 'admin_menu' , 'remove_taxonomy_metaboxes' );
*/
