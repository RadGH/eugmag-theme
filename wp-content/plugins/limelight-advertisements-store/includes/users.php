<?php

function ldadstore_warn_no_registration() {
	$error = false;

	if ( !get_option('users_can_register') ) {
		$error = 'User registrations are currently disabled. Enable the option "Anyone can register" under <a href="'.admin_url('options-general.php').'">Settings &gt; General</a>.';
	}

	if ( $error ) {
		?>
		<div class="error">
			<p><strong>Limelight - Advertisements Store:</strong> Error</p>
			<?php echo wpautop($error); ?>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ldadstore_warn_no_registration' );