define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {

    var i18n = {};

    var cachedModulesAndSelectors = null;

    // global css selectors
    var getGlobalCssSelectors = function () {
        var globalSel =  {};

        globalSel.other = [{
            title: i18n['selector.allChildModules'],
            selector: ' .isModule'
        }, {
            title: i18n['selector.allFirstLevelChildModules'],
            // includes selector for modules with wrappers, e.g. rz_box and rz_grid
            selector: ' > .isModule, > :not(.isModule) > .isModule, > :not(.isModule) > :not(.isModule) > .isModule'
        }];

        return globalSel;
    };


    // ----

    var formValueChangeHandler = function (cfg) {

        var modulesAndSelectors = getModulesAndSelectors();

        // update module list
        if (cfg.key == 'module') {
            // update selector list
            var currentSelectors = modulesAndSelectors.selectorByModule[cfg.newValue];
            updateSelectorList(cfg.unitId, currentSelectors);

            // select first selector
            var firstSelectorInList = currentSelectors[0][0] || '';
            CMS.set(cfg.unitId, 'selectorChooser', firstSelectorInList);
            selectorChooserValueChanged(cfg, modulesAndSelectors);

        } else if (cfg.key == 'selectorChooser') {
            // update name and stuff
            selectorChooserValueChanged(cfg, modulesAndSelectors);
        }

        // update the hidden field
        updateAdditionalSelectorHiddenField(cfg);

    };

    // HELPER methods

    // Nth Child

    var updateNthChildChooser = function (cfg, hide) {
        CMS.updateFormFieldConfig(cfg.unitId, 'nthChildEnable', {locked: !!hide});
    };

    var generateCustomNthTypeOf = function (formValues) {
        var additionalPseudoClass = '' + formValues.customPseudoClass.value;

        switch (formValues.customType.value) {
        case 'single':
            additionalPseudoClass += '(' + formValues.singleStartIndex.value + ')';
            break;
        case 'multiple':
            additionalPseudoClass += '(-n+' + formValues.multipleCount.value + ')';
            break;
        case 'all':
            additionalPseudoClass += '(n+' + formValues.allStartIndex.value + ')';
            break;
        case 'multipleOffset':
            additionalPseudoClass += '(' + formValues.multipleOffsetNth.value + 'n+' + formValues.multipleOffsetStartIndex.value + ')';
            break;
        }

        return additionalPseudoClass;
    };

    var generateNthTypeOf = function (cfg) {
        var formValues = CMS.get(cfg.unitId).formValues;
        var nthChildSelector = '';

        // handle types change hidden field
        switch (formValues.type.value) {
        case 'preset':
            nthChildSelector = '' + formValues.presets.value;
            break;
        case 'custom':
            nthChildSelector = generateCustomNthTypeOf(formValues);
            break;
        }

        return nthChildSelector;
    };

    // Other Stuff

    var selectorChooserValueChanged = function (cfg, modulesAndSelectors) {
        var formValues = CMS.get(cfg.unitId).formValues;
        var currentModule = formValues.module.value;
        var selectorConfig = modulesAndSelectors.selectorConfigByModule[currentModule] || {};
        var selectorChooserValue = formValues.selectorChooser.value;

        // show or hide nthChild
        var showNthChild = (selectorConfig[selectorChooserValue] && selectorConfig[selectorChooserValue].enableNthTypeOf);
        updateNthChildChooser(cfg, !showNthChild);

        // uncheck nthChildEnable
        CMS.set(cfg.unitId, 'nthChildEnable', false);

        // update name
        var selName = selectorConfig[selectorChooserValue] ? selectorConfig[selectorChooserValue].title : '';
        CMS.setInfo(cfg.unitId, 'selector', selName);
    };

    var updateAdditionalSelectorHiddenField = function (cfg) {
        var unit = CMS.get(cfg.unitId);
        var formValues = unit.formValues;
        var curSelector = formValues.selectorChooser.value;

        var nthTypeOf = formValues.nthChildEnable.value ? ':' + generateNthTypeOf(cfg) : '';
        var completeSelector = curSelector + nthTypeOf;
        CMS.set(cfg.unitId, 'additionalSelector', completeSelector);

        // trigger refresh of css (this is required as the CMS.set() will not trigger formValueChange of the hidden field)
        cssHelper.refreshCSS(unit);
    };

    // iterates over the parentUnits until a default unit is found
    var getNextParentDefaultUnit = function (unit) {
        var parentUnit = CMS.get(unit.parentUnitId, false);
        if (CMS.getModule(parentUnit.moduleId).extensionModule) {
            return getNextParentDefaultUnit(parentUnit);
        } else {
            return parentUnit;
        }
    };

    // init a unit of this module
    var initUnit = function (cfg) {
        var unitId = cfg.unitId;

        var modulesAndSelectors = getModulesAndSelectors();

        var moduleList = modulesAndSelectors.allModules;
        var selectorByModule = modulesAndSelectors.selectorByModule;
        updateModuleList(unitId, moduleList);


        var unit = CMS.get(unitId);
        var currentModule = 0;

        if (unit) {
            currentModule = unit.formValues.module.value;
        }

        // init current module with parent std module
        if (currentModule === 0) {
            var parentDefaultUnit = getNextParentDefaultUnit(unit);
            currentModule = parentDefaultUnit.moduleId;

            // current parent module doesn't offer selectors, use the first in the moduleList
            var chooseFirstSelector = true;
            if (!selectorByModule[currentModule]) {
                currentModule = moduleList[0][0];
                chooseFirstSelector = false;
            }

            CMS.set(unitId, 'module', currentModule);

            var currentSelectors = modulesAndSelectors.selectorByModule[currentModule];
            // update the selector list because CMS.set does not fire an formValueChange!
            updateSelectorList(unitId, currentSelectors);

            // select first selector
            if (chooseFirstSelector) {
                var firstSelectorInList = currentSelectors[0][0] || '';
                CMS.set(unitId, 'selectorChooser', firstSelectorInList);
                selectorChooserValueChanged(cfg, modulesAndSelectors);
                // update hidden field
                updateAdditionalSelectorHiddenField(cfg);
            }
        } else {
            // update the selector list because CMS.set does not fire an formValueChange!
            updateSelectorList(unitId, modulesAndSelectors.selectorByModule[currentModule]);
        }

        // update nthChild visibility
        unit = CMS.get(unitId); // update formValues
        var selectorConfigByModule = modulesAndSelectors.selectorConfigByModule[currentModule];
        var curSelConfig = selectorConfigByModule ? selectorConfigByModule[unit.formValues.selectorChooser.value] : {};
        var hideNthChild = !unit.formValues.selectorChooser.value || !(curSelConfig && curSelConfig.enableNthTypeOf);
        updateNthChildChooser(cfg, hideNthChild);
    };


    // fetch all modules and selectors
    var getModulesAndSelectors = function () {

        // fetch data only once!
        if (!cachedModulesAndSelectors) {
            // TODO: move to root module lib?
            var availableSelectors = JSON.parse($('#available_selectors').text()) || {};
            // fix php empty object == empty array
            if (availableSelectors && availableSelectors.length === 0) {
                availableSelectors = {};
            }

            var allSelectors = $.extend({}, availableSelectors, getGlobalCssSelectors());

            // [key, value] pairs for the drop-down/selectlist options
            var allModules = [];
            var selectorByModule = {};
            var selectorConfigByModule = {};

            $.each(allSelectors, function (module, selectors) {

                // only add the default "All X Modules" selector
                if (selectors === true) {
                    selectors = [];
                }

                // use id as initial moduleName
                var moduleName = module;

                // customize module name
                if (module === 'other') {
                    moduleName = i18n['selector.other'];
                } else {
                    // fetch module name, if module is an moduleId
                    var moduleInfo = CMS.getModule(module);
                    if (moduleInfo) {
                        moduleName = moduleInfo.name;
                    }

                    // add All <Module-Name> Modules selector
                    var allXModulesTitle = JSON.parse(JSON.stringify(i18n['selector.allXModules'])) || ['{moduleName}'];
                    Object.keys(allXModulesTitle).forEach(function (key) {
                        allXModulesTitle[key] = allXModulesTitle[key].replace('{moduleName}', moduleName);
                    });

                    var allXModulesSelector = '.' + module; // TODO: legacy modules added &.moduleId
                    selectors.unshift({title: allXModulesTitle, selector: allXModulesSelector});
                }

                // add to list
                allModules.push([module, moduleName]);

                // save selector by module and selector config by selector
                selectorByModule[module] = [];
                selectorConfigByModule[module] = {};

                var addSelectors = function thisFn(module, moduleSelectors, hierarchy) {
                    $.each(moduleSelectors, function (idx, sel) {
                        selectorByModule[module].push([sel.selector, sel.title, hierarchy]);
                        selectorConfigByModule[module][sel.selector] = {
                            enableNthTypeOf: sel.enableNthTypeOf,
                            title: sel.title
                        };

                        // has children?
                        if (sel && sel.items) {
                            thisFn(module, sel.items, hierarchy + 1);
                        }
                    });
                };
                // run addSelectors
                addSelectors(module, selectors, 0);
            });

            cachedModulesAndSelectors = {
                allModules: allModules,
                selectorByModule: selectorByModule,
                selectorConfigByModule: selectorConfigByModule
            };

        }

        return cachedModulesAndSelectors;
    };

    // update the module list
    var updateModuleList = function (unitId, allModules) {
        // sort the module list
        allModules.sort(function (a, b) {
            return (a[1] < b[1] ? -1 : (a[1] > b[1] ? 1 : 0));
        });

        CMS.updateFormFieldConfig(unitId, 'module', {
            options: allModules
        });
    };

    // update selector list
    var updateSelectorList = function (unitId, currentSelectors) {
        CMS.updateFormFieldConfig(unitId, 'selectorChooser', {
            options: currentSelectors || [
                ['', '']
            ]
        });
    };

    return {
        // init on different "events"
        init: function (data) {

            i18n = JSON.parse(data.i18n);

            var eventFilter = {moduleId: data.moduleId};
            // formValueChange
            CMS.on('formValueChange', eventFilter, formValueChangeHandler);

            // init all units of this module after dom ready once
            CMS.getAllUnitIds(data.moduleId).forEach(function (unitId) {
                initUnit({unitId: unitId});
            });
        }
    };

});


