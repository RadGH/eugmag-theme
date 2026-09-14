<?php
/**
 * Full-screen reels viewer template.
 *
 * Deliberately minimal: no theme header/footer chrome. The JS in assets/reels.js
 * builds the interactive viewer from the data localized in wp_enqueue_scripts.
 *
 * @package ZM_Reels
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'zm-reels-body' ); ?>>
	<div id="zm-reels-app" role="application" aria-label="<?php esc_attr_e( 'Reels viewer', 'zm-reels' ); ?>">
		<div class="zm-reels-empty"><?php esc_html_e( 'No reels to show yet.', 'zm-reels' ); ?></div>
	</div>
	<?php wp_footer(); ?>
</body>
</html>
