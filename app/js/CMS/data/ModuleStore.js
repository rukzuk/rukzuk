Ext.ns('CMS.data');

CMS.data.moduleFields = [{
    name: 'id',
    type: 'string',
    allowBlank: false
}, {
    name: 'websiteId',
    type: 'string',
    allowBlank: false
}, {
    name: 'name',
    sortType: Ext.data.SortTypes.asTranslatedText,
    defaultValue: 'Ohne Titel',
    allowBlank: false
}, {
    name: 'description',
    sortType: Ext.data.SortTypes.asTranslatedText,
    defaultValue: '',
    allowBlank: true
}, {
    name: 'version',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'category',
    sortType: Ext.data.SortTypes.asTranslatedText,
    defaultValue: '',
    allowBlank: true
}, {
    name: 'icon',
    type: 'string',
    defaultValue: 'brick.png',
    allowBlank: false
}, {
    name: 'form',
    defaultValue: function () {
        return [{
            'name': '-',
            'icon': '',
            'formGroupData': [],
            'id': '' + SB.util.UUID() + ''
        }];
    },
    type: 'array',
    allowBlank: false
}, {
    name: 'formValues',
    type: 'json',
    defaultValue: {},
    allowBlank: false
}, {
    name: 'moduleType',
    type: 'string',
    defaultValue: CMS.config.moduleTypes.defaultModule
}, {
    name: 'reRenderRequired',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'allowedChildModuleType',
    type: 'string',
    defaultValue: '*'
}, {
    name: 'checkbox', // for use in checkbox grids
    type: 'boolean'
}, {
    name: 'sourceType',
    type: 'string',
    defaultValue: ''
}, {
    name: 'overwritten',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'ghostContainerMode',
    type: 'string',
    defaultValue: ''
}];

/**
* @class CMS.data.ModuleRecord
* @extends Ext.data.Record
*/
CMS.data.ModuleRecord = CMS.data.Record.create(CMS.data.moduleFields);

CMS.data.isModuleRecord = function (record) {
    return record && (record.constructor == CMS.data.ModuleRecord);
};

CMS.data.isRootModuleRecord = function (record) {
    return CMS.data.isModuleRecord(record) && (record.get('moduleType') === CMS.config.moduleTypes.root);
};

CMS.data.isExtensionModuleRecord = function (record) {
    return CMS.data.isModuleRecord(record) && (record.get('moduleType') === CMS.config.moduleTypes.extension);
};

CMS.data.isDefaultModuleRecord = function (record) {
    return CMS.data.isModuleRecord(record) && (record.get('moduleType') === CMS.config.moduleTypes.defaultModule);
};


Ext.apply(CMS.data.ModuleRecord.prototype, {
    /**
    * Gets the embedded form data from the module record
    */
    getFormValues: function () {
        var form = this.data.form;
        //console.log('[ModuleStore] form:', form);
        var result = {};
        var getItemDefaults = function (formItem) {
            var varName;
            // create entry
            Ext.each(formItem.params, function (param) {
                if (param.name == 'CMSvar') {
                    varName = param.value;
                    result[varName] = null;
                    return false;
                }
            });

            // set default value
            if (varName) {
                Ext.each(formItem.params, function (param) {
                    if (param.name == 'value') {
                        result[varName] = param.value;
                        return false;
                    }
                });
            }
            if (formItem.items) {
                Ext.each(formItem.items, getItemDefaults);
            }
        };
        Ext.each(form, function (formGroup) {
            Ext.each(formGroup.formGroupData, getItemDefaults);
        });
        console.log('[ModuleRecord] formValues:', result);
        return result;
    },

    /**
    * Gets the embedded RTE config for the form field with the given CMSvar
    * @param {String} varName
    * @return {Object} RTE configuration object
    */
    getRichTextEditorConfigForField: function (varName) {
        var form = this.data.form;
        var result;

        var checkItem = function (formItem) {
            var varFound = false;
            // create entry
            Ext.each(formItem.params, function (param) {
                if (param.name == 'CMSvar' && param.value == varName) {
                    varFound = true;
                    return false;
                }
            });

            if (varFound) {
                Ext.each(formItem.params, function (param) {
                    if (param.name == 'richTextEditorConfig') {
                        result = param.value;
                        return false;
                    }
                });
                return false;
            }

            if (formItem.items) {
                Ext.each(formItem.items, checkItem);
            }
        };

        Ext.each(form, function (formGroup) {
            Ext.each(formGroup.formGroupData, checkItem);
        });

        return result;
    },

    /**
    * Gets the formGroup id of the form field with the given CMSvar
    * @param {String} varName
    * @return {String} formGroup id
    */
    getFormGroupOfField: function (varName) {
        var form = this.data.form;
        var formGroup;
        var result;
        var param;

        var checkItem = function (formItem) {
            for (var i = 0, l = formItem.params.length; i < l; i++) {
                param = formItem.params[i];
                if (param.name == 'CMSvar') {
                    if (param.value == varName) {
                        result = formGroup.id;
                        return false;
                    }
                    break;
                }
            }

            if (formItem.items) {
                formItem.items.forEach(checkItem);
            }
        };

        for (var i = 0, l = form.length; i < l; i++) {
            formGroup = form[i];
            formGroup.formGroupData.forEach(checkItem);
            if (result) {
                break;
            }
        }

        return result;
    },

    /**
    * Instantiate a {@link CMS.data.UnitRecord} from this module
    * @param {String} [id] (optional) The new record's id
    */
    createUnit: function (id) {
        if (!id) {
            id = Ext.id();
        }

        return new CMS.data.UnitRecord({
            id: id,
            moduleId: this.get('id'),
            // set ghost container mode
            ghostContainer: (this.get('ghostContainerMode') === 'force_on'),
            name: '', // SBCMS-2002 do not copy/translate module name -> the GUI show the translated module-name if the unit has no own name
            // name: CMS.translateInput(this.get('name')), // SBCMS-1856 i18n of module names
            // description: this.get('description'),  -> SBCMS-1281: do not copy module description when creating units from modules
            formValues: SB.util.cloneObject(this.get('formValues')),
            expanded: true
        }, id);
    },

    /**
     * Checks if the module can be used as a ghostContainer.
     * This doesn't make sense e.g. if no children are allowed.
     * This does not cover force_on. This only applies to user settings.
     * @returns {boolean}
     */
    canBeGhostContainer: function () {
        return this.get('allowedChildModuleType') === '*'
            && this.get('ghostContainerMode') !== 'force_on'
            && this.get('ghostContainerMode') !== 'force_off';
    }
});

/**
* @class CMS.data.ModuleStore
* This is a true singleton. Use {@link #getInstance} to access its instance.
*/
CMS.data.ModuleStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.ModuleStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            autoLoad: false,
            url: CMS.config.urls.getAllModules,
            root: CMS.config.roots.getAllModules,
            fields: CMS.data.ModuleRecord,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllModules),
            reader: new Ext.data.JsonReader(config)
        }));
    }
});

