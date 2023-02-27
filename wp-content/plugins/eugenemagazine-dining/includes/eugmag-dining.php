<?php

// load template for single restaurant posts

function restaurant_template( $single ) {
	global $post;
	if ( $post->post_type == "restaurant" ) {
		wp_enqueue_style( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.css' );
		wp_enqueue_script( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.pkgd.min.js', array( 'jquery' ), null, true );
		if ( file_exists( AS_PATH . '/assets/single-restaurant.php' ) ) {
			return AS_PATH . '/assets/single-restaurant.php';
		}
	}

	return $single;
}

add_filter( 'single_template', 'restaurant_template' );


// load template for the dining guide page

function dining_guide_template( $single ) {
	global $post;
	if ( $post->ID == 292 && file_exists( AS_PATH . '/assets/page-dining-guide.php' ) ) {
		return AS_PATH . '/assets/page-dining-guide.php';
	}

	return $single;
}

add_filter( 'page_template', 'dining_guide_template' );


// shortcode to output dining guide page (incl. map and paginated list)

function dining_guide_shortcode() {
	ob_start();

	// OUTPUT MAP WITH RESTAURANT PINS

	$args = array(
		'post_type'      => 'restaurant',
		'posts_per_page' => - 1,
		'no_found_rows'  => true,
	);

	if ( ! empty( $_REQUEST['search'] ) ) {
		// doing a search: don't use the transient
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => 'restaurant_description',
				'value'   => $search,
				'compare' => 'LIKE'

			);
		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;

		$restaurantPins = new WP_Query( $args );
	} else {
		// not doing a search: use the transient
		$restaurantPins = get_transient( 'restaurantPins' );

		if ( ! $restaurantPins ) {
			$restaurantPins = new WP_Query( $args );
			set_transient( 'restaurantPins', $restaurantPins, 60 * 60 * 24 );
		}
	}

	if ( $restaurantPins ) {
		echo '<div id="rest_map" class="rest_map">';
		while ( $restaurantPins->have_posts() ) {
			$restaurantPins->the_post();
			if ( $map = get_field( 'restaurant_gmaps' ) ) {
				$neighborhoodList = '';
				if ( $getTerms = get_the_terms( get_the_ID(), 'neighborhood' ) ) {
					foreach ( $getTerms as $term ) {
						$neighborhoodList .= ' ' . $term->term_id;
					}
					$neighborhoodList = trim( $neighborhoodList );
				}
				$foodTypeList = '';
				if ( $getTerms = get_the_terms( get_the_ID(), 'food_type' ) ) {
					foreach ( $getTerms as $term ) {
						$foodTypeList .= ' ' . $term->term_id;
					}
					$foodTypeList = trim( $foodTypeList );
				}
				echo '<div class="marker" title="' . esc_attr( get_the_title() ) . '" data-neighborhoods="' . esc_attr( $neighborhoodList ) . '" data-foodTypes="' . esc_attr( $foodTypeList ) . '" data-id="' . get_the_ID() . '" data-lat="' . $map['lat'] . '" data-lng="' . $map['lng'] . '">';
				echo '<div class="marker-title">' . esc_attr( get_the_title() ) . '</div>';
				echo '<p class="marker-desc">' . esc_attr( get_field( 'restaurant_description' ) ) . '</p>';
				echo '<p class="marker-more"><a href="' . get_post_permalink() . '">More info &raquo;</a></p>';
				echo '</div>';
			}
		}
		echo '</div>';
	}

	// OUTPUT PAGINATED RESTAURANT LIST
	echo '<div id="dining-guide-list-wrapper">', paginated_dining_guide(), '</div>';
	wp_reset_postdata();
	?>
	<script>
		const dining_guide_url = <?php echo json_encode( get_the_permalink() ); ?>
	</script>
	<?php

	echo ob_get_clean();
}

if ( ! is_admin() ) {
	add_shortcode( 'dining_guide', 'dining_guide_shortcode' );
}


// update restaurant list
function clear_restaurant_pin_cache( $post_id ) {
	if ( get_post_type( $post_id ) != "restaurant" ) {
		return;
	}
	delete_transient( 'restaurantPins' );
}

add_action( 'save_post', 'clear_restaurant_pin_cache' );
add_action( 'trash_post', 'clear_restaurant_pin_cache' );


// paginated dining guide list

function paginated_dining_guide_query( $page_number, $foodtype ) {

	$args = array(
		'post_type'      => 'restaurant',
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

	if ( ! $foodtype && isset( $_REQUEST['food'] ) ) {
		$foodtype = intval( $_REQUEST['food'] );
	}

	if ( $foodtype ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'food_type',
				'terms'    => (int) $foodtype,
			),
		);
	}

	// search
	if ( ! empty( $_REQUEST['search'] ) ) {
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => 'restaurant_description',
				'value'   => $search,
				'compare' => 'LIKE'

			);

		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;
	}

	return new WP_Query( $args );
}

