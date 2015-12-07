Ext.ns('CMS');

/**
 * @class       CMS.home.TemplateSnippetPropertiesWindow
 * @extends     Ext.Window
 * Window for editing the properties of a templateSnippet.
 */
CMS.home.TemplateSnippetPropertiesWindow = Ext.extend(Ext.Window, {

    width: 400,
    modal: true,
    resizable: false,

    /**
     * @cfg {Object} website
     * Website which contains the templateSnippets
     */
    websiteId: undefined,

    /**
     * current edited snippetData
     *
     * @property currentSnippet
     * @type {Object}
     * @private
     */
    currentSnippet: undefined,

    initComponent: function () {
        this.title = CMS.i18n('Snippet-Eigenschaften');
        this.items = [{
            xtype: 'form',
            ref: 'form',
            monitorValid: true,
            listeners: {
                clientvalidation: this.validationHandler,
                scope: this
            },
            defaults: {
                anchor: '100%'
            },
            items: [{
                xtype: 'hidden',
                name: 'id'
            }, Ext.apply({
                xtype: 'textfield',
                fieldLabel: CMS.i18n('Name'),
                name: 'name'
            }, CMS.config.validation.templateSnippetName), {
                xtype: 'CMSfilterbox',
                fieldLabel: CMS.i18n('Kategorie'),
                ref: '../../categoryField',
                store: CMS.data.StoreManager.get('templateSnippet', this.websiteId),
                field: 'category',
                name: 'category',
                emptyText: CMS.i18n('(keine)'),
                editable: true,
                typeAhead: false
            }, {
                xtype: 'textarea',
                height: 120,
                fieldLabel: CMS.i18n('Beschreibung'),
                name: 'description'
            }]
        }];

        this.buttonAlign = 'center';
        this.buttons = [{
            text: CMS.i18n('Speichern'),
            cls: 'primary',
            iconCls: 'save',
            ref: '../saveButton',
            scope: this,
            handler: this.saveTemplateSnippetProperties
        }, {
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            cls: 'cancel',
            scope: this,
            handler: this.close
        }];

        CMS.home.TemplateSnippetPropertiesWindow.superclass.initComponent.call(this);
    },

    /**
     * Sets a templateSnippet for editing and updates the form
     * @param {Object} templateSnippet
     */
    setTemplateSnippet: function (templateSnippet) {
        this.currentSnippet = templateSnippet;
        // update form
        this.form.getForm().setValues(this.currentSnippet.data);
    },

    /**
     * Saves the templateSnippet properties on the server-side
     * @private
     */
    saveTemplateSnippetProperties: function () {
        var values = this.form.getForm().getValues();

        // update snippetData
        this.currentSnippet.beginEdit();
        Ext.iterate(values, function (key, value) {
            this.currentSnippet.set(key, value);
        }, this);
        this.currentSnippet.endEdit();

        CMS.app.trafficManager.sendRequest({
            action: 'editTemplateSnippet',
            data: this.currentSnippet.data,
            scope: this,
            success: function () {
                // update local store
                this.currentSnippet.commit();
                this.close();
            },
            failure: function () {
                this.currentSnippet.reject();
            },
            failureTitle: CMS.i18n('Fehler beim Bearbeiten eines Snippets')
        });
    },

    /**
     * Toggles the save button depending on the form valid state
     * @private
     */
    validationHandler: function (editor, valid) {
        this.saveButton.setDisabled(!valid);
    }
});
