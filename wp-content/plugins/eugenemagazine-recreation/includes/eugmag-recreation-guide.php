<?php

// load template for single recreation posts
function rg_recreation_template( $single ) {
	global $post;
	if ( $post->post_type == "recreation" ) {
		wp_enqueue_style( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.css' );
		wp_enqueue_script( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.pkgd.min.js', array( 'jquery' ), null, true );
		if ( file_exists( EUGMAG_REC_PATH . '/assets/single-recreation.php' ) ) {
			return EUGMAG_REC_PATH . '/assets/single-recreation.php';
		}
	}

	return $single;
}

add_filter( 'single_template', 'rg_recreation_template' );


// add custom template to Page Attributes > Template dropdown
function eugmag_rec_add_template_to_select( $post_templates ) {
	$post_templates['page-recreation-guide.php'] = 'Recreation Guide';

	return $post_templates;
}

add_filter( 'theme_page_templates', 'eugmag_rec_add_template_to_select' );


// load custom template when selected for a page
function eugmag_rec_page_template( $page_template ) {
	if ( get_page_template_slug() == 'page-recreation-guide.php' && file_exists( EUGMAG_REC_PATH . '/assets/page-recreation-guide.php' ) ) {
		$page_template = EUGMAG_REC_PATH . '/assets/page-recreation-guide.php';
	}

	return $page_template;
}

add_filter( 'page_template', 'eugmag_rec_page_template' );


// shortcode to output recreation guide page (incl. map and paginated list)

function rg_recreation_guide_shortcode() {
	ob_start();

	// OUTPUT MAP WITH PINS

	$args = array(
		'post_type'      => 'recreation',
		'posts_per_page' => - 1,
		'no_found_rows'  => true,
	);

	if ( ! empty( $_REQUEST['search'] ) ) {
		// doing a search: don't use the transient
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => 'recreation_description',
				'value'   => $search,
				'compare' => 'LIKE'

			);
		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;

		$recreationPins = new WP_Query( $args );
	} else {
		// not doing a search: use the transient
		$recreationPins = get_transient( 'recreationPins' );

		if ( ! $recreationPins ) {
			$recreationPins = new WP_Query( $args );
			set_transient( 'recreationPins', $recreationPins, 60 * 60 * 24 );
		}
	}

	if ( $recreationPins ) {
		?>
		<div id="recreation_guide_map" class="recreation_guide_map"></div>
		<div id="recreation_map_markers">
			<?php
			while ( $recreationPins->have_posts() ) {
				$recreationPins->the_post();
				if ( $map = get_field( 'recreation_gmaps' ) ) {
					$recreationTypeList = '';
					if ( $getTerms = get_the_terms( get_the_ID(), 'activity' ) ) {
						foreach ( $getTerms as $term ) {
							$recreationTypeList .= ' ' . $term->term_id;
						}
						$recreationTypeList = trim( $recreationTypeList );
					}
					echo '<div class="marker" title="' . esc_attr( get_the_title() ) . '" data-recreationTypes="' . esc_attr( $recreationTypeList ) . '" data-lat="' . $map['lat'] . '" data-lng="' . $map['lng'] . '">';
					echo '<div class="marker-title">' . esc_attr( get_the_title() ) . '</div>';
					echo '<p class="marker-desc">' . esc_attr( get_field( 'recreation_description' ) ) . '</p>';
					echo '<p class="marker-more"><a href="' . get_post_permalink() . '">More info &raquo;</a></p>';
					echo '</div>';
				}
			}
			?>
		</div>
		<?php
	}

	// OUTPUT PAGINATED RECREATION LIST
	echo '<div id="recreation-guide-list-wrapper">', rg_paginated_recreation_guide(), '</div>';
	wp_reset_postdata();
	?>
	<script>
		const recreation_guide_url = <?php echo json_encode( get_the_permalink() ); ?>
	</script>
	<?php

	echo ob_get_clean();
}

if ( ! is_admin() ) {
	add_shortcode( 'recreation_guide', 'rg_recreation_guide_shortcode' );
}


// update recreation list
function rg_clear_recreation_pin_cache( $post_id ) {
	if ( get_post_type( $post_id ) != "recreation" ) {
		return;
	}
	delete_transient( 'recreationPins' );
}

add_action( 'save_post', 'rg_clear_recreation_pin_cache' );
add_action( 'trash_post', 'rg_clear_recreation_pin_cache' );


// paginated recreation guide list

function rg_paginated_recreation_guide_query( $page_number, $recreationtype ) {

	$args = array(
		'post_type'      => 'recreation',
		'posts_per_page' => 10,
		'paged'          => (int) $page_number,
		'meta_query'     => array(
			'featured_clause' => array(
				'key'  => 'featured',
				'type' => 'NUMERIC'
			)
		),
		'orderby'        => array(
			'featured_clause' => 'DESC',
			'title'           => 'ASC',
		),
	);

	if ( ! $recreationtype && isset( $_REQUEST['activity'] ) ) {
		$recreationtype = intval( $_REQUEST['activity'] );
	}

	if ( $recreationtype ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'activity',
				'terms'    => (int) $recreationtype,
			),
		);
	}

	// search
	if ( ! empty( $_REQUEST['search'] ) ) {
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => 'recreation_description',
				'value'   => $search,
				'compare' => 'LIKE'

			);

		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;
	}

	return new WP_Query( $args );
}

