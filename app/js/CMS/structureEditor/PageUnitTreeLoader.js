Ext.ns('CMS.structureEditor');

/**
 * A tree loader specifically designed for creating PageUnitTreeNodes
 *
 * @class CMS.structureEditor.PageUnitTreeLoader
 * @extends CMS.structureEditor.UnitTreeLoader
 * @requires CMS.structureEditor.PageUnitTreeNode
 */
CMS.structureEditor.PageUnitTreeLoader = Ext.extend(CMS.structureEditor.UnitTreeLoader, {
    baseAttrs: {
        loaded: true,
        nodeType: 'CMSpageunittreenode'
    }
});
