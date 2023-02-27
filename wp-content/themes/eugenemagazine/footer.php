
<footer id="footer">
    <div class="inside narrow">
        <div class="footer-left">
            <?php
            if ( $menu = ld_nav_menu( 'footer', 'pages' ) ) {
                echo '<h2 class="eugmaglogo"><a href="/">Eugene Magazine</a></h2>';
                echo '<nav class="nav-menu nav-footer nav-pages">';
                echo $menu;
                echo '<ul class="nav-login"><li><a href="/wp-admin/">Log in</a></li></ul>';
                echo '</nav>';
            }
            ld_social_menu();
            ?>
        </div>
        <div class="footer-center">
            <?php
            if ( $menu = ld_nav_menu( 'footer', 'departments' ) ) {
                echo '<h2>Departments</h2>';
                echo '<nav class="nav-menu nav-footer nav-departments">';
                echo $menu;
                echo '</nav>';
            }
            ?>
        </div>
        <div class="footer-right">
            <?php
                echo do_shortcode('[ad location="Footer"]');
            ?>

            <?php
            if ( $copyright = get_field('copyright_text', 'options') ) {
                echo '<div class="copyright">';
                echo do_shortcode( wpautop( $copyright ) );
                echo '</div>';
            }
            ?>
        </div>
    </div>
</footer> <!-- /#footer -->

<?php wp_footer(); ?>
</body>
</html>