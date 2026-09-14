document.addEventListener( 'DOMContentLoaded', function () {
	const gallery = document.querySelector( '.guide_gallery' );
	if ( gallery ) {
		const itemCount = gallery.querySelectorAll( '.carousel-cell' ).length;
		if ( itemCount > 1 ) new Flickity( gallery, {cellAlign: 'left', contain: true} );
	}

	const player = document.querySelector( '.reel-player' );
	if ( player ) {
		const video = player.querySelector( 'video' );
		const overlay = player.querySelector( '.reel-player__overlay' );
		if ( !video || !overlay ) return;

		overlay.addEventListener( 'click', () => {
			video.play();
			video.setAttribute( 'controls', '' );
			overlay.remove();
		} );
	}
} );
