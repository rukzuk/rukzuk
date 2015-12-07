Ext.ns('CMS.structureEditor');

/**
 * An implementation of {@link CMS.ContextMenu} to provide a context menu
 * for the node of a {@link Ext.TreePanel}
 *
 * @class CMS.TreeContextMenu
 * @extends CMS.ContextMenu
 */
CMS.TreeContextMenu = Ext.extend(CMS.ContextMenu, {
    /** @lends CMS.TreeContextMenu */

    /**
     * Plugin init method;
     * overrides superclass to register the contextmenu handler
     */
    init: function (structureEditor) {
        CMS.TreeContextMenu.superclass.init.call(this, structureEditor);

        this.component.on('contextMenu', this.contextmenuHandler, this);
    },

    /**
     * Handler method for the "contextmenu" event of the structur tree
     * @private
     */
    contextmenuHandler: function (node, e) {
        node.select();
        this.showContextMenu(node.id, e);
    },

    /**
     * Cleanup method;
     * overrides superclass to unregister the contextmenu handler
     */
    destroy: function () {
        this.component.un('contextmenu', this.contextmenuHandler, this);
        CMS.TreeContextMenu.superclass.destroy.call(this);
    }
});
