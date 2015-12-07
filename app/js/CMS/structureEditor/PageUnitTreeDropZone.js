Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.PageUnitTreeDropZone
* @extends Ext.tree.TreeDropZone
* A dropzone particularly for use with {@link CMS.structureEditor.PageUnitTreePanel}
* It can handle drops from a grid or from a tree
*/
CMS.structureEditor.PageUnitTreeDropZone = function (tree, config) {
    var defaults = {
        ddGroup: CMS.config.ddGroups.modules
    };
    CMS.structureEditor.PageUnitTreeDropZone.superclass.constructor.call(this, tree, Ext.applyIf(defaults, config));
};

Ext.extend(CMS.structureEditor.PageUnitTreeDropZone, Ext.tree.TreeDropZone, {

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    onNodeDrop: function (nodeData, source, e, data) {
        if (this.tree.disabled) {
            return false;
        }
        var point = this.getDropPoint(e, nodeData, source);
        if (data.selections) {
            return this.onNodeDropFromGrid(nodeData.node, source, e, data, point);
        } else if (data.node) {
            return this.onNodeDropFromSelf(nodeData.node, source, e, data, point);
        } else {
            throw 'Unknown drag source';
        }
    },

    /**
    * @private
    * Handle node drop of a record (from grid)
    */
    onNodeDropFromGrid: function (node, source, e, data, point) {
        console.log('[PageTreeDropZone] nodeDrop', node, source, e, data, point);
        var index;
        var parent;

        switch (point) {
        case 'append':
            parent = node;
            index = false;
            break;
        case 'above':
            parent = node.parentNode;
            index = parent.indexOf(node);
            break;
        case 'below':
            parent = node.parentNode;
            index = parent.indexOf(node) + 1;
            break;
        default:
            return false;
        }

        if (parent.allowChildren) {
            var newNode = this.tree.insertNodeFromRecord(data.selections[0], parent, index);
            this.tree.fireEvent('afterdrop', newNode);
            return true;
        }
        return false;
    },

    /**
    * @private
    * Handle node drop of a tree node (from tree)
    */
    onNodeDropFromSelf: function (targetNode, source, e, data, point) {
        console.log('[PageTreeDropZone] nodeDrop', targetNode, source, e, data, point);
        var parent;
        switch (point) {
        case 'append':
            if (targetNode.allowChildren) {
                if (targetNode.lastChild != data.node) {
                    this.tree.moveNode(data.node, targetNode);
                }
                return true;
            }
            break;
        case 'above':
            parent = targetNode.parentNode;
            if (parent.allowChildren) {
                this.tree.moveNode(data.node, parent, targetNode);
                return true;
            }
            break;
        case 'below':
            parent = targetNode.parentNode;
            if (parent.allowChildren) {
                this.tree.moveNode(data.node, parent, targetNode.nextSibling);
                return true;
            }
            break;
        default:
            break;
        }
        return false;
    },


    onContainerOver: function (dd, e, data) {
        if (this.tree.disabled) {
            return this.dropNotAllowed;
        }
        //console.log('[Tree] containerOver', arguments);
        if (data.selections) {
            return this.onContainerOverFromGrid(dd, e, data);
        } else if (data.node) {
            return this.onContainerOverFromSelf(dd, e, data);
        } else {
            throw 'Unknown drag source';
        }
    },

    /**
    * @private
    * Handle containerover of a record (from grid)
    */
    onContainerOverFromGrid: function (dd, e, data) {
        if (!this.allowContainerDrop) {
            return this.dropNotAllowed;
        }
        if (data.selections.length != 1) {
            throw 'Can\'t handle multiple selection';
        }
        var record = data.selections[0];
        if (!record.id) {
            this.currentTarget = null;
            return this.dropNotAllowed;
        }
        // drop directly to root allowed?
        var root = this.tree.getRootNode();
        if (CMS.data.isRootModuleRecord(record) && root.hasChildNodes()) {
            this.currentTarget = null;
            return this.dropNotAllowed;
        }
        if (CMS.data.isRootModuleRecord(record)) {
            this.currentTarget = root;
            return this.dropAllowed;
        }
        // drop to some child node allowed?
        this.currentTarget = null;
        root.cascade(function (node) {
            if (node.allowChildren) {
                this.currentTarget = node;
                return false;
            }
        }, this);
        if (this.currentTarget) {
            return this.dropAllowed;
        } else {
            return this.dropNotAllowed;
        }
    },

    /**
    * @private
    * Handle containerover of a tree node (from tree)
    */
    onContainerOverFromSelf: function (dd, e, data) {
        var module = data.node.getModule();
        console.log('[Tree] TODO: determine valid drop nodes', dd, e, data);
        return this.onContainerOverFromGrid(dd, e, {selections: [module]});
    },

    onContainerDrop: function (source, e, data) {
        console.log('[Tree] containerDrop', arguments);
        if (this.tree.disabled) {
            return false;
        }
        if (!this.currentTarget) {
            return false;
        }
        if (data.selections) {
            this.tree.insertNodeFromRecord(data.selections[0], this.currentTarget);
            return true;
        } else {
            this.onNodeDropFromSelf(this.currentTarget, source, e, data, 'append');
        }
    }

});
