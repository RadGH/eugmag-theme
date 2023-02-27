<?php
/* 
This page shows a list of ads on the dashboard. They are grouped by date (time slot name). Clicking an ad will go to the edit screen for an ad.
*/

include( LDAdStore_PATH . '/advertisements-store/templates/parts/navigation.php' );

$args = array(
	'post_type' => 'ld_ad',
	'post_author' => get_current_user_id(),

	'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
	'posts_per_page' => 10,

	'paged' => get_query_var('paged'),

	'meta_query' => array(
		array(
			'key' => 'customer',
			'value' => get_current_user_id(),
			'compare' => '=',
		),
	),

	'meta_key' => 'start_date_timestamp',
	'orderby' => 'meta_value_num',
	'order' => 'ASC',
);
$purchased_ads = new WP_Query($args);

if ( $purchased_ads->have_posts() ) {
	?>
	<div class="ad-item-list">
	<?php
	$previous_time_slot = null;

	// We'll put expired ads at the bottom of the list.
	$expired_ads = array();

	while( $purchased_ads->have_posts() ): $purchased_ads->the_post();

		$locations = get_field( 'ad-locations', get_the_ID() );
		$time_slot_name = get_field( 'time_slot_name', get_the_ID() );
		$order_id = get_field( 'order', get_the_ID() );
		$start_time = get_field( 'start_date_timestamp', get_the_ID() );
		$end_time = get_field( 'end_date_timestamp', get_the_ID() );
		$status = get_post_status();

		if ( $end_time < time() ) {
			ob_start();
		}

		if ( $time_slot_name != $previous_time_slot ) {
			?>
			<div class="ad-time-slot-header">
				<p>
					<strong class="time-slot-name"><?php echo $time_slot_name; ?></strong>
					<span class="date-range">(<?php echo date(ad_date_format(), $start_time); ?> &ndash; <?php echo date(ad_date_format(), $end_time); ?>)</span>
				</p>
			</div>
			<?php
			$previous_time_slot = $time_slot_name;
		}
		
		?>
		<div class="ad-item ad-archive action-view ad-status-<?php echo $status; ?> ad-id-<?php the_ID(); ?>">
			<div class="ad-preview"></div>

			<h2><a href="<?php echo esc_attr(ldadstore_get_ad_link( get_the_ID(), 'edit' )); ?>"><?php echo implode( ", ", $locations ); ?></a></h2>

			<div class="ad-summary">
				<div class="summary-status">
					<span class="label">Status:</span>
					<span class="value"><?php echo ldadstore_get_status_info(); ?></span>
				</div>
			</div>
		</div>
		<?php

		if ( $end_time < time() ) {
			$expired_ads[] = ob_get_clean();
		}
	endwhile;

	if ( $expired_ads ) {
		?>
		<div class="expired-ads">
			<h3><span>The following ads have expired</span></h3>
			<?php echo implode("\n", $expired_ads); ?>
		</div>
		<?php
	}
	?>
	</div>
	
	<div class="ad-pagination">
		<div class="ad-page-previous alignleft"><?php previous_posts_link( '&laquo; Previous Page' ); ?></div>
		<div class="ad-page-next alignright"><?php next_posts_link( 'Next Page &raquo;', $purchased_ads->max_num_pages ); ?></div>
	</div>
	<?php
}else{
	?>
	<div class="no-ads">
		<p><strong>You have no advertisements yet.</strong></p>
	</div>
	<?php
}

wp_reset_postdata();