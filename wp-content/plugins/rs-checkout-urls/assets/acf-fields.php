<?php

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	
	acf_add_local_field_group( array(
		'key' => 'group_67294ef3488b3',
		'title' => 'RS Checkout URLs - Settings',
		'fields' => array(
			array(
				'key' => 'field_67294ef3ca1e1',
				'label' => 'Checkout URLs',
				'name' => 'checkout_urls',
				'aria-label' => '',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'layout' => 'block',
				'pagination' => 0,
				'min' => 0,
				'max' => 0,
				'collapsed' => '',
				'button_label' => 'Add Row',
				'rows_per_page' => 20,
				'sub_fields' => array(
					array(
						'key' => 'field_67294f3125d6f',
						'label' => 'Slug',
						'name' => 'slug',
						'aria-label' => '',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'maxlength' => '',
						'allow_in_bindings' => 0,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'parent_repeater' => 'field_67294ef3ca1e1',
					),
					array(
						'key' => 'field_67294f5125d71',
						'label' => 'Products',
						'name' => 'products',
						'aria-label' => '',
						'type' => 'repeater',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'table',
						'pagination' => 0,
						'min' => 0,
						'max' => 0,
						'collapsed' => '',
						'button_label' => 'Add Row',
						'rows_per_page' => 20,
						'sub_fields' => array(
							array(
								'key' => 'field_67294f7025d72',
								'label' => 'Product',
								'name' => 'product_id',
								'aria-label' => '',
								'type' => 'post_object',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'post_type' => array(
									0 => 'product',
								),
								'post_status' => '',
								'taxonomy' => '',
								'return_format' => 'id',
								'multiple' => 0,
								'allow_null' => 0,
								'allow_in_bindings' => 0,
								'bidirectional' => 0,
								'ui' => 1,
								'bidirectional_target' => array(
								),
								'parent_repeater' => 'field_67294f5125d71',
							),
							array(
								'key' => 'field_67294f8925d73',
								'label' => 'Action',
								'name' => 'action',
								'aria-label' => '',
								'type' => 'select',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_67294f7025d72',
											'operator' => '!=empty',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array(
									'add' => 'Add to cart',
									'remove' => 'Remove from cart',
								),
								'default_value' => 'add',
								'return_format' => 'value',
								'multiple' => 0,
								'allow_null' => 0,
								'allow_in_bindings' => 0,
								'ui' => 0,
								'ajax' => 0,
								'placeholder' => '',
								'parent_repeater' => 'field_67294f5125d71',
							),
							array(
								'key' => 'field_67294fc225d74',
								'label' => 'Quantity',
								'name' => 'quantity',
								'aria-label' => '',
								'type' => 'number',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_67294f8925d73',
											'operator' => '==',
											'value' => 'add',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 1,
								'min' => '',
								'max' => '',
								'allow_in_bindings' => 0,
								'placeholder' => '',
								'step' => '',
								'prepend' => '',
								'append' => '',
								'parent_repeater' => 'field_67294f5125d71',
							),
						),
						'parent_repeater' => 'field_67294ef3ca1e1',
					),
					array(
						'key' => 'field_672950958ea15',
						'label' => 'Coupons',
						'name' => 'coupons',
						'aria-label' => '',
						'type' => 'repeater',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'table',
						'min' => 0,
						'max' => 0,
						'collapsed' => '',
						'button_label' => 'Add Row',
						'rows_per_page' => 20,
						'sub_fields' => array(
							array(
								'key' => 'field_672950958ea1a',
								'label' => 'Coupon Code',
								'name' => 'coupon_code',
								'aria-label' => '',
								'type' => 'text',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => '',
								'maxlength' => '',
								'allow_in_bindings' => 0,
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'parent_repeater' => 'field_672950958ea15',
							),
							array(
								'key' => 'field_672950958ea1b',
								'label' => 'Action',
								'name' => 'action',
								'aria-label' => '',
								'type' => 'select',
								'instructions' => '',
								'required' => 0,
								'conditional_logic' => array(
									array(
										array(
											'field' => 'field_672950958ea1a',
											'operator' => '!=empty',
										),
									),
								),
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'choices' => array(
									'add' => 'Add to cart',
									'remove' => 'Remove from cart',
								),
								'default_value' => 'add',
								'return_format' => 'value',
								'multiple' => 0,
								'allow_null' => 0,
								'allow_in_bindings' => 0,
								'ui' => 0,
								'ajax' => 0,
								'placeholder' => '',
								'parent_repeater' => 'field_672950958ea15',
							),
						),
						'parent_repeater' => 'field_67294ef3ca1e1',
					),
					array(
						'key' => 'field_67294ffc25d76',
						'label' => 'Redirect',
						'name' => 'redirect',
						'aria-label' => '',
						'type' => 'select',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array(
							'checkout' => 'Checkout (Default)',
							'cart' => 'Cart',
							'custom' => 'Custom URL',
						),
						'default_value' => false,
						'return_format' => 'value',
						'multiple' => 0,
						'allow_null' => 0,
						'allow_in_bindings' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'parent_repeater' => 'field_67294ef3ca1e1',
					),
					array(
						'key' => 'field_6729501b25d77',
						'label' => 'Redirect (Custom URL)',
						'name' => 'redirect_custom_url',
						'aria-label' => '',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => array(
							array(
								array(
									'field' => 'field_67294ffc25d76',
									'operator' => '==',
									'value' => 'custom',
								),
							),
						),
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'maxlength' => '',
						'allow_in_bindings' => 0,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'parent_repeater' => 'field_67294ef3ca1e1',
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'rscu-settings',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => 'Options Page: WooCommerce > Checkout URLs',
		'show_in_rest' => 0,
	) );
} );