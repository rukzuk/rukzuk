Ext.ns('CMS.home');

/**
* @class CMS.home.PageTreeDragZone
* A dragzone particularly for use with {@link CMS.home.PageTreePanel}
* @extends Ext.tree.TreeDragZone
*/
CMS.home.PageTreeDragZone = function (tree, config) {
    var defaults = {
        ddGroup: CMS.config.ddGroups.pages
    };
    CMS.home.PageTreeDragZone.superclass.constructor.call(this, tree, Ext.applyIf(defaults, config));
};

Ext.extend(CMS.home.PageTreeDragZone, Ext.tree.TreeDragZone, {
    onInitDrag: function (e) {
        var data = this.dragData;
        this.tree.eventModel.disable();
        this.proxy.update('');
        data.node.ui.appendDDGhost(this.proxy.ghost.dom);
        this.tree.fireEvent('startdrag', this.tree, data.node, e);
    }
});
