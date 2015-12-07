Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.TemplateUnitTreeDragZone
* @extends Ext.tree.TreeDragZone
* A dragzone particularly for use with {@link CMS.structureEditor.TemplateUnitTreePanel}
*/
CMS.structureEditor.TemplateUnitTreeDragZone = function (tree, config) {
    var defaults = {
        ddGroup: CMS.config.ddGroups.modules
    };
    CMS.structureEditor.TemplateUnitTreeDragZone.superclass.constructor.call(this, tree, Ext.applyIf(defaults, config));
};

Ext.extend(CMS.structureEditor.TemplateUnitTreeDragZone, Ext.tree.TreeDragZone, {
    onInitDrag: function (e) {
        var data = this.dragData;
        this.tree.eventModel.disable();
        this.proxy.update('');
        data.node.ui.appendDDGhost(this.proxy.ghost.dom);
        this.tree.fireEvent('startdrag', this.tree, data.node, e);
    }
});
