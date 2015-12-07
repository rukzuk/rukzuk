Ext.ns('CMS.form');

/**
* @class CMS.form.WebsiteChooser
* @extends Ext.form.Chooser
* A form element for selecting a module of the current website
*/
CMS.form.WebsiteChooser = Ext.extend(CMS.form.Chooser, {
    initComponent: function () {
        this.originalStore = CMS.data.WebsiteStore.getInstance();

        CMS.form.ModuleChooser.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('CMSwebsitechooser', CMS.form.WebsiteChooser);
