Ext.ns('CMS.form');

/**
 * A list of elements which can be selected (multi or single select)
 */

CMS.form.SelectList = Ext.extend(Ext.ux.form.MultiSelect, {

    multiSelect: true,
    width: '100%',

    initComponent: function () {
        // map store to options (name convention - see ux-comboselect)
        this.store = this.options;
        CMS.form.SelectList.superclass.initComponent.apply(this, arguments);
    }

});

Ext.reg('CMSselectlist', CMS.form.SelectList);
