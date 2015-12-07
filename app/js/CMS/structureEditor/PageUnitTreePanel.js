Ext.ns('CMS.structureEditor');

/**
 * A treepanel representing one page
 *
 * @class CMS.structureEditor.PageUnitTreePanel
 * @extends CMS.structureEditor.UnitTreePanel
 * @requires CMS.structureEditor.PageUnitTreeLoader
 * @requires CMS.structureEditor.PageUnitTreeDragZone
 * @requires CMS.structureEditor.PageUnitTreeDropZone
 */
CMS.structureEditor.PageUnitTreePanel = Ext.extend(CMS.structureEditor.UnitTreePanel, {
    /** @lends CMS.structureEditor.PageUnitTreePanel.prototype */

    cls: 'CMSpageunittreepanel',
    bubbleEvents: ['CMSbeforeinsertunit'],
    mode: 'page',

    /**
     * The unitstore that holds the current page's units
     * @property unitStore
     * @type CMS.data.UnitStore
     */
    unitStore: null,

    /**
     * The associated tooltips instance
     * @property toolTips
     * @type Ext.ToolTip
     */
    toolTips: null,

    /**
     * A info message to inform the user that he has not sufficient rights to edit the selected unit
     * @property noRightsMessage
     * @type String
     */
    noRightsMessage: null,

    /** @protected */
    initComponent: function () {
        this.dragConfig = this.dragConfig || {
            Class: CMS.structureEditor.PageUnitTreeDragZone
        };

        this.dropConfig = this.dropConfig || {
            websiteId: this.websiteId,
            Class: CMS.structureEditor.PageUnitTreeDropZone
        };

        this.loader = new CMS.structureEditor.PageUnitTreeLoader({ websiteId: this.websiteId });
        this.ddGroup = CMS.config.ddGroups.modules;
        this.mon(this.unitStore, 'update', this.storeUpdateHandler, this);

        Ext.apply(this, this.initialConfig);

        CMS.structureEditor.PageUnitTreePanel.superclass.initComponent.apply(this, arguments);
    },

    /**
     * @param {CMS.data.PageRecord} record The record to be opened
     */
    openRecord: function (record) {
        console.log('[PageTree] loading record ', record);
        if (!record || !record.hasOwnProperty('id')) {
            return;
        }
        this.enable();
        this.suspendEvents();
        this.unitStore.loadPage(record);
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
        }
    },

    /**
     * Converts the tree data to a Page Record
     * @return {CMS.data.PageRecord}
     */
    createPageRecord: function () {
        return new CMS.data.PageRecord({
            id: this.recordId,
            content: this.createJSON()
        });
    },

    /**
     * Generates a CMS.data.PageRecord from the tree data, that can be passed
     * to PreviewArea for rendering.
     * This re-uses a single record in order to limit memory usage
     * @private
     */
    generatePreview: function () {
        if (!this.tempRecord) {
            this.tempRecord = new CMS.data.PageRecord();
        }
        this.tempRecord.set('content', this.createJSON());
        this.tempRecord.set('id', this.recordId);
        this.tempRecord.set('name', this.recordName);
        return this.tempRecord;
    },

    /** @private */
    markDropTargets: function (record, draggedNode) {
        if (record && this.toolTips) {
            this.toolTips.disable();
        }

        CMS.structureEditor.PageUnitTreePanel.superclass.markDropTargets.apply(this, arguments);

        if (!record && this.toolTips) {
            this.toolTips.enable();
        }
    },

    /**
     * Determines whether a unit record can be dropped on a certain tree node.
     * @param {Ext.data.Record} record The dragged module record
     * @param {Ext.tree.TreeNode} node The potential drop node
     * @param {Ext.tree.TreeNode} root This tree's root node, passed for convenience
     * @param {CMS.data.UnitRecord} draggedUnit The dragged unit record
     */
    isValidDropNode: function (record, node, root, draggedUnit) {
        if (!draggedUnit.isMovable('page')) {
            return false;
        }
        var childModuleList;
        var id = record && record.id;
        if (node != root) {
            var unit = this.unitStore.getById(node.id);
            if (unit) {
                childModuleList = unit.get('ghostChildren');
            }
        }
        if (node.id != this.unitStore.getParentUnit(draggedUnit).id) {
            return false;
        }
        return (Ext.isArray(childModuleList) && Ext.pluck(childModuleList, 'moduleId').indexOf(id) != -1);
    },

    /**
     * Overrides superclass to destroy tooltips
     */
    destroy: function () {
        if (this.toolTips) {
            this.toolTips.destroy();
        }

        CMS.structureEditor.PageUnitTreePanel.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSpageunittreepanel', CMS.structureEditor.PageUnitTreePanel);
