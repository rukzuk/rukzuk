Ext.ns('CMS.form');

/**
* @class CMS.form.AlbumChooser
* @extends Ext.form.Chooser
* A form element for selecting a MediaDB album of the current website
*/
CMS.form.AlbumChooser = Ext.extend(CMS.form.Chooser, {
    initComponent: function () {
        try {
            this.originalStore = CMS.data.StoreManager.get('album', this.websiteId);
        } catch(e) {
            console.warn('[AlbumChooser] could not get album data, use dummy data', e);
            this.originalStore = new Ext.data.JsonStore({
                id: 0,
                fields: ['id', 'name', 'value'],
                data: []
            });
        }

        CMS.form.AlbumChooser.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('CMSalbumchooser', CMS.form.AlbumChooser);
