/* global module, require */
/* jshint es5: true */

//var _ = require('lodash');

/**
 * Converts old responsive module to new responsive module form
 *
 * @param {Object} moduleObj - data in this object will be changed!
 * @returns {Object} convertedObj - new structure
 */
var convertLegacy = function (moduleObj) {
    // new module object which only contains the relevant accordion-pages
    var convertedObj = {};
    convertedObj.form = [];

    // convert radiofieldsets (in all accordion-pages)
    moduleObj.form.forEach(function (form) {
        form.formGroupData = convertRadioFSToTabbedFS(form.formGroupData, moduleObj.formValues);
    });

    // convert responsive fields (only in the first accordion-page)
    // assume the first one is always the "All Breakpoints" tab
    var form0 = moduleObj.form[0];
    convertResponsiveFields(form0.formGroupData);


    // update name of "All Breakpoints"
    if (form0.name.match(/All breakpoints/)) {
        form0.name = JSON.stringify({de: 'Modul Einstellungen', en: 'Module Settings'});
    }

    // always add the first accordion-page (as its the most important one)!
    convertedObj.form.push(form0);

    // remove responsive accordion pages
    for (var i = 1; i < moduleObj.form.length; i++) {
        var form = moduleObj.form[i];
        // responsive tabs
        var match = form.name.match(/Breakpoint ([1-3])/);
        if (!match) {
            // other tabs
            convertedObj.form.push(form); // TODO: also convert radio field sets!
        }
    }

    // formValues: convert to responsive values
    convertedObj.formValues = convertFormValues(moduleObj.formValues, true);

    return convertedObj;
};


/**
 * Converts the formValues to new responsive format
 *
 * @param formValues
 * @param onlySetDefaultValue - skip the values of res1 - res3 (otherwise they are explicitly set)
 * @returns {}
 */
var convertFormValues = function (formValues, onlySetDefaultValue) {
    var respFormValues = {};

    Object.keys(formValues).forEach(function (fvName) {
        // look for responsive values
        var match = fvName.match(/^(css.+)([0-9])$/g);
        if (match) {
            var newName = match[1];
            var resNum = Number(match[2]);
            var resId = resNum === 0 ? 'default' : 'res' + resNum;

            if (!respFormValues[newName] || !respFormValues[newName].type) {
                respFormValues[newName] = {type: 'bp'};
            }
            // skip all others but default if onlySetDefaultValue = true
            if ((onlySetDefaultValue && resNum === 0) || !onlySetDefaultValue) {
                respFormValues[newName][resId] = formValues[fvName];
            }
        } else {
            // just copy (non responsive values)
            respFormValues[fvName] = formValues[fvName];
        }

    });

    return respFormValues;
};

/**
 * Replaces all CMSradiofieldset params in an formGroupData Array
 * with the CMStabbedfieldsets, which sounds easy, but isn't ...
 *
 * @param {Array} fgData - fgData array, changes are made in place (please copy if you need the old data)
 * @param {Object} formValues - the form Values are required to pre-define the value of new tabbed fieldsets
 * @param {Object} [parentFormElement] - optional parent form element (useful if called for formElements.items)
 * @returns {Array} - new fgData Array
 */
