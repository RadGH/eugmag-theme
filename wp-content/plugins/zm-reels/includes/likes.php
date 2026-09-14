<?php
/**
 * ZM Reels — likes (custom table + AJAX).
 *
 * @package ZM_Reels
 */

defined( 'ABSPATH' ) || exit;

define( 'ZM_REELS_LIKES_DB_VERSION', '1' );

/** @return string The likes table name. */
function zm_reels_likes_table(): string {
	global $wpdb;
	return $wpdb->prefix . 'zm_reel_likes';
}

/**
 * Create / update the likes table.
 *
 * @return void
 */
function zm_reels_install_likes_table(): void {
	global $wpdb;
	$table   = zm_reels_likes_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		reel_id BIGINT UNSIGNED NOT NULL,
		user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
		ip VARCHAR(100) NOT NULL DEFAULT '',
		created DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY reel_id (reel_id),
		KEY reel_user (reel_id, user_id),
		KEY reel_ip (reel_id, ip)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'zm_reels_likes_db', ZM_REELS_LIKES_DB_VERSION );
}

/**
 * Ensure the table exists even after a code-only update.
 *
 * @return void
 */
function zm_reels_maybe_install_likes(): void {
	if ( get_option( 'zm_reels_likes_db' ) !== ZM_REELS_LIKES_DB_VERSION ) {
		zm_reels_install_likes_table();
	}
}
add_action( 'admin_init', 'zm_reels_maybe_install_likes' );

/**
 * The visitor's IP address. Filterable (e.g. to read CF-Connecting-IP).
 *
 * @return string
 */
function zm_reels_get_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	return (string) apply_filters( 'zm_reels_client_ip', $ip );
}

/**
 * Total likes for a reel.
 *
 * @param int $reel_id Reel ID.
 * @return int
 */
function zm_reels_like_count( int $reel_id ): int {
	global $wpdb;
	$table = zm_reels_likes_table();
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE reel_id = %d", $reel_id ) ); // phpcs:ignore WordPress.DB
}

/**
 * Whether the current visitor already likes this reel.
 *
 * Logged-in visitors are matched by user ID; guests by IP address.
 *
 * @param int $reel_id Reel ID.
 * @return bool
 */
function zm_reels_user_liked( int $reel_id ): bool {
	global $wpdb;
	$table = zm_reels_likes_table();

	if ( is_user_logged_in() ) {
		$uid = get_current_user_id();
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE reel_id = %d AND user_id = %d LIMIT 1", $reel_id, $uid ) ); // phpcs:ignore WordPress.DB
	}

	$ip = zm_reels_get_ip();
	if ( '' === $ip ) {
		return false;
	}
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE reel_id = %d AND user_id = 0 AND ip = %s LIMIT 1", $reel_id, $ip ) ); // phpcs:ignore WordPress.DB
}

/**
 * Toggle the current visitor's like for a reel.
 *
 * @param int $reel_id Reel ID.
 * @return array{liked:bool, count:int}
 */
function zm_reels_toggle_like( int $reel_id ): array {
	global $wpdb;
	$table = zm_reels_likes_table();
	$uid   = get_current_user_id();
	$ip    = zm_reels_get_ip();

	if ( is_user_logged_in() ) {
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE reel_id = %d AND user_id = %d LIMIT 1", $reel_id, $uid ) ); // phpcs:ignore WordPress.DB
	} else {
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE reel_id = %d AND user_id = 0 AND ip = %s LIMIT 1", $reel_id, $ip ) ); // phpcs:ignore WordPress.DB
	}

	if ( $existing ) {
		$wpdb->delete( $table, array( 'id' => $existing ), array( '%d' ) );
		$liked = false;
	} else {
		$wpdb->insert(
			$table,
			array(
				'reel_id' => $reel_id,
				'user_id' => $uid,
				'ip'      => $ip,
				'created' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s' )
		);
		$liked = true;
	}

	return array( 'liked' => $liked, 'count' => zm_reels_like_count( $reel_id ) );
}

/**
 * AJAX: toggle a like.
 *
 * @return void
 */
function zm_reels_ajax_like(): void {
	check_ajax_referer( 'zm_reels', 'nonce' );

	if ( ! zm_reels_likes_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Likes are turned off.', 'zm-reels' ) ), 403 );
	}

	$reel_id = isset( $_POST['reel'] ) ? absint( $_POST['reel'] ) : 0;
	if ( ! $reel_id || 'reel' !== get_post_type( $reel_id ) || 'publish' !== get_post_status( $reel_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Reel not found.', 'zm-reels' ) ), 400 );
	}

	if ( ! is_user_logged_in() ) {
		if ( ! zm_reels_likes_guests() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to like.', 'zm-reels' ), 'requireAuth' => true ), 401 );
		}
		$token = isset( $_POST['turnstile'] ) ? sanitize_text_field( wp_unslash( $_POST['turnstile'] ) ) : '';
		if ( ! zm_reels_turnstile_verify( $token ) ) {
			wp_send_json_error( array( 'message' => __( 'Verification failed. Please try again.', 'zm-reels' ), 'turnstile' => true ), 403 );
		}
	}

	wp_send_json_success( zm_reels_toggle_like( $reel_id ) );
}
add_action( 'wp_ajax_zm_reels_like', 'zm_reels_ajax_like' );
add_action( 'wp_ajax_nopriv_zm_reels_like', 'zm_reels_ajax_like' );

/**
 * Remove a reel's likes when the reel is deleted.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function zm_reels_delete_likes( int $post_id ): void {
	if ( 'reel' !== get_post_type( $post_id ) ) {
		return;
	}
	global $wpdb;
	$wpdb->delete( zm_reels_likes_table(), array( 'reel_id' => $post_id ), array( '%d' ) );
}
add_action( 'before_delete_post', 'zm_reels_delete_likes' );
