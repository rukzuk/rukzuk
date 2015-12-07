Ext.ns('CMS.home');

/**
 * @class CMS.home.WebsiteSelection
 * @extends Ext.Panel
 */
CMS.home.WebsiteSelection = Ext.extend(Ext.Panel, {

    frame: true,

    initComponent: function () {

        this.emptyText = CMS.i18n('(Keine Websites vorhanden)');

        var bbar = [{
            tooltip: {
                text: CMS.i18n('Neue Website'),
                align: 't-b?'
            },
            iconCls: 'addwebsite add',
            ref: '../newButton',
            hidden: !CMS.app.userInfo.canManageSites(),
            handler: this.newButtonHandler,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n('Duplizieren'),
                align: 't-b?'
            },
            iconCls: 'clonewebsite clone',
            ref: '../cloneButton',
            hidden: !CMS.app.userInfo.canManageSites(),
            handler: this.cloneButtonHandler,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n('Löschen'),
                align: 't-b?'
            },
            iconCls: 'deletewebsite delete',
            ref: '../deleteButton',
            hidden: !CMS.app.userInfo.canManageSites(),
            handler: this.deleteButtonHandler,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n('Umbenennen'),
                align: 't-b?'
            },
            iconCls: 'renamewebsite rename',
            ref: '../renameButton',
            hidden: !CMS.app.userInfo.canManageSites(),
            handler: this.renameButtonHandler,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n(null, 'websiteSelection.importWebsite'),
                align: 't-b?'
            },
            ref: '../importButton',
            iconCls: 'import',
            hidden: !CMS.app.userInfo.canImport(),
            handler: function () {
                var cfg = {allowedType: CMS.config.importTypes.website};
                CMS.app.ImportHelper.startImport(null, cfg);
            }
        }, {
            tooltip: {
                text: CMS.i18n(null, 'websiteSelection.exportWebsite'),
                align: 't-b?'
            },
            iconCls: 'export exportwebsite',
            ref: '../exportButton',
            handler: this.exportBtnHandler,
            hidden: !CMS.app.userInfo.canExport(),
            disabled: true,
            scope: this
        }, {
            xtype: 'button',
            cls: 'primary',
            iconCls: 'openwebsite open',
            tooltip: {
                text: CMS.i18n('Öffnen'),
                align: 't-b?'
            },
            ref: '../openButton',
            disabled: true,
            handler: this.editButtonHandler,
            scope: this
        }];

        var tpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="wrap">',
                    '<div class="titlebar"><span>{name}</span></div>',
                    '<div class="thumb"><img class="screenshot" src="{[values.screenshot || Ext.BLANK_IMAGE_URL]}" title="{name}" width="256" height="192"></div>',
                    '<div class="publishedIndicator {[values.publishingEnabled ? "active" : "inactive"]}" title="{[values.publishingEnabled ? values.publishInfo.url : ""]}">',
                        '<span>',
                        '{[values.publishingEnabled ? String(values.publishInfo.url).replace(/^http[s]?:\\/\\//, "") : ""]}',
                        '</span>',
                    '</div>',
                '</div>',
            '</tpl>'
        );

        Ext.apply(this, {
            layout: 'fit',
            items: [{
                xtype: 'CMSthumbview',
                itemSelector: 'div.wrap',
                overClass: 'hover',
                selectedClass: 'selected',
                singleSelect: true,
                tpl: tpl,
                store: CMS.data.WebsiteStore.getInstance(),
                ref: 'thumbView',
                trackOver: true,
                scrollOffset: 10,
                plugins: [new CMS.DataViewContextMenu({
                    items: [{
                        iconCls: 'edit',
                        text: CMS.i18n('Bearbeiten'),
                        handler: this.editButtonHandler,
                        scope: this
                    }, {
                        iconCls: 'rename',
                        text: CMS.i18n('Umbenennen'),
                        handler: this.renameButtonHandler,
                        condition: {fn: this.checkCanManageSites},
                        scope: this
                    }, {
                        iconCls: 'clone',
                        text: CMS.i18n('Duplizieren'),
                        handler: this.cloneButtonHandler,
                        condition: {fn: this.checkCanManageSites},
                        scope: this
                    }, {
                        iconCls: 'export exportwebsite',
                        text: CMS.i18n(null, 'websiteSelection.exportWebsite'),
                        handler: this.exportBtnHandler,
                        scope: this,
                        condition: {
                            fn: function () {
                                return CMS.app.userInfo.canExport();
                            }
                        }
                    }, {
                        iconCls: 'delete',
                        text: CMS.i18n('Löschen'),
                        handler: this.deleteButtonHandler,
                        condition: {fn: this.checkCanManageSites},
                        scope: this
                    }]
                })],
                listeners: {
                    selectionchange: this.selectionchangeHandler,
                    dblclick: this.dblclickHandler,
                    scope: this
                }
            }],
            bbar: bbar.length ? bbar : false
        });

        CMS.home.WebsiteSelection.superclass.initComponent.apply(this, arguments);

        this.dataView = this.thumbView.dataView;
        this.store = this.dataView.store;

        // show ghost if we can add websites
        if (this.checkCanManageSites()) {
            // show add new website ghost
            this.thumbView.ghostCfg = {
                text: CMS.i18n('Neue Website', 'websiteSelection.newWebsiteGhostText'),
                cb: function () {
                    this.newButtonHandler();
                },
                scope: this
            };
            // show new website dialog if there are no websites
            this.store.on('load', function () {
                if (this.store.getTotalCount() === 0) {
                    this.showNewWebsiteWindow(true);
                }
            }, this, {single: true});
        }

    },

    /**
     * Handler for ModuleGrid's selectionchange event
     * @private
     */
    selectionchangeHandler: function (dataView) {
        var site = dataView.getSelectedRecords()[0];

        this.cloneButton.setDisabled(!site);
        this.deleteButton.setDisabled(!site);
        this.exportButton.setDisabled(!site);
        this.renameButton.setDisabled(!site);
        this.openButton.setDisabled(!site);

        if (site) {
            this.importButton.website = site;
        } else {
            delete this.importButton.website;
        }

        CMS.app.ImportHelper.setSelectedWebsite(site);
    },

    /**
     * Checks if the user can create, delete and/or modify websites
     * @private
     */
    checkCanManageSites: function () {
        return CMS.app.userInfo.canManageSites();
    },

    editButtonHandler: function () {
        var record = this.dataView.getSelectedRecords()[0];
        this.fireEvent('select', record);
    },

    cloneButtonHandler: function () {
        var record = this.dataView.getSelectedRecords()[0];
        CMS.Message.prompt(
            /* title -> */ CMS.i18n('Bezeichnung eingeben'),
            /* message -> */ CMS.i18n('Bezeichnung der neuen Website'),
            /* callback -> */ function (btnId, title) {
                if (btnId === 'ok') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'cloneWebsite',
                        modal: true,
                        data: {
                            id: record.id,
                            name: title
                        },
                        successCondition: 'data.id',
                        success: function (resp) {
                            var websiteId = resp.data.id;
                            this.store.reload({
                                callback: function () {
                                    this.selectWebSite(websiteId);
                                },
                                scope: this
                            });
                        },
                        failureTitle: CMS.i18n('Fehler beim Duplizieren der Website'),
                        failure: function () {
                            this.store.reload();
                        },
                        scope: this
                    });
                }
            },
            /* scope -> */ this,
            /* multiline -> */ false,
            /* value -> */ CMS.i18n('{name} – Kopie').replace('{name}', record.get('name')),
            /* fieldconfig -> */ CMS.config.validation.websiteName
        );
    },


    newButtonHandler: function () {
        this.showNewWebsiteWindow();
    },

    /**
     * Show New Website Window
     * @param fullModal
     */
    showNewWebsiteWindow: function (fullModal) {

        // simplest possible check for quotas, just to have a better error message
        // TODO: this should update quota and website count
		var websiteCount = this.store.getCount();
        if (websiteCount >= CMSSERVER.data.quota.website.maxCount) {
            (new CMS.home.WebsiteQuotaReachedMarketingWindow()).show();
            // reload store (this is async, but helps if you try again)
            this.store.reload();
            return;
        }

        (new CMS.home.NewWebsiteWindow({
            callback: function (response) {
                var responseData = response && response.data;
                var websiteId = responseData && (responseData.id || responseData.websiteId);

                // try to open imported website (store is already up to date)
                if (!websiteId) {
                    return;
                }
                var website = this.dataView.getStore().getById(websiteId);
                if (website) {
                    this.fireEvent('select', website);
                } else {
                    // store reload is required after new empty page
                    this.store.reload({
                        scope: this,
                        callback: function () {
                            var website = this.dataView.getStore().getById(websiteId);
                            this.fireEvent('select', website);
                        }
                    });
                }
            },
            scope: this,
            fullModal: fullModal,
        })).show(undefined, undefined);
    },

    deleteButtonHandler: function () {
        var record = this.dataView.getSelectedRecords()[0];
        Ext.MessageBox.confirm(
            /* title -> */ CMS.i18n('Löschen?'),
            /* message -> */ CMS.i18n('Website „{name}“ wirklich löschen?').replace('{name}', (record.get('name') || CMS.i18n('unbenannt'))),
            /* callback -> */ function (btnId) {
                if (btnId === 'yes') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'deleteWebsite',
                        modal: true,
                        data: {
                            id: record.id
                        },
                        scope: this,
                        success: function () {
                            this.store.reload({
                                scope: this,
                                callback: function () {
                                    this.dataView.clearSelections();
                                }
                            });
                        },
                        failureTitle: CMS.i18n('Fehler beim Löschen der Website'),
                        failure: function () {
                            this.store.reload();
                        }
                    });
                }
            },
            /* scope -> */ this
        );
    },

    /**
     * Handler for the rename button
     * @private
     */
    renameButtonHandler: function () {
        var record = this.dataView.getSelectedRecords()[0];
        CMS.Message.prompt(
            /* title -> */ CMS.i18n('Bezeichnung eingeben'),
            /* message -> */ CMS.i18n('Neue Bezeichnung der Website:'),
            /* callback -> */ function (btnId, title) {
                if (btnId === 'ok') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'editWebsite',
                        data: {
                            id: record.id,
                            name: title
                        },
                        success: function () {
                            record.set('name', title);
                        },
                        failureTitle: CMS.i18n('Fehler beim Umbenennen der Website'),
                        scope: this
                    });
                }
            },
            /* scope -> */ this,
            /* multiline -> */ false,
            /* value -> */ record.get('name'),
            /* fieldconfig -> */ CMS.config.validation.websiteName
        );
    },

    /**
     * Handler for export button
     * @private
     */
    exportBtnHandler: function () {
        // simplest possible check for quotas, just to have a better error message
        if (!CMSSERVER.data.quota.export.exportAllowed) {
            (new CMS.home.ExportQuotaMarketingWindow()).show();
            return;
        }

        var record = this.dataView.getSelectedRecords()[0];
        if (!record) {
            return;
        }
        CMS.Message.prompt(
            /* title -> */ CMS.i18n(null, 'websiteSelection.exportWebsitePromptTitle'),
            /* message -> */ CMS.i18n(null, 'websiteSelection.exportWebsitePromptLabel'),
            /* callback -> */ function (btnId, name) {
                if (btnId === 'ok') {
                    CMS.app.downloadHelper.startDownload({
                        action: 'exportWebsite',
                        data: {
                            websiteId: record.id,
                            name: name
                        },
                        urlKey: 'data.url',
                        failureTitle: CMS.i18n('Fehler beim Exportieren')
                    });
                }
            },
            /* scope -> */ this,
            /* multiline -> */ false,
            /* value -> */ CMS.i18n(null, 'websiteSelection.exportWebsitePromptFileName').replace('{name}', record.get('name')).replace('{date}', new Date().format('Y-m-d'))
        );
    },

    /**
     * Selects and scrolls a website row into view
     * @private
     */
    selectWebSite: function (websiteId) {
        this.thumbView.selectItem(websiteId);
    },

    /**
     * Handler for the dataView's dblclick event
     * @private
     */
    dblclickHandler: function (dataview, index) {
        var record = dataview.getStore().getAt(index);

        /**
         * @event select
         * Fired when the user selects to open a website
         * @param {CMS.data.WebsiteRecord} record The selected website record
         */
        this.fireEvent('select', record);
    },

    destroy: function () {
        this.importButton.website = null;
        this.dataView = null;
        this.store = null;

        CMS.home.WebsiteSelection.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSwebsiteselection', CMS.home.WebsiteSelection);