// https://wordpress.stackexchange.com/a/208939
function eugmag_rec_search_titles_and_meta_keys( $q ) {
	if ( $search = $q->get( '_meta_or_title' ) ) {
		add_filter( 'get_meta_sql', function ( $sql ) use ( $search ) {
			global $wpdb;

			// Only run once:
			static $nr = 0;
			if ( 0 != $nr ++ ) {
				return $sql;
			}

			// find the second-to-last close parentheses, and insert metafield search immediately before it
			$found = 0;
			for ( $i = 1; $i < mb_strlen( $sql['where'] ); $i ++ ) {
				$parentheses_search = mb_substr( $sql["where"], - $i, 1 );
				if ( $parentheses_search === ")" ) {
					$found ++;
				}
				if ( $found == 2 ) {
					$insert       = ' OR (' . $wpdb->prepare( "{$wpdb->posts}.post_title like '%%%s%%'", $search ) . ')';
					$sql['where'] = substr_replace( $sql['where'], $insert, - $i - 1, 0 );
					break;
				}
			}

			return $sql;
		} );
	}
}

add_action( 'pre_get_posts', 'eugmag_rec_search_titles_and_meta_keys' );


function rg_paginated_recreation_guide( $page_number = null, $recreationtype = 0 ) {

	if ( $page_number === null ) {
		$page_number = get_query_var( 'paged' );
	}

	if ( $page_number < 1 ) {
		$page_number = 1;
	}

	$query = rg_paginated_recreation_guide_query( $page_number, $recreationtype );

	ob_start();

	if ( $query->have_posts() ) :
		?>
		<ul class="recreation-guide-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				if ( get_post_meta( get_the_ID(), 'featured', true ) ) {
					echo '<li class="recreation-guide-featured-li">';
					echo '<div class="recreation-guide-featured">Featured</div>';
				} else {
					echo '<li>';
				}
				echo '<div class="recreation-guide-title"><a href="', get_post_permalink(), '">', get_the_title(), '</a></div>';


				echo '<div class="retail-guide-meta">';
				$meta   = array();
				$meta[] = esc_html( get_field( 'recreation_price' ) );
				if ( $tax_terms = strip_tags( get_the_term_list( get_the_ID(), 'activity', '', ', ', '' ) ) ) {
					$meta[] = esc_html( $tax_terms );
				}
				$meta[] = esc_html( get_field( 'recreation_address' ) );
				$meta[] = esc_html( get_field( 'recreation_phone' ) );
				echo implode( ' &bull; ', array_filter( $meta ) );
				echo '</div>';


				echo '<div class="recreation-guide-desc">', esc_html( get_field( 'recreation_description' ) ), ' <a class="recreation-guide-more" href="' . get_post_permalink() . '">More info &raquo;</a></div>';
				echo '</li>';
			endwhile;
			?>
		</ul>
		<?php

		if ( $query->max_num_pages > 1 ) :
			$recreationtypeBase = $recreationtype ? '?recreation=' . $recreationtype : '';
			echo '<div id="recreation-guide-pagination" class="pagination clear">';
			echo paginate_links( array(
				'base'     => home_url() . '/recreation-guide/page/%#%/' . $recreationtypeBase,
				'format'   => '/page/%#%',
				'end_size' => 2,
				'mid_size' => 4,
				'current'  => max( 1, $page_number ),
				'total'    => $query->max_num_pages,
			) );
			echo '</div>';
		endif;
	else:
		?>
		<p>No results found.</p>
	<?php
	endif;

	return ob_get_clean();
}


// outputs results from a given page of the recreation guide
function rg_ajax_paginated_recreation_guide() {
	$page = (int) $_POST['page'];

	$recreationtype = 0;
	if ( isset( $_POST['recreationtype'] ) ) {
		$recreationtype = (int) $_POST['recreationtype'];
	}
	echo rg_paginated_recreation_guide( $page, $recreationtype );
	wp_die();
}

add_action( 'wp_ajax_nopriv_paginated_recreation_guide', 'rg_ajax_paginated_recreation_guide' );
add_action( 'wp_ajax_paginated_recreation_guide', 'rg_ajax_paginated_recreation_guide' );


// scheduled thingy

function rg_save_recreation( $post_id, $post ) {

	// skip if not a featured recreation
	if ( $post->post_type != 'recreation' || ! get_field( 'featured', $post_id ) ) {
		return;
	}

	// unhook the scheduled feature/unfeature actions (we'll re-add them if still valid)
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_feature', true ), 'feature_recreation', array(
		$post_id,
		'feature',
	) );
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_unfeature', true ), 'feature_recreation', array(
		$post_id,
		'unfeature',
	) );
	delete_post_meta( $post_id, 'scheduled_feature' );
	delete_post_meta( $post_id, 'scheduled_unfeature' );

	if ( $start = get_field( 'recreation_feature_start', $post_id ) ) {
		$start = strtotime( $start );
		if ( $start < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', true );
		} else {
			update_post_meta( $post_id, 'featured', false );
			// schedule the update
			wp_schedule_single_event( $start, 'feature_recreation', array(
				$post_id,
				'feature',
			) );
			update_post_meta( $post_id, 'scheduled_feature', $start );
		}
	}

	if ( $end = get_field( 'recreation_feature_end', $post_id ) ) {
		$end = strtotime( $end );
		if ( $end < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', false );
		} else {
			// schedule the update
			wp_schedule_single_event( $end, 'feature_recreation', array(
				$post_id,
				'unfeature',
			) );
			update_post_meta( $post_id, 'scheduled_unfeature', $end );
		}
	}
}

add_action( 'save_post', 'rg_save_recreation', 10, 2 );


// do the thingy

function rg_feature_recreation_fun( $post_id, $action ) {
	if ( $action == 'feature' ) {
		update_post_meta( $post_id, '_featured', true );
		delete_post_meta( $post_id, 'scheduled_feature' );
	} else {
		update_post_meta( $post_id, '_featured', false );
		delete_post_meta( $post_id, 'scheduled_unfeature' );
	}
}

add_action( 'feature_recreation', 'rg_feature_recreation_fun' );
