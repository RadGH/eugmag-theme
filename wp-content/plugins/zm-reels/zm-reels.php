<?php
/**
 * Plugin Name: ZM: Reels
 * Description: A "Reels" post type (Facebook Reels / YouTube Shorts / TikTok style) with a full-screen video viewer at /reels/ and /reels/{slug}/. Videos can be uploaded or pulled from YouTube/Vimeo links, with likes, threaded comments, business info + action links, and Cloudflare Turnstile. Uses ACF for custom fields and the classic editor (no Gutenberg).
 * Version: 1.3.1
 * Author: RadGH
 * License: GPL-2.0-or-later
 * Text Domain: zm-reels
 *
 * @package ZM_Reels
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

define( 'ZM_REELS_VERSION', '1.3.0' );
define( 'ZM_REELS_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/turnstile.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/likes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/comments.php';

// ------------------------------------------------------------
// Post type + taxonomy
// ------------------------------------------------------------

/**
 * Register the Reel post type. Public slug is /reels/ for both archive and single.
 *
 * @return void
 */
function zm_reels_register_cpt(): void {
	$labels = array(
		'name'               => __( 'Reels', 'zm-reels' ),
		'singular_name'      => __( 'Reel', 'zm-reels' ),
		'add_new'            => __( 'Add New', 'zm-reels' ),
		'add_new_item'       => __( 'Add New Reel', 'zm-reels' ),
		'edit_item'          => __( 'Edit Reel', 'zm-reels' ),
		'new_item'           => __( 'New Reel', 'zm-reels' ),
		'view_item'          => __( 'View Reel', 'zm-reels' ),
		'view_items'         => __( 'View Reels', 'zm-reels' ),
		'search_items'       => __( 'Search Reels', 'zm-reels' ),
		'not_found'          => __( 'No reels found.', 'zm-reels' ),
		'not_found_in_trash' => __( 'No reels found in Trash.', 'zm-reels' ),
		'all_items'          => __( 'All Reels', 'zm-reels' ),
		'menu_name'          => __( 'Reels', 'zm-reels' ),
	);

	register_post_type(
		'reel',
		array(
			'labels'       => $labels,
			'public'       => true,
			'has_archive'  => 'reels',
			'rewrite'      => array(
				'slug'       => 'reels',
				'with_front' => false,
			),
			'menu_icon'    => 'dashicons-video-alt3',
			'menu_position' => 20,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'comments' ),
			// Disable REST so the block editor is off (classic editor only).
			'show_in_rest' => false,
			'taxonomies'   => array( 'reel_category' ),
		)
	);
}
add_action( 'init', 'zm_reels_register_cpt' );

/**
 * Register the Reel Category taxonomy.
 *
 * @return void
 */
function zm_reels_register_taxonomy(): void {
	$labels = array(
		'name'          => __( 'Reel Categories', 'zm-reels' ),
		'singular_name' => __( 'Reel Category', 'zm-reels' ),
		'search_items'  => __( 'Search Categories', 'zm-reels' ),
		'all_items'     => __( 'All Categories', 'zm-reels' ),
		'edit_item'     => __( 'Edit Category', 'zm-reels' ),
		'update_item'   => __( 'Update Category', 'zm-reels' ),
		'add_new_item'  => __( 'Add New Category', 'zm-reels' ),
		'new_item_name' => __( 'New Category Name', 'zm-reels' ),
		'menu_name'     => __( 'Categories', 'zm-reels' ),
	);

	register_taxonomy(
		'reel_category',
		'reel',
		array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'rewrite'           => array( 'slug' => 'reel-category' ),
		)
	);
}
add_action( 'init', 'zm_reels_register_taxonomy' );

/**
 * Force the classic editor for reels (belt-and-suspenders with show_in_rest=false).
 *
 * @param bool   $use       Whether to use the block editor.
 * @param string $post_type Current post type.
 * @return bool
 */
function zm_reels_disable_gutenberg( bool $use, string $post_type ): bool {
	return 'reel' === $post_type ? false : $use;
}
add_filter( 'use_block_editor_for_post_type', 'zm_reels_disable_gutenberg', 10, 2 );

// ------------------------------------------------------------
// ACF fields
// ------------------------------------------------------------

/**
 * Register the Reel custom fields via ACF.
 *
 * @return void
 */
