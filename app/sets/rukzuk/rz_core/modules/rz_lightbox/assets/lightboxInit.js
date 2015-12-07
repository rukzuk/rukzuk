define(['jquery', 'rz_lightbox/swipebox'], function ($, rz_lightbox) {
	return {
		init: function () {
			$('.rz_lightbox').on('click', 'img.responsiveImage', function (e) {
				e.preventDefault();
				rz_lightbox.open(e);
			});
		}
	};
});