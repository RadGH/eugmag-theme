<?php
/*
This file includes the different template files when appropriate, using a shortcode.

[advertisement_store] (Same as "store")
[advertisement_store page="store"]
[advertisement_store page="dashboard"]
*/

function ldadstore_shortcode( $atts, $content = '' ) {
	$page = isset($atts['page']) ? $atts['page'] : false;

	ob_start();

	echo '<div class="ldadstore-container">';

	if ( !is_user_logged_in() ) {
		include( LDAdStore_PATH . '/advertisements-store/templates/login.php' );
	}else{
		switch( $page ) {
			case '':
			case 'store':
				include( LDAdStore_PATH . '/advertisements-store/templates/store.php' );
				break;
			
			case 'dashboard':
				if ( isset($_REQUEST['action']) ) {
					switch( stripslashes($_REQUEST['action']) ) {
						case 'edit':
							include( LDAdStore_PATH . '/advertisements-store/templates/dashboard/edit.php' );
							break;
						
						default:
							include( LDAdStore_PATH . '/advertisements-store/templates/404.php' );
							break;
					}
				}else{
					include( LDAdStore_PATH . '/advertisements-store/templates/dashboard/view-all.php' );
				}

				break;
			
			default:
				include( LDAdStore_PATH . '/advertisements-store/templates/404.php' );
				break;
		}
	}

	echo '</div>';

	return ob_get_clean();
}
add_shortcode( 'advertisement_store', 'ldadstore_shortcode' );