function zm_reels_register_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_zm_reels',
			'title'                 => __( 'Reel Details', 'zm-reels' ),
			'fields'                => array(
				array(
					'key'          => 'field_zm_reel_video',
					'label'        => __( 'Video File', 'zm-reels' ),
					'name'         => 'reel_video',
					'type'         => 'file',
					'return_format' => 'array',
					'library'      => 'all',
					'mime_types'   => 'mp4,webm,mov,m4v',
					'instructions' => __( 'Upload the reel video (mp4 recommended, portrait 9:16 looks best). Leave blank if you set an external URL below.', 'zm-reels' ),
				),
				array(
					'key'          => 'field_zm_reel_video_url',
					'label'        => __( 'Video URL (YouTube / Vimeo / direct)', 'zm-reels' ),
					'name'         => 'reel_video_url',
					'type'         => 'url',
					'instructions' => __( 'Optional. Paste a YouTube, Vimeo, or direct video (.mp4) URL. If set, this is used instead of the uploaded file above.', 'zm-reels' ),
				),
				array(
					'key'           => 'field_zm_reel_date',
					'label'         => __( 'Date Released', 'zm-reels' ),
					'name'          => 'reel_date_released',
					'type'          => 'date_picker',
					'return_format' => 'Y-m-d',
					'display_format' => 'F j, Y',
					'first_day'     => 0,
					'instructions'  => __( 'Optional. Defaults to the publish date. Reels are ordered newest first by this date.', 'zm-reels' ),
				),
				array(
					'key'          => 'field_zm_reel_business_type',
					'label'        => __( 'Business Type', 'zm-reels' ),
					'name'         => 'business_type',
					'type'         => 'text',
					'instructions' => __( 'e.g. Restaurant, Magazine, Clinic. Shown as "Type: Name" in the sidebar.', 'zm-reels' ),
				),
				array(
					'key'   => 'field_zm_reel_business_name',
					'label' => __( 'Business Name', 'zm-reels' ),
					'name'  => 'business_name',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_zm_reel_business_url',
					'label'        => __( 'Business URL', 'zm-reels' ),
					'name'         => 'business_url',
					'type'         => 'url',
					'instructions' => __( 'Optional. If set, the Business Name links to this URL.', 'zm-reels' ),
				),
				array(
					'key'          => 'field_zm_reel_actions',
					'label'        => __( 'Actions', 'zm-reels' ),
					'name'         => 'reel_actions',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add Action', 'zm-reels' ),
					'instructions' => __( 'Buttons shown under the business info, e.g. "Visit our website" or "Buy this magazine". External URLs open in a new tab.', 'zm-reels' ),
					'sub_fields'   => array(
						array(
							'key'   => 'field_zm_reel_action_text',
							'label' => __( 'Text', 'zm-reels' ),
							'name'  => 'action_text',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_zm_reel_action_url',
							'label' => __( 'URL', 'zm-reels' ),
							'name'  => 'action_url',
							'type'  => 'url',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'reel',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'hide_on_screen'        => array( 'discussion', 'trackbacks' ),
			'description'           => '',
		)
	);
}
add_action( 'acf/init', 'zm_reels_register_fields' );

/**
 * Admin notice if ACF is missing (fields will not appear).
 *
 * @return void
 */
function zm_reels_acf_notice(): void {
	if ( function_exists( 'acf_add_local_field_group' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>ZM: Reels</strong> &mdash; ' . esc_html__( 'Advanced Custom Fields is not active, so the reel video/date fields will not appear. Activate ACF to manage reels.', 'zm-reels' ) . '</p></div>';
}
add_action( 'admin_notices', 'zm_reels_acf_notice' );

// ------------------------------------------------------------
// Data
// ------------------------------------------------------------

/**
 * Work out how to play a video URL: a YouTube/Vimeo embed, or a direct file.
 *
 * @param string $url Raw URL from the External Video URL field.
 * @return array{type:string, src:string} type is 'embed' or 'file'.
 */
function zm_reels_parse_video_url( string $url ): array {
	$url = trim( $url );
	if ( '' === $url ) {
		return array( 'type' => '', 'src' => '' );
	}

	// YouTube: watch, youtu.be, shorts, embed.
	if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|shorts/|embed/|v/)|youtu\.be/)([\w-]{11})~i', $url, $m ) ) {
		$id  = $m[1];
		$src = 'https://www.youtube.com/embed/' . $id . '?autoplay=1&mute=1&loop=1&playlist=' . $id . '&playsinline=1&rel=0&modestbranding=1';
		return array( 'type' => 'embed', 'src' => $src );
	}

	// Vimeo: vimeo.com/ID, player.vimeo.com/video/ID, optional /HASH for unlisted.
	if ( preg_match( '~vimeo\.com/(?:video/)?(\d+)(?:/(\w+))?~i', $url, $m ) ) {
		$id   = $m[1];
		$hash = isset( $m[2] ) ? $m[2] : '';
		$src  = 'https://player.vimeo.com/video/' . $id . '?autoplay=1&muted=1&loop=1&playsinline=1' . ( $hash ? '&h=' . $hash : '' );
		return array( 'type' => 'embed', 'src' => $src );
	}

	// Anything else is treated as a direct video file (e.g. .mp4 on a CDN).
	return array( 'type' => 'file', 'src' => $url );
}

