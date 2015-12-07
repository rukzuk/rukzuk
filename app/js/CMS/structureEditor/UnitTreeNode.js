Ext.ns('CMS.structureEditor');

/**
 * A data node that stores a CMS unit (an instance of a module)
 * @class CMS.structureEditor.UnitTreeNode
 * @extends Ext.tree.TreeNode
 * @constructor
 * @param {@link CMS.data.ModuleRecord} record The module record to create the tree node from
 */
CMS.structureEditor.UnitTreeNode = function (record) {
    var attributes;
    if (typeof record.loader == 'object') { // check if constructor was called by loader
        attributes = record;
    } else {
        console.log('[CMS.structureEditor.UnitTreeNode] generating treenode');
        attributes = this.nodeFromRecord(record);
        console.log('[CMS.structureEditor.UnitTreeNode] new treeNode: ', attributes);
    }

    if (attributes.moduleId) {
        var module = CMS.data.StoreManager.get('module', record).getById(attributes.moduleId);
        if (module) {
            attributes.icon = attributes.icon || module.get('icon');
            attributes.moduleType = attributes.moduleType || module.get('moduleType');
        }
    }
    attributes.text = attributes.text || attributes.name;

    CMS.structureEditor.UnitTreeNode.superclass.constructor.call(this, attributes);
};

Ext.extend(CMS.structureEditor.UnitTreeNode, Ext.tree.TreeNode, {
    /** @lends CMS.structureEditor.UnitTreeNode.prototype */

    /**
     * Converts a {@link CMS.data.ModuleRecord} or a {@link CMS.data.UnitRecord} to a treeNode config object
     * @private
     */
    nodeFromRecord: function (record) {
        var attributes;
        // convert module to unit
        if (!CMS.data.isUnitRecord(record)) {
            record = record.createUnit();
        }
        // clone unit
        attributes = SB.util.cloneObject(record.data);
        attributes.text = record.getUIName();

        return attributes;
    },

    /**
    * Find the corresponding unit in unitStore
    */
    getUnit: function () {
        return this.ownerTree.unitStore.getById(this.id);
    },

    /**
    * Find the corresponding module that was used to generate the unit
    */
    getModule: function () {
        return this.getUnit().getModule();
    },

    isExtensionUnit: function () {
        return this.attributes.moduleType === CMS.config.moduleTypes.extension;
    },

    hasDefaultChildNodes: function () {
        var hasDefaultChildNodes = false;
        if (this.hasChildNodes()) {
            Ext.each(this.childNodes, function (child) {
                if (child.attributes.moduleType === CMS.config.moduleTypes.defaultModule) {
                    hasDefaultChildNodes = true;
                    return false;
                }
            });
        }
        return hasDefaultChildNodes;
    },

    hasExtensionChildNodes: function () {
        var hasExtensionChildNodes = false;
        if (this.hasChildNodes()) {
            Ext.each(this.childNodes, function (child) {
                if (child.attributes.moduleType === CMS.config.moduleTypes.extension) {
                    hasExtensionChildNodes = true;
                    return false;
                }
            });
        }
        return hasExtensionChildNodes;
    },

    /**
     * Show or hide extension units
     * @param {Boolean} state (optional)
     */
    showExtensionUnits: function (state) {
        if (this.isExtensionUnit()) {
            return;
        }

        var delay = 0;
        if (!this.childrenRendered) {
            this.renderChildren();
            delay = 100;
        }

        (function () {
            this.ui.showExtensionUnits(state);
        }).defer(delay, this);
    }
});
