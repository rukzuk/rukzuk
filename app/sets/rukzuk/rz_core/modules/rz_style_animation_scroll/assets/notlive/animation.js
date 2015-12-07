define(['rz_root/notlive/js/baseJsModule', 'CMS', 'jquery', 'rz_style_animation_scroll/animationHelper'], function (JsModule, CMS, $, animationHelper) {

    var reInitAllAnimations = function () {
        $(window.rz_style_animation_scroll).each(function (index, selector) {
            $(selector).fracs('unbind');
        });
        animationHelper.initAllAnimations();
    };

    var viewAllAnimations = function () {
        $(window.rz_style_animation_scroll).each(function (index, selector) {
            var $element = $(selector);
            $element.addClass('resetAnimationName');
            window.setTimeout(function () {
                $element.removeClass('resetAnimationName');
                $element.addClass('previewAnimation');
                $element.one('webkitAnimationEnd animationend', function () {
                    $element.removeClass('previewAnimation');
                });
            }, 10);
        });
    };

    return JsModule.extend({
        initModule: function () {
            // re-init all animations after a unit (including rz_selector_elements) got replaced
            CMS.on('afterRenderUnit', function () {
                reInitAllAnimations();
            });

            // init all animations after page load
            animationHelper.initAllAnimations();
        },

        onFormValueChange: function (cfg) {
            if (cfg.key === 'previewAnimations') {
                viewAllAnimations();
            }
        }
    });
});
