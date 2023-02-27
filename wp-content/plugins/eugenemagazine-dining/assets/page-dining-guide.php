<?php get_header(); ?>

	<div id="content" class="clearfix">
		<div class="inside">

			<?php if ( have_posts() ) {
				the_post();
				get_template_part( "template-parts/cover" ); ?>

				<div class="inside narrow">
					<article <?php post_class( 'loop-single' ); ?>>

						<div class="loop-header">
							<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
						</div>

						<div class="dining-guide-body loop-body">

							<div class="dining-guide-sidebar">
								<form role="search" method="GET" class="searchform searchform-widget clearfix" action="<?php the_permalink(); ?>">
									<label for="dining-guide-search">Search for a restaurant:</label>
									<div class="dining-guide-search">
										<input name="search" id="dining-guide-search" class="search-input" type="text" value="<?php if ( ! empty( $_GET["search"] ) ) {
											echo esc_attr( wp_unslash( $_GET["search"] ) );
										} ?>"/>
										<input type="submit" value="Search" class="button"/>
									</div>
								</form>
								<?php

								// neighborhood filter not being used, but don't delete it or else it'll break everything forever
								echo '<div class="filter" style="display: none"><label for="neighborhoods" class="filter-title">Filter by neighborhood:</label>';
								echo '<select class="neighborhoods" id="neighborhoods"><option value="0">Any</option>';
								/*
								$neighborhoods = get_categories( array(
									'taxonomy' => 'neighborhood',
								) );
								foreach ( $neighborhoods as $val ) {
									echo '<option value="' . $val->term_id . '">' . $val->name . '</option>';
								}
								*/
								echo '</select></div>';

								// food type filter

								echo '<div class="filter"><label for="food_types" class="filter-title">Filter by type of food:</label>';
								echo '<select class="food_types" id="food_types"><option value="0">Any</option>';

								$food_types = get_categories( array(
									'taxonomy' => 'food_type',
								) );

								$food = isset( $_REQUEST['food'] ) ? intval( $_REQUEST['food'] ) : false;

								foreach ( $food_types as $val ) {
									$selected = ( $val->term_id == $food );
									echo '<option value="' . $val->term_id . '" ' . selected( $food, $val->term_id, false ) . '>' . $val->name . '</option>';
								}

								echo '</select></div>';

								echo do_shortcode( '[ad location="Dining Guide Sidebar"]' );
								?>
							</div>

							<div class="dining-guide-content loop-content">
								<?php the_content(); ?>
							</div><!-- .loop-content -->

						</div><!-- .loop-body -->

					</article>
				</div>

			<?php } ?>

		</div> <!-- /.inside -->
	</div> <!-- /#content -->

<?php get_footer();