var convertRadioFSToTabbedFS = function thisFn(fgData, formValues, parentFormElement) {

    var newFgData = [];
    var createdTabbedFieldSets = {};

    fgData.forEach(function (formElement) {

        // transform params Array to an object with the name property as key
        var paramColl = {};
        formElement.params.forEach(function (param) {
            paramColl[param.name] = param;
        });

        // remove label align (to avoid the hide value!)
        removeObjFromArray(formElement.params, paramColl.labelAlign);

        // radio fieldsets
        if (paramColl.xtype && paramColl.xtype.value === 'CMSradiofieldset') {
            console.info('[convertRadioFSToTabbedFS] convert CMSradiofieldset');

            // convert radiofieldset to tabPage
            formElement.descr = {
                text: '__i18n_formElements.elementTabPage',
                iconCls: 'tabpage',
                hasValue: false,
                allowChildNodes: true
            };

            // change and remove params
            var cmsVar = paramColl.CMSvar.value;
            paramColl.xtype.value = 'CMStabpage';
            paramColl.groupValue.name = 'tabValue';
            paramColl.title.allowBlank = false;
            paramColl.title.emptyText = '__i18n_formElements.title';

            // remove old params of radiofieldset
            removeObjFromArray(formElement.params, paramColl.CMSvar);
            removeObjFromArray(formElement.params, paramColl.setAdditionalCls);
            removeObjFromArray(formElement.params, paramColl.value);

            // create Tabbed page for this radiofieldset "name" if there is none yet
            if (!createdTabbedFieldSets[cmsVar]) {
                // default field label for generated tabbbed field set
                var generatedFieldLabel = '';

                // try to get the label (title) of the parent
                if (parentFormElement) {
                    var parentParamColl = {};
                    parentFormElement.params.forEach(function (param) {
                        parentParamColl[param.name] = param;
                    });

                    if (parentParamColl.title && parentParamColl.title.value) {
                        generatedFieldLabel = parentParamColl.title.value;
                    } else if (parentParamColl.fieldLabel && parentParamColl.fieldLabel.value) {
                        generatedFieldLabel = parentParamColl.fieldLabel.value;
                    }

                }

                // tabbed field set
                var newTabbedFieldset = {
                    descr: { text: '__i18n_formElements.elementTabbedFieldSet', iconCls: 'tabbedfieldset', hasValue: true, allowChildNodes: true },
                    params: [ {
                        "name": "CMSvar",
                        "value": cmsVar,
                        "allowBlank": false,
                        "minLength": 3,
                        "maskRe": {},
                        "stripCharsRe": {},
                        "xtype": "textfield",
                        "emptyText": "__i18n_generatedFormPanel.varNameEmptyText",
                        "fieldLabel": "__i18n_generatedFormPanel.varNameLabel"
                    }, {
                        name: 'xtype',
                        value: 'CMStabbedfieldset',
                        xtype: null // not configurable
                    }, {
                        name: 'isMeta',
                        value: false,
                        fieldLabel: '__i18n_formElements.isGlobal',
                        xtype: 'checkbox'
                    }, {
                        name: 'fieldLabel',
                        value: generatedFieldLabel,
                        xtype: 'textfield',
                        allowBlank: true,
                        emptyText: '__i18n_formElements.fieldLabelOptionalEmptyText',
                        fieldLabel: '__i18n_formElements.fieldLabel'
                    }, {
                        name: 'value',
                        xtype: null,
                        value: formValues[cmsVar]
                    }],
                    items: []
                };
                // remember this tabbed field set (by cmsVar - as this is the key to match the radiofields)
                createdTabbedFieldSets[cmsVar] = newTabbedFieldset;
                newFgData.push(newTabbedFieldset);
            }
            createdTabbedFieldSets[cmsVar].items.push(formElement);

        }
        // checkbox -> on off
        else if (paramColl.xtype && paramColl.xtype.value === 'checkbox') {
            // change xtype
            paramColl.xtype.value = 'CMSonofftogglebutton';

            // merge title and boxLabel
            var boxLabel = paramColl.boxLabel && paramColl.boxLabel.value ? JSON.parse(paramColl.boxLabel.value) : {de: '', en: ''};
            var fieldLabel = paramColl.fieldLabel && paramColl.fieldLabel.value ? JSON.parse(paramColl.fieldLabel.value) : {de: '', en: ''};

            console.info('[convertRadioFSToTabbedFS] checkobox boxLabel', boxLabel, 'fieldLabel', fieldLabel);

            ['de', 'en'].forEach(function (lang) {
                var newLabel = [];
                if (fieldLabel[lang] && fieldLabel[lang] != 'Label') {
                    newLabel.push(fieldLabel[lang]);
                }

                if (boxLabel[lang]) {
                    newLabel.push(boxLabel[lang]);
                }

                fieldLabel[lang] = newLabel.join(' / ');
            });

            console.info('[convertRadioFSToTabbedFS] checkbox merged', fieldLabel);

            // set merged field label
            paramColl.fieldLabel.value = JSON.stringify(fieldLabel);

            removeObjFromArray(formElement.params, paramColl.boxLabel);

            // push element
            newFgData.push(formElement);
        }
        // Checkbox FieldSets
        else if (paramColl.xtype && paramColl.xtype.value === 'CMScheckboxfieldset') {
            // change xtype
            paramColl.xtype.value = 'CMSonofffieldset';
            // remember title
            var title = paramColl.title.value;
            // remove title form params
            removeObjFromArray(formElement.params, paramColl.title);
            // add fieldLabel with content of title
            formElement.params.push({
                name: 'fieldLabel',
                value: title,
                xtype: 'textfield',
                allowBlank: true,
                emptyText: '__i18n_formElements.fieldLabelOptionalEmptyText',
                fieldLabel: '__i18n_formElements.fieldLabel'
            });

            // push element
            newFgData.push(formElement);
        }
        else if (paramColl.xtype && paramColl.xtype.value === 'fieldset') {
            paramColl.xtype.value = 'CMSfieldset';

            // convert title to fieldLabel
            var fsTitle = paramColl.title.value;
            removeObjFromArray(formElement.params, paramColl.title);
            formElement.params.push({
                name: 'fieldLabel',
                value: fsTitle,
                xtype: 'textfield',
                allowBlank: false,
                emptyText: '__i18n_formElements.fieldLabelOptionalEmptyText',
                fieldLabel: '__i18n_formElements.fieldLabel'
            });

            // push element
            newFgData.push(formElement);

        } else {
            console.info('[convertRadioFSToTabbedFS] just copy element');
            newFgData.push(formElement);
        }

        // recursive call
        if (formElement.items) {
            formElement.items = thisFn(formElement.items, formValues, formElement);
        }
    });

    return newFgData;
};


