Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.TemplateUnitTreeNodeUI
* @extends CMS.structureEditor.UnitTreeNodeUI
* TreeNode UI for the {@link CMS.structureEditor.TemplateUnitTreePanel}
*/
CMS.structureEditor.TemplateUnitTreeNodeUI = Ext.extend(CMS.structureEditor.UnitTreeNodeUI, {

    renderElements: function (n, a, targetNode, bulkRender) {

        // always show context menu
        this.hasContextMenuItems = true;

        //add insert icon if extensions are allowed
        var allowedChildModuleType = n.getModule().data.allowedChildModuleType;
        if (allowedChildModuleType === CMS.config.moduleTypes.extension || allowedChildModuleType === '*') {
            this.allowInsertExtensions = true;
        }

        CMS.structureEditor.TemplateUnitTreeNodeUI.superclass.renderElements.apply(this, arguments);
    },

    /**
     * Changes css class if ghostContainer attribute changes
     * @param {Boolean} state
     */
    setGhostContainer: function (state) {
        if (state === true) {
            this.addClass(this.ghostContainerCls);
        } else {
            this.removeClass(this.ghostContainerCls);
        }
    },

    /**
     * Changes css class if visibleFormGroups attribute changes
     * @param {Boolean} state
     */
    setVisibleFormGroups: function (visibleFormGroups) {
        if (visibleFormGroups && visibleFormGroups.length) {
            this.addClass(this.editableCls);
        } else {
            this.removeClass(this.editableCls);
        }
    }
});
