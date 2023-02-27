<?php
/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template'
 * is selected in Events -> Settings -> Template -> Events Template.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

get_header();
?>

	<div id="content" class="clearfix">
		<div class="inside">

			<?php

			if ( !is_front_page() && !is_singular( "post" ) && !is_category() ) {
				get_template_part( "template-parts/cover" );
			}
			?>
			<div id="tribe-events-pg-template">
				<?php tribe_events_before_html(); ?>
				<?php tribe_get_view(); ?>
				<?php tribe_events_after_html(); ?>
			</div> <!-- #tribe-events-pg-template -->

		</div> <!-- /.inside -->
	</div> <!-- /#content -->

<?php
get_footer();