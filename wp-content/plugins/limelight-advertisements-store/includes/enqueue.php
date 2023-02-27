<?php

function ldadstore_enqueue() {
	wp_enqueue_style( 'limelight-ad-store', LDAdStore_URL . '/assets/ld-ad-store.css', array(), LDAds_VERSION );
	wp_enqueue_script( 'limelight-ad-store', LDAdStore_URL . '/assets/ld-ad-store.js', array( 'jquery' ), LDAds_VERSION );
}
add_action( 'wp_enqueue_scripts', 'ldadstore_enqueue' );

function ldadstore_enqueue_admin() {
	wp_enqueue_style( 'limelight-ad-store-admin', LDAdStore_URL . '/assets/ld-ad-store-admin.css', array(), LDAds_VERSION );
	wp_enqueue_script( 'limelight-ad-store-admin', LDAdStore_URL . '/assets/ld-ad-store-admin.js', array( 'jquery' ), LDAds_VERSION );
}
add_action( 'admin_enqueue_scripts', 'ldadstore_enqueue_admin' );