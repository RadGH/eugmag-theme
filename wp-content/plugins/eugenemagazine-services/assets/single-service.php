<?php
get_header();
?>

	<div id="content" class="clearfix">
		<div class="inside">

			<?php

			while ( have_posts() ) :
				the_post();

				get_template_part( "template-parts/cover" );
				?>

				<div class="inside narrow">
					<article <?php post_class( 'loop-single' ); ?>>

						<div class="loop-header">
							<div class="breadcrumbs"><a href="/services-guide/">&larr; Services Guide</a></div>
							<div class="social-sharing">
								Share this page: <?php echo implode( generate_sharing_links() ); ?>
							</div>
							<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
						</div>

						<div class="single-service-body loop-body">

							<div class="single-service-sidebar">
								<?php echo do_shortcode( '[ad location="Service Guide Sidebar"]' ); ?>
							</div>

							<div class="single-service-content loop-content">

								<?php
								if ( get_field( 'service_logo' ) ) {
									echo '<img src="', esc_attr( get_field( 'service_logo' )['sizes']['thumbnail'] ), '" alt="logo" />';
								}

								if ( $images = get_field( 'service_gallery' ) ): ?>
									<div class="service_gallery">
										<?php foreach ( $images as $image ) : ?>
											<div class="carousel-cell">
												<a href="<?php echo esc_attr( $image['url'] ); ?>"><img src="<?php echo esc_attr( $image['sizes']['thumbnail'] ); ?>" alt="<?php echo $image['title']; ?>" /></a>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; // endif gallery
								?>

								<?php

								if ( get_field( 'service_address' ) ) {
									echo '<div class="service_address"><div class="service_meta_title">Address</div><div class="service_meta_content">', get_field( 'service_address' ), '</div></div>';
								}
								if ( get_field( 'service_description' ) ) {
									echo '<div class="service_description"><div class="service_meta_title">Description</div><div class="service_meta_content">', get_field( 'service_description' ), '</div></div>';
								}
								if ( get_field( 'service_hours' ) ) {
									echo '<div class="service_hours"><div class="service_meta_title">Hours</div><div class="service_meta_content">', get_field( 'service_hours' ), '</div></div>';
								}
								if ( get_field( 'service_price' ) ) {
									echo '<div class="service_price"><div class="service_meta_title">Price Scale</div><div class="service_meta_content">', get_field( 'service_price' ), '</div></div>';
								}

								if ( $terms = get_the_terms( get_the_ID(),'service_type' ) ) {
									$servicetypes = array();
									foreach ( $terms as $term ) {
										$servicetypes[] = $term->name;
									}
									echo '<div class="service_service_type"><div class="service_meta_title">Service Types</div><div class="service_meta_content">', implode( ', ', $servicetypes ), '</div></div>';
								}

								if ( get_field( 'service_info' ) ) {
									echo '<div class="service_info"><div class="service_meta_title">Important Info</div><div class="service_meta_content">', get_field( 'service_info' ), '</div></div>';
								}
								if ( get_field( 'service_phone' ) ) {
									echo '<div class="service_phone"><div class="service_meta_title">Phone</div><div class="service_meta_content">', get_field( 'service_phone' ), '</div></div>';
								}
								if ( $website = get_field( 'service_website' ) ) {
									echo '<div class="service_website"><div class="service_meta_title">Website</div><div class="service_meta_content"><a href="', esc_url( $website ), '" target="_blank">', $website, '</a></div></div>';
								}
								if ( get_field( 'service_facebook' ) || get_field( 'service_instagram' ) || get_field( 'service_twitter' ) ) {
									echo '<div class="service_socmed">';
									if ( $fb = get_field( 'service_facebook' ) ) {
										echo '<a class="service_fb" href="', esc_attr( $fb ), '">Facebook</a> ';
									}
									if ( $instagram = get_field( 'service_instagram' ) ) {
										echo '<a class="service_instagram" href="', esc_attr( $instagram ), '">Instagram</a> ';
									}
									if ( $twitter = get_field( 'service_twitter' ) ) {
										echo '<a class="service_twitter" href="', esc_attr( $twitter ), '">Twitter</a>';
									}
									echo '</div>';
								}

								// MAP
								if ( $location = get_field( 'service_gmaps' ) ):
									?>
									<div id="service_guide_map" class="service_map" data-markerdir="<?php echo get_template_directory_uri() . '/img/markers/'; ?>">
										<div class="marker" data-lat="<?php echo esc_attr( $location['lat'] ); ?>" data-lng="<?php echo esc_attr( $location['lng'] ); ?>" title="<?php the_title(); ?>"></div>
									</div>
								<?php endif; ?>

							</div><!-- .loop - content-->

						</div><!-- .loop - body-->

					</article>
				</div>
			<?php endwhile; ?>
		</div> <!-- /.inside -->
	</div> <!-- /#content -->

<?php
get_footer();
