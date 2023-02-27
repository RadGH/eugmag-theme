<?php
/*
Plugin Name: Eugene Magazine Gravity Forms Email Blacklist
Description: Blocks certain emails from submitting Gravity Forms entries
Author: Radley Sustaire
Author URI: https://radleysustaire.com/
Version: 1.0.2
*/

function em_gf_validation_blacklist( $validation_result ) {
	$blacklist = array(
		'sample@email.tst',
		// 'radleygh@gmail.com',
	);
	
	$email_fields = array(
		// form id => field id
		5 => 2,
		4 => 4,
		3 => 4,
		1 => 5,
		2 => 5,
	);
	
	$form = $validation_result['form'];
	
	if ( isset( $email_fields[ $form['id'] ] ) ) {
		
		$email_field_id = $email_fields[ $form['id'] ];
		$email = rgpost( 'input_' . $email_field_id );
		$email = strtolower($email);
		
		if ( in_array( $email, $blacklist ) ) {
			
			// set the form validation to false
			$validation_result['is_valid'] = false;
			
			foreach( $form['fields'] as &$field ) {
				if ( $field->id == $email_field_id ) {
					$field->failed_validation = true;
					$field->validation_message = 'Temporarily unavailable';
					$validation_result['form'] = $form;
					break;
				}
			}
			
		}
		
	}
	
	return $validation_result;
	
}
add_filter( 'gform_validation', 'em_gf_validation_blacklist', 80 );