Ext.ns('CMS.websiteSettings');

/**
 * A window which contains the website settings form
 *
 * @class CMS.websiteSettings.WebsiteSettingsWindow
 * @extends CMS.MainWindow
 */
CMS.websiteSettings.WebsiteSettingsWindow = Ext.extend(CMS.MainWindow, {
    /** @lends CMS.websiteSettings.WebsiteSettingsWindow.prototype */

    modal: true,

    maxWidth: 950,

    websiteId: '',

    closeAction: 'checkBeforeClose',

    settingsId: '',

    /**
     * Initialize component instance
     * @private
     */
    initComponent: function () {
        this.closeAction = 'checkBeforeClose';
        this.title = CMS.i18n(null, 'websiteSettingsWindow.title');
        this.items = [{
            id: 'websiteSettingsPanel',
            xtype: 'CMSwebsitesettingspanel',
            ref: 'websiteSettings',
            websiteId: this.websiteId,
            settingsId: this.settingsId,
            listeners: {
                // close button
                requestClose: this.close,
                scope: this,
            }
        }];

        CMS.websiteSettings.WebsiteSettingsWindow.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Used as Close action
     */
    checkBeforeClose: function () {
        this.websiteSettings.closeAction();
    }

});
