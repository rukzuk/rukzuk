Ext.ns('CMS.moduleEditor');

//noinspection JSUnusedGlobalSymbols,JSUnusedGlobalSymbols
/**
* @class CMS.moduleEditor.FormEditor
* @extends Ext.Container
*/
CMS.moduleEditor.FormEditor = Ext.extend(Ext.Container, {
    layout: 'border',
    defaults: {
        collapsible: true,
        border: false
    },

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    initComponent: function () {
        this.store = this.store || new Ext.data.ArrayStore({
            fields: CMS.data.FormGroupRecord
        });
        this.activeGroup = null;
        this.items = [{
            region: 'west',
            xtype: 'CMSformtablist',
            title: CMS.i18n('Reiter'),
            ref: 'formGroupList',
            websiteId: this.websiteId,
            store: this.store,
            width: 253,
            split: true,
            frame: false,
            floatable: false,
            collapsible: false,
            getCollapsedEl: function () {
                if (!this.collapsedEl) {
                    this.collapsedEl = Ext.layout.BorderLayout.Region.prototype.getCollapsedEl.apply(this, arguments);
                    this.collapsedEl.createChild({
                        tag: 'span',
                        cls: 'x-panel-header-text x-panel-header-text-collapsed',
                        html: this.panel.title
                    });
                }
                return this.collapsedEl;
            }
        }, {
            region: 'center',
            frame: false,
            collapsible: false,
            layout: 'hbox',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            items: [{
                xtype: 'CMSformtabeditor',
                ref: '../formGroupEditor',
                flex: 1,
                disabled: true,
                websiteId: this.websiteId,
                plugins: ['CMSradiofieldsetplugin'],
                listeners: {
                    scope: this,
                    itemfocus: this.editFormProperties
                },
                border: false
            }, {
                xtype: 'CMSpropertyeditorformpanel',
                ref: '../propertyeditor',
                websiteId: this.websiteId,
                width: 400,
                border: false,
                autoScroll: true
            }]
        }, {
            region: 'east',
            title: 'JSON',
            width: '40%',
            bbar: [{
                text: 'Download form.json',
                handler: this.downloadAsFile,
                scope: this
            }, {
                xtype: 'textfield',
                inputType: 'file',
                ref: '../../inputFile',
                hidden: true,
                listeners: {
                    afterrender: function (cmp) {
                        var that = this;
                        cmp.getEl().dom.addEventListener('change', function () {
                            var fileToLoad = this.files[0];
                            var fileReader = new FileReader();
                            if (fileToLoad) {
                                fileReader.onload = function (fileLoadedEvent) {
                                    that.loadJSONString(fileLoadedEvent.target.result);
                                };
                                fileReader.readAsText(fileToLoad, "UTF-8");
                            }
                            // reset value (so the same file can be loaded again)
                            this.value = '';
                        });
                    },
                    scope: this
                }
            }, {
                text: 'Import form.json',
                handler: function () {
                    this.inputFile.el.dom.click();
                },
                scope: this,
            }],
            items: [{
                layout: 'fit',
                xtype: 'textarea',
                ref: '../resultJSONField',
                width: '100%',
                height: '100%',
                readOnly: true,
                selectOnFocus: true,
            }]
        }];
        CMS.moduleEditor.FormEditor.superclass.initComponent.apply(this, arguments);
        this.mon(this.formGroupList, 'select', this.selectionHandler, this);
        this.mon(this.formGroupList, 'clear', this.clearHandler, this);
        this.mon(this.formGroupEditor, 'paramformconfigchanged', this.configChangedHandler, this);
    },

    editFormProperties: function (cmp, callback) {
        this.propertyeditor.loadFormElData(cmp, callback);
    },

    listeners: {
        // fix buggy borderlayout
        'show': function (self) {
            self.doLayout();
        }
    },

    /**
    * @private
    */
    selectionHandler: function (record) {
        console.log('[FormEditor] select', record.data);
        this.formGroupEditor.enable();
        this.propertyeditor.clear();
        this.formGroupEditor.loadRecord(record);
        this.activeGroup = record;
    },

    /**
    * @private
    * Handler for the 'clear' event from FormGroupList
    */
    clearHandler: function (record) {
        this.formGroupEditor.removeAll();
        this.formGroupEditor.disable();
        this.activeGroup = null;
    },

    /**
    * @private
    * called when the form group's config is changed in the formGroupEditor
    */
    configChangedHandler: function (formGroupData) {
        console.log('[FormEditor] setting formGroupData:', formGroupData);
        if (!this.formGroupEditor.focusedCmp) {
            this.propertyeditor.clear();
        }
        this.activeGroup.set('formGroupData', formGroupData);

        // TODO: better event?
        this.updateResultJson();
    },

    /**
    * Load a list of form items from a module record
    * @param {CMS.data.ModuleRecord} record The record to load the data from
    */
    loadRecord: function (record) {
        this.formGroupList.store.removeAll();
        var formGroups = record.get('form');
        var newRecords = [];
        Ext.each(formGroups, function (fg) {
            fg.id = fg.id || SB.util.UUID();
            newRecords.push(new CMS.data.FormGroupRecord(fg));
        });
        if (newRecords.length) {
            this.formGroupList.store.add(newRecords);
            this.formGroupList.selectFirstRow();
        } else {
            this.formGroupEditor.removeAll();
            this.formGroupEditor.disable();
            this.formGroupList.clearSelections();
        }
    },

    /**
    * Persists the values in this field into the passed Ext.data.Record object
    * in a beginEdit/endEdit block.
    * @param {Ext.data.Record} record The record to save to
    */
    updateRecord: function (record) {
        var data = SB.util.cloneObject(Ext.pluck(this.formGroupList.store.getRange(), 'data'));
        console.log('[FormEditor] saving data ', data);
        record.set('form', data);
        record.set('formValues', record.getFormValues());
    },

    /**
    * Rejects all changes in formGroup records (SBCMS-567)
    */
    rejectChanges: function () {
        Ext.each(this.formGroupList.store.getRange(), function (record) {
            record.reject();
        });
    },

    /**
     *
     * @returns {*}
     */
    getAsJSONString: function () {
        var fakeModuleRecord = new CMS.data.ModuleRecord();
        this.updateRecord(fakeModuleRecord);
        return JSON.stringify({
            form: fakeModuleRecord.get('form'),
            formValues: fakeModuleRecord.get('formValues')
        }, null, '    ');
    },

    /**
     *
     * @param jsonStr
     * @returns {CMS.data.ModuleRecord}
     */
    recordFromJSONString: function (jsonStr) {
        var fakeModuleRecord = new CMS.data.ModuleRecord();
        var data = JSON.parse(jsonStr);
        fakeModuleRecord.set('form', data.form);
        fakeModuleRecord.set('formValues', data.formValues);
        return fakeModuleRecord;
    },

    /**
     * Update result JSON
     */
    updateResultJson: function () {
        this.resultJSONField.setValue(this.getAsJSONString());
    },

    /**
     * Download JSON as file
     */
    downloadAsFile: function () {
        var textToWrite = this.getAsJSONString();
        var textFileAsBlob = new Blob([textToWrite], {type: 'application/json'});
        var fileNameToSaveAs = 'form.json';

        var downloadLink = document.createElement("a");
        downloadLink.download = fileNameToSaveAs;
        downloadLink.innerHTML = "Download File";
        if (window.webkitURL !== null) {
            // Chrome allows the link to be clicked
            // without actually adding it to the DOM.
            downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
        }
        else {
            // Firefox requires the link to be added to the DOM
            // before it can be clicked.
            downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
            downloadLink.onclick = function () {
                document.body.removeChild(event.target);
            };
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
        }

        downloadLink.click();
    },

    /**
     * Import JSON
     */
    loadJSONString: function (jsonStr) {
        var rec = this.recordFromJSONString(jsonStr);
        this.loadRecord(rec);
        this.updateResultJson();
    }

});

Ext.reg('CMSformeditor', CMS.moduleEditor.FormEditor);
