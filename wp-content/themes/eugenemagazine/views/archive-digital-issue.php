<?php
$post_id = get_the_ID();
$cover_image_id = get_field( 'cover_image', $post_id );
$magazine_url = get_field( 'magazine_url', $post_id );
if ( !$magazine_url ) $magazine_url = get_permalink($post_id);
?>
<article <?php post_class('issue'); ?>>

	<div class="issue-cover">
		
		<a href="<?php echo esc_attr($magazine_url); ?>" target="_blank">
			<?php echo wp_get_attachment_image($cover_image_id, 'full'); ?>
		</a>
		
		<?php the_title( '<h3><a href="' . esc_url( $magazine_url ) . '" target="_blank">', '</a></h3>' ); ?>
		
	</div>
	
</article>