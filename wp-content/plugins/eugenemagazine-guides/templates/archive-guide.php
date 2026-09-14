<?php

// TODO maybe don't need the term filtering and description search anymore

$template             = get_page_template_slug();
$permalink            = get_permalink();
$only_show_spotlights = (bool) $_GET['spotlights'];

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
$query        = function( $featured ) use ( $post_type, $page_number, $taxonomy, $search_field, $only_show_spotlights ) {
	$args = array(
		'post_type'      => $post_type,
		'posts_per_page' => $featured
			? ( $only_show_spotlights ? - 1 : 9 )
			: 12,
		'paged'          => (int) $page_number,
		'meta_key'       => 'featured',
		'meta_value'     => $featured ? '1' : '0',
		'orderby'        => array(
			'title' => 'ASC',
		),
	);

	if ( $_REQUEST['term'] ) {
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

// fetch featured posts
$featured_posts = $query( true );

// fetch regular posts (if only showing spotlights, don't show regular posts at all)
$regular_posts = $only_show_spotlights ? null : $query( false );

// work out the pagination: if only showing spotlights, there's only one page; otherwise need enough pages to accomodate all featured and all regular posts
$max_pages = $only_show_spotlights ? 1 : max( $featured_posts->max_num_pages, $regular_posts->max_num_pages );

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
								if ( $featured_posts->have_posts() ): ?>
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
										while ( $featured_posts->have_posts() ) :
											$featured_posts->the_post();
											$img             = get_field( 'spotlight-card-bg' ) ?: get_field( 'cover-image' );
											$img_src         = $img ? wp_get_attachment_image_url( $img, 'medium' ) : false;
											$tagline         = get_field( $post_type . '_tagline' ) ?: get_field( $post_type . '_description' );
											$tagline_cropped = wp_trim_words( wp_strip_all_tags( $tagline ), 75, '…' );
											?>
											<li class="guide-item">
												<?php if ( $img_src ) : ?>
													<img src="<?php echo esc_url( $img_src ); ?>" alt="" class="guide-image">
												<?php endif; ?>
												<div class="guide-featured">Featured</div>
												<div class="guide-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></div>
												<div class="guide-desc">
													<?php echo esc_html( $tagline_cropped ) ?>
												</div>
												<a class="guide-more" href="<?php the_permalink() ?>">View profile &rarr;</a>
											</li>
										<?php
										endwhile;
										wp_reset_postdata();
										?>
									</ul>
								<?php endif; ?>
								<?php
								if ( $regular_posts && $regular_posts->have_posts() ): ?>
									<ul class="guide-list -regular">
										<?php
										while ( $regular_posts->have_posts() ) :
											$regular_posts->the_post();
											$tagline = get_field( $post_type . '_tagline' ) ?: get_field( $post_type . '_description' );
											?>
											<li class="guide-item">
												<div class="guide-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></div>
												<div class="guide-desc">
													<?php echo esc_html( $tagline ) ?>
												</div>
											</li>
										<?php
										endwhile;
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
