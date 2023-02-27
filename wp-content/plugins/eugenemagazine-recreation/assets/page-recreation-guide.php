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
						<div class="recreation-guide-body loop-body">
							<div class="recreation-guide-sidebar">
								<form role="search" method="GET" class="searchform searchform-widget clearfix" action="<?php the_permalink(); ?>">
									<label for="recreation-guide-search">Search for a location:</label>
									<div class="recreation-guide-search">
										<input name="search" id="recreation-guide-search" class="search-input" type="text" value="<?php echo empty( $_GET["search"] ) ? '' : esc_attr( wp_unslash( $_GET["search"] ) ); ?>"/>
										<input type="submit" value="Search" class="button"/>
									</div>
								</form>
								<?php if ( $activities = get_categories( array( 'taxonomy' => 'activity' ) ) ) : ?>
									<div class="filter">
										<label for="activity" class="filter-title">Filter by activity:</label>
										<select id="activity">
											<option value="0">Any</option>
											<?php
											$recreation = ! empty( $_REQUEST['activity'] ) ? intval( $_REQUEST['activity'] ) : false;
											foreach ( $activities as $val ) {
												echo '<option value="' . $val->term_id . '"' . selected( $recreation, $val->term_id, false ) . '>' . $val->name . '</option>';
											}
											?>
										</select>
									</div>
								<?php endif; ?>
								<?php echo do_shortcode( '[ad location="Recreation Guide Sidebar"]' ); ?>
							</div>
							<div class="recreation-guide-content loop-content">
								<?php the_content(); ?>
							</div><!-- .loop-content -->
						</div><!-- .loop-body -->
					</article>
				</div>
			<?php } ?>
		</div> <!-- /.inside -->
	</div> <!-- /#content -->
<?php
get_footer();
