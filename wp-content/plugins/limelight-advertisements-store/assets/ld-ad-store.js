jQuery(function() {
	init_ad_store_calculator();
});

function init_ad_store_calculator() {
	var $form = jQuery('form.ad-store-form');
	if ( $form.length < 1 ) return;

	var $ad_location = jQuery('#ad-store-location');
	var $ad_time_slot_container = $form.find('.ad-store-time-slots');

	var $location_details = $form.find('.location-details');
	var $cart_details = $form.find('.ad-store-add-to-cart');

	var $text_price = $form.find('.price-value');
	var $text_width = $form.find('.width-value');
	var $text_height = $form.find('.height-value');
	var $text_display = $form.find('.display-value');
	var $text_description = $form.find('.description-value');
	var $ad_preview = $form.find('.ad-store-preview');

	var $text_total = $form.find('.total-value');
	var $text_slot_quantity = $form.find('.slot-quantity-value');
	var $text_quantity_details = $form.find('.slot-quantity-detail');

	var selected_location = false;

	// Convert a number (int or float) to a 2-decimal value. Rounds up to nearest penny.
	var number_to_price = function( num ) {
		return parseFloat(Math.ceil(num * 100) / 100).toFixed(2);
	};

	var get_ad_location_details = function( selected_location ) {
		for ( var i in ldadstore ) {
			if ( !ldadstore.hasOwnProperty(i) ) continue;

			if ( ldadstore[i].location == selected_location ) return ldadstore[i];
		}

		return false;
	};

	var setup_location = function( location ) {
		if ( !location ) {
			$location_details.css('display', 'none');
			$ad_preview.css('display', 'none');
			$ad_time_slot_container.css('display', 'none');
			setup_add_to_cart( false );
			selected_location = false;
			return;
		}

		selected_location = location;

		if ( !location.desc ) location.desc = "(No description)";

		// Set the text for the location across several elements
		$text_price.text( number_to_price( location.price ) );
		$text_width.text( location.width );
		$text_height.text( location.height );
		$text_description.html( location.desc );

		if ( location.desktop && location.mobile ) $text_display.text( "Desktop and mobile" );
		else if ( location.desktop ) display_text = $text_display.text( "Desktop only" );
		else if ( location.mobile ) display_text = $text_display.text( "Mobile only" );
		else $text_display.text( "Error: Ad will not be displayed. Please contact the admin to let us know about this isssue. Thank you." );

		$location_details.css('display', '');

		// Resize the ad preview and show the container. Note: The resized element is a child of the preview container.
		$ad_preview
			.find('.ad-preview-frame')
				.css({
						width: location.width,
						height: location.height,
					})
				.end()
			.css({
				display: 'block'
			});

		// Populate the time slot list and make sure we hide the cart details since the locations will change
		$cart_details.css('display', 'none');
		$ad_time_slot_container.children().remove();

		for ( var i in location.slots ) {
			if ( !location.slots.hasOwnProperty(i) ) continue;

			var slot = location.slots[i];
			var $slot_field = jQuery('<div>', {class: 'time-slot-item'});

			if ( slot.available ) {
				$slot_field
					.addClass('slot-available')
					.append(
						jQuery('<input>', {
							class: 'slot-checkbox',
							type: 'checkbox',
							name: 'ldadstore[time_slot][]',
							id: 'slot-' + slot.key,
							value: slot.name
						})
					);
			}else{
				$slot_field
					.addClass('slot-unavailable')
					.append(
						jQuery('<input>', {
							class: 'slot-checkbox slot-disabled',
							type: 'checkbox',
							id: 'slot-' + slot.key
						}).prop('disabled', true)
					);
			}

			$slot_field
				.append(' ')
				.append(
					jQuery('<label>', {
						class: 'slot-label',
						for: 'slot-' + slot.key
					}).append(
						jQuery('<strong>')
							.addClass( 'slot-name')
							.html( slot.name )
					).append(
						': '
					).append(
						jQuery('<span>')
							.addClass( 'slot-desc')
							.html( slot.date_range )
					)
				);

			if ( !slot.available ) {
				$slot_field.find('label').before('(Unavailable) ');
				$slot_field.find('label').wrapInner( jQuery('<del>') );
			}

			$ad_time_slot_container.append( $slot_field );
		}

		if ( $ad_time_slot_container.children().length < 1 ) {
			$ad_time_slot_container.append( 'Error: No time slots are available' );
		}

		$ad_time_slot_container.css('display', '');

	};

	var setup_add_to_cart = function() {
		var checked = $ad_time_slot_container.find('input:checkbox:checked');

		if ( checked.length < 1 ) {
			$cart_details.css('display', 'none');
			return;
		}

		var total = number_to_price( selected_location.price ) * checked.length;

		$text_total.text( total );
		
		if ( checked.length == 1 ) {
			// One time slot selected
			$text_quantity_details.css('display', 'none');
		}else{
			// Multiple time slots are selected
			$text_slot_quantity.text( checked.length );
			$text_quantity_details.css('display', '');
		}

		$cart_details.css('display', '');
	};

	$ad_location.on('change', function(e) {
		var value = jQuery(this).val();
		var location = get_ad_location_details( value );

		if ( !value ) {
			setup_location( false );
		}else{
			setup_location( location );
		}
	});

	$ad_time_slot_container.on('change', 'input:checkbox', function(e) {
		setup_add_to_cart();
	});
}