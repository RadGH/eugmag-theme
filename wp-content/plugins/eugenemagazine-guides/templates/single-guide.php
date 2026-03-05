<?php

if ( ! isset($args) ) {
	wp_die( 'No guide data provided, $args is empty' );
	exit;
}

$image_url = EUGMAG_GUIDES_URL . '/assets';

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
					
					<div class="loop-header">
						
						<?php
						if ( ! empty( $args['back_link'] ) ) {
							$back_text = $args['back_link_text'] ?? '&larr; Go back';
							echo '<div class="breadcrumbs"><a href="' . esc_attr( $args['back_link'] ) . '">' . $back_text . '</a></div>';
						}
						?>
						
						<?php /*
						<div class="social-sharing">
							Share this page: <?php echo implode( generate_sharing_links() ); ?>
						</div>
 						*/ ?>
						
						<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
					
					</div>
					
					<div class="single-guide-body loop-body">
						
						<div class="single-guide-sidebar">
							
							<?php
							if ( !empty( $args['ad_sidebar'] ) ) {
								echo do_shortcode( '[ad location="' . esc_attr( $args['ad_sidebar'] ) . '"]' );
							}
							?>
							
							<?php
							switch( get_post_type() ) {
								case 'restaurant':
									$icon_url = $image_url . '/images/art-dining.png';
									break;
								case 'recreation':
									$icon_url = $image_url . '/images/art-recreation.png';
									break;
								case 'retailer':
									$icon_url = $image_url . '/images/art-retail.png';
									break;
								case 'service':
								default:
									$icon_url = $image_url . '/images/art-services.png';
									break;
							}
							echo '<div class="guide_category_art">';
							echo '<img src="', esc_attr($icon_url), '" alt="">';
							echo '</div>';
							?>
							
							<?php
							$gallery_image_ids = $args['gallery_image_ids'] ?? false;
							if ( !empty( $gallery_image_ids ) ) {
								?>
								<div class="guide_gallery">
									<?php foreach ( $gallery_image_ids as $image_id ) :
										$img_title = get_the_title( $image_id );
										$img_url = wp_get_attachment_url( $image_id );
										$thumbnail = wp_get_attachment_image_src( $image_id, 'thumbnail' );
										?>
										<div class="carousel-cell">
											<a href="<?php echo esc_attr( $img_url ); ?>"><img src="<?php echo esc_attr( $thumbnail[0] ?: $img_url ); ?>" alt="<?php echo $img_title; ?>" /></a>
										</div>
									<?php endforeach; ?>
								</div>
								<?php
							}
							?>
							
							<?php
							$gmaps = $args['gmaps'] ?? false;
							
							// MAP
							if ( $gmaps && isset($gmaps['lat']) && isset($gmaps['lng']) ) {
								?>
								<div id="guide_map" class="retail_map" data-markerdir="<?php echo get_template_directory_uri() . '/img/markers/'; ?>">
									<div class="marker" data-lat="<?php echo esc_attr( $gmaps['lat'] ); ?>" data-lng="<?php echo esc_attr( $gmaps['lng'] ); ?>" title="<?php the_title(); ?>"></div>
								</div>
								<?php
							}
							?>
							
						</div>
						
						<div class="single-guide-content loop-content">
							
							<?php
							$logo_id = $args['logo_id'] ?? false;
							if ( $logo_id ) {
								echo '<img src="', esc_attr( wp_get_attachment_image_url( $logo_id, 'thumbnail' ) ), '" alt="logo" />';
							}
							?>
							
							<?php
							$description = $args['description'] ?? false;
							
							if ( $description ) {
								echo '<div class="guide_description">', wpautop($description), '</div>';
							}
							?>
							
							<?php
							$hours = $args['hours'] ?? false;
							$price = $args['price'] ?? false;
							$meals_served = $args['meals_served'] ?? false;
							$info = $args['info'] ?? false;
							
							if ( $hours ) {
								echo '<div class="guide_meta_item guide_hours"><div class="guide_meta_title">Hours:</div><div class="guide_meta_content">', $hours, '</div></div>';
							}
							if ( $price ) {
								echo '<div class="guide_meta_item guide_price"><div class="guide_meta_title">Price Scale:</div><div class="guide_meta_content">', $price, '</div></div>';
							}
							if ( $meals_served ) {
								echo '<div class="guide_meta_item guide_meals_served"><div class="guide_meta_title">Meals Served:</div><div class="guide_meta_content">', $meals_served, '</div></div>';
							}
							if ( $info ) {
								echo '<div class="guide_meta_item guide_info"><div class="guide_meta_title">Important Info:</div><div class="guide_meta_content">', $info, '</div></div>';
							}
							?>
							
							<?php
							$phone = $args['phone'] ?? false;
							$website = $args['website'] ?? false;
							$address = $args['address'] ?? false;
							
							$category_terms = $args['category_terms'] ?? false;
							$category_label = $args['category_label'] ?? 'Categories';
							
							if ( $address || $phone || $website || !empty($category_terms) ) {
								echo '<div class="guide_icons_list">';
								if ( $address ) {
									echo '<div class="guide_icon_item guide_address">';
									echo '<img class="guide_icon_image" src="', esc_attr($image_url . '/images/icon-location.png'), '" alt="Address">';
									echo '<div class="guide_icon_content">', $address, '</div>';
									echo '</div>';
								}
								if ( $phone ) {
									echo '<div class="guide_icon_item guide_phone">';
									echo '<img class="guide_icon_image" src="', esc_attr($image_url . '/images/icon-phone.png'), '" alt="Phone">';
									echo '<div class="guide_icon_content">', EM_Guides_Template::get_phone_link( $phone ), '</div>';
									echo '</div>';
								}
								if ( $website ) {
									echo '<div class="guide_icon_item guide_website">';
									echo '<img class="guide_icon_image" src="', esc_attr($image_url . '/images/icon-website.png'), '" alt="Website">';
									echo '<div class="guide_icon_content"><a href="', esc_url( $website ), '" target="_blank">', $website, '</a></div>';
									echo '</div>';
								}
								if ( ! empty($category_terms) ) {
									$category_names = array();
									foreach ( $category_terms as $term ) {
										$category_names[] = $term->name;
									}
									switch( get_post_type() ) {
										case 'restaurant':
											$icon_url = $image_url . '/images/category-dining.png';
											break;
										case 'recreation':
											$icon_url = $image_url . '/images/category-recreation.png';
											break;
										case 'retailer':
											$icon_url = $image_url . '/images/category-retail.png';
											break;
										case 'service':
										default:
											$icon_url = $image_url . '/images/category-services.png';
											break;
									}
									echo '<div class="guide_icon_item guide_categories">';
									echo '<img class="guide_icon_image" src="', esc_attr($icon_url), '" alt="', esc_attr($category_label), '">';
									echo '<div class="guide_icon_content">', implode( ', ', $category_names ), '</div>';
									echo '</div>';
								}
								echo '</div>';
							}
							?>
							
							<?php
							$facebook = $args['facebook'] ?? false;
							$instagram = $args['instagram'] ?? false;
							$twitter = $args['twitter'] ?? false;
							
							if ( $facebook || $instagram || $twitter ) {
								echo '<div class="guide_socmed">';
								if ( $facebook ) {
									echo '<a class="guide_fb" href="', esc_attr( $facebook ), '">Facebook</a> ';
								}
								if ( $instagram ) {
									echo '<a class="guide_instagram"a href="', esc_attr( $instagram ), '">Instagram</a> ';
								}
								if ( $twitter ) {
									echo '<a class="guide_twitter" href="', esc_attr( $twitter ), '">Twitter</a>';
								}
								echo '</div>';
							}
							?>
							
							<?php
							$featured = $args['featured'] ?? false;
							if ( $featured ) {
								echo '<div class="guide_featured">';
								echo '<img src="', esc_attr($image_url . '/images/featured-badge.png'), '" alt="Featured">';
								echo '</div>';
							}
							?>
							
							<?php
							// View args passed to the template for debugging purposes
							if ( isset($_GET['debug_guide_args']) && current_user_can('manage_options') ) {
								echo '<pre>';
								var_dump( $args );
								echo '</pre>';
							}
							?>
						
						</div><!-- .loop - content-->
					
					</div><!-- .loop - body-->
				
				</article>
			</div>
		<?php endwhile; ?>
	</div> <!-- /.inside -->
</div> <!-- /#content -->

<?php
get_footer();
