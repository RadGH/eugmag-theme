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
							<div class="breadcrumbs"><a href="/recreation-guide/">&larr; Recreation Guide</a></div>
							<div class="social-sharing">
								Share this page: <?php echo implode( generate_sharing_links() ); ?>
							</div>
							<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
						</div>

						<div class="single-recreation-body loop-body">

							<div class="single-recreation-sidebar">
								<?php echo do_shortcode( '[ad location="Recreation Guide Sidebar"]' ); ?>
							</div>

							<div class="single-recreation-content loop-content">

								<?php
								if ( get_field( 'recreation_logo' ) ) {
									echo '<img src="', esc_attr( get_field( 'recreation_logo' )['sizes']['thumbnail'] ), '" alt="logo" />';
								}

								if ( $images = get_field( 'recreation_gallery' ) ): ?>
									<div class="recreation_gallery">
										<?php foreach ( $images as $image ) : ?>
											<div class="carousel-cell">
												<a href="<?php echo esc_attr( $image['url'] ); ?>"><img src="<?php echo esc_attr( $image['sizes']['thumbnail'] ); ?>" alt="<?php echo $image['title']; ?>" /></a>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; // endif gallery
								?>

								<?php

								if ( get_field( 'recreation_address' ) ) {
									echo '<div class="recreation_address"><div class="recreation_meta_title">Address</div><div class="recreation_meta_content">', get_field( 'recreation_address' ), '</div></div>';
								}
								if ( get_field( 'recreation_description' ) ) {
									echo '<div class="recreation_description"><div class="recreation_meta_title">Description</div><div class="recreation_meta_content">', get_field( 'recreation_description' ), '</div></div>';
								}
								if ( get_field( 'recreation_hours' ) ) {
									echo '<div class="recreation_hours"><div class="recreation_meta_title">Hours</div><div class="recreation_meta_content">', get_field( 'recreation_hours' ), '</div></div>';
								}
								if ( get_field( 'recreation_price' ) ) {
									echo '<div class="recreation_price"><div class="recreation_meta_title">Price Scale</div><div class="recreation_meta_content">', get_field( 'recreation_price' ), '</div></div>';
								}

								if ( $terms = get_the_terms( get_the_ID(),'activity' ) ) {
									$recreationtypes = array();
									foreach ( $terms as $term ) {
										$recreationtypes[] = $term->name;
									}
									echo '<div class="recreation_activity"><div class="recreation_meta_title">Recreation Types</div><div class="recreation_meta_content">', implode( ', ', $recreationtypes ), '</div></div>';
								}

								if ( get_field( 'recreation_info' ) ) {
									echo '<div class="recreation_info"><div class="recreation_meta_title">Important Info</div><div class="recreation_meta_content">', get_field( 'recreation_info' ), '</div></div>';
								}
								if ( get_field( 'recreation_phone' ) ) {
									echo '<div class="recreation_phone"><div class="recreation_meta_title">Phone</div><div class="recreation_meta_content">', get_field( 'recreation_phone' ), '</div></div>';
								}
								if ( $website = get_field( 'recreation_website' ) ) {
									echo '<div class="recreation_website"><div class="recreation_meta_title">Website</div><div class="recreation_meta_content"><a href="', esc_url( $website ), '" target="_blank">', $website, '</a></div></div>';
								}
								if ( get_field( 'recreation_facebook' ) || get_field( 'recreation_instagram' ) || get_field( 'recreation_twitter' ) ) {
									echo '<div class="recreation_socmed">';
									if ( $fb = get_field( 'recreation_facebook' ) ) {
										echo '<a class="recreation_fb" href="', esc_attr( $fb ), '">Facebook</a> ';
									}
									if ( $instagram = get_field( 'recreation_instagram' ) ) {
										echo '<a class="recreation_instagram" href="', esc_attr( $instagram ), '">Instagram</a> ';
									}
									if ( $twitter = get_field( 'recreation_twitter' ) ) {
										echo '<a class="recreation_twitter" href="', esc_attr( $twitter ), '">Twitter</a>';
									}
									echo '</div>';
								}

								// MAP
								if ( $location = get_field( 'recreation_gmaps' ) ):
									?>
									<div id="recreation_guide_map" class="recreation_map" data-markerdir="<?php echo get_template_directory_uri() . '/img/markers/'; ?>">
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
