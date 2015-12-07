Ext.ns('CMS.structureEditor');

/**
 * An implementation of {@link CMS.ContextMenu} to provide a context menu
 * for the items of an Ext.DataView.
 * Luckily it works for {@link CMS.ThumbnailView}s too.
 *
 * @class CMS.DataViewContextMenu
 * @extends CMS.ContextMenu
 */
CMS.DataViewContextMenu = Ext.extend(CMS.ContextMenu, {

    // overrides superclass to register the contextmenu handler
    init: function (cmp) {
        CMS.DataViewContextMenu.superclass.init.call(this, cmp);

        this.component.on('contextMenu', this.contextmenuHandler, this);
    },

    /**
     * Handler method for the "contextmenu" event of the structur tree
     * @private
     */
    contextmenuHandler: function (dataview, index, htmlEl, ev) {
        var item = dataview.getStore().getAt(index);
        if (item) {
            dataview.select(index);
            this.showContextMenu(item.id, ev);
        }
    },

    // overrides superclass to unregister the contextmenu handler
    destroy: function () {
        if (this.component) {
            this.component.un('contextmenu', this.contextmenuHandler, this);
        }
        CMS.DataViewContextMenu.superclass.destroy.call(this);
    }
});
