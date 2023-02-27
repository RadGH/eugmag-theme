<?php get_header(); ?>

	<div id="content" class="clearfix">
		<div class="inside">

			<?php if ( have_posts() ) {
				the_post();
				get_template_part( "template-parts/cover" ); ?>

				<div class="inside narrow">
					<article <?php post_class( 'loop-single' ); ?>>

						<div class="loop-header">
							<?php the_title( '<h1 class="loop-title">', '</h1>' ); ?>
						</div>

						<div class="retail-store-body loop-body">

							<div class="retail-store-sidebar">
								<h2>Find a Copy</h2>
								<form action="/retail-stores/" type="GET">
									<p><label>City, State, or Zip:<br /><input type="text" name="address"<?php if (isset($_GET['address'])) {echo ' value="', esc_attr($_GET['address']), '"';} ?>/></label></p>
									<p><label>Search radius:<br />
										<select name="radius">
											<option value="1"<?php selected('1',$_GET["radius"]); ?>>1 mile</option>
											<option value="5"<?php selected('5',$_GET["radius"]); ?>>5 miles</option>
											<option value="10"<?php selected('10',$_GET["radius"]); ?>>10 miles</option>
											<option value="20"<?php selected('20',$_GET["radius"]); ?>>20 miles</option>
										</select>
									</label></p>
									<input type="submit" value="Find" class="button" />
								</form>
								<?php echo do_shortcode( '[ad location="Retail Store Locator Sidebar"]' ); ?>
							</div>

							<div class="retail-store-content loop-content">
								<?php the_content(); ?>
							</div><!-- .loop-content -->

						</div><!-- .loop-body -->

					</article>
				</div>

			<?php } ?>

		</div> <!-- /.inside -->
	</div> <!-- /#content -->

<?php get_footer();