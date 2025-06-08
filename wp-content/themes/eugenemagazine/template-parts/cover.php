<?php

//$id = get_option('page_on_front');

$cover = em_get_cover_header();

$classes = array();
$classes[] = 'cover-header';
$classes[] = $cover['is_front'] ? 'cover-front' : 'cover-notfront';
$classes[] = $cover['image'] ? 'has-cover-photo' : 'no-cover-photo';
$classes[] = ($cover['iconcolor'] == 'light') ? 'light-photo' : 'dark-photo';

$bg_tag = '';
if ( $cover['image'] ) {
	$id = is_array($cover['image']) ? $cover['image']['ID'] : (int) $cover['image'];
	$url = $id ? wp_get_attachment_image_url( $id, 'large' ) : false;
	if ( $url ) $bg_tag = 'style="background-image: url('. esc_attr($url) .');"';
}

$logo_id = '';
if ( $cover['logo']['image'] ) {
	$logo_id = is_array($cover['logo']['image']) ? $cover['logo']['image']['ID'] : (int) $cover['logo']['image'];
}

$mobile_bg_tag = false;
if ( ! empty( $cover['mobile_image']) ) {
	$id = is_array($cover['mobile_image']) ? $cover['mobile_image']['ID'] : (int) $cover['mobile_image'];
	$url = $id ? wp_get_attachment_image_url( $id, 'mobile-alt' ) : false;
	if ( $url ) $mobile_bg_tag = 'style="background-image: url('. esc_attr($url) .');"';
}

?>

<header id="header" class="<?php echo implode(' ', $classes); ?>">
	<div class="cover-image" <?php echo $bg_tag; ?>>
		
		<?php if ( $mobile_bg_tag ) echo '<div class="cover-image-mobile-alt" '. $mobile_bg_tag.'>'; ?>
		
		<div class="inside narrow clearfix">
			
			<?php get_template_part( 'template-parts/menu-button' ); ?>
			
			<div class="cover-inside">
				
				<?php if ( $logo_id ) { ?>
					<div class="cover-logo">
						<a href="/"><?php echo wp_get_attachment_image($logo_id, 'full'); ?></a>
					</div>
				<?php }else{ ?>
					<div class="eugmaglogo"><a href="/"><?php bloginfo( 'title' ); ?></a></div>
				<?php } ?>
				
			</div>
			
		</div>
		</div>
	
		<?php if ( $mobile_bg_tag) echo '</div>'; ?>
	
	</div>
</header> <!-- /#header -->