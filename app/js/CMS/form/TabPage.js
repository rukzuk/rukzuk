Ext.ns('CMS.form');

/**
 * Wrapper for multiple tab items - only useful in a {@link CMS.form.TabbedFieldSet}
 * @class CMS.form.TabPage
 * @extends Ext.Container
 */
CMS.form.TabPage = Ext.extend(Ext.Container, {

    cls: 'x-tab CMStabpage',
    layout : 'form',
    autoHeight: true,
    title: 'TabPage',

    initComponent: function () {

        CMS.form.TabPage.superclass.initComponent.apply(this, arguments);

        // remove field Label
        this.fieldLabel = null;

        // promote title to wrapper component
        if (this.ownerCt) {
            this.ownerCt.title = this.title;
        }

        // disallow TabPages in TabPages
        this.on('beforeadd', function (container, cmp, index) {
            // unwrap if its wrapped
            var formField = cmp.getFormField ? cmp.getFormField() : cmp;
            if (formField.isXType('CMStabpage')) {
                return false;
            }
        }, this);

        // set tabValue in DOM to be displayed by CSS (for FormTabEditor)
        this.on('afterrender', function () {
            this.el.set({
                'data-tabValue': this.tabValue,
                'data-tabValuePrefix': CMS.i18n('Aktiver Wert:', 'tabpage.tabValueEditModeFormLabel')
            });
        }, this);
    },

    /**
     * Returns the value configured by module author
     * @returns {string}
     */
    getValue: function () {
        return this.tabValue;
    }

});

CMS.form.TabPage.prototype.getRawValue = CMS.form.TabPage.prototype.getValue;

Ext.reg('CMStabpage', CMS.form.TabPage);
