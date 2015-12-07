Ext.ns('CMS.form');

/**
 * Tries to convert old legacy components into
 * new ones.
 * @class CMS.form.LegacyComponentConverter
 * @singleton
 */
CMS.form.LegacyComponentConverter = {
    /**
     * The mapping between old and new
     * components
     * @property mapping
     * @type {Object}
     */
    mapping: {
        'ux-colorpicker': {
            xtype: 'ux-colorpickerfield',
            enableAlpha: {
                name: 'enableAlpha',
                value: true,
                xtype: 'checkbox',
                fieldLabel: 'Transparenz (Alphakanal)'
            },
            returnColorNames: {
                name: 'returnColorNames',
                value: true,
                xtype: 'checkbox',
                fieldLabel: 'Farbnamen benutzen'
            }
        },
        'CMSmodulecombobox': {
            xtype: 'CMSmodulechooser'
        },
        'CMSclearableimagebutton': {
            xtype: 'CMSclearablemediabutton'
        },
        'ux-colorschemepicker': {
            xtype: 'CMScolorschemepickerwitheditbutton'
        },
        'fieldset': {
            xtype: 'fieldset',
            collapsible: {
                name: 'collapsible',
                value: false,
                xtype: null
            }
        }
    },

    /**
     * The parameter array which is currently converted
     * @property parameters
     * @type {Array}
     * @private
     */
    parameters: [],

    /**
     * The name keys of all parameter objects
     * @property parameterKeys
     * @type {Array}
     * @private
     */
    parameterKeys: [],

    /**
    * Checks whether a components is a legacy one and
    * tries to convert it if necessary
    * @param {Array} parameters The array of parameters
    * describing the component
    * @param {Object} description (Optional) The object describing the component
    * @return {Object} The converted array and an array with information about possible errors.
    * Errors take the following form:<pre>
    {
        type: string,
        xtypes: {
            before: oldXtype,
            after: newXtype
        }
    }</pre>
    *
    * <tt>type</tt> can be one of
    <ul>
    <li>'unknown': The xtype is not valid. The form element was replaced with a textfield
    <li>'converted': The xtype is obsolete and was converted to another xtype
    <li>'paramschanged': The xtype is valid, but some parameters may have been changed.
    </ul>
    */
    convert: function (parameters, description) {
        this.parameters = SB.util.cloneObject(parameters);
        var errors = [];
        Ext.each(this.parameters, function (param) {
            if (param && (param.name === 'xtype' || param.xtype)) {
                var error = this.checkXType(param, description);
                if (error) {
                    errors.push(error);
                }
            }
        }, this);
        return {
            parameters: this.parameters,
            description: description,
            errors: errors
        };
    },

    /**
    * @private
    * Checks whether the objects inside the parameter array need
    * to be modified or augmented
    * @param {String} xtype The xtype of the component
    */
    checkParameters: function (xtype) {
        this.parameterKeys = Ext.pluck(this.parameters, 'name');
        var mappingKeys = SB.util.getKeys(this.mapping[xtype]).remove('xtype');
        this.modifyParameters(mappingKeys, xtype);
        this.augmentParameters(mappingKeys, xtype);
    },

    /**
    * @private
    * We check if any key in the mapping object is not
    * in the parameter keys object meaning the new component
    * has more parameter objects than the old one and
    * we have to add the new ones.
    * @param {Array} mappingKeys The name key of the mapping
    * @param {String} xtype The current xtype
    */
    augmentParameters: function (mappingKeys, xtype) {
        Ext.each(mappingKeys, function (key) {
            if (this.parameterKeys.indexOf(key) === -1) {
                this.parameters.push(SB.util.cloneObject(this.mapping[xtype][key]));
            }
        }, this);
    },

    /**
    * @private
    * We check if any parameter object in the parameter array
    * has the same key as the ones in our mapping object meaning they
    * need to be overwritten
    * @param {Array} mappingKeys The name key of the mapping
    * @param {String} xtype The current xtype
    */
    modifyParameters: function (mappingKeys, xtype) {
        var intersection = SB.util.setIntersection(this.parameterKeys, mappingKeys);
        if (intersection.length) {
            Ext.each(intersection, function (key) {
                Ext.each(this.parameters, function (paramObj, index) {
                    if (paramObj.name === key) {
                        this.parameters[index] = SB.util.cloneObject(this.mapping[xtype][key]);
                    }
                }, this);
            }, this);
        }
    },

    /**
    * @private
    * Checks whether the xtype is legacy one or unknown one
    * @param {Object} param The parameter object of form element
    * @param {Object} description (Optional) The object describing the component
    * @return {Object} The potential error
    */
    checkXType: function (param, description) {
        var error;
        var newXtype;
        var propertyName;
        if (param.xtype) {
            propertyName = 'xtype';
        } else if (param.value) {
            propertyName = 'value';
        }
        if (propertyName) {
            // We checks if the xtype can be found
            // in our mapping, which meanings it
            // belongs to a legacy component
            if (this.mapping[param[propertyName]]) {
                newXtype = this.mapping[param[propertyName]].xtype;
                if (propertyName === 'value') {
                    this.checkParameters(param[propertyName]);
                }
                error = {
                    xtypes: {
                        before: param[propertyName],
                        after: newXtype
                    }
                };
                error.type = (error.xtypes.before == error.xtypes.after) ? 'paramschanged' : 'converted';
                // We give it the correct xtype
                param[propertyName] = newXtype;
            // Additionally we check whether the given xtype
            // has been registered at all
            } else if (!Ext.ComponentMgr.types[param[propertyName]]) {
                newXtype = 'textfield';
                error = {
                    type: 'unknown',
                    xtypes: {
                        before: param[propertyName],
                        after: newXtype
                    }
                };
                // The text field is our fallback xtype
                param[propertyName] = newXtype;
                if (description) {
                    description.allowChildNodes = false;
                }
            }
        }
        return error;
    }
};
