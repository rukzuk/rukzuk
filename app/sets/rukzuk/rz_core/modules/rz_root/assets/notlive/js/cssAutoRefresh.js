define('DynCSS', [], function () {
    return window.DynCSS;
});

/**
 * Dynamic CSS Browser Component
 * Triggers client-side recompile of css code if data changes
 */
define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {

    var formValueChangeHandler = function (changeData) {
        if (cssHelper.isCssFormValue(changeData.key)) {
            // refresh CSS
            cssHelper.refreshCSS(changeData.unitId);
            // prevent rendering of this unit (this works inside of
            // formValueChange events since client commit c2513d6)
            CMS.preventRendering();
        }
    };

    return {
        init: function () {
            cssHelper.initDynCSS();
            CMS.on('formValueChange', {}, formValueChangeHandler, this);

            // refresh triggered by other modules
            $('body').on('refreshCSS', function (e, unitId) {
                cssHelper.refreshCSS(unitId);
            });

        }
    };
});
