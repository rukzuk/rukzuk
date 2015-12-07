define(['CMS', 'jquery', 'rz_root/notlive/js/baseJsModule', 'rz_root/notlive/js/breakpointHelper'], function (CMS, $, JsModule, bpHelper) {

    var loadFontIfRequired = function (fontName) {
        // look for this font
        var linkTags = $('link[data-font-name="' + fontName + '"]');
        // none found - add it
        if (linkTags.length === 0) {
            var $link = $('<link />', {
                href: 'https://fonts.googleapis.com/css?family=' + fontName + ':100,200,300,400,500,600,700,800,900,100italic,200italic,300italic,400italic,500italic,600italic,700italic,800italic,900italic',
                rel: 'stylesheet',
                'data-font-name': fontName
            });
            $('head').append($link);
        }
    };

    return JsModule.extend({

        /**
         * Add Fonts which are not already loaded
         * @param config
         */
        onFormValueChange: function (config) {
            if (config.key === 'cssFontFamilyGoogle') {
                bpHelper.forEachBreakpointValue(config.newValue, loadFontIfRequired);
            }
        }
    });
});
