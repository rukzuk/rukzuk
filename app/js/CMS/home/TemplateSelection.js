Ext.ns('CMS.home');

/**
 * @class CMS.home.TemplateSelection
 * @extends CMS.ThumbnailView
 */
CMS.home.TemplateSelection = Ext.extend(CMS.home.TemplateThumbnailView, {
    /** @lends CMS.home.TemplateSelection.prototype */

    bubbleEvents: ['CMSopenworkbench', 'CMScloseworkbench', 'CMSbeforeimport', 'CMSopentemplate'],
    deferEmptyText: true,

    /**
     * The id of the current website
     *
     * @property websiteId
     * @type String
     * @readonly
     */
    websiteId: undefined,

    /** @protected */
    initComponent: function () {
        this.bbar = this.createBBItems();
        this.plugins = (this.plugins || []).concat(this.createContextMenu());

        // initially use the dummy tpl
        this.startWithDummyTpl = true;

        CMS.home.TemplateSelection.superclass.initComponent.apply(this, arguments);

        // override refresh method of dataview to preselect the first item automatically
        this.dataView.refresh = function () {
            var selectedIndex = this.getSelectedIndexes()[0];
            Ext.DataView.prototype.refresh.call(this);
            if (Ext.isNumber(selectedIndex) && selectedIndex >= 0) {
                this.select(selectedIndex);
            }
        };

        // Switch the template if the TemplateSelection becomes visible/hidden
        // If it is not visible it uses a dummy template which does not request any screenshots
        this.on('show', function () {
            this.dataView.tpl = this.actualTpl;
            this.dataView.hasSkippedEmptyText = false; // to fix deferEmptyText
            this.dataView.refresh();
            this.showPopovers();
        }, this);
        this.on('hide', function () {
            this.dataView.tpl = this.dummyTpl;
            this.dataView.refresh();
        }, this);

        this.mon(this.dataView, 'dblclick', this.dblclickHandler, this);
        this.mon(this.dataView, 'selectionchange', this.syncButtonStates, this);
    },

    /**
     * Creates the context menu for the template list
     * @private
     */
    createContextMenu: function () {
        return new CMS.DataViewContextMenu({
            items: [{
                iconCls: 'edit',
                text: CMS.i18n('Bearbeiten'),
                handler: this.onEditBtnClick,
                scope: this
            }, {
                iconCls: 'rename',
                text: CMS.i18n('Umbenennen'),
                handler: this.onRenameBtnClick,
                scope: this
            }, {
                iconCls: 'clone',
                text: CMS.i18n('Duplizieren'),
                handler: this.onCloneBtnClick,
                scope: this
            }, {
                iconCls: 'delete',
                text: CMS.i18n('Löschen'),
                handler: this.onDeleteBtnClick,
                scope: this
            }]
        });
    },

    /**
     * Creates the item configuration for the button bar
     * @private
     */
    createBBItems: function () {
        return [{
            tooltip: {
                text: CMS.i18n(null, 'templateSelection.new'),
                align: 't-b?'
            },
            iconCls: 'add addtemplate',
            ref: '../newButton',
            handler: this.onNewBtnClick,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n(null, 'templateSelection.duplicate'),
                align: 't-b?'
            },
            iconCls: 'clone clonetemplate',
            ref: '../cloneButton',
            handler: this.onCloneBtnClick,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n(null, 'templateSelection.delete'),
                align: 't-b?'
            },
            iconCls: 'delete deletetemplate',
            ref: '../deleteButton',
            handler: this.onDeleteBtnClick,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n(null, 'templateSelection.rename'),
                align: 't-b?'
            },
            iconCls: 'rename renametemplate',
            ref: '../renameButton',
            handler: this.onRenameBtnClick,
            disabled: true,
            scope: this
        }, '->', {
            tooltip: {
                text: CMS.i18n(null, 'templateSelection.edit'),
                align: 't-b?'
            },
            cls: 'primary',
            iconCls: 'edit edittemplate',
            ref: '../editButton',
            handler: this.onEditBtnClick,
            disabled: true,
            scope: this
        }];
    },

    /**
     * Open the given template
     * @private
     *
     * @param {CMS.data.TemplateRecord} record The record to be opened
     */
    openTemplate: function (record) {
        // Handled in PageAndTemplateManagementPanel
        this.fireEvent('CMSopentemplate', record);
    },

    /**
     * Handler for dataview's selectionchange event
     * Enables/disables the toolbar button depending on the current selection state
     * @private
     */
    syncButtonStates: function (dataView, selections) {
        var selectedCount = selections.length;

        // enable/disable buttons
        this.editButton.setDisabled(selectedCount !== 1);
        this.cloneButton.setDisabled(selectedCount !== 1);
        this.deleteButton.setDisabled(selectedCount !== 1);
        this.renameButton.setDisabled(selectedCount !== 1);

        if (selectedCount !== 1) {
            this.destroyPopovers();
        }
    },

    //
    //
    // button click handler
    //
    //

    /**
     * Allows the user to create a new template
     * @private
     */
    onNewBtnClick: function () {
        (new CMS.home.NewTemplateWindow({
            websiteId: this.websiteId,
            callback: function (templateId) {
                this.selectTemplate(templateId);
                // open new template for editing
                var record = this.store.getById(templateId);
                if (record) {
                    this.openTemplate(record);
                }
            },
            scope: this
        })).show();
    },

    /**
     * Allows the user to rename an existing template
     * @private
     */
    onRenameBtnClick: function () {
        var record = this.dataView.getSelectedRecords()[0];
        CMS.Message.prompt(CMS.i18n('Bezeichnung eingeben'), CMS.i18n('Neue Bezeichnung des Templates:'), function (btnId, title) {
            if (btnId === 'ok') {
                CMS.app.trafficManager.sendRequest({
                    action: 'editTemplateMeta',
                    data: {
                        id: record.id,
                        websiteId: this.websiteId,
                        name: title
                    },
                    success: function () {
                        CMS.Message.toast(CMS.i18n('Umbenennen erfolgreich.'));
                        this.store.reload({
                            callback: function () {
                                this.fireEvent('CMStemplaterenamed', record.id);
                                this.selectTemplate(record.id);
                            },
                            scope: this
                        });
                    },
                    failureTitle: CMS.i18n('Fehler beim Umbenennen des Templates'),
                    failure: function () {
                        this.store.reload();
                    },
                    scope: this
                });
            }
        }, this, false, record.get('name'), CMS.config.validation.templateName);
    },

    /**
     * Handler for clone button
     * @private
     */
    onCloneBtnClick: function () {
        var record = this.dataView.getSelectedRecords()[0];
        CMS.Message.prompt(CMS.i18n('Bezeichnung eingeben'), CMS.i18n('Bezeichnung des neuen Templates:'), function (btnId, title) {
            if (btnId === 'ok') {
                CMS.app.trafficManager.sendRequest({
                    action: 'createTemplate',
                    data: {
                        name: title,
                        websiteId: this.websiteId,
                        content: record.get('content'),
                        pageType: record.get('pageType')
                    },
                    successCondition: 'data.id',
                    success: function (resp) {
                        var templateId = resp.data.id;
                        this.store.reload({
                            callback: function () {
                                this.selectTemplate(templateId);
                            },
                            scope: this
                        });
                    },
                    failureTitle: CMS.i18n('Fehler beim Duplizieren des Templates'),
                    failure: function () {
                        this.store.reload();
                    },
                    scope: this
                });
            }
        }, this, false, CMS.i18n('{name} – Kopie').replace('{name}', record.get('name')), CMS.config.validation.templateName);
    },

    /**
     * Handler for click button
     * @private
     */
    onDeleteBtnClick: function () {
        var record = this.dataView.getSelectedRecords()[0];
        Ext.MessageBox.confirm(CMS.i18n('Löschen?'), CMS.i18n('Template „{name}“ wirklich löschen?').replace('{name}', (record.get('name') || CMS.i18n('unbenannt'))), function (btnId) {
            if (btnId === 'yes') {
                CMS.app.trafficManager.sendRequest({
                    action: 'deleteTemplate',
                    data: {
                        websiteId: this.websiteId,
                        id: record.id
                    },
                    scope: this,
                    success: function () {
                        this.fireEvent('CMScloseworkbench', record.id, true);
                    },
                    failureTitle: CMS.i18n('Fehler beim Löschen des Templates'),
                    callback: function () {
                        this.store.reload();
                    }
                });
            }
        }, this);
    },

    /**
     * Handler for the edit button
     * @private
     */
    onEditBtnClick: function () {
        this.destroyPopovers();

        var record = this.dataView.getSelectedRecords()[0];
        this.openTemplate(record);
    },

    /**
     * Handler for WebsiteSelection's dblclick event
     * @private
     */
    dblclickHandler: function (dataView, index) {
        this.destroyPopovers();

        var record = this.store.getAt(index);
        this.openTemplate(record);
    },

    /**
     * Shows the popovers if the TemplateSelection becomes visible
     * @private
     */
    showPopovers: function () {
        var createAndShowPopover = function () {
            window.localStorage.setItem('CMSpopoverEditTemplateBtn', 1);

            this.editTemplatePopover = new CMS.Popover({
                target: this.editButton.getEl(),
                title: CMS.i18n(null, 'templateSelection.edit.popover.title'),
                html: CMS.i18n(null, 'templateSelection.edit.popover.text'),
                anchorOffset: 10,
                maxWidth: 420,
                offsets: {
                    right: [0, 0],
                    left: [7, 0]
                },
                destroyOnDocMouseDown: false
            });

            // make sure popovers are destroyed when this component gets invisible
            this.on('beforehide', this.destroyPopovers, this, {single: true});
            this.on('destroy', this.destroyPopovers, this, {single: true});
        };

        // Show template edit button popover onetime
        if (window.localStorage) {
            var showCount = parseInt(window.localStorage.getItem('CMSpopoverEditTemplateBtn'), 10) || 0;
            if (showCount === 0) {
                if (this.store.getTotalCount() > 0) {
                    createAndShowPopover.call(this);
                }
            }
        }
    },

    /**
     * Destroys the popovers
     * @private
     */
    destroyPopovers: function () {
        if (this.editTemplatePopover) {
            this.editTemplatePopover.destroy();
            this.editTemplatePopover = null;
        }
    }
});

Ext.reg('CMStemplateselection', CMS.home.TemplateSelection);
