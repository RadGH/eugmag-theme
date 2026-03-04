(function($) {

	$(document).ready(function () {
		let $gallery = $(".guide_gallery");
		if ($gallery.length) {
			$gallery.flickity({
				cellAlign: 'left',
				contain: true
			});
		}
	});

})(jQuery);