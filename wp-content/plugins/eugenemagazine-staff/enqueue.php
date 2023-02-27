<?php

function ld_staff_enqueue_scripts() {
	if ( get_the_ID() == 8622 ) {
		$v = filemtime( LDS_PATH . '/includes/staff-new.css' );
		wp_enqueue_style( 'ld-staff', LDS_URL . '/includes/staff-new.css', array(), $v );
	} elseif ( get_the_ID() == 124 ) {
		$v = filemtime( LDS_PATH . '/includes/staff.css' );
		wp_enqueue_style( 'ld-staff', LDS_URL . '/includes/staff.css', array(), $v );
	}
}

add_action( 'wp_enqueue_scripts', 'ld_staff_enqueue_scripts' );