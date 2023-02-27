<?php
/*
Not actually a login form, but a form you see when you need to log in. This will use WordPress's login/register system.
*/
?>

<p>You must be logged in to purchase advertisements on our website.</p>

<p>
	<a href="<?php echo esc_attr( wp_login_url( get_permalink() ) ); ?>" class="button">Sign In</a>
	<?php if ( get_option( 'users_can_register' ) ) { ?>

	<a href="<?php echo esc_attr( wp_registration_url() ); ?>" class="button">Create Account</a>
	<?php } ?>

	<?php if($button = get_field("ld_ad_store_custom_button","options")) { ?>
		<a href="<?php echo get_permalink($button[0]["page"] ); ?>" class="button"><?php echo $button[0]["title"]; ?></a>
	<?php } ?>
</p>

<?php if ( !get_option( 'users_can_register' ) ) { ?>
<p>New user registration is currently <strong>closed</strong>.</p>
<?php } ?>