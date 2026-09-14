<?php

function eugmag_register_guide_post_types_and_taxonomies() {
	eugmag_register_restaurant_post_type();
	//eugmag_register_restaurant_neighborhood_taxonomy();
	eugmag_register_restaurant_food_type_taxonomy();

	eugmag_register_recreation_post_type();
	eugmag_register_recreation_activity_taxonomy();

	eugmag_register_retail_post_type();
	eugmag_register_retail_type_taxonomy();

	eugmag_register_service_post_type();
	eugmag_register_service_type_taxonomy();
}

add_action( 'init', 'eugmag_register_guide_post_types_and_taxonomies' );

function eugmag_register_restaurant_neighborhood_taxonomy() {
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
}

function eugmag_register_restaurant_food_type_taxonomy() {
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
		'rewrite'           => false,
	);

	register_taxonomy( 'food_type', array( 'restaurant' ), $args );
}

function eugmag_register_restaurant_post_type() {
	$args = array(
		'label'           => 'Restaurant',
		'description'     => 'Restaurants included in the Eugene Magazine dining guide',
		'labels'          => array(
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
		'supports'        => array(
			'title',
			'revisions',
		),
		'hierarchical'    => true,
		'public'          => true,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 4,
		'menu_icon'       => 'dashicons-format-aside',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'      => true,
		'has_archive'     => false,
		'capability_type' => 'page',
	);

	register_post_type( 'restaurant', $args );
}

function eugmag_register_recreation_activity_taxonomy() {
	$args = array(
		'labels'            => array(
			'name'                  => 'Recreation Activities',
			'singular_name'         => 'Recreation Activity',
			'menu_name'             => 'Recreation Activities',
			'all_items'             => 'All Activities',
			'new_item_name'         => 'New Activity',
			'add_new_item'          => 'Add New Activity',
			'edit_item'             => 'Edit Activity',
			'update_item'           => 'Update Activity',
			'view_item'             => 'View Activity',
			'popular_items'         => 'Popular Activities',
			'search_items'          => 'Search Activities',
			'not_found'             => 'Not Found',
			'no_terms'              => 'No items',
			'items_list'            => 'Recreation activity list',
			'items_list_navigation' => 'Recreation activity list navigation',
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'rewrite'           => false,
	);

	register_taxonomy( 'activity', array( 'recreation' ), $args );
}

function eugmag_register_recreation_post_type() {
	$args = array(
		'label'           => 'Recreation Location',
		'labels'          => array(
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
		'supports'        => array(
			'title',
			'revisions',
		),
		'hierarchical'    => true,
		'public'          => true,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 4,
		'menu_icon'       => 'dashicons-format-aside',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'      => true,
		'has_archive'     => false,
		'capability_type' => 'page',
	);

	register_post_type( 'recreation', $args );
}

function eugmag_register_retail_type_taxonomy() {
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
		'rewrite'           => false,
	);

	register_taxonomy( 'retail_type', array( 'retailer' ), $args );
}

function eugmag_register_retail_post_type() {
	$args = array(
		'label'           => 'Retailer',
		'labels'          => array(
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
		'supports'        => array(
			'title',
			'revisions',
		),
		'hierarchical'    => true,
		'public'          => true,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 4,
		'menu_icon'       => 'dashicons-format-aside',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'      => true,
		'has_archive'     => false,
		'capability_type' => 'page',
	);

	register_post_type( 'retailer', $args );
}

function eugmag_register_service_type_taxonomy() {
	$args = array(
		'labels'            => array(
			'name'                  => 'Service Types',
			'singular_name'         => 'Service Type',
			'menu_name'             => 'Service Types',
			'all_items'             => 'All Types',
			'new_item_name'         => 'New Type',
			'add_new_item'          => 'Add New Type',
			'edit_item'             => 'Edit Type',
			'update_item'           => 'Update Type',
			'view_item'             => 'View Type',
			'popular_items'         => 'Popular Types',
			'search_items'          => 'Search Types',
			'not_found'             => 'Not Found',
			'no_terms'              => 'No items',
			'items_list'            => 'Service type list',
			'items_list_navigation' => 'Service type list navigation',
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'rewrite'           => false,
	);

	register_taxonomy( 'service_type', array( 'service' ), $args );
}

function eugmag_register_service_post_type() {
	$args = array(
		'label'           => 'Service Location',
		'labels'          => array(
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
		'supports'        => array(
			'title',
			'revisions',
		),
		'hierarchical'    => true,
		'public'          => true,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_position'   => 4,
		'menu_icon'       => 'dashicons-format-aside',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'      => true,
		'has_archive'     => false,
		'capability_type' => 'page',
	);

	register_post_type( 'service', $args );
}
