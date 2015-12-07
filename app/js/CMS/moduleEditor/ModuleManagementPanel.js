Ext.ns('CMS.moduleEditor');

/**
 * Panel which contains the module development info
 *
 * @class       CMS.moduleEditor.ModuleManagementPanel
 * @extends     CMS.moduleEditor.ManagementPanel
 */
CMS.moduleEditor.ModuleManagementPanel = Ext.extend(CMS.home.ManagementPanel, {

    initComponent: function () {

        var bbar = [{
            text: CMS.i18n('Modulliste neu laden', 'modulemanagementpanel.btn.reload'),
            ref: '../../reloadButton',
            handler: this.reloadHandler,
            scope: this
        }];

        Ext.apply(this, {
            items: [{
                xtype: 'panel',
                bbar: bbar,
                items: [{
                    xtype: 'CMSmoduledeveloperinfo',
                    cls: 'CMSmoduledeveloperinfomngpanel',
                    ref: '../sidebarDevInfo'
                }]
            }]
        });

        CMS.moduleEditor.ModuleManagementPanel.superclass.initComponent.call(this);
    },

    /**
    * Open the specified site
    * @param {CMS.data.WebsiteRecord} record The site to be opened
    */
    setSite: function (record) {
        CMS.moduleEditor.ModuleManagementPanel.superclass.setSite.call(this, record);
        this.sidebarDevInfo.loadData({websiteId: this.websiteId, id: ''});
    },


    /**
     * Handler for reload button
     * @private
     */
    reloadHandler: function () {
        CMS.data.StoreManager.get('module', this.websiteId).reload({
            callback: function () {
                CMS.Message.toast(CMS.i18n('Modulliste wurde vom Server neu geladen', 'modulemanagementpanel.toast.afterReload'));
            },
            scope: this
        });
        // TODO: reload website Settings and pageType Settings
    },

});

Ext.reg('CMSmodulemanagementpanel', CMS.moduleEditor.ModuleManagementPanel);
