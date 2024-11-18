<article <?php post_class('loop-archive'); ?>>

	<div class="loop-header">
		<?php the_title( '<h2><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
	</div>

	<div class="loop-body">

		<div class="loop-summary">
			<?php the_excerpt(); ?>
		</div><!-- .loop-summary -->

	</div><!-- .loop-body -->

</article>