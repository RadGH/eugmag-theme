<?php
if( function_exists('acf_add_local_field_group') ):
	acf_add_local_field_group(array (
		'key' => 'group_5734cf9652545',
		'title' => 'Location Pricing',
		'fields' => array (
			array (
				'key' => 'field_5734d07d4777e',
				'label' => 'Location Prices',
				'name' => 'ldadstore_location_prices',
				'type' => 'repeater',
				'instructions' => 'Use a price of $0.00 if the ad location is not purchasable.',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => '',
				'min' => 11,
				'max' => 11,
				'layout' => 'table',
				'button_label' => 'Add Location Pricing',
				'sub_fields' => array (
					array (
						'key' => 'field_5734d0f04777f',
						'label' => 'Location',
						'name' => 'location',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 1,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5734d0f647780',
						'label' => 'Price',
						'name' => 'price',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '$',
						'append' => '',
						'min' => 0,
						'max' => '',
						'step' => '0.01',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5734dbbb6a3ed',
						'label' => 'Availability',
						'name' => 'availability',
						'type' => 'php_message',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'php_message' => '',
						'new_lines' => 'wpautop',
						'esc_html' => 0,
						'message' => '',
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'ld-ad-store',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));
endif;