Ext.ns('CMS.mediaDB');

/**
* @class CMS.home.MediaDBManagementPanel
* @extends CMS.home.ManagementPanel
*/
CMS.home.MediaDBManagementPanel = Ext.extend(CMS.home.ManagementPanel, {
    layout: 'fit',

    /**
    * Open the MediaDB for a specified website
    * @param {CMS.data.WebsiteRecord} record The site to be opened
    */
    setSite: function (record) {
        this.removeAll();

        CMS.home.MediaDBManagementPanel.superclass.setSite.call(this, record);

        if (record && record.id) {
            this.add({
                xtype: 'CMSmediadbpanel',
                websiteId: this.websiteId,
                border: false
            });
            this.doLayout();
        }
    }
});

Ext.reg('CMSmediadbmanagementpanel', CMS.home.MediaDBManagementPanel);
