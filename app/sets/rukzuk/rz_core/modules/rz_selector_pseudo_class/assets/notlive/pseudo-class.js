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

                    var prefix = '&.';
                    if ((cfg.newValue == 'hover') || (cfg.newValue == 'active') || (cfg.newValue == 'focus')) {
                        prefix = ':';
                    }
                    // set additional selector
                    CMS.set(cfg.unitId, 'additionalSelector', prefix + cfg.newValue);

                    // trigger refresh of css (this is required as the CMS.set() will not trigger formValueChange of the hidden field)
                    cssHelper.refreshCSS(cfg.unitId);
                }
            });
            CMS.on('unitSelect', eventFilter, function (cfg) {
                if (cfg.editable && window.rz_trigger_event) {
                    var states = [];
                    states.push(['hover', i18n['state.hover']]);
                    states.push(['active', i18n['state.active']]);
                    states.push(['focus', i18n['state.focus']]);

                    var unitData = CMS.get(cfg.unitId, false);

                    // set event states only when parent module is a dom element
                    if ($('#' + unitData.parentUnitId).length > 0) {
                        for (var unitId in window.rz_trigger_event) {
                            var stateName = window.rz_trigger_event[unitId].stateName;
                            if (stateName !== '') {
                                var displayName = {"de": stateName + " (" + i18n['state.event'].de + ")", "en": stateName + " (" + i18n['state.event'].en + ")"};
                                states.push([stateName, displayName]);
                            }
                        }
                        var uniq = function(items, key) {
                            var set = {};
                            return items.filter(function(item) {
                                var k = key ? key.apply(item) : item;
                                return k in set ? false : set[k] = true;
                            });
                        };
                        var uniqueStates = [];
                        uniqueStates = uniq(states, [].join);
                        CMS.updateFormFieldConfig(cfg.unitId, 'pseudoClass', {
                            options: uniqueStates
                        });
                    }

                }

            });
        }
    };
});
