Ext.ns('CMS.home');

/**
 * Panel for managing pages and designs (templates)
 *   +-------------+------------------+
 *   |+---+ Page A |                  |
 *   ||            |                  |
 *   |+-+-+ Page B |                  |
 *   |  |          |                  |
 *   |  +-+ ...    |     Preview      |
 *   +-------------+                  |
 *   | Buttons     |                  |
 *   |-------------+                  |
 *   |[] Design 1  |                  |
 *   |[] Design 2  |                  |
 *   |[] ...       |                  |
 *   +-------------+                  |
 *   | Buttons     |                  |
 *   +-------------+------------------+
 *
 * @class CMS.home.PageAndTemplateManagementPanel
 * @extends CMS.home.ManagementPanel
 */
CMS.home.PageAndTemplateManagementPanel = Ext.extend(CMS.home.ManagementPanel, {
    /** @lends CMS.home.PageAndTemplateManagementPanel.prototype */

    bubbleEvents: ['CMScloseworkbench', 'CMSopenworkbench', 'CMSopentemplate'],

    preventPreviewLoadingOnAppear: false,

    /**
     * The currently opened website
     * @property website
     * @type CMS.data.WebsiteRecord
     */
    website: null,

    /** @private */
    initComponent: function () {
        Ext.apply(this, {
            layout: 'border',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            items: [{
                width: 340,
                cls: 'CMSsidebar',
                layout: 'vbox',
                layoutConfig: {
                    align: 'stretch',
                    pack: 'start'
                },
                region: 'west',
                split: true,
                items: [{
                    /**
                     * A reference to template selection panel
                     * @property
                     * @name templateSelection
                     * @type CMS.home.TemplateSelection
                     * @memberOf CMS.home.PageAndTemplateManagementPanel.prototype
                     * @private
                     */
                    title: CMS.i18n('Designs', 'PageAndTemplateManagementPanel.templateSelection.title'),
                    xtype: 'CMStemplateselection',
                    ref: '../templateSelection',
                    websiteId: this.website.id,
                    listeners: {
                        CMStemplaterenamed: this.onTemplateRename,
                        scope: this
                    },
                    height: 300
                }, {
                    /**
                     * A reference to the page tree
                     * @property
                     * @name pageTreePanel
                     * @type CMS.home.PageTreePanel
                     * @memberOf CMS.home.PageAndTemplateManagementPanel.prototype
                     * @private
                     */
                    header: true,
                    headerCfg: {
                        tag: 'div',
                        cls: 'x-panel-header',
                        children: [
                            { tag: 'span', cls: 'CMSpagetreetitle', 'html': CMS.i18n(null, 'PageAndTemplateManagementPanel.pagetree.title') },
                            { tag: 'span', cls: 'CMSpagetreetitlelayout', 'html': CMS.i18n(null, 'PageAndTemplateManagementPanel.pagetree.title.template') }
                        ]
                    },
                    xtype: 'CMSpagetreepanel',
                    ref: '../pageTreePanel',
                    flex: 1,
                    websiteId: this.website.id
                }]
            }, {
                /**
                 * A reference to the preview panel
                 * @property
                 * @name previewPanel
                 * @type CMS.home.PreviewPanel
                 * @memberOf CMS.home.PageAndTemplateManagementPanel.prototype
                 * @private
                 */
                xtype: 'CMSpreviewpanel',
                ref: 'previewPanel',
                cls: 'CMSpreviewpanel',
                flex: 1,
                region: 'center',
                listeners: {
                    locationchanged: this.previewLocationChangedHandler,
                    CMSview: this.handleView,
                    scope: this
                }
            }]
        });

        CMS.home.PageAndTemplateManagementPanel.superclass.initComponent.apply(this, arguments);

        this.mon(this.pageTreePanel.getSelectionModel(), 'selectionchange', function (sm, node) {
            this.handleSelectionChange(node, true);
        }, this);

        this.mon(this.templateSelection.dataView, 'selectionchange', function (dataView) {
            this.handleSelectionChange(dataView.getSelectedRecords()[0], false);
        }, this);

        this.on('afterrender', this.initSelection, this, {delay: 100});
    },

    /**
     * Initial selection: select first template or first page
     * @private
     */
    initSelection: function () {
        if (CMS.app.userInfo.canEditTemplates(this.website)) {
            CMS.data.StoreManager.get('template', this.website.id, {
                callback: function () {
                    this.templateSelection.selectFirstTemplate();
                },
                scope: this
            });
        } else {
            this.pageTreePanel.selectFirstPage();
        }
    },

    /**
     * Unified selection change handler for page tree and template list
     * @private
     */
    handleSelectionChange: function (item, isPage) {
        if (item && item.id) {
            this.selectItem(item.id);
        } else if (isPage === this.isPageId(this.selectedItemId)) {
            // we had a template selected and now we are unselecting this template
            // or we had a page selected and now we are unselecting this page
            // -> either way nothing should be selected anymore
            this.selectItem(null);
        }
    },

    /**
     * Selects the item (page or template) with the given id
     * @param {String} id The id of the item to be selected; Use null to clear
     *      selection
     * @param {Boolean} [preventPreviewUpdate] - does not call this.showPreview, but selects the item
     */
    selectItem: function (id, preventPreviewUpdate) {
        if (id === undefined || id === this.selectedItemId) {
            return;
        }

        this.selectedItemId = id;

        if (id === null) {
            // select nothing (clear selection)
            this.pageTreePanel.getSelectionModel().clearSelections();
            this.templateSelection.dataView.clearSelections();
        } else if (this.isPageId(id)) {
            // select a page node
            var node = this.pageTreePanel.getNodeById(id);
            if (node && !node.isSelected()) {
                node.select();
            }
            this.templateSelection.dataView.clearSelections();
        } else {
            // select template
            var templateRecord = this.templateSelection.store.getById(id);
            if (templateRecord && !this.templateSelection.dataView.isSelected(templateRecord)) {
                this.templateSelection.dataView.select(templateRecord);
            }
            this.pageTreePanel.getSelectionModel().clearSelections();
        }
        if (!preventPreviewUpdate) {
            this.showPreview(this.selectedItemId);
        }
    },

    /**
     * Checks if the given id is a page node id
     * @private
     */
    isPageId: function (id) {
        return (/^PAGE-/).test(id);
    },

    /**
     * refresh preview when reactivating
     * @private
     */
    onAppear: function () {
        this.templateSelection.setVisible(CMS.app.userInfo.canEditTemplates(this.website));

        if (this.selectedItemId && !this.preventPreviewLoadingOnAppear) {
            this.showPreview.defer(100, this, [this.selectedItemId]);
        }
    },

    /**
     * clear preview when deactivate tab
     * @private
     */
    onDisappear: function () {
        this.templateSelection.hide();
        this.previewPanel.setContent();
    },

    /**
     * Handler for the "locationchanged" event of the previewPanel;
     * Updates the selection of the pageTreePanel to match the content of the
     * previewPanel
     * @private
     */
    previewLocationChangedHandler: function (cmp, params) {
        var node = params && this.pageTreePanel.getNodeById(params.pageId);
        if (node) {
            this.selectItem(node.id, true);
        }
    },

    /**
     * Handler for the "view" button
     * @private
     */
    handleView: function () {
        var id = this.selectedItemId;
        var url, params;

        if (this.isPageId(id)) {
            params = CMS.app.trafficManager.createPostParams(Ext.apply({
                websiteId: this.website.id,
                pageId: id
            }, CMS.config.params.previewPageById));
            url = Ext.urlAppend(CMS.config.urls.previewPageById, Ext.urlEncode(params));
        } else {
            params = CMS.app.trafficManager.createPostParams(Ext.apply({
                websiteId: this.website.id,
                templateId: id
            }, CMS.config.params.previewTemplateById));
            url = Ext.urlAppend(CMS.config.urls.previewTemplateById, Ext.urlEncode(params));
        }

        window.open(url, '_blank');
    },

    /**
     * Open the given template
     * @private
     *
     * @param {CMS.data.TemplateRecord} record The record to be opened
     */
    openTemplate: function (record, cb, scope) {
        var websiteId = this.website.id;
        var options = {
            action: 'getTemplate',
            data: {
                id: record.id,
                websiteId: websiteId
            },
            success: function (response) {
                record.beginEdit();
                record.data = response.data;
                record.endEdit();
                var title = '';
                if (record.get('name')) {
                    title = CMS.i18n('Template „{templatename}“ bearbeiten').replace('{templatename}', record.get('name'));
                }
                var panelConfig = {
                    xtype: 'CMStemplateworkbenchpanel',
                    cls: 'CMStemplateEditorPanel',
                    plugins: ['CmsApi'],
                    border: false,
                    websiteId: websiteId,
                    record: record,
                    title: title,
                    requiredStores: ['module', 'template', 'templateSnippet'],
                    templateId: record.id,
                    isTemplate: true
                };
                this.fireEvent('CMSopenworkbench', panelConfig);
                if (cb) {
                    cb.call(scope);
                }
            },
            scope: this,
            callback: function () {
                if (this.createTemplateMask) {
                    this.createTemplateMask.unmask();
                    this.createTemplateMask = null;
                }
                record = null;
            },
            failureTitle: CMS.i18n('Fehler beim Laden des Templates')
        };

        CMS.app.lockManager.requestLock({
            id: record.id,
            websiteId: websiteId,
            type: 'template',
            success: function () {
                CMS.app.trafficManager.sendRequest(options);
            }
        });
    },

    /**
     * refreshes the content of the previewPanel with the new page
     * @private
     */
    showPreview: function (id) {
        if (!this.website) {
            return;
        }
        if (id && !this.hidden) {
            var cfg = {
                websiteId: this.website.id
            };
            if (this.isPageId(id)) {
                cfg.pageId = id;
            } else {
                cfg.templateId = id;
            }
            this.previewPanel.setContent(cfg);
        } else {
            this.previewPanel.setContent();
        }
    },

    onTemplateRename: function (templateId) {
        this.pageTreePanel.fireEvent('CMStemplaterenamed', templateId);
    }

});

Ext.reg('CMSpageandtemplatemanagementpanel', CMS.home.PageAndTemplateManagementPanel);
