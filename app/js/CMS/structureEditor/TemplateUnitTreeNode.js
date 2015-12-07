Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.TemplateUnitTreeNode
* @extends CMS.structureEditor.UnitTreeNode
* A data node that stores a CMS unit (an instance of a module), for use in {@link CMS.structureEditor.TemplateUnitTreePanel}
* @requires CMS.structureEditor.TemplateUnitTreeNodeUI
*/
CMS.structureEditor.TemplateUnitTreeNode = Ext.extend(CMS.structureEditor.UnitTreeNode, {

    defaultUI: CMS.structureEditor.TemplateUnitTreeNodeUI

});

Ext.reg('CMStemplateunittreenode', CMS.structureEditor.TemplateUnitTreeNode);

Ext.tree.TreePanel.nodeTypes.CMStemplateunittreenode = CMS.structureEditor.TemplateUnitTreeNode;
