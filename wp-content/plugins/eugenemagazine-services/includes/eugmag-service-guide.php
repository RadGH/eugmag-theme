<?php

// load template for single service posts
function rg_service_template( $single ) {
	global $post;
	if ( $post->post_type == "service" ) {
		wp_enqueue_style( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.css' );
		wp_enqueue_script( 'flickity', get_template_directory_uri() . '/includes/libraries/flickity/flickity.pkgd.min.js', array( 'jquery' ), null, true );
		if ( file_exists( EUGMAG_SERVICES_PATH . '/assets/single-service.php' ) ) {
			return EUGMAG_SERVICES_PATH . '/assets/single-service.php';
		}
	}

	return $single;
}

add_filter( 'single_template', 'rg_service_template' );


// add custom template to Page Attributes > Template dropdown
function eugmag_services_add_template_to_select( $post_templates ) {
	$post_templates['page-service-guide.php'] = 'Services Guide';

	return $post_templates;
}

add_filter( 'theme_page_templates', 'eugmag_services_add_template_to_select' );


// load custom template when selected for a page
function eugmag_services_page_template( $page_template ) {
	if ( get_page_template_slug() == 'page-service-guide.php' && file_exists( EUGMAG_SERVICES_PATH . '/assets/page-service-guide.php' ) ) {
		$page_template = EUGMAG_SERVICES_PATH . '/assets/page-service-guide.php';
	}

	return $page_template;
}

add_filter( 'page_template', 'eugmag_services_page_template' );


// shortcode to output service guide page (incl. map and paginated list)

function rg_service_guide_shortcode() {
	ob_start();

	// OUTPUT MAP WITH PINS

	$args = array(
		'post_type'      => 'service',
		'posts_per_page' => - 1,
		'no_found_rows'  => true,
	);

	if ( ! empty( $_REQUEST['search'] ) ) {
		// doing a search: don't use the transient
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => 'service_description',
				'value'   => $search,
				'compare' => 'LIKE'

			);
		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;

		$servicePins = new WP_Query( $args );
	} else {
		// not doing a search: use the transient
		$servicePins = get_transient( 'servicePins' );

		if ( ! $servicePins ) {
			$servicePins = new WP_Query( $args );
			set_transient( 'servicePins', $servicePins, 60 * 60 * 24 );
		}
	}

	if ( $servicePins ) {
		?>
		<div id="service_guide_map" class="service_guide_map"></div>
		<div id="service_map_markers">
			<?php
			while ( $servicePins->have_posts() ) {
				$servicePins->the_post();
				if ( $map = get_field( 'service_gmaps' ) ) {
					$serviceTypeList = '';
					if ( $getTerms = get_the_terms( get_the_ID(), 'service_type' ) ) {
						foreach ( $getTerms as $term ) {
							$serviceTypeList .= ' ' . $term->term_id;
						}
						$serviceTypeList = trim( $serviceTypeList );
					}
					echo '<div class="marker" title="' . esc_attr( get_the_title() ) . '" data-serviceTypes="' . esc_attr( $serviceTypeList ) . '" data-lat="' . $map['lat'] . '" data-lng="' . $map['lng'] . '">';
					echo '<div class="marker-title">' . esc_attr( get_the_title() ) . '</div>';
					echo '<p class="marker-desc">' . esc_attr( get_field( 'service_description' ) ) . '</p>';
					echo '<p class="marker-more"><a href="' . get_post_permalink() . '">More info &raquo;</a></p>';
					echo '</div>';
				}
			}
			?>
		</div>
		<?php
	}

	// OUTPUT PAGINATED service LIST
	echo '<div id="service-guide-list-wrapper">', rg_paginated_service_guide(), '</div>';
	wp_reset_postdata();
	?>
	<script>
		const service_guide_url = <?php echo json_encode( get_the_permalink() ); ?>
	</script>
	<?php

	echo ob_get_clean();
}

if ( ! is_admin() ) {
	add_shortcode( 'services_guide', 'rg_service_guide_shortcode' );
}


// update service list
function rg_clear_service_pin_cache( $post_id ) {
	if ( get_post_type( $post_id ) != "service" ) {
		return;
	}
	delete_transient( 'servicePins' );
}

add_action( 'save_post', 'rg_clear_service_pin_cache' );
add_action( 'trash_post', 'rg_clear_service_pin_cache' );


// paginated service guide list

function rg_paginated_service_guide_query( $page_number, $servicetype ) {

	$args = array(
		'post_type'      => 'service',
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

	if ( ! $servicetype && isset( $_REQUEST['service_type'] ) ) {
		$servicetype = intval( $_REQUEST['service_type'] );
	}

	if ( $servicetype ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'service_type',
				'terms'    => (int) $servicetype,
			),
		);
	}

	// search
	if ( ! empty( $_REQUEST['search'] ) ) {
		$search                                = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
		$args["meta_query"]["search_clause"][] =
			array(
				'key'     => 'service_description',
				'value'   => $search,
				'compare' => 'LIKE'

			);

		// see eugmag_search_titles_and_meta_keys()
		$args['_meta_or_title'] = $search;
	}

	return new WP_Query( $args );
}

