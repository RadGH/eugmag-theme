<?php
/*
Template Name: Front Page
*/

get_header();
?>

<?php get_template_part( 'template-parts/cover', 'front-page' ); ?>

<div id="content">
    <?php get_template_part( 'template-parts/coverstories', 'front-page' ); ?>
    <?php get_template_part( 'template-parts/dept-posts', 'front-page' ); ?>
</div>
<!-- #content -->

<?php
get_footer();