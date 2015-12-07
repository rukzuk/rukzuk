define(['jquery', 'CMS', 'rz_lightbox/swipebox'], function ($, CMS, rz_lightbox) {

	var Rz_lightbox_edit_mode = function () {};

	Rz_lightbox_edit_mode.prototype				= rz_lightbox;
	Rz_lightbox_edit_mode.prototype.constructor = Rz_lightbox_edit_mode;
	Rz_lightbox_edit_mode.prototype.init		= function () {
		var that = this;
		$('.rz_lightbox').off('click', 'img.responsiveImage').on('click', 'img.responsiveImage', function (e) {
			e.preventDefault();
			that.open(e);
		});
	};
	Rz_lightbox_edit_mode.prototype.state = function () {
		var that = this;
		var state = {
			save: function () {
				if ($.swipebox.isOpen) {
					var lastOpenState = {
						timestamp: new Date().getTime(),
						lightboxId: that.options.parentElement.attr('id'),
						imageIndex: that.options.initialIndexOnArray
					};
					sessionStorage.setItem('rz_lightbox:openState', JSON.stringify(lastOpenState));
				} else {
					sessionStorage.removeItem('rz_lightbox:openState');
				}
			},
			restore: function () {
				var openState = sessionStorage.getItem('rz_lightbox:openState');
				if (openState) {
					openState = JSON.parse(openState);
					// abort if last reload was more than 10 seconds ago
					// maybe the page was closed, so the lightbox shouldn't open again
					var now = new Date().getTime();
					if (now >= openState.timestamp + 10000) {
						return;
					}
					// trigger click on image element to open the lightbox again
					$('#' + openState.lightboxId).find('img.responsiveImage').eq(openState.imageIndex).click();
				}
			}
		};
		return state;
	};

	$.extend({
		rz_lightbox: function () {
			var	arg			= arguments[0] || {};
			var callbackFnk = arguments[1] || {};
			var object		= new Rz_lightbox_edit_mode(arg, callbackFnk);
			return object;
		}
	});

	$.rz_lightbox().init();

	CMS.on('afterRenderUnit', function () {
		$.rz_lightbox().init();
	});

	CMS.on('beforeRenderPage', function () {
		$.rz_lightbox().state().save();
	});

	CMS.onAfterRenderPage(function () {
		$.rz_lightbox().state().restore();
	}, this);

});