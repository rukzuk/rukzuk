Ext.ns('CMS.layout');

/**
 * SideBarPanel which contains all template edit components and functionality
 *
 * @class       CMS.layout.TemplateWorkbenchPanel
 * @extends     CMS.layout.IframeWorkbenchPanel
 * @author      Thomas Sojda
 * @copyright   (c) 2011, by Seitenbau GmbH
 */
CMS.layout.TemplateWorkbenchPanel = Ext.extend(CMS.layout.IframeWorkbenchPanel, {
    /** @lends CMS.layout.TemplateWorkbenchPanel.prototype */

    /** @private */
    initComponent: function () {
        this.mode = 'template';
        this.editorXType = 'CMStemplateuniteditor';

        CMS.layout.TemplateWorkbenchPanel.superclass.initComponent.call(this);

        //Set up events
        this.on('CMScreatetemplatesnippet', this.createTemplateSnippetHandler, this);

        this.on('activate', function() {
            if (this.unitStore.getCount() === 0) {
                this.openEmptyTemplate(function() {
                    this.save(null, true);
                    this.structureEditor.fireEvent('CMSshowPopovers');
                });
            } else {
                this.structureEditor.fireEvent('CMSshowPopovers');
            }
        }, this);
    },

    /**
     * Generates the TemplateEditor window config
     * @private
     */
    buildStructureEditor: function () {
        return {
            xtype: 'CMStemplatestructureeditor',
            unitStore: this.unitStore,
            websiteId: this.websiteId,
            title: CMS.i18n('Template-Struktur')
        };
    },

    /**
     * Reverts all unsaved changes
     * @private
     */
    restore: function () {
        CMS.layout.TemplateWorkbenchPanel.superclass.restore.call(this, 'getTemplate');
    },

    /**
     * Called when the user clicks the save button
     * (Overrides CMS.layout.IframeWorkbenchPanel#save and maps the intial parameter)
     *
     * @param {Function} [callback] Optional. A callback function which will be executed after successful saving
     * @param {Boolean} [silent] Optional. If set to <code>true</code> no success message will appear and there
     *      will be no masking (defaults to <code>false</code>)
     */
    save: function (callback, silent) {
        var record = this.structureEditor.dataTree.createTemplateRecord();
        var params = Ext.apply(record.data, { websiteId: this.websiteId });
        var action = 'editTemplate';

        CMS.layout.TemplateWorkbenchPanel.superclass.save.call(this, record, params, action, callback, !!silent);
    },

    /**
     * Handles the "CMScreatetemplatesnippet" event
     * Opens a window to create or update templateSnippets
     *
     * @param {String|Ext.data.Record} unit The the record (or its id) of the unit which
     *       should be used as the root for the templateSnippet
     *
     * @private
     */
    createTemplateSnippetHandler: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.unitStore.getById(unit);
        }

        if (Ext.isObject(unit)) {
            var win = new CMS.structureEditor.CreateTemplateSnippetWindow({
                websiteId: this.websiteId,
                unit: unit
            });
            win.show();
        }
    },

    /**
     * @private
     */
    openEmptyTemplate: function (cb) {
        var baseLayoutList = this.getBaseLayoutList();

        if (baseLayoutList.length <= 0) {
            return;
        }

        if (baseLayoutList.length > 1) {
            this.openLayoutChooserDialog(baseLayoutList, {scope: this, callback: cb});
        } else {
            var rootModuleUnit = this.createUnitRecordFromBaseLayout(baseLayoutList[0]);
            this._insertRootModule(rootModuleUnit);
            cb.call(this);
        }
    },

    /**
     * Create array with base layout snippets and root modules
     * @returns {Array}
     * @private
     */
    getBaseLayoutList: function() {
        var snippetStore = CMS.data.StoreManager.get('templateSnippet', this.websiteId);
        var tplPageType = this.record.get('pageType');
        var baseLayouts = [];
        var baseLayoutsRecords = snippetStore.query('baseLayout', true).getRange();
        baseLayoutsRecords.forEach(function (l) {
            // filter for page types - if array is not empty
            var filterForPageTypes = l.get('pageTypes');
            if (filterForPageTypes.length > 0) {
                if (filterForPageTypes.indexOf(tplPageType) === -1) {
                    // page type of this template was not found in pageTypes of the current snippet
                    return;
                }
            }
            // add to base layout array
            baseLayouts.push ({
                id: l.id,
                name: CMS.translateInput(l.data.name),
                previewImageUrl: l.data.previewImageUrl,
                type: 'snippet'
            });
        });

        var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);
        var rootModules = moduleStore.query('moduleType', CMS.config.moduleTypes.root).getRange().map(function (m) {
            return {
                id: m.id,
                name: CMS.i18n(null, 'templateWorkbenchPanel.baseLayout.moduleNamePrefix') + CMS.translateInput(m.data.name),
                previewImageUrl: null,
                type: 'module'
            };
        });

        return baseLayouts.concat(rootModules);
    },

    /**
     * Create unit from module or template snippet
     * @param {Object} baseLayout
     * @returns {CMS.data.UnitRecord} unit record
     * @private
     */
    createUnitRecordFromBaseLayout: function(baseLayout) {
        if (baseLayout.type == 'module') {
            var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);
            return moduleStore.getById(baseLayout.id).createUnit();
        } else {
            var snippetStore = CMS.data.StoreManager.get('templateSnippet', this.websiteId);
            var snippet = snippetStore.getById(baseLayout.id);
            return CMS.data.createUnitRecordFromTemplateSnippet(snippet, this.websiteId);
        }
    },

    /**
     * Show insert base layout window
     * @param {Array} baseLayoutList
     * @param {Object} options
     * @private
     */
    openLayoutChooserDialog: function (baseLayoutList, options) {
        (new CMS.structureEditor.ChooseBaseLayoutWindow({
            baseLayouts: baseLayoutList,
            scope: this,
            callback: function (baseLayoutData) {
                if (baseLayoutData) {
                    var rootModuleUnit = this.createUnitRecordFromBaseLayout(baseLayoutData);
                    this._insertRootModule(rootModuleUnit);
                }
                if (options.callback) {
                    options.callback.call(options.scope || window);
                }
            }
        })).show();
    },

    /**
     * Inserts a root module
     * @param rootModuleUnit - the root module unit (see ModuleStore.createUnit)
     * @private
     */
    _insertRootModule: function (rootModuleUnit) {
        var cfg = {
            templateUnit: rootModuleUnit,
            websiteId: this.websiteId,
            name: rootModuleUnit.data.name
        };
        this.fireEvent('CMSinsertunit', cfg);
        //Reset dirty state - prevents onBeforeUnload warning
        this.unitStore.isDirty = false;
    }

});

Ext.reg('CMStemplateworkbenchpanel', CMS.layout.TemplateWorkbenchPanel);
