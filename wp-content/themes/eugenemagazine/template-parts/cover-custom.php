<?php

$cover = em_get_cover_header();

?>
<?php
echo do_shortcode('[ad location="Before Header (full width)"]');
?>
<header id="header" class="cover-header cover-front <?php echo ($cover['iconcolor']=='light') ? 'light-photo' : 'dark-photo'; ?>">
	<div class="cover-image <?php echo $cover['mobile_image'] ? "with-mobile-alt" : "no-mobile-alt"; ?>" <?php if ( $cover['image'] ) echo $cover['image']; ?>>
		
		<?php if ( $cover['mobile_image'] ) echo '<div class="cover-image-mobile-alt" '. $cover['mobile_image'].'>'; ?>
		
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
		
		<?php if ( $cover['mobile_image']) echo '</div>'; ?>
	
	</div>
</header> <!-- /#header -->
