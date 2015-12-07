Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.MediaDBPanel
* @extends Ext.Panel
* @requires CMS.mediaDB.MediaPropertyWindow
* @requires CMS.mediaDB.UploadWindow
*/
CMS.mediaDB.MediaDBPanel = Ext.extend(Ext.Panel, {
    layout: 'border',
    cls: 'CMSmediadb',
    minButtonWidth: 0,
    border: false,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {String} filterType
    * Filter media by type
    */
    filterType: '',

    /**
     * The types allowed as filters, and their respective window titles as well as the description shown in combo box
     * @property possibleTypes
     * @type Object
     */
    possibleTypes: undefined,

    initComponent: function () {
        this.possibleTypes = {
            '': [CMS.i18n('Datei'), CMS.i18n('Alle Dateien')],
            'image': [CMS.i18n('Bild'), CMS.i18n('Bilder')]
        };

        var comboItems = [];
        Ext.iterate(this.possibleTypes, function (type, descr) {
            comboItems.push([type, descr[1]]);
        });
        this.items = [{
            region: 'north',
            border: false,
            layout: 'fit',
            height: 147,
            items: {
                xtype: 'CMSalbumselector',
                websiteId: this.websiteId,
                ref: '../albumSelector',
                height: 147
            }
        }, {
            region: 'center',
            border: false,
            xtype: 'CMSmediaselector',
            ref: 'mediaSelector',
            websiteId: this.websiteId,
            tbar: ['->', {
                xtype: 'label',
                text: CMS.i18n('Filter nach Medientyp')
            }, {
                xtype: 'tbspacer'
            }, {
                xtype: 'combo',
                ref: '../../typeSelector',
                editable: false,
                triggerAction: 'all',
                lazyRender: true,
                mode: 'local',
                displayField: 'descr',
                valueField: 'type',
                store: new Ext.data.ArrayStore({
                    fields: ['type', 'descr'],
                    data: comboItems
                }),
                listeners: {
                    'select': this.handleTypeSelect,
                    scope: this
                }
            }]
        }];

        this.tbar = [{
            text: CMS.i18n('Hochladen'),
            iconCls: 'upload',
            ref: '../uploadButton',
            handler: function () {
                (new CMS.mediaDB.UploadWindow({
                    websiteId: this.websiteId,
                    albumId: this.albumSelector.view.getSelectedRecords()[0].id,
                    listeners: {
                        'destroy': this.mediaWindowCloseHandler,
                        scope: this
                    }
                })).show();
            },
            disabled: true,
            scope: this
        }, '-', {
            text: CMS.i18n('Album erstellen'),
            handler: this.createAlbumButtonHandler,
            scope: this,
            ref: '../createAlbumButton',
            iconCls: 'add addalbum'
        }, {
            text: CMS.i18n('Album umbenennen'),
            handler: this.editAlbumButtonHandler,
            scope: this,
            ref: '../editAlbumButton',
            disabled: true,
            iconCls: 'rename renamealbum'
        }, {
            text: CMS.i18n('Album löschen'),
            handler: this.deleteAlbumButtonHandler,
            scope: this,
            ref: '../deleteAlbumButton',
            disabled: true,
            iconCls: 'delete deletealbum'
        }, '-', {
            text: CMS.i18n('Datei(en) löschen'),
            disabled: true,
            handler: this.deleteButtonHandler,
            scope: this,
            ref: '../deleteButton',
            iconCls: 'delete deletemedia'
        }, '->', {
            cls: 'search',
            width: 180,
            xtype: 'twintrigger',
            emptyText: CMS.i18n('Suche'),
            trigger1Class: 'x-form-clear-trigger',
            trigger2Class: 'x-form-search-trigger',
            ref: '../searchField',
            onTrigger1Click: function () {
                if (this.getValue()) {
                    this.fireEvent('dosearch', '', false);
                }
                this.setValue('');
            },
            onTrigger2Click: function () {
                this.fireEvent('dosearch', this.getValue(), true);
            },
            listeners: {
                specialkey: function (self, e) {
                    if (e.getKey() === e.ENTER) {
                        self.onTrigger2Click();
                    }
                }
            }
        }, {
            xtype: 'tbspacer',
            width: 10
        }];

        CMS.mediaDB.MediaDBPanel.superclass.initComponent.apply(this, arguments);

        if (this.filterType) {
            this.filterMediaType(this.filterType);
        }

        this.relayEvents(this.mediaSelector, ['mark', 'unmark', 'rowdblclick']);
        this.relayEvents(this.albumSelector, ['CMSalbumselect']);

        this.mon(this.searchField, 'dosearch', this.searchHandler, this);
        this.on('mark', this.markHandler, this);
        this.on('unmark', this.unmarkHandler, this);
        this.on('CMSalbumselect', this.albumSelectHandler, this);
    },

    /**
    * @private
    * Handler for the dosearch event fired by searchField
    */
    searchHandler: function (term, forceReload) {
        this.albumSelector.clearSelections();
        this.mediaSelector.filterBySearchTerm(term, forceReload);
    },

    /**
    * @private
    * Handler for the select event from type selection combo box
    */
    handleTypeSelect: function (combo, record, index) {
        this.filterMediaType(combo.value);
    },

    /**
    * Filter the associated store by media type
    * @param {String} type One of the types configured in {@link #possibleTypes}
    */
    filterMediaType: function (type) {
        var oldType = this.filterType;
        this.filterType = type;
        if (type) {
            if (!this.possibleTypes[type]) {
                console.warn('Invalid media type', type);
                return;
            }
            this.mediaSelector.store.baseParams.type = type;
            this.typeSelector.setValue(type);
        } else {
            delete this.mediaSelector.store.baseParams.type;
            this.typeSelector.setValue('');
        }
        if (oldType != type && this.mediaSelector.store.baseParams.albumId) {
            this.mediaSelector.reload();
        }
    },

    /**
    * @private
    * Handler for click on deleteButton
    */
    deleteButtonHandler: function () {
        var files = this.mediaSelector.getSelectionModel().getSelections();
        var msg;
        if (files.length === 1) {
            msg = CMS.i18n('"{name}" löschen?', 'mediadb.confirmDelete').replace(/{name}/g, files[0].get('name'));
        } else {
            msg = CMS.i18n('Wirklich {num} Dateien löschen?', 'mediadb.confirmDeletePlural').replace(/{num}/g, files.length);
        }

        Ext.MessageBox.confirm(CMS.i18n('Löschen?'), msg, function (btnId) {
            if (btnId == 'yes') {
                var ids = [];
                Ext.each(files, function (file) {
                    ids.push(file.get('id'));
                });
                CMS.app.trafficManager.sendRequest({
                    action: 'deleteMedia',
                    data: {
                        ids: ids,
                        websiteId: this.websiteId
                    },
                    modal: true,
                    success: function () {
                        CMS.Message.toast(CMS.i18n('Löschen erfolgreich'));
                        this.deleteButton.disable();
                    },
                    failureTitle: CMS.i18n('Fehler beim Löschen'),
                    callback: function () {
                        this.mediaSelector.store.reload();
                    },
                    scope: this
                });
            }
        }, this);
    },

    /**
    * @private
    * Handler for click on editButton
    */
    editButtonHandler: function () {
        var files = this.mediaSelector.getSelectionModel().getSelections();
        (new CMS.mediaDB.MediaPropertyWindow({
            records: files,
            title: CMS.i18n('Details bearbeiten'),
            websiteId: this.websiteId,
            listeners: {
                'destroy': this.mediaWindowCloseHandler,
                scope: this
            }
        })).show();
    },

    /**
    * @private
    * Handler for click on downloadButton
    */
    downloadButtonHandler: function () {
        var files = this.mediaSelector.getSelectionModel().getSelections();
        var params = CMS.app.trafficManager.createPostParams(Ext.apply({
            id: files[0].id,
            websiteId: this.websiteId
        }, CMS.config.params.downloadMedia));
        var href = Ext.urlAppend(CMS.config.urls.downloadMedia, Ext.urlEncode(params));

        window.open(href);
    },

    /**
     * @private
     * Handler for click on replaceButton
     */
    replaceButtonHandler: function () {
        var files = this.mediaSelector.getSelectionModel().getSelections();
        (new CMS.mediaDB.UploadWindow({
            websiteId: this.websiteId,
            albumId: '',
            mediaId: files[0].id,
            singleFile: true,
            title: String.format(CMS.i18n('Datei „{0}“ ersetzen', 'mediadb.replaceWindowTitle'), files[0].data.name),
            listeners: {
                'destroy': function () {
                    // we should check if there was an actual replacement or not
                    this.fireEvent('CMSmediareplaced');
                    this.mediaWindowCloseHandler.apply(this, arguments);
                },
                scope: this
            }
        })).show();
    },


    /**
    * @private
    * Handler for click on createAlbumButton
    */
    createAlbumButtonHandler: function () {
        CMS.Message.prompt(CMS.i18n('Neues Album erstellen'), CMS.i18n('Bezeichnung des neuen Albums:'), function (btnId, title, msgbox) {
            if (btnId == 'ok') {
                var record = new CMS.data.AlbumRecord({
                    name: title,
                    websiteId: this.websiteId
                });
                CMS.app.trafficManager.sendRequest({
                    action: 'createAlbum',
                    data: record.data,
                    successCondition: 'data.id',
                    success: function (resp) {
                        var albumId = resp.data.id;
                        this.albumSelector.store.reload({
                            callback: function () {
                                this.albumSelector.selectByAlbumId(albumId);
                            },
                            scope: this
                        });
                    },
                    failureTitle: CMS.i18n('Fehler beim Erstellen des Albums'),
                    scope: this
                });
            }
        }, this, false, CMS.i18n('Neues Album'), CMS.config.validation.albumName);
    },

    /**
    * @private
    * Handler for click on editAlbumButton
    */
    editAlbumButtonHandler: function () {
        var album = this.albumSelector.view.getSelectedRecords()[0];
        CMS.Message.prompt(CMS.i18n('Album umbenennen'), CMS.i18n('Bezeichnung des Albums:'), function (btnId, title, msgbox) {
            if (btnId == 'ok') {
                album.set('name', title);

                CMS.app.trafficManager.sendRequest({
                    action: 'editAlbum',
                    data: album.data,
                    success: function (resp) {
                        this.albumSelector.store.reload({
                            callback: function () {
                                this.albumSelector.selectByAlbumId(album.id);
                            },
                            scope: this
                        });
                    },
                    failureTitle: CMS.i18n('Fehler beim Umbenennen des Albums'),
                    scope: this
                });
            }
        }, this, false, album.get('name'), CMS.config.validation.albumName);
    },

    /**
    * @private
    * Handler for click on deleteAlbumButton
    */
    deleteAlbumButtonHandler: function () {
        var album = this.albumSelector.view.getSelectedRecords()[0];
        var msg = 'Album "' + album.get('name') + '" wirklich löschen? Dabei werden alle im Album vorhandenen Dateien unwiderruflich gelöscht.';
        Ext.MessageBox.confirm(CMS.i18n('Löschen?'), msg, function (btnId) {
            if (btnId == 'yes') {
                CMS.app.trafficManager.sendRequest({
                    action: 'deleteAlbum',
                    data: {
                        id: album.get('id'),
                        websiteId: this.websiteId
                    },
                    modal: true,
                    success: function (resp) {
                        this.albumSelector.store.reload({
                            callback: function () {
                                this.albumSelector.select(0);
                            },
                            scope: this
                        });
                    },
                    failureTitle: CMS.i18n('Fehler beim Löschen'),
                    failure: function () {
                        this.albumSelector.selectByAlbumId(album.id);
                    },
                    scope: this
                });
            }
        }, this);
    },

    /**
    * @private
    * Handler for mediaCatalog's albumSelect event
    */
    albumSelectHandler: function (album) {
        if (album) {
            this.uploadButton.enable();
            this.editAlbumButton.enable();
            this.deleteAlbumButton.enable();

            this.searchField.setValue('');
            this.mediaSelector.filterByAlbum(album.id);
        } else {
            this.uploadButton.disable();
            this.editAlbumButton.disable();
            this.deleteAlbumButton.disable();

            this.mediaSelector.store.removeAll();
        }
        this.deleteButton.disable();
    },

    /**
    * @private
    * Handler for mediaCatalog's mark event
    */
    markHandler: function (record, grid, sm) {
        this.deleteButton.enable();
    },

    /**
    * @private
    * Handler for mediaCatalog's unmark event
    */
    unmarkHandler: function (record, grid, sm) {
        if (sm && sm.hasSelection()) {
            this.markHandler(null, grid, sm);
        } else {
            this.deleteButton.disable();
        }
    },

    /**
    * @private
    * Handler for upload/mediaProperty window's close event
    */
    mediaWindowCloseHandler: function () {
        if (this.mediaSelector) { // may be gone when called during shutdown
            this.mediaSelector.store.reload();
        }
    }
});

Ext.reg('CMSmediadbpanel', CMS.mediaDB.MediaDBPanel);
