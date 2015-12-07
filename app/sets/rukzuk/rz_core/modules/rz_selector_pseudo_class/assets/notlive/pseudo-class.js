define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {

    var i18n = {};
    var _stateMap = null;

    var getStateMap = function () {
        if (!_stateMap) {
            _stateMap = {
                hover: i18n['state.hover'],
                active: i18n['state.active'],
                focus: i18n['state.focus']
            };
        }
        return _stateMap;
    };

    var updateName = function (cfg) {
        // update formValues with newValue
        var formValues = CMS.get(cfg.unitId).formValues;

        var stateMap = getStateMap();
        // set name
        var name = stateMap[formValues.pseudoClass.value] || formValues.pseudoClass.value;
        CMS.setInfo(cfg.unitId, 'state', name);
    };

    return {
        init: function (data) {
            // i18n
            i18n = JSON.parse(data.i18n);

            var eventFilter = {moduleId: data.moduleId};

            // initial insert
            CMS.getAllUnitIds(data.moduleId).forEach(function (unitId) {
                updateName({unitId: unitId});
            }, this);

            // formValue change
            CMS.on('formValueChange', eventFilter, function (cfg) {
                // update name
                if (cfg.key === 'pseudoClass') {
                    updateName(cfg);

                    // set additional selector
                    CMS.set(cfg.unitId, 'additionalSelector', ':' + cfg.newValue);

                    // trigger refresh of css (this is required as the CMS.set() will not trigger formValueChange of the hidden field)
                    cssHelper.refreshCSS(cfg.unitId);
                }
            });
        }
    };
});
