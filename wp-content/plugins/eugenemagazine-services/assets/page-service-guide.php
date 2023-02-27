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
						<div class="service-guide-body loop-body">
							<div class="service-guide-sidebar">
								<form role="search" method="GET" class="searchform searchform-widget clearfix" action="<?php the_permalink(); ?>">
									<label for="service-guide-search">Search for a service:</label>
									<div class="service-guide-search">
										<input name="search" id="service-guide-search" class="search-input" type="text" value="<?php echo empty( $_GET["search"] ) ? '' : esc_attr( wp_unslash( $_GET["search"] ) ); ?>"/>
										<input type="submit" value="Search" class="button"/>
									</div>
								</form>
								<?php if ( $service_types = get_categories( array( 'taxonomy' => 'service_type' ) ) ) : ?>
									<div class="filter">
										<label for="service_type" class="filter-title">Filter by service type:</label>
										<select id="service_type">
											<option value="0">Any</option>
											<?php
											$service = ! empty( $_REQUEST['service_type'] ) ? intval( $_REQUEST['service_type'] ) : false;
											foreach ( $service_types as $val ) {
												echo '<option value="' . $val->term_id . '"' . selected( $service, $val->term_id, false ) . '>' . $val->name . '</option>';
											}
											?>
										</select>
									</div>
								<?php endif; ?>
								<?php echo do_shortcode( '[ad location="Service Guide Sidebar"]' ); ?>
							</div>
							<div class="service-guide-content loop-content">
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
