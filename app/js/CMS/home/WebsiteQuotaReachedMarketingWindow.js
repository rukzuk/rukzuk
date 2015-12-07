Ext.ns('CMS.home');

/**
 * A window which show marketing information
 *
 * @class CMS.home.WebsiteQuotaReachedMarketingWindow
 * @extends CMS.IframeWindow
 */
CMS.home.WebsiteQuotaReachedMarketingWindow = Ext.extend(CMS.IframeWindow, {
	/** @lends CMS.moduleEditor.ModuleMarketingWindow.prototype */

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
		this.title = CMS.i18n(null, 'newWebsiteWindow.windowTitle');
		this.src = CMS.config.quotaWebsiteMarketing + '?hl=' + CMS.app.lang;
		CMS.home.WebsiteQuotaReachedMarketingWindow.superclass.initComponent.apply(this, arguments);
	}
});
