Ext.ns('CMS.form');

/**
 * On Off Field Set
 * @class CMS.form.OnOffFieldSet
 * @extends CMS.form.CheckboxFieldSet
 */
CMS.form.OnOffFieldSet = Ext.extend(CMS.form.CheckboxFieldSet, {

    cls: 'CMSonofffieldset',
    removeFieldLabel: false,
    isResponsive: false,

    initComponent: function () {
        // remove title
        this.title = null;
        CMS.form.OnOffFieldSet.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Special responsive handling for on/off fieldsets
     * @override
     */
    onCheckClick: function (e) {

        // responsive field, visible content (expaneded/true), and not in default resolution
        if (this.isResponsive && this.value && !this.ownerCt.isDefaultResolution()) {

            // value is inherited -> set active in current resolution
            if (this.ownerCt.isInherited()) {
                this.value = true;
                if (!this.swallowChangeEvent) {
                    this.fireEvent('change', this, true, false);
                }
            }
            // value is explicit set -> reset values
            else {
                // call in next tick to prevent events
                var fn = this.ownerCt.handleResponsiveIndicatorClick.createDelegate(this.ownerCt);
                setTimeout(fn, 0);
            }
            // do not change checkbox (on/off state)
            e.preventDefault();
        }
        // normal action
        else {
            CMS.form.OnOffFieldSet.superclass.onCheckClick.apply(this, arguments);
        }
    }

});

Ext.reg('CMSonofffieldset', CMS.form.OnOffFieldSet);
