Ext.ns('CMS.form');

CMS.form.RadioFieldSetPlugin = {

    /**
    * Initialize plugin and set up event listeners.
    * @param {Ext.Component} owner Component which owns the plugin
    */
    init: function (owner) {
        this.Factory.prototype = this;
        var instance = new this.Factory(owner);
        owner.on('createwrapper', this.createWrapperHandler, instance);
        owner.on('valuechanged', this.valueChangedHandler, instance);
        owner.on('paramschanged', this.paramsChangedHandler, instance);
        owner.on('insert', this.insertHandler, instance);
        owner.on('beforedelete', this.beforeDeleteHandler, instance);
        owner.on('destroy', function () {
            instance = null;
        });
    },

    // create one instance per owner; SBCMS-520
    Factory: function (owner) {
        this.owner = owner;
    },

    /**
    * Get all field sets that belong to the given group
    * @param {String} name Name of the group
    * @return {Array} Array of field sets
    */
    getGroup: function (name) {
        return this.owner.findBy(function (formField) {
            return formField.isXType('CMSradiofieldset') && formField.checkboxName === name;
        }, this);
    },

    /**
    * Called when a wrapper is created. Sets the name for the radio element to the variable name of the field set.
    * @param {Object} wrapperCfg Configuration object of the wrapper
    * @param {Object} varCfg Configuration object of the form field's variable
    * @private
    */
    createWrapperHandler: function (wrapperCfg, varCfg) {
        //console.log('[RadioFieldSetPlugin] onCreateWrapper', wrapperCfg, varCfg);
        var innerCfg = wrapperCfg.items;

        if (innerCfg.xtype === 'CMSradiofieldset') {
            innerCfg.checkboxName = varCfg.value;
        }
    },

    /**
    * Called when the value of the field set changes. Ensures that all field sets in the same group act accordingly.
    * @param {Object} cmp The (wrapper) component containing the form field
    * @param {Object}} valueObj The object which contains the value which has been set
    * @private
    */
    valueChangedHandler: function (cmp, valueObj) {
        var formField = cmp.items.get(0),
            group;

        if (!formField || !formField.isXType('CMSradiofieldset')) {
            return;
        }
        console.log('[RadioFieldSetPlugin] onValueChanged');

        if (valueObj.newValue === formField.groupValue) {
            group = this.getGroup(formField.checkboxName);
            this.setGroupValue(group, valueObj.newValue);
        }

    },

    /**
    * Called when a new fieldset is inserted. Ensures that it gets a unique variable value and that only
    * one field set in the group is selected afterwards.
    * @param {Object} cmp The (wrapper) component containing the form field
    * @private
    */
    insertHandler: function (cmp) {
        var formField = cmp.items.get(0),
            counter = 1,
            group,
            groupValue,
            originalFieldSet,
            unique;
        var makeUnique = function (fieldSet) {
            if (fieldSet !== formField) {
                if (fieldSet.groupValue === formField.groupValue) {
                    unique = false;
                    if (counter == 1) {
                        originalFieldSet = fieldSet;
                    }
                }
            }
            return unique;
        };

        if (!formField || !formField.isXType('CMSradiofieldset')) {
            return;
        }

        group = this.getGroup(formField.checkboxName);

        Ext.each(cmp.params, function (item, index) {
            if (item.name === 'groupValue') {
                groupValue = item;
            }
        }, this);

        do {
            unique = true;
            Ext.each(group, makeUnique, this);
            if (!unique) {
                formField.groupValue = groupValue.value = originalFieldSet.groupValue + counter;
                counter++;
            }
        } while (!unique);

        if (originalFieldSet) {
            originalFieldSet.setValue(formField.value);
        }

        this.cleanUpGroup(group);
    },

    /**
    * Called before a field set is deleted. Ensures another field set in the same group gets expanded if
    * the deleted field set was expanded.
    * @param {Object} cmp The (wrapper) component containing the form field
    * @private
    */
    beforeDeleteHandler: function (cmp) {
        var formField = cmp.items.get(0),
            group;

        if (!formField || !formField.isXType('CMSradiofieldset')) {
            return;
        }

        if (formField.value === formField.groupValue) {
            group = this.getGroup(formField.checkboxName);
            group.remove(formField);

            this.cleanUpGroup(group);
        }
    },

    /**
    * Called when the parameters of a field set are changed. Handles changing of the variable name - triggers
    * a clean up of the old as well as the new group - and changing of the variable value - if the changed
    * field set was expanded, the new value must be set to the whole group.
    * @param {Object} oldParams The parameters before the change
    * @param {Object} newParams The parameters after the change
    * @private
    */
    paramsChangedHandler: function (oldParams, newParams) {
        console.log('[RadioFieldSetPlugin] paramsChangedHandler', oldParams, newParams);
        var oldVar, oldGroupValue, newVar, newGroupValue;

        var isRadio;

        Ext.each(oldParams, function (param) {
            if (param.name == 'groupValue') {
                oldGroupValue = param.value;
            }
            if (param.name == 'CMSvar') {
                oldVar = param.value;
            }
            if (param.name == 'xtype' && param.value == 'CMSradiofieldset') {
                isRadio = true;
            }
        });

        if (!isRadio) {
            return;
        }

        Ext.each(newParams, function (param) {
            if (param.name == 'groupValue') {
                newGroupValue = param.value;
            }
            if (param.name == 'CMSvar') {
                newVar = param.value;
            }
        });

        if (oldGroupValue === newGroupValue && oldVar === newVar) {
            return;
        }

        if (oldVar !== newVar) {
            this.cleanUpGroup(oldVar);
            this.cleanUpGroup(newVar);
        } else {
            this.setGroupValue(newVar, newGroupValue);
        }

    },

    /**
    * Ensures exactly one field set of the given group is expanded.
    * @param {String/Array} group Either the id of a group or an array containing the respective field sets
    * @private
    */
    cleanUpGroup: function (group) {
        var checked = [], checkedFieldSet;

        if (Ext.isString(group)) {
            group = this.getGroup(group);
        }

        Ext.each(group, function (fieldSet) {
            if (fieldSet.value === fieldSet.groupValue) {
                checked.push(fieldSet);
            }
        }, this);

        if (checked.length !== 1) {
            checkedFieldSet = checked[0] || group[0];
        }

        if (checkedFieldSet) {
            this.setGroupValue(group, checkedFieldSet.groupValue);
        }
    },

    /**
    * Sets the given value to all field sets in the given group
    * @param {String/Array} group Either the id of a group or an array containing the respective field sets
    * @param {String} newValue The value to be set
    * @private
    */
    setGroupValue: function (group, newValue) {
        if (Ext.isString(group)) {
            group = this.getGroup(group);
        }

        this.owner.suspendEvents();
        Ext.each(group, function (fieldSet) {
            fieldSet.setValue(newValue);
        }, this);
        this.owner.resumeEvents();
    }
};

Ext.preg('CMSradiofieldsetplugin', CMS.form.RadioFieldSetPlugin);
