define(['CMS', 'rz_root/notlive/js/baseJsModule', 'rz_root/notlive/js/cssHelper'], function (CMS, JsModule, cssHelper) {

    var updateUnitName = function (unitId) {
        var unit = CMS.get(unitId);
        var name = unit.formValues.webfontId.value;
        CMS.setInfo(unitId, 'name', name);
    };

    return JsModule.extend({

        initUnit: function (unitId) {
            updateUnitName(unitId);
        },

        /**
         * Refresh CSS of font styles (as they are the only modules which can use this webfont)
         * @param config
         */
        onFormValueChange: function (config) {
            if (config.key === 'webfontId') {
                updateUnitName(config.unitId);
            }

            // Fix for (at least) Chrome, which doesn't forget any loaded fonts (you can replace them, but not with something invalid!)
            if (config.key.match(/(woff|ttf)/) && !config.newValue) {
                CMS.refresh();
            } else {
                // update our own css
                cssHelper.refreshCSS(config.unitId);
                // update depended modules (all style_font)
                var unitIds = CMS.getAllUnitIds('rz_style_font');
                unitIds.forEach(function (unitId) {
                    cssHelper.refreshCSS(unitId);
                });
            }
        }
    });
});
