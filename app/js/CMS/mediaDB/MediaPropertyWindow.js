Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.MediaPropertyWindow
* @extends Ext.Window
* A window for editing media files
*/
CMS.mediaDB.MediaPropertyWindow = Ext.extend(Ext.Window, {

    layout: 'fit',
    modal: true,
    constrainHeader: true,
    width: 500,
    height: 195,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {Array} records
    * (required) An array of {@link CMS.data.MediaRecord}s
    */
    records: [],

    initComponent: function () {
        if (!Ext.isArray(this.records) || !this.records.length) {
            throw 'No records provided';
        }

        this.albumStore = CMS.data.StoreManager.get('album', this.websiteId || -1, { disableLoad: true });

        var imagesrcs = [];
        var filenames = [];
        Ext.each(this.records, function (rec) {
            imagesrcs.push(rec.get('icon'));
            filenames.push(rec.get('name'));
        });

        var filenameItem = {
            flex: 1,
            border: false,
            style: {
                padding: '10px'
            }
        };
        this.renamingEnabled = (this.records.length == 1);
        if (this.renamingEnabled) {
            this.originalName = filenames[0];
            filenameItem.layout = 'form';
            var uploadTimeStamp = this.records[0].get('dateUploaded');
            filenameItem.items = [{
                xtype: 'textfield',
                ref: '../../../renameField',
                value: this.originalName,
                fieldLabel: CMS.i18n('Dateiname'),
                anchor: '-' + (Ext.getPreciseScrollBarWidth() - 10)
            }, {
                xtype: 'displayfield',
                value: this.albumStore.getById(this.records[0].get('albumId')).get('name'),
                fieldLabel: CMS.i18n('Album')
            }, {
                xtype: 'displayfield',
                value: uploadTimeStamp ? Ext.util.Format.date(SB.date.dateFromUnixTimeStamp(uploadTimeStamp), CMS.config.date.format + ' ' + CMS.config.date.timeFormat) : '',
                fieldLabel: CMS.i18n('Datum')
            }];
        } else {
            filenameItem.html = '<h2>' + filenames.length + ' ' + CMS.i18n('Datei(en) ausgewählt') + '</h2><p>'
                    + Ext.util.Format.ellipsis(filenames.join(', '), 500, true)
                + '</p>';
        }

        this.items = {
            layout: 'vbox',
            hideBorders: true,
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            items: [{
                layout: 'hbox',
                items: [{
                    xtype: 'sb-multiimage',
                    imagesrcs: imagesrcs,
                    imageSize: CMS.config.params.getAllMedia.maxIconWidth,
                    spread: 37,
                    style: {
                        padding: '10px 10px 15px'
                    }
                }, filenameItem]
            }]
        };

        this.buttons = [{
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            handler: this.cancelHandler,
            scope: this
        }, {
            text: CMS.i18n('Speichern'),
            iconCls: 'ok',
            handler: this.okHandler,
            scope: this
        }];
        CMS.mediaDB.MediaPropertyWindow.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * Handler for click on the 'cancel' button
    */
    cancelHandler: function () {
        this.close();
    },

    /**
    * @private
    * Handler for click on the 'ok' button
    */
    okHandler: function () {
        var nameChanged = (this.renamingEnabled && (this.originalName != this.renameField.getValue()) && !!this.renameField.getValue());
        if (nameChanged) {
            var items = Ext.pluck(this.records, 'id');
            var data = {
                websiteId: this.websiteId
            };
            if (items.length == 1) {
                data.id = items[0];
            } else {
                data.items = items;
            }
            if (this.renamingEnabled) {
                data.name = this.renameField.getValue();
            }
            CMS.app.trafficManager.sendRequest({
                action: 'editMedia',
                data: data,
                success: function () {
                    if (nameChanged) {
                        CMS.Message.toast(CMS.i18n('Datei erfolgreich umbenannt'));
                    }
                    this.destroy();
                },
                scope: this,
                failureTitle: CMS.i18n('Fehler beim Editieren')
            });
        } else {
            this.destroy();
        }
    },

    destroy: function () {
        this.albumStore = null;

        CMS.mediaDB.MediaPropertyWindow.superclass.destroy.apply(this, arguments);
    }
});
