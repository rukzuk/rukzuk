define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {


    var updateName = function (cfg) {
        var name = CMS.get(cfg.unitId).formValues.cssStyleSetName.value;
        if (name === '') {
            name = "Styleset";
        }
        CMS.setInfo(cfg.unitId, 'complete', name);
    };

    var setCssClassName = function (unitId) {
        if (CMS.get(unitId).formValues.cssStyleSet.value === '') {
            CMS.set(unitId, 'cssStyleSet', unitId.replace(/MUNIT/g, 'STS'));
        }
    };

    return {
        init: function (data) {
            var eventFilter = {moduleId: data.moduleId};

            // initial insert
            CMS.getAllUnitIds(data.moduleId).forEach(function (unitId) {
                updateName({unitId: unitId});
                setCssClassName(unitId);
            }, this);

            // formValue change
            CMS.on('formValueChange', eventFilter, function (cfg) {
                // update name

                if (cfg.key === 'cssStyleSetName') {
                    updateName(cfg);
                    CMS.refresh(cfg.unitId);
                }

            });
        }
    };
});
