<?php

// TODO maybe don't need the term filtering and description search anymore

$template             = get_page_template_slug();
$permalink            = get_permalink();
$only_show_spotlights = ! empty( $_GET['spotlights'] );

$post_type = match ( $template ) {
	'page-dining-guide.php' => 'restaurant',
	'page-recreation-guide.php' => 'recreation',
	'page-retail-guide.php' => 'retailer',
	'page-service-guide.php' => 'service',
};

$spotlights_heading = match ( $post_type ) {
	'restaurant' => 'Dining Spotlights',
	'recreation' => 'Recreation Spotlights',
	'retailer' => 'Retail Spotlights',
	'service' => 'Service Spotlights',
};

$page_number  = max( 1, (int) get_query_var( 'paged' ) );
$taxonomy     = match ( $post_type ) {
	'restaurant' => 'food_type',
	'recreation' => 'activity',
	'retailer' => 'retail_type',
	'service' => 'service_type',
};
$search_field = match ( $post_type ) {
	'restaurant' => 'restaurant_description',
	'recreation' => 'activity_description',
	'retailer' => 'retail_description',
	'service' => 'service_description',
};
$posts_per_page = 12;

// A single query returns featured guides first, then regular guides, so each page is filled with up to
// $posts_per_page guides regardless of the mix. When only showing spotlights, return every featured guide.
$query = function() use ( $post_type, $page_number, $posts_per_page, $taxonomy, $search_field, $only_show_spotlights ) {

	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $only_show_spotlights ? - 1 : $posts_per_page,
		'paged'          => $only_show_spotlights ? 1 : $page_number,
		'meta_query'     => array(
			// "featured" is '1' when featured and '0' (or '' when unfeatured by the scheduler) otherwise, so sorting DESC puts featured first
			'featured_clause' => $only_show_spotlights
				? array( 'key' => 'featured', 'value' => '1' )
				: array( 'key' => 'featured', 'compare' => 'EXISTS' ),
		),
		'orderby'        => array(
			'featured_clause' => 'DESC',
			'title'           => 'ASC',
		),
	);

	if ( ! empty( $_REQUEST['term'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'name',
				'terms'    => $_REQUEST['term'],
			),
		);
	}

	if ( ! empty( $_REQUEST['search'] ) ) {
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => $search_field,
				'value'   => $search,
				'compare' => 'LIKE'
			);

		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;
	}

	return new WP_Query( $args );
};

$guides_query = $query();

// split this page's results into featured and regular guides, which are displayed differently
$featured_posts = array();
$regular_posts  = array();

foreach ( $guides_query->posts as $guide ) {
	if ( get_post_meta( $guide->ID, 'featured', true ) ) {
		$featured_posts[] = $guide;
	} else {
		$regular_posts[] = $guide;
	}
}

$max_pages = $only_show_spotlights ? 1 : (int) $guides_query->max_num_pages;

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
					<article <?php post_class( 'loop-archive archive-guide' ); ?>>
						<div class="archive-guide-body loop-body">
							<div class="archive-guide-content loop-content">
								<div class="loop-header">
									<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
								</div>
								<div class="guide-intro">
									<div class="guide-intro__content">
										<?php
										the_content();
										if ( $term = sanitize_text_field( wp_unslash( $_REQUEST['term'] ?? '' ) ) ) {
											$full_guide_url = remove_query_arg( 'term' );
											echo '<p>Showing results for: <em>' . esc_html( $term ) . '</em><br><a href="' . $full_guide_url . '">Back to full guide</a></a></p>';
										}
										?>
									</div>
									<div class="guide-intro__byline">
										A digital guide by <span>Eugene Magazine</span>
									</div>
								</div>
								<?php
								if ( $featured_posts ): ?>
									<div class="guide-spotlights-header">
										<h2><?php echo $spotlights_heading ?></h2>
										<?php if ( ! $only_show_spotlights ): ?>
											<a href="<?php
											echo add_query_arg(
												array(
													'term'       => null,
													'paged'      => null,
													'spotlights' => '1',
												),
												$permalink
											)
											?>">View all spotlights &rarr;</a>
										<?php endif; ?>
									</div>

									<ul class="guide-list -featured">
										<?php
										foreach ( $featured_posts as $post ) :
											setup_postdata( $post );
											$img             = get_field( 'spotlight-card-bg' ) ?: get_field( 'cover-image' );
											$img_src         = $img ? wp_get_attachment_image_url( $img, 'medium' ) : false;
											$tagline         = get_field( $post_type . '_tagline' ) ?: get_field( $post_type . '_description' );
											$tagline_cropped = wp_trim_words( wp_strip_all_tags( $tagline ), 75, '…' );
											?>
											<li class="guide-item"<?php if ( $img_src ) : ?> style="background-image: url('<?php echo esc_url( $img_src ); ?>');"<?php endif; ?>>
												<div class="guide-featured">Featured</div>
												<div class="guide-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></div>
												<div class="guide-desc">
													<?php echo esc_html( $tagline_cropped ) ?>
												</div>
												<a class="guide-more" href="<?php the_permalink() ?>">View profile &rarr;</a>
											</li>
										<?php
										endforeach;
										wp_reset_postdata();
										?>
									</ul>
								<?php endif; ?>
								<?php
								if ( $regular_posts ): ?>
									<ul class="guide-list -regular">
										<?php
										foreach ( $regular_posts as $post ) :
											setup_postdata( $post );
											$tagline = get_field( $post_type . '_tagline' ) ?: get_field( $post_type . '_description' );
											?>
											<li class="guide-item">
												<div class="guide-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></div>
												<div class="guide-desc">
													<?php echo esc_html( $tagline ) ?>
												</div>
											</li>
										<?php
										endforeach;
										wp_reset_postdata();
										?>
									</ul>
								<?php endif; ?>
								<?php
								if ( $max_pages > 1 ) :
									echo '<div class="pagination clear">';
									echo paginate_links( array(
										'base'     => add_query_arg(
											array(
												'paged' => '%#%',
											),
											$permalink
										),
										'format'   => '',
										'end_size' => 2,
										'mid_size' => 4,
										'current'  => max( 1, $page_number ),
										'total'    => $max_pages,
									) );
									echo '</div>';
								endif;
								?>
							</div>
						</div>
					</article>
				</div>
			<?php endwhile; ?>
		</div> <!-- /.inside -->
	</div> <!-- /#content -->
<?php
get_footer();
