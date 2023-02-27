<?php

$cover = array(
	'logo' => array(
		'image' => get_field( 'cover-logo' ),
		'align' => get_field( 'logo-position' ),
	),

	'image' => get_field( 'cover-image' ),
	'mobile_image' => false,
	'align' => strtolower( get_field( 'cover-position' ) ),

	'title' => array(
		'text' => get_field( 'cover-title' ),
		'color' => get_field( 'cover-title-color' ),
		'align' => strtolower( get_field( 'cover-title-align' ) ),
	),

	'subtitle' => array(
		'text' => get_field( 'cover-subtitle' ),
		'color' => get_field( 'cover-subtitle-color' ),
		'align' => strtolower( get_field( 'cover-subtitle-align' ) ),
	),

	'button' => array(
		'data' => get_field( 'cover-button' ),
		'background' => get_field( 'cover-button-bg-color' ),
		'color' => get_field( 'cover-button-text-color' ),
		'align' => strtolower( get_field( 'cover-button-align' ) ),
	),

	'iconcolor' => get_field( 'cover-icon-color' )
);

// Convert cover image into an inline CSS background property
if ( $cover['image'] ) {
	$i = wp_get_attachment_image_src($cover['image'], 'large');
	if ( $i ) {
		if ( $m = ld_get_attachment_mobile( $cover['image'] ) ) {
			$cover['mobile_image'] = sprintf( 'style="background-image: url(%s);"', esc_attr($m[0]) );
		}

		$cover['image'] = sprintf( 'style="background-image: url(%s);"', esc_attr($i[0]) );
	}
}

if ( !$cover['logo']['align'] ) $cover['logo']['align'] = 'center';

// Convert cover logo into html img element
if ( $cover['logo']['image'] ) {
	$i = wp_get_attachment_image_src($cover['logo']['image'], 'full');
	if ( $i ) {
		$cover['logo']['image'] = sprintf( '<img src="%s" alt="%s" width="%s" height="%s" />', esc_attr($i[0]), esc_attr(smart_media_alt($i[0])), (int) $i[1], (int) $i[2] );
	}
}

// Split button URL / Text out of their initial array
if ( !empty($cover['button']['data'][0]['url']) ) {
	$cover['button']['url']  = $cover['button']['data'][0]['url'];
	$cover['button']['label'] = $cover['button']['data'][0]['label'] ? $cover['button']['data'][0]['label'] : 'Learn More';
    unset($cover['button']['data']);
}else{
	$cover['button'] = false;
}

?>
<header id="header" class="cover-header cover-front <?php echo ($cover['iconcolor']=='light') ? 'light-photo' : 'dark-photo'; ?>">
	<div class="cover-image <?php echo $cover['mobile_image'] ? "with-mobile-alt" : "no-mobile-alt"; ?>" <?php if ( $cover['image'] ) echo $cover['image']; ?>>
		
		<?php // if ( $cover['mobile_image'] ) echo '<div class="cover-image-mobile-alt" '. $cover['mobile_image'].'>'; ?>

		<div class="inside narrow clearfix">

            <?php get_template_part( 'template-parts/menu-button' ); ?>

			<div class="cover-inside first-header-post">
				<?php if ( $cover['logo']['image'] ) { ?>
					<div class="cover-logo logo-align-<?php echo strtolower($cover['logo']['align']); ?>">
						<?php echo $cover['logo']['image']; ?>
					</div>
				<?php } ?>

				<?php if ( $cover['title']['text'] || $cover['subtitle']['text'] || $cover['button'] ) { ?>
					<div class="cover-panel align-<?php echo $cover['align'] ? esc_attr($cover['align']) : 'right'; ?>">

						<?php if ( $cover['title']['text'] ) { ?>
							<div class="cover-title align-<?php echo $cover['title']['align'] ? esc_attr($cover['title']['align']) : 'left'; ?>">
								<h2 <?php if ( $cover['title']['color'] ) echo 'style="color: '.esc_attr($cover['title']['color']).'"'; ?>><?php echo $cover['title']['text']; ?></h2>
							</div>
						<?php } ?>

						<?php if ( $cover['subtitle']['text'] ) { ?>
							<div class="cover-subtitle align-<?php echo $cover['subtitle']['align'] ? esc_attr($cover['subtitle']['align']) : 'right'; ?>">
								<h3 <?php if ( $cover['subtitle']['color'] ) echo 'style="color: '.esc_attr($cover['subtitle']['color']).'"'; ?>><?php echo $cover['subtitle']['text']; ?></h3>
							</div>
						<?php } ?>

						<?php if ( $cover['button'] ) { ?>
							<div class="cover-button align-<?php echo $cover['button']['align'] ? esc_attr($cover['button']['align']) : 'right'; ?>">
								<a href="<?php echo esc_attr($cover['button']['url']); ?>" class="button" style="background-color: <?php echo esc_attr($cover['button']['background']); ?>; color: <?php echo esc_attr($cover['button']['color']); ?>;"><?php echo $cover['button']['label']; ?></a>
							</div>
						<?php } ?>

					</div>
				<?php } ?>
			</div>
		</div>

		 <?php // if ( $cover['mobile_image']) echo '</div>'; ?>

	</div>
</header> <!-- /#header -->
