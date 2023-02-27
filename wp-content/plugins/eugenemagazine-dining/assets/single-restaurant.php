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
							<div class="breadcrumbs"><a href="/dining-guide/">&larr; Dining Guide</a></div>
							<div class="social-sharing">
								Share this page: <?php echo implode( generate_sharing_links() ); ?>
							</div>
							<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
						</div>

						<div class="single-restaurant-body loop-body">

							<div class="single-restaurant-sidebar">
								<?php echo do_shortcode( '[ad location="Dining Guide Sidebar"]' ); ?>
							</div>

							<div class="single-restaurant-content loop-content">

								<?php
								if ( get_field( 'restaurant_logo' ) ) {
									echo '<img src="', esc_attr( get_field( 'restaurant_logo' )['sizes']['thumbnail'] ), '" alt="logo" />';
								}

								if ( $images = get_field( 'restaurant_gallery' ) ): ?>
									<div class="rest_gallery">
										<?php foreach ( $images as $image ) : ?>
											<div class="carousel-cell">
												<a href="<?php echo esc_attr( $image['url'] ); ?>"><img src="<?php echo esc_attr( $image['sizes']['thumbnail'] ); ?>" alt="<?php echo $image['title']; ?>" /></a>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; // endif gallery
								?>

								<?php


								/*if ( get_field( 'restaurant_neighborhood' ) ) {
									$neighborhood = get_term( intval( get_field( 'restaurant_neighborhood' ) ) )->name;
									echo '<div class="rest_neighborhood"><div class="rest_meta_title">Neighborhood</div><div class="rest_meta_content">', $neighborhood, '</div></div>';
								}*/
								if ( get_field( 'restaurant_address' ) ) {
									echo '<div class="rest_address"><div class="rest_meta_title">Address</div><div class="rest_meta_content">', get_field( 'restaurant_address' ), '</div></div>';
								}
								if ( get_field( 'restaurant_description' ) ) {
									echo '<div class="rest_description"><div class="rest_meta_title">Description</div><div class="rest_meta_content">', get_field( 'restaurant_description' ), '</div></div>';
								}
								if ( get_field( 'restaurant_hours' ) ) {
									echo '<div class="rest_hours"><div class="rest_meta_title">Hours</div><div class="rest_meta_content">', get_field( 'restaurant_hours' ), '</div></div>';
								}
								if ( get_field( 'restaurant_price' ) ) {
									echo '<div class="rest_price"><div class="rest_meta_title">Price Scale</div><div class="rest_meta_content">', get_field( 'restaurant_price' ), '</div></div>';
								}

								if ( $terms = get_the_terms( get_the_ID(),'food_type' ) ) {
									$foodtypes = array();
									foreach ( $terms as $term ) {
										$foodtypes[] = $term->name;
									}
									echo '<div class="rest_food_type"><div class="rest_meta_title">Type of Cuisine</div><div class="rest_meta_content">', implode( ', ', $foodtypes ), '</div></div>';
								}

								if ( get_field( 'restaurant_meals_served' ) ) {
									echo '<div class="rest_meals_served"><div class="rest_meta_title">Meals Served</div><div class="rest_meta_content">', get_field( 'restaurant_meals_served' ), '</div></div>';
								}
								if ( get_field( 'restaurant_info' ) ) {
									echo '<div class="rest_info"><div class="rest_meta_title">Important Info</div><div class="rest_meta_content">', get_field( 'restaurant_info' ), '</div></div>';
								}
								if ( get_field( 'restaurant_phone' ) ) {
									echo '<div class="rest_phone"><div class="rest_meta_title">Phone</div><div class="rest_meta_content">', get_field( 'restaurant_phone' ), '</div></div>';
								}
								if ( $website = get_field( 'restaurant_website' ) ) {
									echo '<div class="rest_website"><div class="rest_meta_title">Website</div><div class="rest_meta_content"><a href="', esc_url( $website ), '" target="_blank">', $website, '</a></div></div>';
								}
								if ( get_field( 'restaurant_facebook' ) || get_field( 'restaurant_instagram' ) || get_field( 'restaurant_twitter' ) ) {
									echo '<div class="rest_socmed">';
									if ( $fb = get_field( 'restaurant_facebook' ) ) {
										echo '<a class="rest_fb" href="', esc_attr( $fb ), '">Facebook</a> ';
									}
									if ( $instagram = get_field( 'restaurant_instagram' ) ) {
										echo '<a class="rest_instagram"a href="', esc_attr( $instagram ), '">Instagram</a> ';
									}
									if ( $twitter = get_field( 'restaurant_twitter' ) ) {
										echo '<a class="rest_twitter" href="', esc_attr( $twitter ), '">Twitter</a>';
									}
									echo '</div>';
								}


								// MAP
								if ( $location = get_field( 'restaurant_gmaps' ) ):
									?>
									<div id="rest_map" class="retail_map" data-markerdir="<?php echo get_template_directory_uri() . '/img/markers/'; ?>">
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
