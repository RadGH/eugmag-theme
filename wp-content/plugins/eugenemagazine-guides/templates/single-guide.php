<?php
$image_url = EUGMAG_GUIDES_URL . '/assets/images';

$post_type      = get_post_type();
$back_text      = match ( $post_type ) {
	'restaurant' => 'Back to Dining Guide',
	'recreation' => 'Back to Recreation Guide',
	'retailer' => 'Back to Retail Guide',
	'service' => 'Back to Service Guide',
};
$back_link      = match ( $post_type ) {
	'restaurant' => '/dining-guide/',
	'recreation' => '/recreation-guide/',
	'retailer' => '/retail-guide/',
	'service' => '/services-guide/',
};
$category_icon  = match ( $post_type ) {
	'restaurant' => $image_url . '/category-dining.png',
	'recreation' => $image_url . '/category-recreation.png',
	'retailer' => $image_url . '/category-retail.png',
	'service' => $image_url . '/category-services.png',
};
$category_terms = match ( $post_type ) {
	'restaurant' => wp_get_post_terms( get_the_ID(), 'food_type' ),
	'recreation' => wp_get_post_terms( get_the_ID(), 'activity' ),
	'retailer' => wp_get_post_terms( get_the_ID(), 'retail_type' ),
	'service' => wp_get_post_terms( get_the_ID(), 'service_type' ),
};
$category_label = match ( $post_type ) {
	'restaurant' => 'Type of Cuisine',
	'recreation' => 'Recreation Types',
	'retailer' => 'Retail Types',
	'service' => 'Service Types',
};

$is_featured   = (bool) get_field( 'featured' );
$ad_sidebar    = $is_featured ? get_field( $post_type . '_ad_sidebar' ) : false;
$reel_type     = $is_featured ? get_field( 'reel_type' ) : false;
$youtube_embed = $reel_type === 'youtube' ? get_field( 'reel_youtube' ) : false;
$video_upload  = $reel_type === 'upload' ? get_field( 'reel_upload' ) : false;

$gallery_images = $is_featured ? get_field( $post_type . '_gallery' ) : false;
$has_sidebar    = $is_featured && ( ! empty( $ad_sidebar ) || ! empty( $youtube_embed ) || ! empty( $video_upload ) || ! empty( $gallery_images ) );

// Converts a formatted phone number into a tel: link format (e.g. (541) 123-4567 -> 5411234567)
function get_phone_link( $phone_number, $display = null ) {
	if ( $display === null ) {
		$display = $phone_number;
	}

	$digits = preg_replace( '/\D+/', '', $phone_number );

	// Add US country code if missing
	if ( strlen( $digits ) === 10 ) {
		$digits = '1' . $digits;
	}

	return sprintf(
		'<a href="tel:+%s">%s</a>',
		esc_attr( $digits ),
		esc_html( $display )
	);
}

function get_address_links() {
	$addresses = array();
	if ( get_field( 'addresses' ) ) {
		$i = 0;
		while ( $address = get_field( 'addresses_' . $i . '_address' ) ) {
			$addresses[] = trim( $address );
			$i ++;
		}
	}
	$address_links = array_map(
		static function( $address ) {
			$maps_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $address );

			return sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $maps_url ),
				esc_html( $address )
			);
		},
		$addresses
	);

	return $address_links;
}

function get_website_link( $post_type ) {
	$website_link = trim( get_field( $post_type . '_website' ) ?: '' );

	if ( $website_link ) {
		$link_text = preg_replace( '/^https?:\/\//i', '', $website_link );
		$link_text = preg_replace( '/^www\./i', '', $link_text );
		$link_text = rtrim( $link_text, '/' );

		$website_link = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $website_link ),
			esc_html( $link_text )
		);
	}

	return $website_link;
}

