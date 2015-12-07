Ext.ns('CMS.form');

/**
 * @class CMS.form.InternalLinkChooser
 * @extends Ext.form.Chooser
 * A form element for selecting a link to an internal page of the current website
 */
CMS.form.InternalLinkChooser = Ext.extend(CMS.form.Chooser, {
    initComponent: function () {
        try {
            this.originalStore = CMS.data.WebsiteStore.getInstance();
        } catch (e) {
            console.warn('[InternalLinkChooser] could not get website data', e);
            this.originalStore = new Ext.data.JsonStore({
                id: 0,
                fields: ['id', 'name'],
                data: []
            });
        }

        CMS.form.InternalLinkChooser.superclass.initComponent.apply(this, arguments);
    },

    /**
     * @private
     * Will get called after initialization and every time the originalStore fires the datachanged event
     */
    datachangedHandler: function () {
        var site = this.originalStore.getById(this.websiteId);
        var records = [];
        if (site) {
            records = site.get('navigation');
        }

        var data = [];

        function iteratePages(items, indenter) {
            indenter = indenter || '';

            Ext.each(items, function (item) {
                data.push([item.id, indenter + item.name]);
                if (item.children && item.children.length) {
                    iteratePages(item.children, indenter + '\u2003\u2003'); // \u2003 is &emsp; in Unicode
                }
            });
        }

        iteratePages(records);

        this.syncComboBoxStore(data);
    }
});

Ext.reg('CMSinternallinkchooser', CMS.form.InternalLinkChooser);

//Old xtype
Ext.reg('CMSlinkchooser', CMS.form.InternalLinkChooser);
