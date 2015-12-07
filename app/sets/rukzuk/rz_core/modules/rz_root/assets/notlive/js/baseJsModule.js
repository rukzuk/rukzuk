define(['jquery', 'CMS'], function ($, CMS) {
    'use strict';

    return {
        extend: function (overrides) {
            return $.extend({

                onFormValueChange: undefined,

                onUnitSelect: undefined,

                onUnitDeselect: undefined,

                onResolutionChange: undefined,

                onVisualHelpersChange: undefined,

                /**
                 * Hook method for initializing code that depends on a single unit
                 * The hook will be called after initializing the module-dependent
                 * code {@link #init} and after rerendering a unit's html ("unit replace")
                 *
                 * @param {String} unitId The id of the unit that should be initialize
                 */
                initUnit: undefined,

                /**
                 * Initializes the JS module
                 *
                 * @param {Object} cfg A configuration object
                 * @param {String} cfg.moduleId The id of the rukzuk module
                 * @param {Object} cfg.i18n An object with texts/translations to be used in js code
                 * @param {String} cfg.assetUrl The url to the module assets
                 */
                init: function (cfg) {
                    this.moduleId = cfg.moduleId;
                    this.assetUrl = cfg.assetUrl;
                    try {
                        this.dic = JSON.parse(cfg.i18n);
                    } catch (e) {
                        this.dic = cfg.i18n;
                    }

                    // attach listeners to CMS events
                    var eventFilter = {
                        moduleId: this.moduleId
                    };

                    if (this.onFormValueChange) {
                        CMS.on('formValueChange', eventFilter, this.onFormValueChange, this);
                    }
                    if (this.onUnitSelect) {
                        CMS.on('unitSelect', eventFilter, this.onUnitSelect, this);
                    }
                    if (this.onUnitDeselect) {
                        CMS.on('unitDeselect', eventFilter, this.onUnitDeselect, this);
                    }
                    if (this.onResolutionChange) {
                        CMS.on('resolutionChange', this.onResolutionChange, this);
                    }
                    if (this.onVisualHelpersChange) {
                        CMS.on('visualHelpersStateChange', this.onVisualHelpersChange, this);
                    }
                    if (this.initUnit) {
                        // call init method for all existing units of this module
                        CMS.getAllUnitIds(this.moduleId).forEach(function (unitId) {
                            this.initUnit(unitId);
                        }, this);

                        // init unit after re-rerendering unit html (unit-replace)
                        CMS.on('afterRenderUnit', eventFilter, this.initUnit, this);
                    }
                    if (this.initModule) {
                        this.initModule();
                    }
                },

                /**
                 * Get the object with language data by key of this module.
                 * Defined in custom.json
                 *
                 * @param {String} key The translation key
                 * @return {Object|String} The lang object or the key if no translation for
                 *      the given key is available
                 */
                i18n: function (key) {
                    var translation = this.dic && this.dic[key];
                    return translation || key;
                },

                /**
                 * Translate a key in the current interface lang.
                 *
                 * @param {String} key The translation key
                 * @returns {String} translated text
                 */
                translate: function (key) {
                    return CMS.i18n(this.i18n(key));
                },

            }, overrides);
        }
    };
});
