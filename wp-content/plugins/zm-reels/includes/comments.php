<?php
/**
 * ZM Reels — comments & replies (native WP comments + AJAX).
 *
 * @package ZM_Reels
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a JSON-friendly representation of a comment for the viewer.
 *
 * @param WP_Comment $comment Comment object.
 * @return array<string, mixed>
 */
function zm_reels_comment_json( $comment ): array {
	$text = get_comment_text( $comment );
	$text = apply_filters( 'comment_text', $text, $comment, array() );

	return array(
		'id'      => (int) $comment->comment_ID,
		'parent'  => (int) $comment->comment_parent,
		'author'  => $comment->comment_author ? $comment->comment_author : __( 'Anonymous', 'zm-reels' ),
		'date'    => sprintf(
			/* translators: %s: human time difference, e.g. "3 hours". */
			__( '%s ago', 'zm-reels' ),
			human_time_diff( strtotime( $comment->comment_date_gmt ), time() )
		),
		'content' => $text,
		'avatar'  => get_option( 'show_avatars' ) ? get_avatar_url( $comment, array( 'size' => 48 ) ) : '',
	);
}

/**
 * AJAX: return approved comments for a reel (flat; the client builds the tree).
 *
 * @return void
 */
function zm_reels_ajax_get_comments(): void {
	check_ajax_referer( 'zm_reels', 'nonce' );

	$reel_id = isset( $_POST['reel'] ) ? absint( $_POST['reel'] ) : 0;
	if ( ! $reel_id || 'reel' !== get_post_type( $reel_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Reel not found.', 'zm-reels' ) ), 400 );
	}

	if ( ! zm_reels_comments_enabled() ) {
		wp_send_json_success( array( 'comments' => array(), 'count' => 0 ) );
	}

	$comments = get_comments(
		array(
			'post_id' => $reel_id,
			'status'  => 'approve',
			'type'    => 'comment',
			'order'   => 'ASC',
		)
	);

	$out = array();
	foreach ( $comments as $comment ) {
		$out[] = zm_reels_comment_json( $comment );
	}

	wp_send_json_success( array( 'comments' => $out, 'count' => count( $out ) ) );
}
add_action( 'wp_ajax_zm_reels_get_comments', 'zm_reels_ajax_get_comments' );
add_action( 'wp_ajax_nopriv_zm_reels_get_comments', 'zm_reels_ajax_get_comments' );

/**
 * Honeypot: our form never sends a website/URL, so any URL value is a bot.
 *
 * @return bool
 */
function zm_reels_honeypot_triggered(): bool {
	foreach ( array( 'website', 'url', 'comment_author_url' ) as $key ) {
		if ( isset( $_POST[ $key ] ) && '' !== trim( (string) wp_unslash( $_POST[ $key ] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked by caller.
			return true;
		}
	}
	return false;
}

/**
 * Force a comment to spam (used only around a honeypot-triggered submission).
 *
 * @return string
 */
function zm_reels_force_spam(): string {
	return 'spam';
}

/**
 * AJAX: post a comment or reply.
 *
 * Runs through wp_new_comment(), so WordPress moderation, disallowed-key
 * checks, and any anti-spam plugin hooked to preprocess_comment all apply.
 * Guests must additionally pass Turnstile (when configured).
 *
 * @return void
 */
function zm_reels_ajax_post_comment(): void {
	check_ajax_referer( 'zm_reels', 'nonce' );

	if ( ! zm_reels_comments_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Comments are turned off.', 'zm-reels' ) ), 403 );
	}

	$reel_id = isset( $_POST['reel'] ) ? absint( $_POST['reel'] ) : 0;
	if ( ! $reel_id || 'reel' !== get_post_type( $reel_id ) || 'publish' !== get_post_status( $reel_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Reel not found.', 'zm-reels' ) ), 400 );
	}

	$logged_in = is_user_logged_in();

	if ( ! $logged_in ) {
		if ( ! zm_reels_comments_guests() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to comment.', 'zm-reels' ), 'requireAuth' => true ), 401 );
		}
		$token = isset( $_POST['turnstile'] ) ? sanitize_text_field( wp_unslash( $_POST['turnstile'] ) ) : '';
		if ( ! zm_reels_turnstile_verify( $token ) ) {
			wp_send_json_error( array( 'message' => __( 'Verification failed. Please try again.', 'zm-reels' ), 'turnstile' => true ), 403 );
		}
	}

	$content = isset( $_POST['content'] ) ? trim( (string) wp_unslash( $_POST['content'] ) ) : '';
	if ( '' === $content ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a comment.', 'zm-reels' ) ), 400 );
	}

	$parent = isset( $_POST['parent'] ) ? absint( $_POST['parent'] ) : 0;
	if ( $parent ) {
		$parent_comment = get_comment( $parent );
		if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $reel_id ) {
			$parent = 0;
		}
	}

	$commentdata = array(
		'comment_post_ID' => $reel_id,
		'comment_parent'  => $parent,
		'comment_content' => $content,
	);

	if ( $logged_in ) {
		$user                                = wp_get_current_user();
		$commentdata['user_id']              = $user->ID;
		$commentdata['comment_author']       = $user->display_name;
		$commentdata['comment_author_email'] = $user->user_email;
		$commentdata['comment_author_url']   = $user->user_url;
	} else {
		$name  = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( '' === $name ) {
			wp_send_json_error( array( 'message' => __( 'Please enter your name.', 'zm-reels' ) ), 400 );
		}
		if ( get_option( 'require_name_email' ) && '' === $email ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email.', 'zm-reels' ) ), 400 );
		}
		$commentdata['comment_author']       = $name;
		$commentdata['comment_author_email'] = $email;
	}

	// Honeypot: a URL/website value can only come from a bot (our form has no such
	// field). Record the attempt as spam and give the bot the same neutral reply a
	// real pending comment gets, so it can't detect the trap.
	$honeypot = zm_reels_honeypot_triggered();
	if ( $honeypot ) {
		add_filter( 'pre_comment_approved', 'zm_reels_force_spam', 999 );
	}

	// wp_new_comment() runs preprocess_comment (anti-spam plugins), moderation,
	// disallowed-key checks, and records the author IP.
	$result = wp_new_comment( wp_slash( $commentdata ), true );

	if ( $honeypot ) {
		remove_filter( 'pre_comment_approved', 'zm_reels_force_spam', 999 );
		wp_send_json_success( array( 'status' => 'pending', 'message' => __( 'Thanks! Your comment is awaiting moderation.', 'zm-reels' ) ) );
	}

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
	}

	$comment = get_comment( $result );
	if ( ! $comment ) {
		wp_send_json_error( array( 'message' => __( 'Could not post your comment.', 'zm-reels' ) ), 500 );
	}

	if ( '1' === (string) $comment->comment_approved ) {
		wp_send_json_success( array( 'status' => 'approved', 'comment' => zm_reels_comment_json( $comment ) ) );
	}

	if ( 'spam' === $comment->comment_approved || 'trash' === $comment->comment_approved ) {
		wp_send_json_error( array( 'message' => __( 'Your comment could not be accepted.', 'zm-reels' ) ), 403 );
	}

	wp_send_json_success( array( 'status' => 'pending', 'message' => __( 'Thanks! Your comment is awaiting moderation.', 'zm-reels' ) ) );
}
add_action( 'wp_ajax_zm_reels_post_comment', 'zm_reels_ajax_post_comment' );
add_action( 'wp_ajax_nopriv_zm_reels_post_comment', 'zm_reels_ajax_post_comment' );
