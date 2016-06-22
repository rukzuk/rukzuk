define(['rz_root/notlive/js/baseJsModule', 'CMS', 'jquery'], function (JsModule, CMS) {

    var updateName = function (cfg) {
        var moduleData = CMS.get(cfg.unitId, true);

        var moduleName = moduleData.formValues.eventType.value;
        moduleName = moduleName.charAt(0).toUpperCase() + moduleName.substr(1);
        if (moduleData.formValues.enableState.value) {
            moduleName += ' - ' + moduleData.formValues.stateName.value;
        }
        CMS.setInfo(cfg.unitId, 'name', moduleName);
    };
    return {
        init: function (data) {

            // initial insert
            CMS.getAllUnitIds(data.moduleId).forEach(function (unitId) {
                updateName({unitId: unitId});
                CMS.set(unitId, 'additionalSelector', '&.' + unitId);
            }, this);

            var eventFilter = {moduleId: data.moduleId};

            // formValue change
            CMS.on('formValueChange', eventFilter, function (cfg) {
                updateName(cfg);

            });
        }
    };
});