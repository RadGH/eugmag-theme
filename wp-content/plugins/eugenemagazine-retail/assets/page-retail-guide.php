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
						<div class="retail-guide-body loop-body">
							<div class="retail-guide-sidebar">
								<form role="search" method="GET" class="searchform searchform-widget clearfix" action="<?php the_permalink(); ?>">
									<label for="retail-guide-search">Search for a retail store:</label>
									<div class="retail-guide-search">
										<input name="search" id="retail-guide-search" class="search-input" type="text" value="<?php echo empty( $_GET["search"] ) ? '' : esc_attr( wp_unslash( $_GET["search"] ) ); ?>"/>
										<input type="submit" value="Search" class="button"/>
									</div>
								</form>
								<div class="filter">
									<label for="retail_type" class="filter-title">Filter by retail type:</label>
									<select id="retail_type">
										<option value="0">Any</option>
										<?php
										if ( $retail_types = get_categories( array( 'taxonomy' => 'retail_type' ) ) ) {
											$retail = ! empty( $_REQUEST['retail_type'] ) ? intval( $_REQUEST['retail_type'] ) : false;
											foreach ( $retail_types as $val ) {
												echo '<option value="' . $val->term_id . '"' . selected( $retail, $val->term_id, false ) . '>' . $val->name . '</option>';
											}
										}
										?>
									</select>
								</div>
								<?php echo do_shortcode( '[ad location="Retail Guide Sidebar"]' ); ?>
							</div>
							<div class="retail-guide-content loop-content">
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
