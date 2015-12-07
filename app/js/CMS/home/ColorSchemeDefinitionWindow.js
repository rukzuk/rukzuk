Ext.ns('CMS');

/**
 * @class       CMS.home.ColorSchemeDefinitionWindow
 * @extends     CMS.MainWindow
 *
 * The wrapper window for the CMS.home.ColorSchemeDefinitionPanel
 *
 */
CMS.home.ColorSchemeDefinitionWindow = Ext.extend(CMS.MainWindow, {

    width: 910,
    height: 600,
    resizable: false,

    /**
     * @cfg {Object} website
     * The website for which the colorscheme should be adapted
     */
    website: undefined,

    initComponent: function () {
        this.title = CMS.i18n('Farbschema');
        this.items = [{
            ref: 'content',
            deferredRender: false,
            xtype: 'CMScolorschemedefinitionpanel',
            websiteId: this.website.id
        }];

        CMS.home.ColorSchemeDefinitionWindow.superclass.initComponent.call(this);
    }
});

