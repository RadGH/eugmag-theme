<?php
/**
 * ZM Reels — settings page (ACF options page under Reels > Settings).
 *
 * @package ZM_Reels
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the ACF options sub-page under the Reels menu.
 *
 * @return void
 */
function zm_reels_register_settings_page(): void {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Reels Settings', 'zm-reels' ),
			'menu_title'  => __( 'Settings', 'zm-reels' ),
			'parent_slug' => 'edit.php?post_type=reel',
			'menu_slug'   => 'zm-reels-settings',
			'capability'  => 'manage_options',
			'update_button' => __( 'Save Settings', 'zm-reels' ),
		)
	);
}
add_action( 'acf/init', 'zm_reels_register_settings_page' );

/**
 * Register the settings field group.
 *
 * @return void
 */
function zm_reels_register_settings_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_zm_reels_settings',
			'title'    => __( 'Reels Settings', 'zm-reels' ),
			'fields'   => array(
				array(
					'key'   => 'field_zm_reels_likes_msg',
					'label' => __( 'Likes', 'zm-reels' ),
					'type'  => 'message',
					'message' => __( 'Let visitors like reels.', 'zm-reels' ),
				),
				array(
					'key'           => 'field_zm_reels_likes_enabled',
					'label'         => __( 'Enable Likes', 'zm-reels' ),
					'name'          => 'likes_enabled',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'           => 'field_zm_reels_likes_guests',
					'label'         => __( 'Allow guests to like', 'zm-reels' ),
					'name'          => 'likes_allow_guests',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
					'instructions'  => __( 'If off, visitors must log in (or register) to like.', 'zm-reels' ),
					'conditional_logic' => array(
						array( array( 'field' => 'field_zm_reels_likes_enabled', 'operator' => '==', 'value' => '1' ) ),
					),
				),
				array(
					'key'   => 'field_zm_reels_comments_msg',
					'label' => __( 'Comments', 'zm-reels' ),
					'type'  => 'message',
					'message' => __( 'Let visitors comment and reply on reels. Comments use WordPress\'s built-in moderation and any anti-spam plugin you have active.', 'zm-reels' ),
				),
				array(
					'key'           => 'field_zm_reels_comments_enabled',
					'label'         => __( 'Enable Comments', 'zm-reels' ),
					'name'          => 'comments_enabled',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'           => 'field_zm_reels_comments_guests',
					'label'         => __( 'Allow guests to comment', 'zm-reels' ),
					'name'          => 'comments_allow_guests',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
					'instructions'  => __( 'If off, visitors must log in (or register) to comment.', 'zm-reels' ),
					'conditional_logic' => array(
						array( array( 'field' => 'field_zm_reels_comments_enabled', 'operator' => '==', 'value' => '1' ) ),
					),
				),
				array(
					'key'   => 'field_zm_reels_ts_msg',
					'label' => __( 'Cloudflare Turnstile', 'zm-reels' ),
					'type'  => 'message',
					'message' => __( 'Optional bot check for guest likes and comments. Get keys at dash.cloudflare.com → Turnstile. When both keys are filled in, guests must pass Turnstile before their like or comment is accepted. Logged-in users are not challenged.', 'zm-reels' ),
				),
				array(
					'key'   => 'field_zm_reels_ts_site',
					'label' => __( 'Turnstile Site Key', 'zm-reels' ),
					'name'  => 'turnstile_site_key',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_zm_reels_ts_secret',
					'label' => __( 'Turnstile Secret Key', 'zm-reels' ),
					'name'  => 'turnstile_secret_key',
					'type'  => 'text',
					'instructions' => __( 'Kept server-side; never exposed to the browser.', 'zm-reels' ),
				),
			),
			'location' => array(
				array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'zm-reels-settings' ) ),
			),
		)
	);
}
add_action( 'acf/init', 'zm_reels_register_settings_fields' );

/**
 * Get a settings value with a fallback (works even if ACF is inactive).
 *
 * @param string $name    Field name.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function zm_reels_opt( string $name, $default = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$value = get_field( $name, 'option' );
	return ( null === $value || '' === $value ) ? $default : $value;
}

/** @return bool */
function zm_reels_likes_enabled(): bool {
	return (bool) zm_reels_opt( 'likes_enabled', true );
}

/** @return bool */
function zm_reels_likes_guests(): bool {
	return (bool) zm_reels_opt( 'likes_allow_guests', true );
}

/** @return bool */
function zm_reels_comments_enabled(): bool {
	return (bool) zm_reels_opt( 'comments_enabled', true );
}

/** @return bool */
function zm_reels_comments_guests(): bool {
	return (bool) zm_reels_opt( 'comments_allow_guests', false );
}
