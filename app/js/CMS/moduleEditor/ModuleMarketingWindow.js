Ext.ns('CMS.moduleEditor');

/**
 * A window which show marketing information
 *
 * @class CMS.moduleEditor.ModuleMarketingWindow
 * @extends CMS.IframeWindow
 */
CMS.moduleEditor.ModuleMarketingWindow = Ext.extend(CMS.IframeWindow, {
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
		this.title = CMS.i18n('Moduleentwicklung', 'moduleManagementWindow.title');
		this.src = CMS.config.quotaModuleMarketing + '?hl=' + CMS.app.lang;
		CMS.moduleEditor.ModuleMarketingWindow.superclass.initComponent.apply(this, arguments);
	}
});
