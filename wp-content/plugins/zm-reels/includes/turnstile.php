<?php
/**
 * ZM Reels — Cloudflare Turnstile verification.
 *
 * @package ZM_Reels
 */

defined( 'ABSPATH' ) || exit;

/** @return string */
function zm_reels_turnstile_site_key(): string {
	return trim( (string) zm_reels_opt( 'turnstile_site_key', '' ) );
}

/** @return string */
function zm_reels_turnstile_secret(): string {
	return trim( (string) zm_reels_opt( 'turnstile_secret_key', '' ) );
}

/**
 * Turnstile is active only when both keys are present.
 *
 * @return bool
 */
function zm_reels_turnstile_enabled(): bool {
	return '' !== zm_reels_turnstile_site_key() && '' !== zm_reels_turnstile_secret();
}

/**
 * Verify a Turnstile token server-side.
 *
 * Returns true when Turnstile is not configured (nothing to check), otherwise
 * validates the token against Cloudflare's siteverify endpoint.
 *
 * @param string $token The cf-turnstile-response token from the client.
 * @return bool
 */
function zm_reels_turnstile_verify( string $token ): bool {
	if ( ! zm_reels_turnstile_enabled() ) {
		return true;
	}

	$token = trim( $token );
	if ( '' === $token ) {
		return false;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => zm_reels_turnstile_secret(),
				'response' => $token,
				'remoteip' => zm_reels_get_ip(),
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $data['success'] );
}
