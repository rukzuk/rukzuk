Ext.ns('CMS.form');

/**
 * A form element for selecting a unit on the current template/page
 *
 * @class CMS.form.UnitChooser
 * @extends Ext.form.Chooser
 */
CMS.form.UnitChooser = Ext.extend(CMS.form.Chooser, {
    /** @lends CMS.form.UnitChooser.prototype */

    initComponent: function () {
        if (this.idSuffix) {
            var storeOptions = {
                disableLoad: true,
                idSuffix: this.idSuffix
            };
            this.originalStore = CMS.data.StoreManager.get('unit', this.websiteId, storeOptions);
        }

        CMS.form.UnitChooser.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Will get called after initialization and every time the originalStore fires the datachanged event
     * @private
     */
    datachangedHandler: function () {
        var records = this.originalStore.query('moduleId', this.moduleId).getRange();

        var data = [];
        Ext.each(records, function (record) {
            var name = Ext.isFunction(record.getUIName) ? record.getUIName() : CMS.translateInput(record.get('name'));
            data.push([record.get('templateUnitId') || record.get('id'), name]);
        });

        data.sort(function (a, b) {
            if (a[1].toLowerCase() < b[1].toLowerCase()) {
                return -1;
            } else {
                return 1;
            }
        });

        this.syncComboBoxStore(data);
    }
});

Ext.reg('CMSunitchooser', CMS.form.UnitChooser);
