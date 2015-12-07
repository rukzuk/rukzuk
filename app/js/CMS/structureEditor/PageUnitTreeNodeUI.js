Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.PageUnitTreeNodeUI
* @extends CMS.structureEditor.UnitTreeNodeUI
* TreeNode UI for the {@link CMS.structureEditor.PageUnitTreePanel}
*/
CMS.structureEditor.PageUnitTreeNodeUI = Ext.extend(CMS.structureEditor.UnitTreeNodeUI, {

    renderElements: function (n, a, targetNode, bulkRender) {
        var isDeletable = n.getUnit().isDeletableInMode('page');

        // show or hide delete button
        this.hideAction = !isDeletable;

        //add editable class if unit is deletable
        var classes = n.attributes.cls ? [n.attributes.cls] : [];
        if (isDeletable) {
            classes.push(this.editableCls);
        }
        n.attributes.cls = classes.join(' ');

        // show or hide context menu button
        this.hasContextMenuItems = n.hasContextMenuItems();

        CMS.structureEditor.PageUnitTreeNodeUI.superclass.renderElements.apply(this, arguments);
    }
});
