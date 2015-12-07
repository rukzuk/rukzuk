define(['rz_root/notlive/js/baseJsModule', 'CMS'], function (JsModule, CMS) {

    var updateUnitName = function (unitId) {
        var unit = CMS.get(unitId);
        var element = unit.formValues.htmlElement.value;
        CMS.setInfo(unitId, 'element', element);
    };

    return JsModule.extend({
        initUnit: function (unitId) {
            updateUnitName(unitId);
        },

        onFormValueChange: function (cfg) {
            if (cfg.key === 'htmlElement') {
                updateUnitName(cfg.unitId);
            }
        }
    });
});