/**
 * Converts old form group data arrays to new responsive format
 * @param Array fgData - formGroupData Array
 */
var convertResponsiveFields = function thisFn(fgData) {


    fgData.forEach(function (formElement) {

        // recursive call
        if (formElement.items) {
            thisFn(formElement.items);
        }

        var responsiveValueFound = false;

        // transform params Array to an object with the name property as key
        var paramColl = {};
        formElement.params.forEach(function (param) {
            paramColl[param.name] = param;
        });

        // radio fieldsets
        if (paramColl.xtype && paramColl.xtype.value === 'CMSradiofieldset') {
            console.info('[convertResponsiveFields] skipped CMSradiofieldset');
            return true;
        }

        // skip the toggle button (its only used to trigger js actions!)
        if (paramColl.xtype && paramColl.xtype.value === 'CMStogglebutton') {
            console.info('[convertResponsiveFields] skipped CMStogglebutton');
            return true;
        }

        // param has CMS variable
        if (paramColl.CMSvar) {
            // change CMSvar: remove 0 in CMSvar = cssSomeProp0
            var cmsVarValueMatch = paramColl.CMSvar.value.match(/(css.+)0/);
            if (cmsVarValueMatch) {
                responsiveValueFound = true;
                var cmsVarValue = cmsVarValueMatch[1];
                paramColl.CMSvar.value = cmsVarValue;
                // convert value to responsive value
                if (paramColl.value && (!paramColl.value.value || !paramColl.value.value.type)) {
                    paramColl.value.value = {
                        'type': 'bp',
                        'default': paramColl.value.value
                    };
                }

            }

            // add responsive settings to this params array
            if (responsiveValueFound && !paramColl.isResponsive) {
                formElement.params.push({
                    name: 'isResponsive',
                    value: true,
                    fieldLabel: '__i18n_formElements.isResponsive',
                    xtype: 'checkbox'
                });
            }

            // TODO: add label if there is none! (in any case?)

        }

    });

};

/**
 * Remove Object from Array
 * @param {Array} array - array with objects
 * @param {Object} obj - needs to be the SAME reference to the object (not just the same values)
 * @returns {boolean} - weather element was removed or not
 */
var removeObjFromArray = function (array, obj) {
    if (array.indexOf && array.splice) {
        var idx = array.indexOf(obj);
        if (idx >= 0) {
            array.splice(idx, 1);
            return true;
        }
    }
    return false;
};

// Grunt Task
module.exports = function (grunt) {
    var name = 'convertLegacyResponsiveModuleData';
    var description = 'Converts all module data which has old responsive settings (form values 0..3) to new format.';

    grunt.registerMultiTask(name, description, function () {
        var i, j;

        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;

            for (j = 0; j < sources.length; j++) {
                var json = grunt.file.readJSON(sources[j]);
                if (!json) {
                    console.info('convertLegacyResponsiveModuleData skipped ', sources[j], 'empty json file!');
                }
                var convertedJson = convertLegacy(json);
                grunt.file.write(sources[j], JSON.stringify(convertedJson, null, 4));
            }
        }
    });
};