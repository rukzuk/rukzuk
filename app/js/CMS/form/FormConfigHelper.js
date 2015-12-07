Ext.ns('CMS.form');

/**
 * Converter for the new formConfig format (v2)
 * See js/CMS/config/formElements.md
 * @type {Object}
 */
CMS.form.FormConfigHelper = Ext.apply({}, {

    /**
     * Converts a formConfig (v2) object using CMS.config.formElements to the legacy (full) format (v1)
     * @public
     * @param {Array} formConfig
     * @param {Array} [form]
     */
    fromConfigToForm: function thisFn(formConfig, form) {
        form = form || [];
        Ext.each(formConfig, function (fc) {

            var formElem = this._getFormElement(fc.type);

            if (formElem) {
                var formObj = this._cloneObj(formElem);

                Ext.each(Object.keys(fc), function (cfgKey) {
                    var cfgValue = fc[cfgKey];

                    // special keys
                    if (cfgKey === 'CMSvar') {
                        formObj.params.push(this._addCMSvarParam(cfgValue));
                    }

                    // update values
                    var param = this._getFormElementParam(formObj.params, fc.type, cfgKey);
                    if (param) {
                        param.value = cfgValue;
                    } else {
                        console.warn('[CMS.form.FormConfigHelper] configured param not found', fc.type, cfgKey, cfgValue);
                    }

                }, this);

                // add to form
                form.push(formObj);

                // handle items (children)
                if (fc._items) {
                    formObj.items = [];
                    thisFn.call(this, fc._items, formObj.items);
                }
            } else {
                console.warn('[CMS.form.FormConfigHelper] form id (type) not found', fc.type);
            }
        }, this);

        return form;
    },

    /**
     * Get a Form element by its id
     * @private
     * @param {String} formElementId
     * @returns {Object} the form element
     */
    _getFormElement: function (formElementId) {
        try {
            return CMS.config.formElements[CMS.config.formElementsIndex[formElementId].idx];
        } catch(e) {
            return null;
        }
    },

    /**
     * Extract a params object out of an array of params (using form config index)
     * @private
     * @param {Array} params - the params array (with objects)
     * @param {String} formElementId - the id of the form element (required for the index lookup)
     * @param {String} paramName - the name of the param
     * @returns {Object} - the param object
     */
    _getFormElementParam: function (params, formElementId, paramName) {
        try {
            return params[CMS.config.formElementsIndex[formElementId].paramIdx[paramName]];
        } catch(e) {
            return null;
        }
    },

    /**
     * Adds a valid CMSvar to the param
     * @private
     * @see CMS.form.GeneratedFormPanel#getClonedArray
     * @param value
     */
    _addCMSvarParam: function (value) {
        return Ext.apply({}, {
            name: 'CMSvar',
            value: value,
            xtype: 'textfield',
            emptyText: '__i18n_generatedFormPanel.varNameEmptyText',
            fieldLabel: '__i18n_generatedFormPanel.varNameLabel'
        }, CMS.config.validation.CMSvar);
    },

    /**
     * Clone (deep) Object
     * @param obj
     * @returns {*}
     * @private
     */
    _cloneObj: function(obj) {
        return SB.util.cloneObject(obj);
    }

});