// https://wordpress.stackexchange.com/a/208939
function eugmag_services_search_titles_and_meta_keys( $q ) {
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

add_action( 'pre_get_posts', 'eugmag_services_search_titles_and_meta_keys' );


function rg_paginated_service_guide( $page_number = null, $servicetype = 0 ) {

	if ( $page_number === null ) {
		$page_number = get_query_var( 'paged' );
	}

	if ( $page_number < 1 ) {
		$page_number = 1;
	}

	$query = rg_paginated_service_guide_query( $page_number, $servicetype );

	ob_start();

	if ( $query->have_posts() ) :
		?>
		<ul class="service-guide-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();

				if ( get_post_meta( get_the_ID(), 'featured', true ) ) {
					echo '<li class="service-guide-featured-li">';
					echo '<div class="service-guide-featured">Featured</div>';
				} else {
					echo '<li>';
				}
				echo '<div class="service-guide-title"><a href="', get_post_permalink(), '">', get_the_title(), '</a></div>';


				echo '<div class="service-guide-meta">';
				$meta   = array();
				$meta[] = esc_html( get_field( 'service_price' ) );
				if ( $tax_terms = strip_tags( get_the_term_list( get_the_ID(), 'service_type', '', ', ', '' ) ) ) {
					$meta[] = esc_html( $tax_terms );
				}
				$meta[] = esc_html( get_field( 'service_address' ) );
				$meta[] = esc_html( get_field( 'service_phone' ) );
				echo implode( ' &bull; ', array_filter( $meta ) );
				echo '</div>';


				echo '<div class="service-guide-desc">', esc_html( get_field( 'service_description' ) ), ' <a class="service-guide-more" href="' . get_post_permalink() . '">More info &raquo;</a></div>';
				echo '</li>';
			endwhile;
			?>
		</ul>
		<?php

		if ( $query->max_num_pages > 1 ) :
			$servicetypeBase = $servicetype ? '?service=' . $servicetype : '';
			echo '<div id="service-guide-pagination" class="pagination clear">';
			echo paginate_links( array(
				'base'     => home_url() . '/services-guide/page/%#%/' . $servicetypeBase,
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


// outputs results from a given page of the service guide
function rg_ajax_paginated_service_guide() {
	$page = (int) $_POST['page'];

	$servicetype = 0;
	if ( isset( $_POST['servicetype'] ) ) {
		$servicetype = (int) $_POST['servicetype'];
	}
	echo rg_paginated_service_guide( $page, $servicetype );
	wp_die();
}

add_action( 'wp_ajax_nopriv_paginated_service_guide', 'rg_ajax_paginated_service_guide' );
add_action( 'wp_ajax_paginated_service_guide', 'rg_ajax_paginated_service_guide' );


// scheduled thingy

function rg_save_service( $post_id, $post ) {

	// skip if not a featured service
	if ( $post->post_type != 'service' || ! get_field( 'featured', $post_id ) ) {
		return;
	}

	// unhook the scheduled feature/unfeature actions (we'll re-add them if still valid)
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_feature', true ), 'feature_service', array(
		$post_id,
		'feature',
	) );
	wp_unschedule_event( get_post_meta( $post_id, 'scheduled_unfeature', true ), 'feature_service', array(
		$post_id,
		'unfeature',
	) );
	delete_post_meta( $post_id, 'scheduled_feature' );
	delete_post_meta( $post_id, 'scheduled_unfeature' );

	if ( $start = get_field( 'service_feature_start', $post_id ) ) {
		$start = strtotime( $start );
		if ( $start < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', true );
		} else {
			update_post_meta( $post_id, 'featured', false );
			// schedule the update
			wp_schedule_single_event( $start, 'feature_service', array(
				$post_id,
				'feature',
			) );
			update_post_meta( $post_id, 'scheduled_feature', $start );
		}
	}

	if ( $end = get_field( 'service_feature_end', $post_id ) ) {
		$end = strtotime( $end );
		if ( $end < time() ) {
			// update now
			update_post_meta( $post_id, 'featured', false );
		} else {
			// schedule the update
			wp_schedule_single_event( $end, 'feature_service', array(
				$post_id,
				'unfeature',
			) );
			update_post_meta( $post_id, 'scheduled_unfeature', $end );
		}
	}
}

add_action( 'save_post', 'rg_save_service', 10, 2 );


// do the thingy

function rg_feature_service_fun( $post_id, $action ) {
	if ( $action == 'feature' ) {
		update_post_meta( $post_id, '_featured', true );
		delete_post_meta( $post_id, 'scheduled_feature' );
	} else {
		update_post_meta( $post_id, '_featured', false );
		delete_post_meta( $post_id, 'scheduled_unfeature' );
	}
}

add_action( 'feature_service', 'rg_feature_service_fun' );
