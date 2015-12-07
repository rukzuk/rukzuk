Ext.ns('CMS.home');

/**
* @class CMS.home.PageTreeNode
* @extends Ext.tree.TreeNode
* Site treenode class which will be used in {@link CMS.home.PageTreePanel}
*
* @constructor
* @param {CMS.data.TemplateRecord|CMS.home.PageTreeNode} record A TemplateRecord can be passed to the constructor
*/
CMS.home.PageTreeNode = function (record) {
    var attributes;
    if (typeof record.loader == 'object') { // check if argument is already a node
        //console.log('[CMS.home.PageTreeNode] reusing treenode');
        attributes = record;
    } else {
        console.log('[CMS.home.PageTreeNode] generating treenode');
        attributes = this.nodeFromRecord(record);
        console.log('[CMS.home.PageTreeNode] new treeNode: ', attributes);
    }
    attributes.text = attributes.name;
    CMS.home.PageTreeNode.superclass.constructor.call(this, attributes);
};

Ext.extend(CMS.home.PageTreeNode, Ext.tree.TreeNode, {

    /**
    * @private
    * Converts a CMS.data.PageRecord to a treeNode config object
    */
    nodeFromRecord: function (record) {
        var attributes = {
            id: CMS.app.UIDManager.getInstance().getId('page'),
            expanded: true,
            templateId: record.id,
            leaf: false,
            name: record.get('name'),
            icon: record.get('icon'),
            nodeType: 'CMSpagetreenode'
        };
        return attributes;
    }
});

Ext.reg('CMSpagetreenode', CMS.home.PageTreeNode);

Ext.tree.TreePanel.nodeTypes.CMSpagetreenode = CMS.home.PageTreeNode;
