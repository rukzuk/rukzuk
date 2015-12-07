/*!
 * jQuery Rukzuk Lightbox Library
 */
define(['jquery'], function ($) {
	var Rz_lightbox = function (options, fn) {
		this.callbackFnk = fn;
		this.options = $.extend({}, this.defaults, options);
	};

	Rz_lightbox.prototype = {

		defaults: {
			hideBarsDelay		: 0,
            hideBarsOnMobile    : false,
			initialIndexOnArray	: 0,
			appendToElement		: 'body',
            parentElement       : undefined,
            beforeOpen          : function() {
                // make sure no modules below the lightbox will be visible when lightbox is open
                this.parentElement.parents().addClass('zIndexLightBox');
            },
            afterClose          : function() {
                this.parentElement.parents().removeClass('zIndexLightBox');
            }
		},
		open: function (e) {
			var that    = this;
			var gallery = [];
			var element = $(e.target);
			var parent  = element.parents('.rz_lightbox');
			that.options.parentElement = parent;

			parent.find('img.responsiveImage').each(function (i) {
				var image = $(this);
				if (e.target === this) {
					that.options.initialIndexOnArray = i;
				}
				gallery.push({href: image.data('cms-origsrc'), title: image.prop('title')});
			});
			this.options.appendToElement = parent;
			$.swipebox(gallery, this.options);
		}
	};

	$.extend({
		rz_lightbox: function () {
			var arg			= arguments[0] || {};
			var callbackFnk = arguments[1] || {};
			var object		= new Rz_lightbox(arg, callbackFnk);
			return object;
		}
	});

	return $.rz_lightbox();
});
