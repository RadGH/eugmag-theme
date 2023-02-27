<?php
/*
This is the form used to buy an ad. You select a location and multiple time periods, then add it to the cart.
*/

global $woocommerce;

$ad_location_settings = ld_get_store_location_settings();

// DO not sell ads with $0.00 cost.
foreach( $ad_location_settings as $k => $loc ) {
	if ( empty($loc['price']) || (float) $loc['price'] < 1 ) {
		unset($ad_location_settings[$k]);
		continue;
	}
}

?>
<form action="<?php echo esc_attr($woocommerce->cart->get_cart_url()); ?>" class="ad-store-form">
	<script>
		var ldadstore = <?php echo json_encode($ad_location_settings); ?>;
	</script>
	
	<?php include( LDAdStore_PATH . '/advertisements-store/templates/parts/navigation.php' ); ?>

	<div class="ad-store">
		<div class="location-select">
			<div class="ad-store-label"><label for="ad-store-location">In which space would you like to advertise?</label></div>
			<select name="ldadstore[location]" id="ad-store-location">
				<option value="">&ndash; Select &ndash;</option>
				<?php
				foreach( $ad_location_settings as $k=> $loc ) {
					if ( empty($loc['price']) || (float) $loc['price'] < 1 ) {
						unset($ad_location_settings[$k]);
						continue;
					}

					printf(
						'<option value="%s">%s</option>',
						esc_attr($loc['location']),
						esc_html($loc['location'])
					);
				}
				?>
			</select>
		</div>

		<div class="location-details" style="display:none">

			<div class="location-detail-table">
				<div class="location-price">
					<div class="ad-store-price"><strong>Price:</strong> $<span class="price-value"></span></div>
				</div>

				<div class="location-sizing">
					<div class="sizing-width"><strong>Width:</strong> <span class="width-value"></span>px</div>
					<div class="sizing-height"><strong>Height:</strong> <span class="height-value"></span>px</div>
				</div>

				<div class="location-devices">
					<div class="devices-display"><strong>Appears on:</strong> <span class="display-value"></span></div>
				</div>

				<div class="location-description">
					<div class="description-display"><strong>Description:</strong> <span class="description-value"></span></div>
				</div>
			</div>


			<div class="time-slots">
				<div class="ad-store-label">Select the time period(s) to run your ad:</div>

				<div class="ad-store-time-slots">

				</div>
			</div>

		</div>

		<div class="ad-store-add-to-cart" style="display: none;">
			<div class="ad-store-label">Purchase Total</div>

			<div class="cart-total">
				<div class="slot-quantity-detail" style="display: none;">
					<span>Location Price:</span> $<span class="price-value"></span>
					<br><span>Time Slots</span> &times; <span class="slot-quantity-value"></span>
				</div>
				<span class="ad-total-price"><strong>Total Price:</strong> $<span class="total-value"></span></span>
			</div>

			<div class="cart-button">
				<input type="hidden" name="ldadstore[nonce]" value="<?php echo esc_attr(wp_create_nonce('ldadstore-add-to-cart')); ?>">
				<input type="submit" value="Add to cart" class="button button-white">
			</div>
		</div>

		<div class="ad-store-preview" style="display: none;">
			<div class="ad-store-label">Ad Size Preview:</div>

			<div class="ad-preview-frame">
				<div class="ad-preview-text"><span class="ad-preview-title">Your Ad Here</span>
					<br><span class="width-value"></span>&times;<span class="height-value"></span></div>
			</div>

			<p class="ad-store-disclaimer"><strong>Important:</strong> You will submit your ad material <em>after</em> purchasing the ad space. Your advertisements must be manually approved before being displayed on the website. If your advertisement is not approved, you can re-submit your advertisement or receive a refund.</p>
		</div>
	</div>

</form>