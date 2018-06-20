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

        function getExpandedNodes(node) {
            var nodes = [];
            var selectedNode;
            node.eachChild(function (child) {
                //function to store state of tree recursively
                var storeTreeState = function (node, expandedNodes) {
                    if (node.isSelected()) {
                        selectedNode = node.id;
                    }
                    if(node.isExpanded() && node.childNodes.length > 0) {
                        expandedNodes.push(node.id);
                        node.eachChild(function (child) {
                            storeTreeState(child, expandedNodes);
                        });
                    }
                };
                storeTreeState(child, nodes);
            });

            return {
                expandedNodes : nodes,
                selectedNode: selectedNode
            };
        }

        function setExpandedNodes(node, expandedNodes, selectedNode) {
            node.eachChild(function (child) {
                //function to set state of tree recursively
                var restoreTreeState = function (node, expandedNodes) {
                    if (expandedNodes.indexOf(node.id) != -1) {
                        node.expand();
                    }
                    if (node.id == selectedNode) {
                        node.select();
                        node.ensureVisible();
                    }
                    node.eachChild(function (child) {
                        restoreTreeState(child, expandedNodes);
                    });
                };
                restoreTreeState(child, expandedNodes);
            });
        }

        var expandedNodes = getExpandedNodes(node);

        node.beginUpdate();
        node.removeAll();
        Ext.each(newChildren, function (c) {
            var cn = node.appendChild(this.createNode(c));
            if (this.preloadChildren) {
                this.doPreload(cn);
            }
        }, this);
        node.endUpdate();
        if (expandedNodes) {
            setExpandedNodes(node, expandedNodes.expandedNodes, expandedNodes.selectedNode);
        }

    },

    createNode: function (c) {
        c.expanded = false;
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
