<?php

function single_staff( $content ) {

	ob_start();

	$portrait = get_post_thumbnail_id( get_the_ID() );
	if ( !$portrait && function_exists( 'get_field' ) ) {
		$portrait = get_field( 'staff_thumbnail', get_the_ID(), false );
	}

	$image = $portrait ? wp_get_attachment_image_src( $portrait, 'staff-portrait' ) : false;
	if ( $image && $image[1] > 300 ) {
		$image = wp_get_attachment_image_src( $portrait, 'medium' );
	} // If staff-portrait size not set, would fall back to full size. Don't do that.

	$alt = get_post_meta( $portrait, '_wp_attachment_image_alt', true );

	$image = apply_filters( 'staff-thumbnail', $image, get_the_ID() );
	$alt = apply_filters( 'staff-thumbnail-alt', $alt, get_the_ID() );


	$gallery = false;
	if ( function_exists( 'get_field' ) ) {
		$gallery = get_field( 'staff_photos', get_the_ID() );
	}

	?>


	<div class="staff-single">
		<article <?php post_class( 'loop-single staff-item' ); ?>>

			<div class="staff-cols">
				<div class="staff-col staff-col-left">

					<?php if ( $image ) { ?>
						<div class="staff-portrait">
							<div class="staff-thumbnail has-thumbnail">
								<img src="<?php echo esc_attr( $image[0] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="<?php echo (int)$image[1]; ?>" height="<?php echo (int)$image[2]; ?>">
							</div>
						</div>
					<?php } ?>

					<?php if ( $gallery ) { ?>
						<div class="staff-media">
							<h3>Media Gallery</h3>
							<div class="staff-media-list">
								<?php
								foreach ( $gallery as $media ) {
									$a_classes = array( 'gallery-item-link' );
									$a_classes = apply_filters( 'staff-media-item-classes', $a_classes );

									$thumb_url = $media['url'];
									if ( !empty( $media['sizes']['thumbnail'] ) ) {
										$thumb_url = $media['sizes']['thumbnail'];
									}

									$alt = $media['alt'];
									?>
									<div class="staff-media-item">
										<?php
										printf( '<a href="%s" class="%s"><img src="%s" alt="%s"></a>', esc_attr( $media['url'] ), esc_attr( implode( ' ', $a_classes ) ), esc_attr( $thumb_url ), esc_attr( $alt ) );
										?>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					<?php } ?>
				</div>

				<div class="staff-col staff-col-right">

					<div class='staff-before-description'>
						<p><strong>Title:</strong> <?php the_field( "staff_title", get_the_ID() ); ?></p>
						<p><strong>Phone:</strong> <?php the_field( "staff_phone", get_the_ID() ); ?></p>
						<p><strong>Email:</strong> <?php the_field( "staff_email", get_the_ID() ); ?></p>
					</div>

					<div class="staff-description">
						<?php echo $content; ?>
					</div>

					<div class="staff-after-description">
						<a href="/staff">← Back to staff directory</a>
					</div>

				</div>
			</div>

		</article>
	</div>


	<?php
	return ob_get_clean();
}