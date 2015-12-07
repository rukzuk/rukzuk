Ext.ns('CMS.structureEditor');

/**
 * A treepanel representing one template
 *
 * @class CMS.structureEditor.TemplateUnitTreePanel
 * @extends CMS.structureEditor.UnitTreePanel
 * @requires CMS.structureEditor.TemplateUnitTreeLoader
 * @requires CMS.structureEditor.TemplateUnitTreeDragZone
 * @requires CMS.structureEditor.TemplateUnitTreeDropZone
 */
CMS.structureEditor.TemplateUnitTreePanel = Ext.extend(CMS.structureEditor.UnitTreePanel, {
    /** @lends CMS.structureEditor.TemplateUnitTreePanel.prototype */

    cls: 'CMStemplateunittreepanel',

    mode: 'template',

    bubbleEvents: ['CMSduplicateunit', 'CMSremoveunit', 'CMSbeforeinsertunit', 'CMSrenameunit', 'CMSinsertunit', 'CMSselectunit'],

    /**
     * The unitstore that holds the current template's units
     *
     * @property unitStore
     * @type CMS.data.UnitStore
     */
    unitStore: null,

    initComponent: function () {
        this.dragConfig = this.dragConfig || {
            Class: CMS.structureEditor.TemplateUnitTreeDragZone
        };

        this.dropConfig = this.dropConfig || {
            websiteId: this.websiteId,
            Class: CMS.structureEditor.TemplateUnitTreeDropZone
        };

        this.menu = new Ext.menu.Menu({
            floating: true,
            items: [{
                text: CMS.i18n('Duplizieren'),
                cls: 'clone',
                ref: 'cloneButton',
                handler: this.cloneHandler,
                scope: this
            }, {
                text: CMS.i18n('Löschen'),
                cls: 'delete',
                handler: this.deleteHandler,
                ref: 'deleteButton',
                scope: this
            }]
        });
        this.loader = new CMS.structureEditor.TemplateUnitTreeLoader({websiteId: this.websiteId});
        this.ddGroup = CMS.config.ddGroups.modules;

        this.treeEditor = new Ext.tree.TreeEditor(this, {
            // the field configuration
            xtype: 'textfield',
            selectOnFocus: true
        }, {
            // the editor configuration
            cancelOnEsc: true,
            completeOnEnter: true,
            hideEl: true,
            updateNode: Ext.emptyFn,
            editDelay: 0
        });

        CMS.structureEditor.TemplateUnitTreePanel.superclass.initComponent.apply(this, arguments);

        this.mon(this.unitStore, 'update', this.storeUpdateHandler, this);
        this.mon(this.treeEditor, 'complete', this.renameCompleteHandler, this);
    },

    /**
     * Triggers the inline renaming of a unit tree node
     * @param {String} unitId The id of the unit to be renamed
     */
    triggerRename: function (unitId) {
        var node = this.getNodeById(unitId);
        if (node) {
            this.treeEditor.unitId = unitId;
            this.treeEditor.triggerEdit(node);
        }
    },

    /**
     * Open a record
     * @param record
     * @param forceReload
     */
    openRecord: function (record, forceReload) {
        console.log('[TemplateTree] loading record ', record);
        if (!record || !record.hasOwnProperty('id') || (record.id == this.recordId && !forceReload)) {
            return;
        }
        this.enable();
        this.suspendEvents();
        this.unitStore.loadTemplate(record);
        this.loader.replaceChildNodes(this.getRootNode(), record.get('content'));
        this.recordId = record.get('id');
        this.recordName = record.get('name');
        this.resumeEvents();
    },

    /**
     * Handler for the unitstore's <tt>datachanged</tt> event
     * @private
     */
    storeUpdateHandler: function (store, record, operation) {
        if (operation == 'commit') {
            var node = this.getNodeById(record.id);
            node.setText(record.getUIName());
            Ext.copyTo(node.attributes, record.data, CMS.config.treeNodeAttributeData);
            node.getUI().setGhostContainer(record.get('ghostContainer'));
            node.getUI().setVisibleFormGroups(record.get('visibleFormGroups'));
        }
    },

    /**
     * Handler for the "complete" event of the tree editor;
     * Fires the "CMSrenameunit" event to write the entered name into the unit store
     * @private
     */
    renameCompleteHandler: function (editor, newValue, oldValue) {
        this.fireEvent('CMSrenameunit', editor.unitId, newValue);
    },

    /**
     * Completes the unit rename process if a unit is currently being edited.
     * Needed to fix SBCMS-2149: remove rename editor when structureEditor is hidden
     */
    completeRename: function () {
        this.treeEditor.completeEdit();
    },

    /**
     * Converts the tree data to a Template Record
     * @return {CMS.data.TemplateRecord}
     */
    createTemplateRecord: function () {
        return new CMS.data.TemplateRecord({
            id: this.recordId,
            content: this.createJSON()
        });
    },

    /**
     * Generates a CMS.data.TemplateRecord from the tree data, that can be passed
     * to PreviewArea for rendering.
     * This re-uses a single record in order to limit memory usage
     * @private
     */
    generatePreview: function () {
        if (!this.tempRecord) {
            this.tempRecord = new CMS.data.TemplateRecord();
        }
        this.tempRecord.set('content', this.createJSON());
        this.tempRecord.set('id', this.recordId);
        this.tempRecord.set('name', this.recordName);
        return this.tempRecord;
    },

    /**
     * Handler for the clone button
     * @private
     */
    cloneHandler: function () {
        var node = this.getSelectionModel().getSelectedNode();

        if (node) {
            this.fireEvent('CMSduplicateunit', node.id);
        }
    },

    /**
     * Handler for the delete button
     * @private
     */
    deleteHandler: function () {
        var node = this.getSelectionModel().getSelectedNode();
        if (node) {
            this.fireEvent('CMSremoveunit', node.id);
        }
    },

    /**
     * Determines whether a module record can be dropped on a certain tree node.
     * @param {Ext.data.Record} record The dragged module record
     * @param {Ext.tree.TreeNode} node The potential drop node
     * @param {Ext.tree.TreeNode} root This tree's root node, passed for convenience
     * @param {CMS.data.UnitRecord} draggedUnit The dragged unit record
     */
    isValidDropNode: function (record, node, root, draggedUnit) {
        var childModuleList;
        var id = record && record.id;
        if (node == root) {
            return !root.hasChildNodes() && CMS.data.isRootModuleRecord(record);
        } else {
            childModuleList = node.getUnit().getInsertableUnitsInMode('template');
            for (var i = 0; i < childModuleList.length; i++) {
                if (childModuleList[i].id == id) {
                    return true;
                }
            }
            return false;
        }
    }
});

Ext.reg('CMStemplateunittreepanel', CMS.structureEditor.TemplateUnitTreePanel);
