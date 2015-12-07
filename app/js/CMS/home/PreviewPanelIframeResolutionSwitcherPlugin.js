Ext.ns('CMS.home');

/**
* @class CMS.home.PreviewPanelIframeResolutionSwitcherPlugin
* @extends CMS.IframeResolutionSwitcherPlugin
*/
CMS.home.PreviewPanelIframeResolutionSwitcherPlugin = Ext.extend(CMS.IframeResolutionSwitcherPlugin, {
    init: function (cmp) {
        this.editMode = false;
        this.forceInitialSetSrc = true;
        this.invisibleEmptySrc = true;

        CMS.home.PreviewPanelIframeResolutionSwitcherPlugin.superclass.init.apply(this, arguments);

        var orgSetContent = cmp.setContent;
        cmp.setContent = function (params) {
            orgSetContent.call(cmp, params);

            if (params && params.websiteId) {
                cmp.resolutionSwitcherToolbar.setSite(params.websiteId);
                cmp.rulerPanel.setSite(params.websiteId);
                this.websiteId = params.websiteId;
            } else {
                cmp.resolutionSwitcherToolbar.setSite();
                cmp.rulerPanel.setSite();
            }
        };
    }

});
Ext.preg('CMSpreviewpaneliframeresolutionswitcher', CMS.home.PreviewPanelIframeResolutionSwitcherPlugin);
