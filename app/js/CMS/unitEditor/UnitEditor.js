Ext.ns('CMS.unitEditor');

/**
 * Abstract unit editor class
 * @class CMS.unitEditor.UnitEditor
 */
CMS.unitEditor.UnitEditor = Ext.extend(Ext.Panel, {
    /** @lends CMS.unitEditor.UnitEditor.prototype */

    layout: 'vbox',
    layoutConfig: {
        align: 'stretch'
    },
    cls: 'CMSuniteditor',
    buttonAlign: 'left',
    collapsible: false,
    title: '&#160;',

    // the length until the title will get truncated
    titleLength: 40,

    /**
     * The currently opened website's id
     * @property websiteId
     * @type String
     */
    websiteId: '',

    /**
     * (required) One of 'page'/'template'
     * @property mode
     * @type String
     */
    mode: '',

    /**
     * Maps the id of the form groups to
     * the index of it form panel in order
     * to open the form group via its id.
     * @property formGroupIdMapping,
     * @type {Object}
     */
    formGroupIdMapping: null,

    initComponent: function () {
        this.title = CMS.i18n('Eigenschaften');

        CMS.unitEditor.UnitEditor.superclass.initComponent.apply(this, arguments);

        this.formGroupIdMapping = {};

    },

    /**
     * Notify the unit editor about changes of the page/template
     * structure, i.e. if a unit was moved, added or removed
     */
    onStructureChange: function () {
        if (this.currentUnit) {
            if (!this.currentUnit.store) {
                // unit has been removed from page/template
                this.currentUnit = null;
            }
        }
    },

    /**
     * Instantiates and opens the "Insert Unit" dialog
     * @param {CMS.data.UnitRecord} [unit] (optional) The unit in whose context the window is opened
     * @param {Integer} [position] (optional) The default position choice in the dialog.
     * Possible values:
     * <ul>
     *      <li>-1 - above</li>
     *      <li> 0 - inside (default)</li>
     *      <li> 1 - below</li>
     * </ul>
     */
    openInsertWindow: function (unit, position) {
        var ownerUnit = unit || this.currentUnit;
        if (ownerUnit) {
            var win = new CMS.liveView.InsertUnitWindow({
                ownerUnit: ownerUnit,
                mode: this.mode,
                position: (typeof position == 'number') ? position : 0
            });
            this.relayEvents(win, ['CMSinsertunit']);
            win.show();
        }
    },

    /**
     * Handles the value change event of the generated form panel.
     * @private
     * @param {Ext.Component} cmp The edited form component
     * @param {Object} valueObj An object describing the edited value
     */
    valueChangeHandler: function (cmp, valueObj) {
        if (this.loading) {
            // config has not changed; form is right now being filled with data
            return;
        }

        // save record
        this.saveRecord(valueObj);

    },

    render: function () {
        // prevent configchange event from causing a reload during render
        this.loading = true;
        CMS.unitEditor.UnitEditor.superclass.render.apply(this, arguments);
        this.loading = false;
    },


    /**
     * Save the all form values or updates the form values with the given valueChangeData
     * @param {Object} [valueChangeData] changed key: value
     */
    saveRecord: function (valueChangeData) {
        this.currentUnit.beginEdit();
        var formValues = SB.util.cloneObject(this.currentUnit.get('formValues'));

        if (valueChangeData) {
            formValues[valueChangeData.key] = valueChangeData.newValue;
        } else {
            this.accordionPanel.items.each(function (item) {
                Ext.apply(formValues, item.getValues());
            }, this);
        }

        this.currentUnit.set('formValues', formValues);

        //Store modified attributes because calling commit will remove those
        this.currentUnit.modifiedUnitAttributes = this.currentUnit.modified;

        if (valueChangeData) {
            // execute (blocking) formValueChange listeners (X-doc-API)
            this.fireEvent('CMSformvaluechange', Ext.apply(valueChangeData, {unitId: this.currentUnit.id}));
        }

        this.currentUnit.commit();
    },


    /**
     * Updates a form field with the given CMSvar without firing events
     * @param {String} cmsVar The CMSvar of the form field
     * @param {String} value The new value
     */
    updateField: function (cmsVar, value) {
        this.loading = true;
        var valueObj = {};
        valueObj[cmsVar] = value;
        this.accordionPanel.items.each(function (item) {
            if (item.setValues) {
                item.setValues(valueObj);
            }
        });
        this.loading = false;
    },

    /**
     * Updates a form fields config
     * @param {String} cmsVar The CMSvar of the form field
     * @param {Object} config The new params of the component
     */
    updateFieldConfig: function (cmsVar, config) {
        var configObj = {};
        configObj[cmsVar] = config;
        this.accordionPanel.items.each(function (item) {
            if (item.updateFormConfig) {
                item.updateFormConfig(configObj);
            }
        });
    },

    /**
     * Update when the unit's name changes
     * @param {String} name The unit's new name
     */
    setUnitName: function (name) {
        if (this.metaEditor) {
            this.metaEditor.getForm().setValues({ name: name });
        }
        this.onStructureChange();
    },

    /**
     * Clears the editor
     */
    clearEditor: function () {
        this.setDisabled(true);
    },

    /**
     * Opens the form group which corresponds to the given
     * form group id.
     * @param {String} formGroupId The UUID of a form group
     */
    openFormPanel: function (formGroupId) {
        var panelIndex = this.formGroupIdMapping[formGroupId];
        if (Ext.isNumber(panelIndex)) {
            if (this.accordionPanel.rendered && panelIndex >= 0 && panelIndex < this.accordionPanel.items.length) {
                this.accordionPanel.getLayout().setActiveItem(panelIndex);
            }
        }
    },

    /**
     * Applies formGroupDataOverrides to a given formGroupData
     *
     * @param {Object} formGroupData formGroup data array with component objects with params[] attribute
     * @param {Object} formGroupDataOverrides   <nameOfTheVar>: {<paramkey>: <value>}
     * @return {Object} modified version of formGroupData (copy)
     */
    applyFormGroupDataOverrides: function (formGroupData, formGroupDataOverrides) {

        if (!formGroupDataOverrides) {
            return formGroupData; // end here
        }

        // clone formGroupData to avoid persistent changes (module store)
        var fgData = SB.util.cloneObject(formGroupData);

        // fgData is an array of objects which contain descr and params
        Ext.each(fgData, function thisFn(cmp) {

            // recursively look into items (container fields like form group data)
            if (cmp.items) {
                Ext.each(cmp.items, thisFn);
            }

            // find name of form field
            var varName;
            Ext.each(cmp.params, function (param) {
                if (param.name == 'CMSvar') {
                    varName = param.value;
                    return false;
                }
            });

            // look if there are overrides for this field
            if (varName && formGroupDataOverrides.hasOwnProperty(varName)) {
                // fetch all config params for this varName
                var updateParams = formGroupDataOverrides[varName];

                // update existing (!) params of component
                var foundParams =  [];
                Ext.each(cmp.params, function (param) {
                    if (updateParams.hasOwnProperty(param.name)) {
                        foundParams.push(param.name);
                        param.value = updateParams[param.name];
                    }
                });

                // add all params which are not updated before (new params)
                Ext.iterate(updateParams, function (key, value) {
                    // this key was not found above
                    if (foundParams.indexOf(key) == -1) {
                        // add new param
                        console.info('[UnitEditor]  applyFormGroupDataOverrides add new param with key', key, 'value', value);
                        cmp.params.push({name: key, value: value});
                    }
                });

            }
        });

        return fgData;
    }
});
