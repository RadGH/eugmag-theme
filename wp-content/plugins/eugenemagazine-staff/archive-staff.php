<?php

function print_staff_archive_new() {
	
	ob_start();
	
	echo '<div class="staff-archive-list">';
	
	$staff = get_posts( array(
		'post_type'      => 'staff',
		'posts_per_page' => - 1,
		'order'          => 'ASC',
		'orderby'        => 'title',
	) );
	
	// Display staff members
	foreach ( $staff as $post ) :
		setup_postdata( $post );
		
		$thumbnail = get_field( 'staff_thumbnail', $post->ID, false );
		if ( ! $thumbnail ) {
			$thumbnail = get_post_thumbnail_id( $post->ID );
		}
		
		$image = $thumbnail ? wp_get_attachment_image_src( $thumbnail, 'woocommerce_thumbnail' ) : false;
		
		?>
		<div class="staff-item">
			<a href="<?php the_permalink( $post->ID ); ?>" rel="bookmark">
				<?php if ( $image ) { ?>
					<div class="staff-photo">
						<img alt="" src="<?php echo esc_attr( $image[0] ); ?>" />
					</div>
				<?php } ?>
				<h2 class="staff-title"><?php echo $post->post_title; ?></h2>
				<div class="staff-position"><?php the_field( "staff_title", $post->ID ); ?></div>
			</a>
		</div>
	<?php
	
	endforeach;
	
	echo '</div>';
	
	wp_reset_postdata();
	
	echo '<!--';
	global $_wp_additional_image_sizes;
	
	$default_image_sizes = get_intermediate_image_sizes();
	
	foreach ( $default_image_sizes as $size ) {
		$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
		$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
		$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
	}
	
	if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
		$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
	}
	
	echo '<pre>';
	print_r( $image_sizes );
	echo '</pre>';
	echo '-->';
	
	return ob_get_clean();
}

function print_staff_archive() {
	
	// SET TO FALSE WHEN HAVE IMAGES
	$hideimages = false;
	
	ob_start();
	
	echo '<div class="staff-archive-list">';
	if ( $hideimages ) {
		echo '<ul>';
	}
	
	$staff = get_posts( array(
		'post_type'      => 'staff',
		'posts_per_page' => - 1,
		'order'          => 'ASC',
		'orderby'        => 'title',
	) );
	
	// Display staff members
	foreach ( $staff as $post ) :
		setup_postdata( $post );
		
		$thumbnail = get_field( 'staff_thumbnail', $post->ID, false );
		if ( ! $thumbnail ) {
			$thumbnail = get_post_thumbnail_id( $post->ID );
		}
		
		$image = $thumbnail ? wp_get_attachment_image_src( $thumbnail, 'full' ) : false;
		$alt   = get_post_meta( $thumbnail, '_wp_attachment_image_alt', true );
		
		if ( ! $hideimages ) {
			?>
			<div class="staff-item">
				<a href="<?php the_permalink( $post->ID ); ?>" rel="bookmark"<?php if ( $image ) {
					echo ' style="background-image: url(', esc_attr( $image[0] ), ')"';
				} ?>>
					<div class="staff-overlay">
						<h2 class="staff-title"><?php echo $post->post_title; ?></h2>
						<div class="staff-position"><?php the_field( "staff_title", $post->ID ); ?></div>
					</div>
				</a>
			</div>
			<?php
		} else {
			?>
			<li>
				<a href="<?php the_permalink( $post->ID ); ?>" rel="bookmark">
					<?php echo $post->post_title; ?>
				</a>
			</li>
			<?php
		}
	endforeach;
	if ( $hideimages ) {
		echo '</ul>';
	}
	echo '</div>';
	
	wp_reset_postdata();
	
	return ob_get_clean();
}
