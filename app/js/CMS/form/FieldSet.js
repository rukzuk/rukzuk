Ext.ns('CMS.form');

/**
 * Simple Field Set (wrapped for special styling)
 * @class CMS.form.FieldSet
 * @extends Ext.form.FieldSet
 */
CMS.form.FieldSet = Ext.extend(Ext.form.FieldSet, {

    cls: 'CMSfieldset',

    initComponent: function () {
        // convert title to fieldLabel
        //this.fieldLabel = this.title;
        //this.title = null;
        CMS.form.FieldSet.superclass.initComponent.apply(this, arguments);
    }

});

Ext.reg('CMSfieldset', CMS.form.FieldSet);
