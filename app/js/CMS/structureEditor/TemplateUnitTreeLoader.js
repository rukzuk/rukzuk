Ext.ns('CMS.structureEditor');

/**
 * A tree loader specifically designed for creating TemplateUnitTreeNodes
 * @class CMS.structureEditor.TemplateUnitTreeLoader
 * @extends CMS.structureEditor.UnitTreeLoader
 * @requires CMS.structureEditor.TemplateUnitTreeNode
 */
CMS.structureEditor.TemplateUnitTreeLoader = Ext.extend(CMS.structureEditor.UnitTreeLoader, {
    baseAttrs: {
        loaded: true,
        nodeType: 'CMStemplateunittreenode'
    }
});