/**
 * Whether a URL points to another site (used to flag external action links).
 *
 * @param string $url URL.
 * @return bool
 */
function zm_reels_is_external_url( string $url ): bool {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! $host ) {
		return false; // relative URL = internal.
	}
	$home = wp_parse_url( home_url(), PHP_URL_HOST );
	return strtolower( (string) $host ) !== strtolower( (string) $home );
}

/**
 * Build the ordered list of reels (newest released first) for the viewer.
 *
 * @return array<int, array<string, mixed>>
 */
function zm_reels_get_data(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'reel',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		)
	);

	$items = array();

	global $post;
	foreach ( $query->posts as $post ) {
		setup_postdata( $post );

		// Video: external URL (YouTube/Vimeo/direct) wins, else the uploaded file.
		$url = (string) get_field( 'reel_video_url' );
		if ( '' !== trim( $url ) ) {
			$parsed = zm_reels_parse_video_url( $url );
			$type   = $parsed['type'];
			$src    = $parsed['src'];
		} else {
			$type = '';
			$src  = '';
			$file = get_field( 'reel_video' );
			if ( is_array( $file ) ) {
				$src = (string) ( $file['url'] ?? '' );
			} elseif ( is_string( $file ) ) {
				$src = $file;
			}
			if ( '' !== $src ) {
				$type = 'file';
			}
		}

		// Date released (Y-m-d) drives ordering + display; fall back to publish date.
		$date_raw = (string) get_field( 'reel_date_released' );
		if ( '' !== $date_raw ) {
			$ts        = strtotime( $date_raw );
			$date_disp = date_i18n( get_option( 'date_format' ), $ts );
		} else {
			$ts        = (int) get_post_time( 'U', true, $post );
			$date_disp = get_the_date( '', $post );
		}

		$terms = get_the_terms( $post->ID, 'reel_category' );
		$cat   = ( ! is_wp_error( $terms ) && $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';

		$poster = get_the_post_thumbnail_url( $post->ID, 'large' );

		// Business info + action links.
		$actions_raw = get_field( 'reel_actions' );
		$actions     = array();
		if ( is_array( $actions_raw ) ) {
			foreach ( $actions_raw as $row ) {
				$txt = isset( $row['action_text'] ) ? trim( (string) $row['action_text'] ) : '';
				$url = isset( $row['action_url'] ) ? trim( (string) $row['action_url'] ) : '';
				if ( '' === $txt || '' === $url ) {
					continue;
				}
				$actions[] = array(
					'text'     => $txt,
					'url'      => $url,
					'external' => zm_reels_is_external_url( $url ),
				);
			}
		}

		$likes_on    = zm_reels_likes_enabled();
		$comments_on = zm_reels_comments_enabled();

		$items[] = array(
			'id'           => (int) $post->ID,
			'slug'         => $post->post_name,
			'title'        => get_the_title( $post ),
			'description'  => apply_filters( 'the_content', $post->post_content ),
			'category'     => $cat,
			'date'         => $date_disp,
			'type'         => $type,
			'src'          => $src,
			'poster'       => $poster ? $poster : '',
			'businessType' => (string) get_field( 'business_type' ),
			'businessName' => (string) get_field( 'business_name' ),
			'businessUrl'  => (string) get_field( 'business_url' ),
			'businessUrlExternal' => zm_reels_is_external_url( (string) get_field( 'business_url' ) ),
			'actions'      => $actions,
			'likeCount'    => $likes_on ? zm_reels_like_count( (int) $post->ID ) : 0,
			'liked'        => $likes_on ? zm_reels_user_liked( (int) $post->ID ) : false,
			'commentCount' => $comments_on ? (int) get_comments_number( $post->ID ) : 0,
			'_ts'          => $ts,
		);
	}
	wp_reset_postdata();

	// Newest released first.
	usort(
		$items,
		static function ( $a, $b ) {
			return $b['_ts'] <=> $a['_ts'];
		}
	);

	foreach ( $items as &$item ) {
		unset( $item['_ts'] );
	}
	unset( $item );

	$cache = $items;
	return $cache;
}

