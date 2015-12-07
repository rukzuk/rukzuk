Ext.ns('CMS.form');

/**
* @class CMS.form.ModuleChooser
* @extends Ext.form.Chooser
* A form element for selecting a module of the current website
*/
CMS.form.ModuleChooser = Ext.extend(CMS.form.Chooser, {
    initComponent: function () {
        this.originalStore = CMS.data.StoreManager.get('module', this.websiteId);

        CMS.form.ModuleChooser.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * Will get called after initialization and every time the originalStore fires the datachanged event.
    * Overwritten for i18n
    */
    datachangedHandler: function () {
        var records = this.originalStore.getRange();

        var data = [];
        Ext.each(records, function (record) {
            data.push([record.get('id'), CMS.translateInput(record.get('name'))]);
        });

        this.syncComboBoxStore(data);
    }
});

Ext.reg('CMSmodulechooser', CMS.form.ModuleChooser);
