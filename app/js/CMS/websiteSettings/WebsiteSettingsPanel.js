Ext.ns('CMS.websiteSettings');

/**
 * Website Settings Panel
 *
 * @class       CMS.websiteSettings.WebsiteSettingsPanel
 * @extends     CMS.home.ManagementPanel
 */
CMS.websiteSettings.WebsiteSettingsPanel = Ext.extend(CMS.home.ManagementPanel, {

    /**
     * the website id (required)
     */
    websiteId: '',

    changedRecords: {},

    settingsId: '',

    initComponent: function () {
        var sidebarWidth = 300;
        this.store = CMS.data.StoreManager.get('websiteSettings', this.websiteId);

        var bbar = ['->',  {
            text: CMS.i18n('Speichern'),
            iconCls: 'save savemodule',
            cls: 'primary',
            ref: '../saveButton',
            scope: this,
            handler: this.saveAction
        }, {
            text: CMS.i18n('Schließen'),
            iconCls: 'cancel cancelmodule',
            scope: this,
            handler: this.closeAction
        }];

        Ext.apply(this, {
            layout: 'hbox',
            bbar: bbar,
            layoutConfig: {
                align: 'stretch'
            },
            items: [{
                xtype: 'panel',
                width: sidebarWidth,
                cls: 'CMSsidebar',
                items: [{
                    xtype: 'CMSwebsitesettingssectionsidebar',
                    cls: 'CMSwebsitesettingssectionsidebar',
                    websiteId: this.websiteId,
                    settingsId: this.settingsId,
                    store: this.store,
                    listeners: {
                        settingsSelectionChange: this.settingsSectionChanged,
                        scope: this
                    }
                }]
            }, {
                xtype: 'panel',
                ref: 'settingsFormPanel',
                cls: 'CMSwebsitesettingsformpanel',
                flex: 1,
                layout: 'fit',
            }]
        });

        CMS.websiteSettings.WebsiteSettingsPanel.superclass.initComponent.call(this);
    },

    settingsSectionChanged: function (selectedRecord) {
        this.settingsFormPanel.removeAll();
        if (selectedRecord) {
            this.generateFormPanel(selectedRecord);
        }
    },

    generateFormPanel: function (record) {
        this.currentSettingsRecord = record;

        var formConfig = CMS.form.FormConfigHelper.fromConfigToForm(record.data.form);

        this.settingsFormPanel.add({
            xtype: 'CMSgeneratedformpanel',
            border: false,
            bodyStyle: { padding: '20px' },
            idSuffix: this.idSuffix,
            websiteId: this.websiteId,
            cfg: formConfig,
            cls: 'CMSgeneratedformpanel',
            record: record,
            plugins: ['CMSradiofieldsetplugin'],
            listeners: {
                scope: this,
                valuechanged: this.valueChangeHandler
            }
        });

        this.settingsFormPanel.doLayout();
    },

    valueChangeHandler: function(cmp, changes) {
        // update record (non commited)
        var fv = SB.util.cloneObject(this.currentSettingsRecord.get('formValues'));
        fv[changes.key] = changes.newValue;
        this.currentSettingsRecord.set('formValues', fv);
    },

    /**
     * Save all changes
     */
    saveAction: function () {
        var data = {
            websiteId: this.websiteId,
            websiteSettings: {}
        };

        Ext.each(this.store.getModifiedRecords(), function (record) {
            data.websiteSettings[record.id] = {
                formValues: record.data.formValues
            };
        });

        // save on server
        CMS.app.trafficManager.sendRequest({
            action: 'editWebsiteSettings',
            data: data,
            modal: true,
            success: function () {
                CMS.Message.toast(CMS.i18n(null, 'websiteSettingsPanel.saveSuccessToast'));
                this.store.commitChanges();
                this.fireEvent('requestClose');
            },
            failure: function () {
                this.rejectChanges();
                this.fireEvent('requestClose');
            },
            failureTitle: CMS.i18n('Fehler'),
            scope: this
        });
    },

    /**
     * Close Action
     */
    closeAction: function () {

        // blur active element
        document.activeElement.blur();

        var isModified = (this.store.getModifiedRecords().length > 0);
        if (isModified) {
            Ext.MessageBox.confirm(
                /* title -> */ CMS.i18n('Änderungen übernehmen?'),
                /* message -> */ CMS.i18n('Ungespeicherte Änderungen übernehmen?'),
                /* callback -> */ function (btnId) {
                    if (btnId == 'yes') {
                        this.saveAction();
                    } else if (btnId == 'no') {
                        this.rejectChanges();
                        this.fireEvent('requestClose');
                    }
                },
                /* scope -> */ this
            );
        } else {
            this.fireEvent('requestClose');
        }
    },

    rejectChanges: function () {
        Ext.each(this.store.getRange(), function (record) {
            record.reject();
        });
    }

});

Ext.reg('CMSwebsitesettingspanel', CMS.websiteSettings.WebsiteSettingsPanel);
