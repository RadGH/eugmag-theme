<?php
/* 
This template is used when editing an ad on the front end. It uses logic to show different information based on the ad status. If the ad is a draft, you will get an ACF form, for example.
*/

global $woocommerce;

// Count the number of ads that have been purchased by this user.
// Note: We just need the "found_posts" value.
$args = array(
	'post_type' => 'ld_ad',
	'post_author' => get_current_user_id(),
	'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
	'posts_per_page' => 1,
	'post__in' => array( (int) $_REQUEST['ad'] ),
);

$the_ad = new WP_Query($args);

include( LDAdStore_PATH . '/advertisements-store/templates/parts/navigation.php' );
?>
<div class="ad-edit-back">
	<a href="<?php the_permalink( ldadstore_get_dashboard_page_id() ); ?>">&laquo; Return to ad dashboard</a>
</div>
<?php

if ( !$the_ad ) {
	?>
	<div class="ad-item ad-single action-edit not-found">
		<h2>Advertisement not found</h2>
		<p>The ad you have requested could not be found.</p>
	</div>
	<?php
}else{
	while( $the_ad->have_posts() ): $the_ad->the_post();

		// The two variables below transfer over to the includes in the switch. Do not remove/change them.
		$locations = get_field( 'ad-locations', get_the_ID() );
		$time_slot_name = get_field( 'time_slot_name', get_the_ID() );

		$status = get_post_status();
		$save_url = ldadstore_get_ad_link( get_the_ID(), 'save' );
		
		foreach( $locations as $location ) {
			?>
			<div class="ad-item ad-single action-edit">
				<h2>Editing: <?php echo $time_slot_name; ?> &ndash; <?php echo $location; ?></h2>
	
				<?php
				if ( $status == "draft" ):
					// Display controls to publish an ad
					$validation = ldadstore_validate_ad( $location, get_the_ID() );

					if ( is_wp_error($validation) ) {
						// Show an error, unless it's because no type was specified. That one should be obvious.
						if ($validation->get_error_code() != "no_type"  ) {
							?>
							<div class="error">
								<p><strong>Error: <?php echo $validation->get_error_code(); ?></strong></p>
								<?php echo wpautop( $validation->get_error_message() ); ?>
							</div>
							<?php
						}
					}else{
						// Allow this ad to be submitted for approval
						$approval_url = ldadstore_get_ad_approval_submit_link( get_the_ID(), $location );
						?>
						<div class="updated">
							<p><strong>Ready for approval!</strong></p>
							<p>Click the button below to submit this ad for approval. Within a few business days, an administrator will review your advertisement. If accepted, your advertisement will be displayed on our website during the time period you selected during checkout.</p>
							<p><a href="<?php echo esc_attr($approval_url); ?>" class="button ad-post-submit" onclick="if ( !confirm('Are you sure you want to submit your ad for approval?') ) return false;">Submit for Approval</a></p>
						</div>
						<?php
					}
				endif;

				if ( $status == "pending" ):
					?>
					<div class="updated">
						<p><strong>Pending approval!</strong></p>
						<p>Your ad has been submitted for approval. You are no longer to make changes to your advertisement.</p>
					</div>
					<?php
				endif;

				if ( $status == "draft" ) {
					// Display the edit form, to change the values of the ad
					acf_form(array(
						'post_id'	    => get_the_ID(),
						'post_title'	=> false,
						'field_groups'  => array('group_563bc732283a8'),
					));
				}

				if ( $status == "publish" ) {
					$start = get_field( 'start_date_timestamp' );
					$end = get_field( 'end_date_timestamp' );

					$state = "scheduled";
					if ( $end < time() ) $state = "expired";
					elseif ( $start < time() ) $state = "active";
					?>
					<div class="ad-publish-content">
						<?php if ( $state == "active" ) { ?>
							<p>Your ad has been approved and is currently <strong>active</strong>. It will expire on <?php echo date( ad_date_format(), $end ); ?>.</p>
						<?php }else if ( $state == "scheduled" ) { ?>
							<p>Your ad has been approved, and scheduled for <strong><?php echo date( "F j, Y", $start ); ?></strong> to <strong><?php echo date( ad_date_format(), $end ); ?></strong>.</p>
						<?php }else if ( $state == "expired" ) { ?>
							<p>This ad expired on <strong><?php echo date( ad_date_format(), $end ); ?></strong>.</p>
						<?php } ?>
					</div>

					<div class="ad-publish-preview">
						<?php
						$type = get_field('ad-type');

						switch( $type ) {
							case 'external_image':
							case 'image':
								if ( $type == "external_image" ) {
									$image_url = get_field( 'ad-external-image' );
								}else{
									$image_id = get_field( 'ad-image' );
									$img = wp_get_attachment_image_src( $image_id, 'full' );
									$image_url = $img ? $img[0] : false;
								}

								$url = get_field( 'ad-url' );
								?>
								<div class="ad-preview-field ad-preview-image">
									<div class="ad-preview-label">Image:</div>
									<div class="ad-preview-value"><img src="<?php echo $image_url; ?>" alt=""></div>
								</div>
								<div class="ad-preview-field ad-preview-url">
									<div class="ad-preview-label">URL:</div>
									<div class="ad-preview-value"><pre class="code"><?php echo esc_html($url); ?></pre></div>
								</div>
								<?php
								break;

							case 'embed':
								$code = get_field( 'ad-embed-code' );
								?>
								<div class="ad-preview-field ad-preview-image">
									<div class="ad-preview-label">Embed Code:</div>
									<div class="ad-preview-value"><pre><?php echo esc_html($code); ?></pre></div>
								</div>
								<div class="ad-preview-field ad-preview-url">
									<div class="ad-preview-label">Preview</div>
									<div class="ad-preview-value"><?php echo $code; ?></div>
								</div>
								<?php
								break;
						}
						?>
					</div>
					<?php
				}


				?>
	
			</div>
			<?php
		}

	endwhile;
	wp_reset_postdata();
}