<?php
/*
Plugin Name: Eugene Magazine Featured
Version:     1.0
Description: Adds featured functionality to Dining (Restaurants), Recreation, Retail, and Service guides.
Author:      Radley Sustaire
Author URI:  https://radleysustaire.com/
License:     Copyright 2022 Radley Sustaire
*/

class EM_Featured {
	public $post_types = array(
		'recreation',
		'service',
		'retailer',
		'restaurant',
	);
	
	function __construct() {
		foreach( $this->post_types as $post_type ) {
			// Register and display column
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'register_columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'display_columns' ), 10, 2 );
			
			// Add custom "subsubsub" view such as: All (10) | Published (10) | Featured (5)
			add_filter( "views_edit-{$post_type}", array( $this, 'register_custom_view_link' ), 40 );
		}
		
		// Sort the custom columns registered above
		add_action( 'pre_get_posts', array( $this, 'filter_posts_list' ) );
		
		// Toggle featured on stars
		add_action( 'wp_ajax_em_make_featured', array( $this, 'toggle_featured_ajax' ) );
		
		// Enqueue custom js/css
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}
	
	function enqueue_admin() {
		if ( !is_admin() ) return;
		
		$post_type = isset($_GET['post_type']) ? stripslashes($_GET['post_type']) : false;
		if ( !in_array($post_type, $this->post_types) ) return;
		
		$url = untrailingslashit( plugin_dir_url( __FILE__ ) );
		$path = dirname(__FILE__);
		
		$file = '/assets/em-featured-admin.css';
		$version = filemtime($path . $file);
		wp_enqueue_style( 'em-featured', $url . $file, array(), $version );
		
		$file = '/assets/em-featured-admin.js';
		$version = filemtime($path . $file);
		wp_enqueue_script( 'em-featured', $url . $file, array('jquery'), $version );
	}
	
	/**
	 * Register the custom "Featured" column for the dashboard posts screen
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	function register_columns( $columns ) {
		$columns = array_merge(
			array_slice( $columns, 0, 1, true ),
			array( 'featured' => 'Featured' ),
			array_slice( $columns, 1, null, true )
		);
		
		return $columns;
	}
	
	/**
	 * Display the custom "Featured" column content
	 *
	 * @param $column
	 * @param $post_id
	 */
	function display_columns( $column, $post_id ) {
		if ( $column != 'featured' ) return;
		
		$post_type = isset($_GET['post_type']) ? stripslashes($_GET['post_type']) : false;
		if ( !in_array( $post_type, $this->post_types ) ) return;
		
		$url = admin_url( 'admin-ajax.php', 'relative' );
		$url = add_query_arg(array('action' => 'em_make_featured', 'post_type' => $post_type, 'post_id' => $post_id), $url);
		
		if ( $this->is_featured($post_id) ) {
			$url = add_query_arg(array('featured' => 0), $url);
			$icon = '<span class="dashicons dashicons-star-filled"></span>';
		}else{
			$url = add_query_arg(array('featured' => 1), $url);
			$icon = '<span class="dashicons dashicons-star-empty"></span>';
		}
		
		echo sprintf(
			'<a href="#" data-href="%s" class="em_make_featured">%s</a>',
			esc_attr($url),
			$icon
		);
	}
	
	function filter_posts_list( $query ) {
		if ( !is_admin() ) return;
		
		$orderby = $query->get('orderby');
		// default: "menu_order title"
		
		if ( $orderby === 'featured' ) {
			$query->set('meta_key', 'featured');
			$query->set('meta_value', 1);
		}
	}
	
	/**
	 * Return true if given post is featured
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	function is_featured( $post_id ) {
		$is_featured = intval( get_post_meta( $post_id, 'featured', true ) );
		
		return $is_featured === 1;
	}
	
	/**
	 * Mark as featured or remove featured for a post using ajax, by clicking the star on the dashboard list
	 *
	 * @return void
	 */
	function toggle_featured_ajax() {
		$make_featured = isset($_GET['featured']) ? stripslashes($_GET['featured']) : false;
		$make_featured = intval($make_featured) === 1;
		
		$post_type = isset($_GET['post_type']) ? stripslashes($_GET['post_type']) : false;
		$post_id = isset($_GET['post_id']) ? stripslashes($_GET['post_id']) : false;
		
		$result = array(
			'message'       => 'no message provided',
			'make_featured' => $make_featured,
			'post_type'     => $post_type,
			'post_id'       => $post_id,
		);
		
		try {
			if ( !in_array( $post_type, $this->post_types ) ) {
				throw new Exception( 'Invalid post type "'. esc_html($post_type) .'".' );
			}
			
			if ( get_post_type($post_id) != $post_type ) {
				throw new Exception( 'Post ID "'. esc_attr($post_id) .'" has incorrect post type "'. esc_html($post_type) .'".' );
			}
			
			if ( $make_featured ) {
				update_post_meta( $post_id, 'featured', 1 );
				$result['message'] = 'Added featured';
			}else{
				update_post_meta( $post_id, 'featured', 0 );
				$result['message'] = 'Removed featured';
			}
			
			wp_send_json_success( $result );
			
		} catch ( Exception $e ) {
			$result['message'] = $e->getMessage();
			wp_send_json_error( $result );
		}
		
		wp_die();
		exit;
	}
	
	/**
	 * Adds a link to the top of the posts list such as "Featured" below:
	 * All (174) | Published (172) | Trash (2) | Featured (43)
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	function register_custom_view_link( $views ) {
		if ( !is_admin() ) return $views;
		
		$orderby = isset($_GET['orderby']) ? stripslashes($_GET['orderby']) : '';
		$is_current_view = ($orderby == 'featured');
		
		$current_filter = current_filter();
		$post_type = str_replace('views_edit-', '',$current_filter);
		
		$name = 'Featured';
		$meta_key = 'featured';
		
		// count
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => $meta_key,
					'value' => 1,
					'compare' => '=',
				),
			),
			'posts_per_page' => 1,
		);
		
		$q = new WP_Query($args);
		
		$count = $q->found_posts;
		
		// create link
		$class = $is_current_view ? 'current' : '';
		
		$link = add_query_arg(array(
			'post_type' => $post_type,
			'paged' => 1,
			'orderby' => 'featured',
		), admin_url( 'edit.php' ) );
		
		$views[ $meta_key ] = sprintf(
			'<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
			esc_attr($link),
			esc_attr($class),
			esc_html($name),
			$count
		);
		
		return $views;
	}
}

global $EM_Featured;
$EM_Featured = new EM_Featured();
