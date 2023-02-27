<?php

function eugmag_register_service_post_type() {

	$args = array(
		'labels'            => array(
			'name'                       => 'Service Types',
			'singular_name'              => 'Service Type',
			'menu_name'                  => 'Service Types',
			'all_items'                  => 'All Types',
			'new_item_name'              => 'New Type',
			'add_new_item'               => 'Add New Type',
			'edit_item'                  => 'Edit Type',
			'update_item'                => 'Update Type',
			'view_item'                  => 'View Type',
			'popular_items'              => 'Popular Types',
			'search_items'               => 'Search Types',
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No items',
			'items_list'                 => 'Service type list',
			'items_list_navigation'      => 'Service type list navigation',
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
	register_taxonomy( 'service_type', array( 'service' ), $args );


	$args = array(
		'label'             => 'Service Location',
		'labels'            => array(
			'name'                  => 'Service Locations',
			'singular_name'         => 'Service Location',
			'menu_name'             => 'Service Guide',
			'name_admin_bar'        => 'Service Locations',
			'archives'              => 'Service Archives',
			'all_items'             => 'All Locations',
			'add_new_item'          => 'Add New Service Location',
			'add_new'               => 'Add Location',
			'new_item'              => 'New Service Location',
			'edit_item'             => 'Edit Service Location',
			'update_item'           => 'Update Service Location',
			'view_item'             => 'View Service Location',
			'search_items'          => 'Search Service Locations',
			'insert_into_item'      => 'Insert into service location',
			'uploaded_to_this_item' => 'Uploaded to this service',
			'items_list'            => 'Service locations list',
			'items_list_navigation' => 'Service locations list navigation',
			'filter_items_list'     => 'Filter service locations list',
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

	register_post_type( 'service', $args );
}

add_action( 'init', 'eugmag_register_service_post_type' );