get_header();
?>

	<div id="content" class="clearfix">
		<div class="inside">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( "template-parts/cover" );
				?>
				<div class="inside">
					<article <?php post_class( 'loop-single single-guide' ); ?>>
						<div class="breadcrumbs">
							<a href="<?php echo esc_attr( $back_link ) ?>">&larr; <?php echo $back_text ?></a>
						</div>
						<div class="single-guide-body loop-body">
							<div class="single-guide-content loop-content">
								<div class="loop-header">
									<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
								</div>
								<?php
								if ( $is_featured ) {
									echo '<div class="guide_featured">';
									echo '<img src="', esc_attr( $image_url . '/featured-badge.png' ), '" alt="Featured">';
									echo '</div>';
								}
								if ( $logo = get_field( $post_type . '_logo' ) ) {
									if ( $logo_src = $logo['sizes']['woocommerce_single'] ) {
										?>
										<div class="guide_logo">
											<img
												src="<?php echo esc_attr( $logo_src ) ?>"
												width="<?php echo esc_attr( $logo['sizes']['woocommerce_single-width'] ) ?>"
												height="<?php echo esc_attr( $logo['sizes']['woocommerce_single-height'] ) ?>"
												alt="logo"/>
										</div>
										<?php
									}
								}
								if ( $description = get_field( $post_type . '_description' ) ) {
									echo '<div class="guide_description">', wpautop( make_clickable( $description ) ), '</div>';
								}
								if ( $hours = get_field( $post_type . '_hours' ) ) {
									echo '<div class="guide_meta_item guide_hours"><div class="guide_meta_title">Hours:</div><div class="guide_meta_content">', $hours, '</div></div>';
								}
								if ( $price = get_field( $post_type . '_price' ) ) {
									// restaurant/retailer only
									echo '<div class="guide_meta_item guide_price"><div class="guide_meta_title">Price Scale:</div><div class="guide_meta_content">', $price, '</div></div>';
								}
								if ( $meals_served = get_field( $post_type . '_meals_served' ) ) {
									// restaurant only
									echo '<div class="guide_meta_item guide_meals_served"><div class="guide_meta_title">Meals Served:</div><div class="guide_meta_content">', $meals_served, '</div></div>';
								}
								if ( $info = get_field( $post_type . '_info' ) ) {
									echo '<div class="guide_meta_item guide_info"><div class="guide_meta_title">Important Info:</div><div class="guide_meta_content">', $info, '</div></div>';
								}

								$phone         = trim( get_field( $post_type . '_phone' ) ?: '' );
								$website       = get_website_link( $post_type );
								$email         = trim( get_field( $post_type . '_email' ) ?: '' );
								$address_links = get_address_links();

								if ( $phone || $website || $email || count( $address_links ) || ! empty( $category_terms ) ) {
									echo '<div class="guide_icons_list">';
									if ( $address_links ) {
										echo '<div class="guide_icon_item guide_address">';
										echo '<img class="guide_icon_image" src="', esc_attr( $image_url . '/icon-location.png' ), '" alt="Address">';
										echo '<div class="guide_icon_content">';
										echo implode( '<br>', $address_links );
										echo '</div>';
										echo '</div>';
									}
									if ( $phone ) {
										echo '<div class="guide_icon_item guide_phone">';
										echo '<img class="guide_icon_image" src="', esc_attr( $image_url . '/icon-phone.png' ), '" alt="Phone">';
										echo '<div class="guide_icon_content">', get_phone_link( $phone ), '</div>';
										echo '</div>';
									}
									if ( $website ) {
										echo '<div class="guide_icon_item guide_website">';
										echo '<img class="guide_icon_image" src="', esc_attr( $image_url . '/icon-website.png' ), '" alt="Website">';
										echo '<div class="guide_icon_content">', $website, '</div>';
										echo '</div>';
									}
									if ( $email ) {
										echo '<div class="guide_icon_item guide_email">';
										echo '<img class="guide_icon_image" src="', esc_attr( $image_url . '/icon-email.png' ), '" alt="Email">';
										echo '<div class="guide_icon_content"><a href="mailto:', esc_attr( $email ), '" target="_blank">', $email, '</a></div>';
										echo '</div>';
									}
									if ( ! empty( $category_terms ) ) {
										$category_names = array();
										foreach ( $category_terms as $term ) {
											$category_names[] = '<a href="' . esc_attr( $back_link ) . '?term=' . rawurlencode( $term->name ) . '">' . $term->name . '</a>';
										}
										echo '<div class="guide_icon_item guide_categories">';
										echo '<img class="guide_icon_image" src="', esc_attr( $category_icon ), '" alt="', esc_attr( $category_label ), '">';
										echo '<div class="guide_icon_content">', implode( ', ', $category_names ), '</div>';
										echo '</div>';
									}
									echo '</div>';
								}

								$facebook  = get_field( $post_type . '_facebook' );
								$instagram = get_field( $post_type . '_instagram' );
								$twitter   = get_field( $post_type . '_twitter' );

								if ( $facebook || $instagram || $twitter ) {
									echo '<div class="guide_socmed">';
									echo $facebook ? '<a class="guide_fb" href="' . esc_attr( $facebook ) . '">Facebook</a>' : '';
									echo $instagram ? '<a class="guide_instagram" href="' . esc_attr( $instagram ) . '">Instagram</a> ' : '';
									echo $twitter ? '<a class="guide_twitter" href="' . esc_attr( $twitter ) . '">Twitter</a>' : '';
									echo '</div>';
								}
								?>
							</div>
							<?php if ( $has_sidebar ) : ?>
								<div class="single-guide-sidebar">
									<?php
									if ( $ad_sidebar ) {
										echo do_shortcode( '[ad location="' . esc_attr( $ad_sidebar ) . '"]' );
									}
									if ( $youtube_embed ) {
										echo $youtube_embed;
									} elseif ( $video_upload ) {
										?>
										<div class="reel-player">
											<video width="<?php echo $video_upload['width'] ?>" height="<?php echo $video_upload['height'] ?>" preload="metadata" playsinline>
												<source src="<?php echo $video_upload['url']; ?>" type="video/mp4">
												Your browser does not support the video tag.
											</video>
											<div class="reel-player__overlay">
												Watch Reel
											</div>
										</div>
										<?php
									}
									if ( $gallery_images ) : ?>
										<div class="guide_gallery">
											<?php foreach ( $gallery_images as $image ) : ?>
												<div class="carousel-cell">
													<a href="<?php echo esc_attr( $image['url'] ); ?>" target="_blank">
														<img
															src="<?php echo esc_attr( $image['sizes']['thumbnail'] ); ?>"
															width="<?php echo $image['sizes']['thumbnail-width'] ?>"
															height="<?php echo $image['sizes']['thumbnail-height'] ?>"
															alt="<?php echo $image['alt'] ?>"/>
													</a>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div><!-- .loop-body-->
					</article>
				</div>
			<?php endwhile; ?>
		</div> <!-- /.inside -->
	</div> <!-- /#content -->
<?php
get_footer();
