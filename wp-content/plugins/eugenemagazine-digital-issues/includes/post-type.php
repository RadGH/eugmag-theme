<?php

/**
 * Register the Digital Issues post type
 *
 * @return void
 */
function di_register_post_type() {
	$args = array(
		'label'             => 'Digital Issues',
		'description'       => 'Display digital issues',
		'labels'            => array(
			'name'                  => 'Digital Issues',
			'singular_name'         => 'Digital Issue',
			'menu_name'             => 'Digital Issues',
			'name_admin_bar'        => 'Digital Issues',
			'archives'              => 'Digital Issue Archives',
			'all_items'             => 'All Digital Issues',
			'add_new_item'          => 'Add New Digital Issue',
			'add_new'               => 'Add Digital Issue',
			'new_item'              => 'New Digital Issue',
			'edit_item'             => 'Edit Digital Issue',
			'update_item'           => 'Update Digital Issue',
			'view_item'             => 'View Digital Issue',
			'search_items'          => 'Search Digital Issue',
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => /** @lang text */ 'Insert into Digital Issue',
			'uploaded_to_this_item' => 'Uploaded to this Digital Issue',
			'items_list'            => 'Digital Issues list',
			'items_list_navigation' => 'Digital Issues list navigation',
			'filter_items_list'     => 'Filter Digital Issues list',
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
		'menu_icon'         => 'dashicons-book',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => false,
		'can_export'        => true,
		'capability_type'   => 'page',
		
		'rewrite'           => array( 'slug' => 'digital-issues' ),
		'has_archive'       => 'digital-issues',
	);
	
	register_post_type( 'digital-issue', $args );
	
	if ( function_exists( 'acf_add_options_sub_page' ) ) {
		$args = array(
			'page_title'  => 'Digital Issues Settings',
			'menu_title'  => 'Settings',
			'parent_slug' => 'edit.php?post_type=digital-issue',
			'post_id'     => 'digital_issues', // instead of get_field( 'name', 'options' ) use get_field( 'name', 'digital_issues' )
			'autoload'    => false,
		);
		acf_add_options_sub_page( $args );
	}
}
add_action( 'init', 'di_register_post_type', 1 );


/**
 * Add thumbnail for cover images (original 816 x 1059)
 *
 * @return void
 */
function di_add_image_sizes() {
	add_image_size( 'di-cover-large', 816, 1060, true );
	add_image_size( 'di-cover', 408, 530, true );
	add_image_size( 'di-cover-thumbnail', 204, 265, true );
}
add_action( 'after_setup_theme', 'di_add_image_sizes' );

/**
 * Show a link to the digital issues page as an admin notice
 *
 * @return void
 */
function di_show_admin_link() {
	if ( is_digital_issues_admin_page() ) {
		?>
		<div class="notice notice-success">
			<p>The digital issues archive can be found at <a href="<?php echo site_url('/digital-issues/'); ?>">/digital-issues/</a></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'di_show_admin_link' );



function di_redirect_single_digital_issues() {
	if ( ! is_singular('digital-issue') ) return;
	
	$magazine_url = get_field( 'magazine_url' );
	
	if ( $magazine_url ) {
		wp_redirect( $magazine_url );
		exit;
	}else{
		wp_die('This digital issue does not yet have a link to view online.', 'Digital issue - Invalid link' );
		exit;
	}
}
add_action( 'template_redirect', 'di_redirect_single_digital_issues' );