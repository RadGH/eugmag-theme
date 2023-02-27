<?php

$cat      = get_the_category()[0];
$deptLink = get_post_type() == 'post' ? get_category_link( $cat->cat_ID ) : get_post_type_archive_link( 'weekender' );
$deptName = get_post_type() == 'post' ? $cat->name : 'The Weekender';

$classes   = array( 'post', 'header-post', 'first-header-post' );
$classes[] = get_field( 'photo_darkness', get_the_ID() ) == 'dark' ? 'dark-photo' : 'light-photo';

$mobile_image = false;
if ( has_post_thumbnail() && $m = ld_get_attachment_mobile( get_post_thumbnail_id() ) ) {
	$mobile_image = sprintf( 'style="background-image: url(%s);"', esc_attr( $m[0] ) );
	$classes[]    = 'with-mobile-alt';
} else {
	$classes[] = 'no-mobile-alt';
}

$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' );
?>

<header style="background-image: url(<?php echo esc_attr( $img[0] ); ?>);" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	
	<?php // if ( $mobile_image ) echo '<div class="cover-image-mobile-alt" ' . $mobile_image . '>'; ?>
	
	<div class="inside narrow">
		<?php get_template_part( 'template-parts/menu-button' ); ?>
		<div class="header-line">
			<h1 class="category-header"><a href="<?php echo esc_url( $deptLink ); ?>"><?php echo $deptName; ?></a></h1>
		</div>
		<div class="eugmaglogo"><a href="/"><?php bloginfo( 'title' ); ?></a></div>
	</div>
	
	<?php // if ( $mobile_image ) echo '</div>'; ?>

</header>

<div class="inside narrow">
	<div class="column-wrapper">
		<article class="main-column">
			<div class="post-header">
				<?php the_title( '<h2>', '</h2>' ); ?>
				<h3 class="subtitle"><?php echo esc_html( get_field( 'subtitle' ) ); ?></h3>
			</div>
			<div class="post-content">
				<div class="social-sharing">
					Share this page: <?php echo implode( generate_sharing_links() ); ?>
				</div>
				
				<?php
				$author = get_field( "post_author" ) ? get_field( "post_author" ) : get_the_author();
				
				echo '<!-- RS Author: ' . $author . ' -->';
				
				if ( $author ) {
					echo '<p class="meta">By ', esc_html($author), '</p>';
					/*?> | Published <?php echo esc_html(get_the_date('F Y')); */
				}
				?>
				
				<?php the_content(); ?>
			</div>
			<?php
			$posts = get_posts( array(
				'numberposts'   => 3,
				'no_found_rows' => true,
				'post_type'     => get_post_type(), // could be "post" or "weekender"
				'category'      => $cat->cat_ID,
				'orderby'       => 'rand',
				'post__not_in'  => array( $post->ID ),
			) );
			if ( $posts ):
				?>
				<h2>Related Stories</h2>
				<div class="post-related">
					<?php
					foreach ( $posts as $post ) :
						setup_postdata( $post );
						$img = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'medium' );
						echo '<div class="post" style="background-image: url(' . esc_attr( $img[0] ) . ')">';
						echo '<a href="' . get_the_permalink() . '"><div class="overlay">';
						echo '<h3>' . get_the_title();
						if ( get_the_title() && get_field( 'subtitle', get_the_ID() ) ) {
							echo ':<br />';
						}
						echo '<span class="overlay-subtitle">' . get_field( 'subtitle', get_the_ID() ) . '</span></h3>';
						echo '</div></a>';
						echo '</div>';
					endforeach;
					?>
				</div>
			<?php
			endif;
			wp_reset_postdata();
			?>
			<div class="post-comments">
				<div class="fb-comments" data-href="<?php echo get_permalink(); ?>" data-width="100%" data-numposts="5"></div>
			</div>
		
		</article>
		<div class="sidebar">
			<?php
			echo do_shortcode( '[ad location="Article Sidebar (first)"]' );
			echo do_shortcode( '[ad location="Article Sidebar (second)"]' );
			echo do_shortcode( '[ad location="Article Sidebar (third)"]' );
			get_sidebar();
			?>
		</div>
	</div>
</div>