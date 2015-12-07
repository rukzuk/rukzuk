Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.AlbumSelector
* @extends Ext.ux.panel.DataCarousel
*/
CMS.mediaDB.AlbumSelector = Ext.extend(Ext.ux.panel.DataCarousel, {
    //emptyText: '<div style="padding:10px;">No images match the specified filter</div>',

    imgWidth: 75,
    imgHeight: 75,
    frameSize: 15,
    borderWidth: 1,
    clickArea: 43,
    imgPerPage: 5,
    scrollStep: 3,
    scrollSpeed: 5,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    initComponent: function () {
        this.store = CMS.data.StoreManager.get('album', this.websiteId || -1, { disableLoad: true });
        //auto-select the first album
        this.store.on('load', function () { this.select(0, true); }, this, { single: true });

        this.on('selectionchange', function (dv, selectedNodes) {
            var album = !Ext.isEmpty(selectedNodes) ? dv.getRecord(selectedNodes[0]) : null;
            this.fireEvent('CMSalbumselect', album);
        }, this);

        CMS.mediaDB.AlbumSelector.superclass.initComponent.apply(this, arguments);

        this.on('render', this.initializeDropZone, this);
        this.on('afterlayout', this.store.reload, this.store, { single: true });
    },

    /**
    * Selects an album by its id
    * @param {String} albumId The album id
    */
    selectByAlbumId: function (albumId) {
        var record = this.store.getById(albumId);
        this.select(this.view.indexOf(record));
    },

    /**
    * @private
    * Initialize the DropZone, so mediaItems can be dropped into albums
    */
    initializeDropZone: function () {
        var self = this;

        this.dropZone = new Ext.dd.DropZone(self.ownerCt.el, {
            ddGroup: 'mediaDD',

            getTargetFromEvent: function (e) {
                return e.getTarget('.thumb-wrap');
            },

            onNodeOver: function (target, dd, e, data) {
                var selectedAlbum = self.view.getSelectedRecords()[0];
                var newAlbum = self.view.getRecord(target);

                return selectedAlbum != newAlbum ? Ext.dd.DropZone.prototype.dropAllowed : Ext.dd.DropZone.prototype.dropNotAllowed;
            },

            onNodeDrop: function (target, dd, e, data) {
                var selectedAlbum = self.view.getSelectedRecords()[0];
                var newAlbum = self.view.getRecord(target);

                //abort if items are already in the album
                if (selectedAlbum == newAlbum) {
                    return false;
                }

                var ids = [];
                Ext.each(data.selections, function (file) {
                    ids.push(file.get('id'));
                });

                CMS.app.trafficManager.sendRequest({
                    action: 'moveMedia',
                    data: {
                        ids: ids,
                        websiteId: newAlbum.get('websiteId'),
                        albumId: newAlbum.id
                    },
                    success: function () {
                        CMS.Message.toast(ids.length + ' ' + CMS.i18n('Datei(en) erfolgreich verschoben'));
                        self.fireEvent('CMSalbumselect', selectedAlbum); //to refresh the mediaSelector
                    },
                    failureTitle: CMS.i18n('Fehler beim Verschieben der Dateien')
                });

                return true;
            },

            destroy: function () {
                self = null;
                Ext.dd.DropZone.prototype.destroy.apply(this, arguments);
            }
        });
    },

    destroy: function () {
        if (this.dropZone) {
            this.dropZone.destroy();
        }
        this.store.destroy();
        CMS.mediaDB.AlbumSelector.superclass.destroy.apply(this, arguments);
        this.store = null;
        this.dropZone = null;
    }

});

Ext.reg('CMSalbumselector', CMS.mediaDB.AlbumSelector);