// https://wordpress.stackexchange.com/a/208939
function eugmag_search_titles_and_meta_keys( $q ) {
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

add_action( 'pre_get_posts', 'eugmag_search_titles_and_meta_keys' );


function paginated_dining_guide( $page_number = null, $foodtype = 0 ) {

	if ( $page_number === null ) {
		$page_number = get_query_var( 'paged' );
	}

	if ( $page_number < 1 ) {
		$page_number = 1;
	}

	$query = paginated_dining_guide_query( $page_number, $foodtype );

	ob_start();

	if ( $query->have_posts() ) :
		?>
		<ul class="dining-guide-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				if ( get_post_meta( get_the_ID(), 'featured', true ) ) {
					echo '<li class="dining-guide-featured-li">';
					echo '<div class="dining-guide-featured">Featured</div>';
				} else {
					echo '<li>';
				}
				echo '<div class="dining-guide-title"><a href="', get_post_permalink(), '">', get_the_title(), '</a></div>';


				echo '<div class="dining-guide-meta">';
				$meta   = array();
				$meta[] = esc_html( get_field( 'restaurant_price' ) );
				if ( $tax_terms = strip_tags( get_the_term_list( get_the_ID(), 'food_type', '', ', ', '' ) ) ) {
					$meta[] = esc_html( $tax_terms );
				}
				$meta[] = esc_html( get_field( 'restaurant_address' ) );
				$meta[] = esc_html( get_field( 'restaurant_phone' ) );
				echo implode( ' &bull; ', array_filter( $meta ) );
				echo '</div>';


				echo '<div class="dining-guide-desc">', esc_html( get_field( 'restaurant_description' ) ), ' <a class="dining-guide-more" href="' . get_post_permalink() . '">More info &raquo;</a></div>';
				echo '</li>';
			endwhile;
			?>
		</ul>
		<?php

		if ( $query->max_num_pages > 1 ) :
			$foodtypeBase = $foodtype ? '?food=' . $foodtype : '';
			echo '<div id="dining-guide-pagination" class="pagination clear">';
			echo paginate_links( array(
				'base'     => home_url() . '/dining-guide/page/%#%/' . $foodtypeBase,
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


// outputs results from a given page of the dining guide
function ajax_paginated_dining_guide() {
	$page = (int) $_POST['page'];

	$foodtype = 0;
	if ( isset( $_POST['foodtype'] ) ) {
		$foodtype = (int) $_POST['foodtype'];
	}
	echo paginated_dining_guide( $page, $foodtype );
	wp_die();
}

add_action( 'wp_ajax_nopriv_paginated_dining_guide', 'ajax_paginated_dining_guide' );
add_action( 'wp_ajax_paginated_dining_guide', 'ajax_paginated_dining_guide' );


// scheduled thingy

function save_restaurant( $post_id, $post ) {

	// skip if not a featured restaurant
	if ( $post->post_type != 'restaurant' || ! get_field( 'featured', $post_id ) ) {
		return;
	}

	// unhook the scheduled feature/unfeature actions (we'll re-add them if still valid)
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_feature', true ), 'feature_restaurant', array(
		$post_id,
		'feature',
	) );
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_unfeature', true ), 'feature_restaurant', array(
		$post_id,
		'unfeature',
	) );
	delete_post_meta( $post_id, 'scheduled_feature' );
	delete_post_meta( $post_id, 'scheduled_unfeature' );

	if ( $start = get_field( 'restaurant_feature_start', $post_id ) ) {
		$start = strtotime( $start );
		if ( $start < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', true );
		} else {
			update_post_meta( $post_id, 'featured', false );
			// schedule the update
			wp_schedule_single_event( $start, 'feature_restaurant', array(
				$post_id,
				'feature',
			) );
			update_post_meta( $post_id, 'scheduled_feature', $start );
		}
	}

	if ( $end = get_field( 'restaurant_feature_end', $post_id ) ) {
		$end = strtotime( $end );
		if ( $end < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', false );
		} else {
			// schedule the update
			wp_schedule_single_event( $end, 'feature_restaurant', array(
				$post_id,
				'unfeature',
			) );
			update_post_meta( $post_id, 'scheduled_unfeature', $end );
		}
	}
}

add_action( 'save_post', 'save_restaurant', 10, 2 );


// do the thingy

function feature_restaurant_fun( $post_id, $action ) {
	if ( $action == 'feature' ) {
		update_post_meta( $post_id, '_featured', true );
		delete_post_meta( $post_id, 'scheduled_feature' );
	} else {
		update_post_meta( $post_id, '_featured', false );
		delete_post_meta( $post_id, 'scheduled_unfeature' );
	}
}

add_action( 'feature_restaurant', 'feature_restaurant_fun' );
