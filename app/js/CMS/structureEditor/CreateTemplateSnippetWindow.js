Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.CreateTemplateSnippetWindow
* @extends Ext.Window
* Window for creating or updating template snippets
*/
CMS.structureEditor.CreateTemplateSnippetWindow = Ext.extend(Ext.Window, {
    cls: 'CMScreatetemplatesnippetwindow',
    modal: true,
    width: 400,
    border: false,
    resizable: false,

    /**
    * @cfg {CMS.data.UnitRecord} unit
    * The unit which should be the root for the templateSnippet. All unit data and
    * all children units will be saved in the templateSnippet.
    */
    unit: null,

    initComponent: function () {
        this.title = CMS.i18n('Snippet erstellen');

        this.templateSnippetStore = CMS.data.StoreManager.get('templateSnippet', this.websiteId);

        this.items = {
            xtype: 'panel',
            items: [{
                xtype: 'form',
                items: [{
                    xtype: 'CMSradiofieldset',
                    ref: '../../modeFieldSet',
                    checkboxName: 'mode',
                    groupValue: 'new',
                    title: CMS.i18n('Neues Snippet'),
                    value: 'new', // default value so first fieldset is opened
                    listeners: {
                        expand: this.fieldsetExpandHandler,
                        scope: this
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: [Ext.apply({
                        xtype: 'textfield',
                        fieldLabel: CMS.i18n('Name'),
                        ref: '../../../nameField',
                        listeners: {
                            valid: this.validationHandler,
                            invalid: this.validationHandler,
                            scope: this
                        }
                    }, CMS.config.validation.templateSnippetName), {
                        xtype: 'CMSfilterbox',
                        fieldLabel: CMS.i18n('Kategorie'),
                        ref: '../../../categoryField',
                        store: this.templateSnippetStore,
                        field: 'category',
                        emptyText: CMS.i18n('(keine)'),
                        editable: true,
                        typeAhead: false
                    }, {
                        xtype: 'textarea',
                        fieldLabel: CMS.i18n('Beschreibung'),
                        ref: '../../../descriptionField',
                        height: 200
                    }]
                }, {
                    xtype: 'CMSradiofieldset',
                    checkboxName: 'mode',
                    groupValue: 'edit',
                    title: CMS.i18n('Vorhandenes Snippet überschreiben'),
                    listeners: {
                        expand: this.fieldsetExpandHandler,
                        scope: this
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: {
                        xtype: 'CMSchooser',
                        hideLabel: true,
                        ref: '../../../editTemplateSnippetField',
                        originalStore: this.templateSnippetStore,
                        allowBlank: false,
                        listeners: {
                            valid: this.validationHandler,
                            invalid: this.validationHandler,
                            scope: this
                        }
                    }
                }]
            }]
        };

        this.buttonAlign = 'center';
        this.buttons = [{
            text: CMS.i18n('Speichern'),
            iconCls: 'ok',
            cls: 'primary',
            ref: '../saveButton',
            disabled: true,
            handler: this.createTemplateSnippet,
            scope: this
        }, {
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            handler: this.destroy,
            scope: this
        }];

        CMS.structureEditor.CreateTemplateSnippetWindow.superclass.initComponent.apply(this, arguments);
    },

    fieldsetExpandHandler: function (panel) {
        Ext.each(this.findByType('CMSradiofieldset'), function (oneFieldset) {
            if (panel !== oneFieldset) {
                oneFieldset.collapse();
            }
        });

        this.validationHandler();
    },

    //overriding focus()-method, otherwise it will steal the focus
    focus: function () {
        Ext.each(this.findByType('CMSradiofieldset'), function (oneFieldset) {
            if (!oneFieldset.collapsed) {
                oneFieldset.items.get(0).focus();
                return false;
            }
        });
    },

    /**
     * @private
     * Toggles the save button depending on the form valid state
     */
    validationHandler: function () {
        var valid = false;
        if (!this.modeFieldSet) {
            return;
        }
        var modeNew = !this.modeFieldSet.collapsed;

        if (modeNew) {
            // suspend events, otherwise we'll get a loop
            this.nameField.suspendEvents();
            valid = this.nameField.isValid();
            this.nameField.resumeEvents();
        } else {
            valid = !!this.editTemplateSnippetField.getValue();
        }

        this.saveButton.setDisabled(!valid);
    },

    /**
     * @private
     * handler for the create button; extracts the data and children data of this.unit
     * and saves the templateSnippet on the server side
     */
    createTemplateSnippet: function () {
        if (CMS.data.isUnitRecord(this.unit)) {
            var modeNew = !this.modeFieldSet.collapsed;
            var data;

            if (modeNew) {
                // create new templateSnippet
                data = {
                    websiteId: this.websiteId,
                    name: this.nameField.getValue(),
                    category: this.categoryField.getValue(),
                    description: this.descriptionField.getValue(),
                    content: [this.unit.data]
                };

                CMS.app.trafficManager.sendRequest({
                    modal: true,
                    action: 'createTemplateSnippet',
                    data: data,
                    success: function () {
                        this.templateSnippetStore.reload();
                        CMS.Message.toast(CMS.i18n('Snippet erfolgreich gespeichert'));
                        this.destroy();
                    },
                    failureTitle: CMS.i18n('Fehler beim Speichern des Snippets'),
                    scope: this
                });

            } else {
                // edit existing templateSnippet
                data = {
                    websiteId: this.websiteId,
                    id: this.editTemplateSnippetField.getValue(),
                    content: [this.unit.data]
                };

                CMS.app.trafficManager.sendRequest({
                    modal: true,
                    action: 'editTemplateSnippet',
                    data: data,
                    success: function () {
                        this.templateSnippetStore.reload();
                        CMS.Message.toast(CMS.i18n('Snippet erfolgreich gespeichert'));
                        this.destroy();
                    },
                    failureTitle: CMS.i18n('Fehler beim Ändern des Snippets'),
                    scope: this
                });
            }
        }
    },

    destroy: function () {
        this.templateSnippetStore = null;

        CMS.structureEditor.CreateTemplateSnippetWindow.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMScreatetemplatesnippetwindow', CMS.structureEditor.CreateTemplateSnippetWindow);
