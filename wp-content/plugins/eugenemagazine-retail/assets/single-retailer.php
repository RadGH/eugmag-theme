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
							<div class="breadcrumbs"><a href="/retail-guide/">&larr; Retail Guide</a></div>
							<div class="social-sharing">
								Share this page: <?php echo implode( generate_sharing_links() ); ?>
							</div>
							<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
						</div>

						<div class="single-retailer-body loop-body">

							<div class="single-retailer-sidebar">
								<?php echo do_shortcode( '[ad location="Retail Guide Sidebar"]' ); ?>
							</div>

							<div class="single-retailer-content loop-content">

								<?php
								if ( get_field( 'retailer_logo' ) ) {
									echo '<img src="', esc_attr( get_field( 'retailer_logo' )['sizes']['thumbnail'] ), '" alt="logo" />';
								}

								if ( $images = get_field( 'retailer_gallery' ) ): ?>
									<div class="retailer_gallery">
										<?php foreach ( $images as $image ) : ?>
											<div class="carousel-cell">
												<a href="<?php echo esc_attr( $image['url'] ); ?>"><img src="<?php echo esc_attr( $image['sizes']['thumbnail'] ); ?>" alt="<?php echo $image['title']; ?>" /></a>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; // endif gallery
								?>

								<?php

								if ( get_field( 'retailer_address' ) ) {
									echo '<div class="retailer_address"><div class="retailer_meta_title">Address</div><div class="retailer_meta_content">', get_field( 'retailer_address' ), '</div></div>';
								}
								if ( get_field( 'retailer_description' ) ) {
									echo '<div class="retailer_description"><div class="retailer_meta_title">Description</div><div class="retailer_meta_content">', get_field( 'retailer_description' ), '</div></div>';
								}
								if ( get_field( 'retailer_hours' ) ) {
									echo '<div class="retailer_hours"><div class="retailer_meta_title">Hours</div><div class="retailer_meta_content">', get_field( 'retailer_hours' ), '</div></div>';
								}
								if ( get_field( 'retailer_price' ) ) {
									echo '<div class="retailer_price"><div class="retailer_meta_title">Price Scale</div><div class="retailer_meta_content">', get_field( 'retailer_price' ), '</div></div>';
								}

								if ( $terms = get_the_terms( get_the_ID(),'retail_type' ) ) {
									$retailertypes = array();
									foreach ( $terms as $term ) {
										$retailertypes[] = $term->name;
									}
									echo '<div class="retailer_retailer_type"><div class="retailer_meta_title">Retail Types</div><div class="retailer_meta_content">', implode( ', ', $retailertypes ), '</div></div>';
								}

								if ( get_field( 'retailer_info' ) ) {
									echo '<div class="retailer_info"><div class="retailer_meta_title">Important Info</div><div class="retailer_meta_content">', get_field( 'retailer_info' ), '</div></div>';
								}
								if ( get_field( 'retailer_phone' ) ) {
									echo '<div class="retailer_phone"><div class="retailer_meta_title">Phone</div><div class="retailer_meta_content">', get_field( 'retailer_phone' ), '</div></div>';
								}
								if ( $website = get_field( 'retailer_website' ) ) {
									echo '<div class="retailer_website"><div class="retailer_meta_title">Website</div><div class="retailer_meta_content"><a href="', esc_url( $website ), '" target="_blank">', $website, '</a></div></div>';
								}
								if ( get_field( 'retailer_facebook' ) || get_field( 'retailer_instagram' ) || get_field( 'retailer_twitter' ) ) {
									echo '<div class="retailer_socmed">';
									if ( $fb = get_field( 'retailer_facebook' ) ) {
										echo '<a class="retailer_fb" href="', esc_attr( $fb ), '">Facebook</a> ';
									}
									if ( $instagram = get_field( 'retailer_instagram' ) ) {
										echo '<a class="retailer_instagram" href="', esc_attr( $instagram ), '">Instagram</a> ';
									}
									if ( $twitter = get_field( 'retailer_twitter' ) ) {
										echo '<a class="retailer_twitter" href="', esc_attr( $twitter ), '">Twitter</a>';
									}
									echo '</div>';
								}

								// MAP
								if ( $location = get_field( 'retailer_gmaps' ) ):
									?>
									<div id="retail_guide_map" class="retail_map" data-markerdir="<?php echo get_template_directory_uri() . '/img/markers/'; ?>">
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
