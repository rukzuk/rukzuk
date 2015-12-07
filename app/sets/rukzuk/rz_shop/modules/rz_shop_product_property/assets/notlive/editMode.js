define(['jquery', 'CMS', 'rz_root/notlive/js/baseJsModule'], function ($, CMS, JsModule) {

    var _i18nPagePropertyMap = {
        pageTitle: 'selector.headline',
        description: 'selector.text',
        date: 'selector.date',
        link: 'selector.link',
        image: 'selector.image',
        // additions of product
        'price': 'selector.price',
        'vat': 'selector.vat',
        'addcart': 'selector.addcart'
    };

    return JsModule.extend({

        /**
         * Updates the name of the unit with the current page property name
         * @param {String} unitId The id of the unit to update
         * @private
         */
        updateUnitName: function (unitId) {
            var unit = CMS.get(unitId);
            var productProperty = unit.formValues.type.value;
            var name = this.i18n(_i18nPagePropertyMap[productProperty] || productProperty);
            CMS.setInfo(unitId, 'productProperty', name);
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
