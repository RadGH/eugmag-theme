jQuery(function() {

	var $links = jQuery('.em_make_featured');

	// Make the href attribute real (instead of href="#")
	$links
		.attr('href', function() { return jQuery(this).attr('data-href'); })
		.removeAttr('data-href');

	// Changes the icon of a link
	var set_link_icon = function( $link, icon ) {
		var $icon = $link.is('.dashicons') ? $link : $link.find('.dashicons');

		$icon
			.toggleClass('dashicons-update', (icon === "update"))
			.toggleClass('dashicons-star-empty', (icon === "star-empty"))
			.toggleClass('dashicons-star-filled', (icon === "star-filled"));
	}

	// Add click handler
	$links.on('click', function() {
		var $link = jQuery(this);
		var $icon = $link.find('span.dashicons');
		var href = $link.attr('href');

		if ( $link.hasClass('loading') ) return;

		$link.addClass('loading');
		set_link_icon( $icon, 'update' );

		jQuery.ajax({
			url: href,
			method: 'GET',
			success: function( data ) {

				$link.removeClass('loading');

				// data = object
				// {"success":true,"data":{"message":"Added featured","make_featured":true,"post_type":"service","post_id":"12971"}}

				if ( ! data.success ) {
					alert('Failed to toggle featured state. See console for details');
					console.log('Failed to toggle featured state. Details:', data );
				}else{

					var is_now_featured = data['data'].make_featured === true;

					// Change the icon
					set_link_icon( $icon, is_now_featured ? 'star-filled' : 'star-empty' );

					// Change the url
					var href_search = is_now_featured ? 'featured=1' : 'featured=0';
					var href_replace = is_now_featured ? 'featured=0' : 'featured=1';

					$link
						.attr('href', function() {
							return href.replace(href_search, href_replace);
						});
				}

			}
		});

		return false;
	});

});