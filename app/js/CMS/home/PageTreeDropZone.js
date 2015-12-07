Ext.ns('CMS.home');

/**
 * A dropzone particularly for use with {@link CMS.home.PageTreePanel}
 * It can handle drops from a grid or from a tree
 *
 * @class CMS.home.PageTreeDropZone
 * @extends Ext.tree.TreeDropZone
 */
CMS.home.PageTreeDropZone = function (tree, config) {
    var defaults = {
        ddGroup: CMS.config.ddGroups.pages
    };
    CMS.home.PageTreeDropZone.superclass.constructor.call(this, tree, Ext.applyIf(defaults, config));
};

Ext.extend(CMS.home.PageTreeDropZone, Ext.tree.TreeDropZone, {
    /** @lends CMS.home.PageTreeDropZone.prototype */

    onNodeDrop: function (nodeData, source, e, data) {
        if (this.tree.disabled) {
            return false;
        }
        var point = this.getDropPoint(e, nodeData, source);
        var node = nodeData.node;
        console.log('[PageTreeDropZone] nodeDrop', node, source, e, data, point);

        if (!this.isValidDropPoint(nodeData, point, source, e, data)) {
            node.ui.endDrop();
            return false;
        }
        if (data.node) {
            this.requestMoveNode({
                id: data.node.id,
                parentId: this.insertionPoint.id,
                insertBeforeId: this.insertionNextSibling && this.insertionNextSibling.id
            });
            return true;
        } else {
            throw 'Unknown drag source';
        }
    },

    isValidDropPoint: function (nodeData, point, source, e, data) {
        var insertionPoint,
        insertionNextSibling,
        node = nodeData.node,
        userInfo = CMS.app.userInfo,
        previousParent = data && data.node && data.node.parentNode;

        // If user isn't allowed to remove the page from it's original position,
        // it doesn't matter where he wants to drop it.
        var canRemove = previousParent && (userInfo.canCreateChildPages(previousParent) || (previousParent.id == 'root' && userInfo.canCreateRootPages(this.tree.websiteId)));
        if (!canRemove) {
            return false;
        }

        // Determine insertion point
        switch (point) {
        case 'append':
            insertionPoint = node;
            insertionNextSibling = null;
            break;
        case 'above':
            insertionPoint = node.parentNode;
            insertionNextSibling = node;
            break;
        case 'below':
            insertionPoint = node.parentNode;
            insertionNextSibling = node.nextSibling || null;
            break;
        default:
            return false;
        }

        this.insertionPoint = insertionPoint;
        this.insertionNextSibling = insertionNextSibling;

        return CMS.home.PageTreeDropZone.superclass.isValidDropPoint.apply(this, arguments);
    },

    onContainerOver: function (dd, e, data) {
        if (CMS.app.userInfo.canCreateRootPages(this.tree.websiteId)) {
            return this.dropAllowed;
        } else {
            return this.dropNotAllowed;
        }
    },

    onContainerDrop: function (dd, e, data) {
        if (!CMS.app.userInfo.canCreateRootPages(this.tree.websiteId)) {
            return false;
        }
        this.requestMoveNode({
            id: data.node.id,
            parentId: 'root'
        });
        return true;
    },

    /**
     * Sends a request to the server for moving an existing node, and reloads the tree afterwards
     * @private
     *
     * @param {Object} data The request data that is sent
     */
    requestMoveNode: function (data) {
        if (data.insertBeforeId == data.id) {
            // nothing to do
            return;
        }
        CMS.app.trafficManager.sendRequest({
            action: 'movePage',
            data: Ext.apply({
                websiteId: this.tree.websiteId
            }, data),
            success: function () {
                CMS.Message.toast(CMS.i18n('Seite erfolgreich verschoben'));
                this.broadcastTreeUpdated();
            },
            failureTitle: CMS.i18n('Fehler beim Verschieben der Website'),
            failure: this.broadcastTreeUpdated,
            scope: this
        });
    },

    /**
     * Called if the tree has been updated and a refresh is required (e.g. after
     * moving a tree node
     * @private
     */
    broadcastTreeUpdated: function () {
        this.tree.broadcastTreeUpdated();
    },

    /**
     * Make event firing possible
     * @private
     */
    fireEvent: function () {
        this.tree.fireEvent.apply(this.tree, arguments);
    }
});
