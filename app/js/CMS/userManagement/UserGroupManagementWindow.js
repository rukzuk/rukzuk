Ext.ns('CMS');

/**
 * @class       CMS.userManagement.UserGroupManagementWindow
 * @extends     CMS.MainWindow
 *
 * The wrapper window for the user group management
 */
CMS.userManagement.UserGroupManagementWindow = Ext.extend(CMS.MainWindow, {

    /**
     * @cfg {Object} website
     * The website for which the user groups should be manipulated
     */
    siteId: undefined,

    initComponent: function () {
        this.title = CMS.i18n('Gruppen und Rechte');
        this.items = [{
            ref: 'content',
            xtype: 'CMSgroupmanagementpanel',
            cls: 'CMSgroupmanagementpanel',
            websiteId: this.website.id
        }];

        CMS.userManagement.UserGroupManagementWindow.superclass.initComponent.call(this);

    }
});

