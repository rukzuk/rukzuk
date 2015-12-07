define(['jquery', 'CMS'], function ($, CMS) {

    function transformConfig(clientCfg) {
        var enabled = clientCfg.enabled;
        return {
            selectionMarker: enabled,
            selectionHelper: enabled,
            inputHints: enabled,
            resizeHandles: enabled,
            toolbar: enabled
        };
    }

    function updateClasses(clientCfg) {
        var $body = $('body');
        var cfg = transformConfig(clientCfg);

        $.each(cfg, function (visualHelper, isEnabled) {
            var cssClass = 'RUKZUKenable' + visualHelper.charAt(0).toUpperCase() + visualHelper.slice(1);

            if (isEnabled) {
                $body.addClass(cssClass);
            } else {
                $body.removeClass(cssClass);
            }
        });
    }

    function init() {
        updateClasses(CMS.getVisualHelpersState());
        CMS.on('visualHelpersStateChange', updateClasses, this);
    }

    function resumeVisualHelper() {
        updateClasses(CMS.getVisualHelpersState());
    }

    function suspendVisualHelper() {
        updateClasses({enabled: false});
    }

    return {
        init: init,
        suspendVisualHelper: suspendVisualHelper,
        resumeVisualHelper: resumeVisualHelper
    };
});
