<?php

if ( ! isset($args) ) {
	wp_die( 'No guide data provided, $args is empty' );
	exit;
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
					
					<?php
					if ( ! empty( $args['back_link'] ) ) {
						$back_text = $args['back_link_text'] ?? '&larr; Go back';
						echo '<div class="breadcrumbs"><a href="' . esc_attr( $args['back_link'] ) . '">' . $back_text . '</a></div>';
					}
					?>
					
					<div class="single-guide-body loop-body">
						
						<div class="single-guide-content loop-content">
							
							<div class="loop-header">
								<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
							</div>
							
							<?php
							if ( !empty( $args['ad_sidebar'] ) ) {
								echo do_shortcode( '[ad location="' . esc_attr( $args['ad_sidebar'] ) . '"]' );
							}
							?>
							
							<?php
							$logo_id = $args['logo_id'] ?? false;
							if ( $logo_id ) {
								echo '<div class="guide_logo">';
								echo '<img src="', esc_attr( wp_get_attachment_image_url( $logo_id, 'thumbnail' ) ), '" alt="logo" />';
								echo '</div>';
							}
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
							$description = $args['description'] ?? false;
							if ( $description ) {
								echo '<div class="guide_description">', wpautop($description), '</div>';
							}
							?>
							
							<?php
							$hours        = $args['hours'] ?? false;
							$price        = $args['price'] ?? false;
							$meals_served = $args['meals_served'] ?? false;
							$info         = $args['info'] ?? false;
							$address      = $args['address'] ?? false;
							$phone        = $args['phone'] ?? false;
							$website      = $args['website'] ?? false;
							
							$category_terms = $args['category_terms'] ?? false;
							$category_label = $args['category_label'] ?? 'Categories';
							
							if ( $address ) {
								echo '<div class="guide_meta_item guide_address"><div class="guide_meta_title">Address:</div><div class="guide_meta_content">', $address, '</div></div>';
							}
							if ( $hours ) {
								echo '<div class="guide_meta_item guide_hours"><div class="guide_meta_title">Hours:</div><div class="guide_meta_content">', $hours, '</div></div>';
							}
							if ( $price ) {
								echo '<div class="guide_meta_item guide_price"><div class="guide_meta_title">Price Scale:</div><div class="guide_meta_content">', $price, '</div></div>';
							}
							if ( $meals_served ) {
								echo '<div class="guide_meta_item guide_meals_served"><div class="guide_meta_title">Meals Served:</div><div class="guide_meta_content">', $meals_served, '</div></div>';
							}
							if ( ! empty( $category_terms ) ) {
								$category_names = array();
								foreach ( $category_terms as $term ) {
									$category_names[] = $term->name;
								}
								echo '<div class="guide_meta_item guide_categories"><div class="guide_meta_title">', esc_html( $category_label ), ':</div><div class="guide_meta_content">', implode( ', ', $category_names ), '</div></div>';
							}
							if ( $info ) {
								echo '<div class="guide_meta_item guide_info"><div class="guide_meta_title">Important Info:</div><div class="guide_meta_content">', $info, '</div></div>';
							}
							if ( $phone ) {
								echo '<div class="guide_meta_item guide_phone"><div class="guide_meta_title">Phone:</div><div class="guide_meta_content">', EM_Guides_Template::get_phone_link( $phone ), '</div></div>';
							}
							if ( $website ) {
								echo '<div class="guide_meta_item guide_website"><div class="guide_meta_title">Website:</div><div class="guide_meta_content"><a href="', esc_url( $website ), '" target="_blank">', $website, '</a></div></div>';
							}
							?>
							
							<?php
							$facebook = $args['facebook'] ?? false;
							$instagram = $args['instagram'] ?? false;
							$twitter   = $args['twitter'] ?? false;
							
							if ( $facebook || $instagram || $twitter ) {
								echo '<div class="guide_socmed">';
								if ( $facebook ) {
									echo '<a class="guide_fb" href="', esc_attr( $facebook ), '">Facebook</a> ';
								}
								if ( $instagram ) {
									echo '<a class="guide_instagram" href="', esc_attr( $instagram ), '">Instagram</a> ';
								}
								if ( $twitter ) {
									echo '<a class="guide_twitter" href="', esc_attr( $twitter ), '">Twitter</a>';
								}
								echo '</div>';
							}
							?>
							
							<?php
							$gmaps = $args['gmaps'] ?? false;
							
							// MAP
							if ( $gmaps && isset($gmaps['lat']) && isset($gmaps['lng']) ) {
								?>
								<div id="guide_map" class="guide_map" data-markerdir="<?php echo get_template_directory_uri() . '/img/markers/'; ?>">
									<div class="marker" data-lat="<?php echo esc_attr( $gmaps['lat'] ); ?>" data-lng="<?php echo esc_attr( $gmaps['lng'] ); ?>" title="<?php the_title(); ?>"></div>
								</div>
								<?php
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
						
						</div><!-- .loop-content-->
					
					</div><!-- .loop-body-->
				
				</article>
			</div>
		<?php endwhile; ?>
	</div> <!-- /.inside -->
</div> <!-- /#content -->

<?php
get_footer();
