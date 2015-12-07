Ext.ns('CMS.home');

/**
 * A window which show marketing information
 *
 * @class CMS.home.ExportQuotaMarketingWindow
 * @extends CMS.IframeWindow
 */
CMS.home.ExportQuotaMarketingWindow = Ext.extend(CMS.IframeWindow, {
	/** @lends home.ExportQuotaMarketingWindow.prototype */

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
		this.title = CMS.i18n(null, 'exportQuotaWindows.windowTitle');
		this.src = CMS.config.quotaExportMarketing + '?hl=' + CMS.app.lang;
		CMS.home.ExportQuotaMarketingWindow.superclass.initComponent.apply(this, arguments);
	}
});
