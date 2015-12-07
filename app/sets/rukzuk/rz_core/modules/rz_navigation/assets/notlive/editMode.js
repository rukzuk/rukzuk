define(['rz_root/notlive/js/baseJsModule', 'rz_root/notlive/js/cssHelper'], function (JsModule, cssHelper) {
    var isLevelEnabledRE = /enableLevel/;
    return JsModule.extend({
        onFormValueChange: function (cfg) {
            if (isLevelEnabledRE.test(cfg.key)) {
                cssHelper.refreshCSS(cfg.unitId);
            }
        }
    });
});

