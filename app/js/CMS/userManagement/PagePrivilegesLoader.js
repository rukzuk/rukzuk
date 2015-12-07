Ext.ns('CMS.userManagement');

/**
 * @class CMS.userManagement.PagePrivilegesLoader
 * @extends Ext.tree.TreeLoader
 *
 * A TreeLoader provides for lazy loading of an {@link Ext.tree.TreeNode}'s child
 * nodes from a specified URL. The loader can get parse the response
 * to get its data from special subroot.
 */
CMS.userManagement.PagePrivilegesLoader = Ext.extend(CMS.TreeLoader, {

    baseAttrs: {
        uiProvider: CMS.userManagement.PagePrivilegesTreeNodeUI
    },

    action: 'getNavigationPrivileges',

    /**
     * @private
     * Overridden to make sure that tree nodes without the "leaf" attribute are
     * instantiated as the correct node type (AsyncTreeNode if the node has
     * children and TreeNode if it is a leaf).
     */
    createNode: function (attr) {
        if (this.baseAttrs) {
            Ext.applyIf(attr, this.baseAttrs);
        }
        if (this.applyLoader !== false && !attr.loader) {
            attr.loader = this;
        }
        if (Ext.isString(attr.uiProvider)) {
            attr.uiProvider = this.uiProviders[attr.uiProvider] || Ext.ns(attr.uiProvider);
        }
        if (attr.nodeType) {
            return new Ext.tree.TreePanel.nodeTypes[attr.nodeType](attr);
        } else {
            // The original code is:
            // return attr.leaf ? new Ext.tree.TreeNode(attr) : new Ext.tree.AsyncTreeNode(attr);
            // which sucks if the node has no leaf attribute. The new code expects
            // a children attribute though!
            return (attr.children && attr.children.length) ? new Ext.tree.AsyncTreeNode(attr) : new Ext.tree.TreeNode(attr);
        }
    }
});
