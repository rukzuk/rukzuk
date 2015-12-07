Ext.ns('CMS.home');

/**
* @class CMS.home.PageTreeLoader
* A tree loader specifically designed for laoding CMS JSON objects
* @requires CMS.home.PageTreeNode
*/
CMS.home.PageTreeLoader = Ext.extend(Ext.tree.TreeLoader, {
    preloadChildren: true,
    baseAttrs: {
        loaded: true,
        nodeType: 'CMSpagetreenode',
        uiProvider: CMS.TreeNodeUI
    },

    /**
    * @param {Ext.tree.TreeNode} node The node whose child nodes should be replaced
    * @param {Array} newChildren An array of new node configs that will replace the existing childnodes
    */
    replaceChildNodes: function (node, newChildren, websiteId) {
        this.baseAttrs.websiteId = websiteId;
        node.beginUpdate();
        node.removeAll();
        Ext.each(newChildren, function (c) {
            var cn = node.appendChild(this.createNode(c));
            if (this.preloadChildren) {
                this.doPreload(cn);
            }
        }, this);
        node.endUpdate();
    },

    createNode: function (c) {
        c.expanded = true;
        // console.log('[PageTreeLoader] node config ', c);
        if (c.id == 'root') {
            c.uiProvider = Ext.tree.RootTreeNodeUI;
        }
        var result = Ext.tree.TreeLoader.prototype.createNode.apply(this, arguments);
        result.setText(c.name);
        if (c.id == 'root') {
            result.allowChildren = CMS.app.userInfo.canCreateRootPages(this.websiteId);
        } else {
            result.allowChildren = CMS.app.userInfo.canCreateChildPages(result);
        }
        return result;
    }
});
