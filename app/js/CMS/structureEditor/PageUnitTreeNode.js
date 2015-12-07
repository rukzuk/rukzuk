Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.PageUnitTreeNode
* @extends CMS.structureEditor.UnitTreeNode
* A data node that stores a CMS unit (an instance of a module), for use in {@link CMS.structureEditor.PageUnitTreePanel}
* @requires CMS.structureEditor.PageUnitTreeNodeUI
*/
CMS.structureEditor.PageUnitTreeNode = Ext.extend(CMS.structureEditor.UnitTreeNode, {

    defaultUI: CMS.structureEditor.PageUnitTreeNodeUI,

    hasContextMenuItems: function() {
        if (this.checkCanInsert()) {
            return true;
        }
        if (this.checkCanDuplicate()) {
            return true;
        }
        if (this.checkCanDelete()) {
            return true;
        }
        return false;
    },

    checkCanInsert: function () {
        var unit = this.getUnit();
        if (unit && !unit.isExtensionUnit()) {
            if (unit.hasInsertableChildrenInMode('page')) {
                return true;
            }
            var parent = unit.getParentUnit();
            if (parent && parent.hasInsertableChildrenInMode('page')) {
                return true;
            }
        }
        return false;
    },

    checkCanDuplicate: function () {
        var unit = this.getUnit();
        return unit && unit.isClonableInMode('page');
    },

    checkCanDelete: function () {
        var unit = this.getUnit();
        return unit && unit.isDeletableInMode('page');
    }

});

Ext.reg('CMSpageunittreenode', CMS.structureEditor.PageUnitTreeNode);

Ext.tree.TreePanel.nodeTypes.CMSpageunittreenode = CMS.structureEditor.PageUnitTreeNode;
