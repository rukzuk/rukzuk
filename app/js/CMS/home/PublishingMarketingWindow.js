Ext.ns('CMS.home');

/**
 * A window which show marketing information
 *
 * @class CMS.home.PublishingMarketingWindow
 * @extends CMS.IframeWindow
 */
CMS.home.PublishingMarketingWindow = Ext.extend(CMS.IframeWindow, {
    /** @lends CMS.home.PublishingMarketingWindow.prototype */

    minWidth: 600,
    minHeight: 400,
    maxWidth: 1100,
    maxHeight: 733,
    modal: true,
    resizable: false,

    /**
     * Initialize component instance
     * @private
     */
    initComponent: function () {
        this.title = CMS.i18n(null, 'publishingMarketingWindow.title');
        this.src = CMS.config.quotaWebhostingMarketing + '?hl=' + CMS.app.lang;
        CMS.home.PublishingMarketingWindow.superclass.initComponent.apply(this, arguments);
    }
});
