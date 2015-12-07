define(['CMS', 'rz_root/notlive/js/baseJsModule', 'rz_root/notlive/js/cssHelper'], function (CMS, JsModule, cssHelper) {

    return JsModule.extend({

        initUnit: function (unitId) {

        },

        onFormValueChange: function (cfg) {
            if (cfg.key === 'switchColors') {
                var unit = CMS.get(cfg.unitId);
                var formValues = unit.formValues;

                // switch colors
                var startColor = formValues.cssBackgroundGradientStartColor.value;
                var endColor = formValues.cssBackgroundGradientEndColor.value;
                CMS.set(cfg.unitId, 'cssBackgroundGradientStartColor', endColor);
                CMS.set(cfg.unitId, 'cssBackgroundGradientEndColor', startColor);

                // trigger update
                cssHelper.refreshCSS(cfg.unitId);
            }
        }
    });
});
