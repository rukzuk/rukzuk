Ext.ns('CMS');

/**
* @class CMS.mediaDB.MediaSelector
* @extends CMS.mediaDB.Selector
* A component that presents a list of media
*/
CMS.mediaDB.MediaSelector = Ext.extend(CMS.mediaDB.Selector, {
    type: 'media',
    cls: 'mediaSelector',
    enableDragDrop: true,
    ddGroup: 'mediaDD',
    enableHdMenu: false,
    autoExpandColumn: 'name',
    headerSort: true,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    initComponent: function () {
        this.albumStore = CMS.data.StoreManager.get('album', this.websiteId || -1, { disableLoad: true });
        this.columns = [{
            id: 'id',
            dataIndex: 'id',
            hidden: true,
            hideable: false
        }, {
            id: 'icon',
            dataIndex: 'icon',
            header: '&#160;',
            width: 100 + 2, // 2px magic number to compensate for inter-cell spacing
            renderer: SB.grid.ImageRenderer,
            resizable: false,
            hideable: false
        }, {
            id: 'name',
            dataIndex: 'name',
            header: CMS.i18n('Name'),
            sortable: true
        }, {
            id: 'dateUploaded',
            dataIndex: 'dateUploaded',
            header: CMS.i18n('Datum'),
            sortable: true,
            width: 130,
            renderer: function (value) {
                return Ext.util.Format.date(SB.date.dateFromUnixTimeStamp(value), CMS.config.date.format + ' ' + CMS.config.date.timeFormat);
            }
        }, {
            id: 'actionReplace',
            dataIndex: '',
            header: '&#160;',
            renderer: function (value, meta, record, rowIndex, colIndex, store) {
                meta.attr = 'ext:qtip="' + CMS.i18n('Datei ersetzen') + '"';
                return '<img class="action replace" src="' + Ext.BLANK_IMAGE_URL + '" width="16">';
            },
            resizable: false,
            width: 26
        }, {
            id: 'actionDownload',
            dataIndex: '',
            header: '&#160;',
            renderer: function (value, meta, record, rowIndex, colIndex, store) {
                meta.attr = 'ext:qtip="' + CMS.i18n('Datei herunterladen') + '"';
                return '<img class="action download" src="' + Ext.BLANK_IMAGE_URL + '" width="16">';
            },
            resizable: false,
            width: 26
        }, {
            id: 'actionEdit',
            dataIndex: '',
            header: '&#160;',
            renderer: function (value, meta, record, rowIndex, colIndex, store) {
                meta.attr = 'ext:qtip="' + CMS.i18n('Datei bearbeiten') + '"';
                return '<img class="action edit" src="' + Ext.BLANK_IMAGE_URL + '" width="16">';
            },
            resizable: false,
            width: 26
        }, {
            id: 'actionDelete',
            dataIndex: '',
            header: '&#160;',
            renderer: function (value, meta, record, rowIndex, colIndex, store) {
                meta.attr = 'ext:qtip="' + CMS.i18n('Datei löschen') + '"';
                return '<img class="action delete" src="' + Ext.BLANK_IMAGE_URL + '" width="16">';
            },
            resizable: false,
            width: 41
        }];

        this.listeners = {
            cellclick: function (grid, rowIndex, colIndex, evt) {
                var downloadIndex = this.getColumnModel().getIndexById('actionDownload');
                var editIndex = this.getColumnModel().getIndexById('actionEdit');
                var deleteIndex = this.getColumnModel().getIndexById('actionDelete');
                var replaceIndex = this.getColumnModel().getIndexById('actionReplace');


                switch (colIndex) {
                case downloadIndex:
                    this.ownerCt.downloadButtonHandler();
                    break;
                case editIndex:
                    this.ownerCt.editButtonHandler();
                    break;
                case deleteIndex:
                    this.ownerCt.deleteButtonHandler();
                    break;
                case replaceIndex:
                    this.ownerCt.replaceButtonHandler();
                    break;
                default:
                    break;
                }
            }
        };

        this.store = CMS.data.StoreManager.get('media', this.websiteId || -1, { disableLoad: true });
        this.emptyText = CMS.i18n('(Keine Medien gefunden)');
        console.log('[MediaSelector] store is', this.store);

        this.sm = new Ext.grid.RowSelectionModel({ singleSelect: false });

        this.bbar = new Ext.PagingToolbar({
            cls: 'paging-toolbar',
            store: this.store,
            pageSize: CMS.config.params.getAllMedia.limit,
            displayInfo: true,
            displayMsg: CMS.i18n('Eintrag {0} - {1} von {2}')
        });

        this.setupEvents(false);
        //hide the refresh button in PagingToolbar
        this.on('afterrender', function (component) {
            component.getBottomToolbar().refresh.hideParent = true;
            component.getBottomToolbar().refresh.hide();
        });
        this.on('afterrender', this.initializeDragZone, this);

        CMS.mediaDB.MediaSelector.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * Needed to show a nice drag ghost
    */
    initializeDragZone: function () {
        var zone = this.getView().dragZone;
        //override onInitDrag method to show a nice drag ghost
        zone.onInitDrag = function (x, y) {
            var imagesrcs = [];
            Ext.each(this.dragData.selections, function (rec) {
                imagesrcs.push(rec.get('icon'));
            });

            zone.multiImgstack = new SB.image.MultiImageStack({
                imagesrcs: imagesrcs,
                imageSize: CMS.config.params.getAllMedia.maxIconWidth,
                spread: 37,
                style: {
                    padding: '10px 10px'
                },
                renderTo: this.proxy.ghost.dom
            });

            this.onStartDrag(x, y);
            return true;
        };

        zone.afterDragDrop = zone.afterInvalidDrop = function (target, e, id) {
            zone.multiImgstack.destroy();
        };
    },

    /**
    * Filter the underlying store by album.
    * @param {String} albumId The album id
    */
    filterByAlbum: function (albumId) {
        this.fireEvent('unmark', null, this, this.sm);

        delete this.store.baseParams.search;
        this.store.baseParams.albumId = albumId;
        this.store.reload();
    },

    /**
    * Filter the underlying store by search term.
    * @param {String} term The search term
    * @param {Boolean} forceReload <tt>true</tt> to force reloading the store
    * even if the filter is identical to the previously set filter
    */
    filterBySearchTerm: function (term, forceReload) {
        this.fireEvent('unmark', null, this, this.sm);

        if (term === '' || typeof term == 'undefined' || term === null) {
            delete this.store.baseParams.search;
        } else {
            delete this.store.baseParams.albumId;
            this.store.baseParams.search = term;
        }
        if (forceReload || this.lastTerm !== term) {
            this.store.reload();
        }
        this.lastTerm = term;
    },

    destroy: function () {
        this.albumStore = null;

        CMS.mediaDB.MediaSelector.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSmediaselector', CMS.mediaDB.MediaSelector);
