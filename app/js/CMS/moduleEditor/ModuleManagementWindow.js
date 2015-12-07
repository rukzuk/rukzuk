Ext.ns('CMS.moduleEditor');

/**
 * A window which contains the module list and allows to add/edit/delete modules
 *
 * @class CMS.moduleEditor.ModuleManagementWindow
 * @extends CMS.MainWindow
 */
CMS.moduleEditor.ModuleManagementWindow = Ext.extend(CMS.MainWindow, {
    /** @lends CMS.moduleEditor.ModuleManagementWindow.prototype */

    modal: true,

    maxWidth: 700,
    maxHeight: 500,

    /**
     * An Ext.Record representing the current website
     * @property website
     * @type {Ext.data.Record}
     */
    website: undefined,

    /**
     * Initialize component instance
     * @private
     */
    initComponent: function () {
        this.title = CMS.i18n('Moduleentwicklung', 'moduleManagementWindow.title');
        this.items = [{
            id: 'modulesPanel',
            xtype: 'CMSmodulemanagementpanel',
        }];

        CMS.moduleEditor.ModuleManagementWindow.superclass.initComponent.apply(this, arguments);

        this.get('modulesPanel').setSite(this.website);
    }
});
