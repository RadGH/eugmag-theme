<?php
// Flyout menu
// Revealed by clicking the menu button.
// See: mobile-button.php
?>
<div id="mobile-menu-wrap">
<div id="mobile-menu-container">
	<div class="inside narrow">
		<div class="mobile-outer">
			<div class="mobile-inner">
				<?php
				if ( $menu = ld_nav_menu( 'mobile', 'departments' ) ) {
					echo '<h2>Departments</h2>';
					echo '<nav class="nav-menu nav-mobile nav-departments">';
					echo $menu;
					echo '</nav>';
				}

				if ( $menu = ld_nav_menu( 'mobile', 'pages' ) ) {
					echo '<nav class="nav-menu nav-mobile nav-pages">';
					echo $menu;
					echo '<ul class="nav-login"><li><a href="/wp-admin/">Log in</a></li></ul>';
					echo '</nav>';
				}
				
				ld_social_menu();
				?>
			</div>
		</div>
	</div>
</div>
</div>