Ext.ns('CMS');

/**
 * @class       CMS.userManagement.UserManagementWindow
 * @extends     CMS.MainWindow
 *
 * The wrapper window for user management
 */
CMS.userManagement.UserManagementWindow = Ext.extend(CMS.MainWindow, {

    width: 900,

    /**
     * @cfg websiteId
     * @type String
     * The currently opened website's id
     */
    websiteId: null,

    initComponent: function () {
        this.title = CMS.i18n('Benutzer', 'usermanagement.windowTitle');
        this.items = [{
            ref: 'content',
            xtype: 'CMSusermanagementpanel',
            cls: 'CMSusermanagementpanel',
            websiteId: this.websiteId
        }];

        CMS.userManagement.UserManagementWindow.superclass.initComponent.call(this);
    }
});

