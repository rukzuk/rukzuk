Ext.ns('CMS.structureEditor');

/**
 * @class CMS.structureEditor.PageStructureEditor
 * @extends Ext.Panel
 */
CMS.structureEditor.PageStructureEditor = Ext.extend(CMS.structureEditor.StructureEditor, {
    /** @lends CMS.structureEditor.PageStructureEditor */

    /**
     * Flag if we updated the pages name
     */
    updatedPageName: false,

    /** @protected */
    initComponent: function () {

        this.items = [{
            xtype: 'CMSpageunittreepanel',
            ref: 'dataTree',
            noRightsMessage: this.noRightsMessage,
            plugins: [new CMS.TreeContextMenu({
                items: [{
                    iconCls: 'add',
                    text: CMS.i18n(null, 'pageStructureEditor.contextMenu.openInsertWindow'),
                    event: 'CMSopeninsertwindow',
                    condition: {
                        fn: this.checkCanInsert,
                        scope: this
                    }
                }, {
                    iconCls: 'clone',
                    text: CMS.i18n('Duplizieren'),
                    event: 'CMSduplicateunit',
                    condition: {
                        fn: this.checkCanDuplicate,
                        scope: this
                    }
                }, {
                    iconCls: 'delete',
                    text: CMS.i18n('Löschen'),
                    event: 'CMSremoveunit',
                    condition: {
                        fn: this.checkCanDelete,
                        scope: this
                    }
                }]
            })],
            region: 'center',
            unitStore: this.unitStore,
            websiteId: this.websiteId,
            dropConfig: {
                websiteId: this.websiteId,
                ddGroup: CMS.config.ddGroups.modules,
                Class: CMS.structureEditor.PageUnitTreeDropZone
            },
            listeners: {
                startdrag: this.onTreeStartDrag,
                movenode: this.onMoveNode,
                append: this.onTreeChanged,
                remove: this.onTreeChanged,
                insert: this.onTreeChanged,
                nodeover: this.onNodeOver,
                nodeout: this.onNodeOut,
                enddrag: this.onTreeEndDrag,
                beforeclick: this.onNodeClicked,
                afterdrop: function (node) {
                    // SBCMS-34 make sure new node is selected
                    this.fireEvent('CMSselectunit', { unit: node.id });
                },
                scope: this
            }
        }];
        this.pageTitle = null;
        CMS.structureEditor.PageStructureEditor.superclass.initComponent.apply(this, arguments);

        this.topToolbar.add(['->', {
            xtype: 'button',
            iconCls: 'add',
            cls: 'CMSbtnmedium',
            ref: 'addUnitButton',
            text: CMS.i18n(null, 'pageStructureEditor.contextMenu.openInsertWindow'),
            disabled: true,
            handler: this.addUnitButtonHandler,
            scope: this
        }, {
            tooltip: CMS.i18n(null, 'pageStructureEditor.showEditPagePropertiesWindowBtn'),
            cls: 'CMSbtnmedium',
            iconCls: 'settings',
            scope: this,
            handler: function (btn) {
                var win = new CMS.home.EditPagePropertiesWindow({
                    websiteId: this.websiteId,
                    pageId: this.pageOrTemplateId,
                    readonly: false,
                    listeners: {
                        metadataupdated: function (data) {
                            // update breadcrumb
                            this.fireEvent('CMScurrentpagenamechange', data.name, data);
                            // reload website stores navigation (triggers update of pageTree) on destroy
                            this.updatedPageName = true;
                            // refresh page
                            this.fireEvent('CMSrender', {
                                record: this.dataTree.generatePreview()
                            });
                            win.close();
                        },
                        cancel: function () {
                            win.close();
                        },
                        scope: this
                    }
                });
                win.show();
            }
        }]);

        this.on('CMSselectunit', this.updateInsertButtonState, this);

    },

    /**
     * Update the state of the insert button based on the currently selected unit
     * @param eventData
     * @private
     */
    updateInsertButtonState: function (eventData) {
        var canInsertUnits = this.checkCanInsert(eventData.unit);
        this.topToolbar.addUnitButton.setDisabled(!canInsertUnits);
    },

    /**
     * Add Module Button Handler (Page Blocks)
     * @private
     */
    addUnitButtonHandler: function () {
        this.fireEvent('CMSopeninsertwindow', this.getSelectedNode().id);
    },

    /**
     * Condition for the "edit unit" context menu item
     * Checks if the user (editor) is allowed to edit the unit for which the context menu has bee requested
     * @private
     */
    checkCanEdit: function (unitId) {
        var unit = this.unitStore.getById(unitId);
        return unit && unit.isEditableInMode('page');
    },


    /**
     * Condition for the "insert unit" context menu item
     * Checks if the user (editor) is allowed to insert units relative to currently selected one
     * @private
     */
    checkCanInsert: function (unitId) {
        var node = this.dataTree.getNodeById(unitId);
        return node && node.checkCanInsert();
    },

    /**
     * Condition for the "delete unit" context menu item
     * Checks if the user (editor) is allowed to delete the unit for which the context menu has bee requested
     * @private
     */
    checkCanDelete: function (unitId) {
        var node = this.dataTree.getNodeById(unitId);
        return node && node.checkCanDelete();
    },

    /**
     * Condition for the "duplicate unit" context menu item
     * Checks if the user (editor) is allowed to clone the unit for which the context menu has bee requested
     * @private
     */
    checkCanDuplicate: function (unitId) {
        var node = this.dataTree.getNodeById(unitId);
        return node && node.checkCanDuplicate();
    },

    /**
     * Inserts a unit into the tree of the page
     * @param {Object} cfg The object which describes the unit which is inserted
     */
    insertUnit: function (cfg) {
        if (cfg && cfg.templateUnit) {
            var targetNode = cfg.parentUnit && this.dataTree.getNodeById(cfg.parentUnit.id);

            var data = SB.util.cloneObject(cfg.templateUnit.data);
            data.websiteId = cfg.websiteId;
            data.name = cfg.name;

            this.insertUnitIntoTree(data, targetNode, cfg.position);
        }
    },

    destroy: function () {
        // reload navigation of the current website if we changed the name
        if (this.updatedPageName) {
            CMS.data.WebsiteStore.refreshWebsiteRecord(this.websiteId, 'navigation');
        }
        CMS.structureEditor.PageStructureEditor.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSpagestructureeditor', CMS.structureEditor.PageStructureEditor);