// ------------------------------------------------------------
// Front-end viewer
// ------------------------------------------------------------

/**
 * Whether the current request should show the reels viewer.
 *
 * @return bool
 */
function zm_reels_is_viewer(): bool {
	return is_singular( 'reel' ) || is_post_type_archive( 'reel' );
}

/**
 * Load the full-screen viewer template for reel archive + single.
 *
 * @param string $template Current template path.
 * @return string
 */
function zm_reels_template( string $template ): string {
	if ( zm_reels_is_viewer() ) {
		$custom = plugin_dir_path( __FILE__ ) . 'templates/reels-viewer.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'zm_reels_template' );

/**
 * Enqueue the viewer assets and hand the reel data to JS.
 *
 * @return void
 */
function zm_reels_enqueue(): void {
	if ( ! zm_reels_is_viewer() ) {
		return;
	}

	$reels = zm_reels_get_data();

	// Where the viewer should start.
	$start = 0;
	if ( is_singular( 'reel' ) ) {
		$current = get_queried_object_id();
		foreach ( $reels as $i => $reel ) {
			if ( $reel['id'] === $current ) {
				$start = $i;
				break;
			}
		}
	}

	wp_enqueue_style( 'zm-reels', plugins_url( 'assets/reels.css', __FILE__ ), array(), ZM_REELS_VERSION );
	wp_enqueue_script( 'zm-reels', plugins_url( 'assets/reels.js', __FILE__ ), array(), ZM_REELS_VERSION, true );

	// Cloudflare Turnstile (only when configured and a social feature is on).
	$turnstile_on = zm_reels_turnstile_enabled() && ( zm_reels_likes_enabled() || zm_reels_comments_enabled() );
	if ( $turnstile_on ) {
		wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion,WordPress.WP.EnqueuedResourceParameters.NotInFooter
	}

	$return_url = is_singular( 'reel' ) ? get_permalink( get_queried_object_id() ) : get_post_type_archive_link( 'reel' );

	wp_localize_script(
		'zm-reels',
		'ZM_REELS',
		array(
			'reels'  => $reels,
			'start'  => $start,
			'home'   => home_url( '/' ),
			'base'   => trailingslashit( get_post_type_archive_link( 'reel' ) ),
			'social' => array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'zm_reels' ),
				'loggedIn'    => is_user_logged_in(),
				'userName'    => is_user_logged_in() ? wp_get_current_user()->display_name : '',
				'likes'       => array(
					'enabled' => zm_reels_likes_enabled(),
					'guests'  => zm_reels_likes_guests(),
				),
				'comments'    => array(
					'enabled'      => zm_reels_comments_enabled(),
					'guests'       => zm_reels_comments_guests(),
					'requireEmail' => (bool) get_option( 'require_name_email' ),
				),
				'loginUrl'    => wp_login_url( $return_url ),
				'canRegister' => (bool) get_option( 'users_can_register' ),
				'registerUrl' => get_option( 'users_can_register' ) ? wp_registration_url() : '',
				'turnstile'   => array(
					'enabled' => $turnstile_on,
					'siteKey' => zm_reels_turnstile_site_key(),
				),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'zm_reels_enqueue' );

// ------------------------------------------------------------
// Admin column: Date Released
// ------------------------------------------------------------

/**
 * Add a "Date Released" column to the reels list table.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function zm_reels_admin_columns( array $columns ): array {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['reel_released'] = __( 'Date Released', 'zm-reels' );
		}
	}
	return $new;
}
add_filter( 'manage_reel_posts_columns', 'zm_reels_admin_columns' );

/**
 * Render the Date Released column value.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function zm_reels_admin_column_value( string $column, int $post_id ): void {
	if ( 'reel_released' !== $column ) {
		return;
	}
	$date = get_field( 'reel_date_released', $post_id );
	echo $date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) $date ) ) ) : '&mdash;';
}
add_action( 'manage_reel_posts_custom_column', 'zm_reels_admin_column_value', 10, 2 );

// ------------------------------------------------------------
// Activation / deactivation (rewrite rules)
// ------------------------------------------------------------

/**
 * Register CPT + taxonomy then flush rewrite rules on activation.
 *
 * @return void
 */
function zm_reels_activate(): void {
	zm_reels_register_cpt();
	zm_reels_register_taxonomy();
	zm_reels_install_likes_table();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'zm_reels_activate' );

/**
 * Flush rewrite rules on deactivation.
 *
 * @return void
 */
function zm_reels_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'zm_reels_deactivate' );
