define(['jquery', 'CMS', 'rz_root/notlive/js/baseJsModule'], function ($, CMS, JsModule) {

    var _i18nPagePropertyMap = {
        pageTitle: 'selector.headline',
        description: 'selector.text',
        date: 'selector.date',
        link: 'selector.link',
        image: 'selector.image',
        additionalFields: 'selector.additionalFields'
    };

    return JsModule.extend({

        /**
         * Updates the name of the unit with the current page property name
         * @param {String} unitId The id of the unit to update
         * @private
         */
        updateUnitName: function (unitId) {
            var unit = CMS.get(unitId);
            var pageProperty = unit.formValues.type.value;
            var name = this.i18n(_i18nPagePropertyMap[pageProperty] || pageProperty);
            CMS.setInfo(unitId, 'pageProperty', name);
        },

        initUnit: function (unitId) {
            this.updateUnitName(unitId);
        },

        onFormValueChange: function (cfg) {
            if (cfg.key === 'type') {
                this.updateUnitName(cfg.unitId);
            }
        }
});
});
