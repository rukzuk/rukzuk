/*global plupload:true*/
/**
 * @class CMS.form.ImportButton
 * @extends Ext.grid.GridPanel
 *
 * Upload panel with a grid and pluploader instance
 *
 */
CMS.CMSimporter = Ext.extend(Ext.grid.GridPanel, {
    autoScroll: false,
    border: false,
    enableHdMenu: false,
    /**
     * Only allow a single file to be uploaded
     * @property
     * @type {Boolean}
     */
    singleFile: true,
    autoExpandColumn: 'name',
    cls: 'mediaDBUploader',

    /**
     * @cfg (String) url
     * The url to upload the files to
     */
    url: '',

    initComponent: function () {
        this.success = [];
        this.failed = [];

        this.viewConfig = this.viewConfig || {};
        this.viewConfig.deferEmptyText = false;
        this.viewConfig.emptyText = [
            '<span class="empty-msg">',
            CMS.i18n('Dateien auswählen…'),
            '</span>'
        ].join('');

        this.store = new Ext.data.JsonStore({
            fields: ['id', 'loaded', 'name', 'size', 'percent', 'status', 'msg'],
            listeners: {
                load: this.onStoreLoad,
                remove: this.onStoreRemove,
                update: this.onStoreUpdate,
                scope: this
            }
        });

        this.columns = [{
            id: 'name',
            dataIndex: 'name',
            sortable: true,
            header: CMS.i18n('Dateiname')
        }, {
            id: 'size',
            dataIndex: 'size',
            width: 65,
            sortable: true,
            header: CMS.i18n('Größe'),
            renderer: 'fileSize'
        }, {
            id: 'status',
            dataIndex: 'status',
            width: 230,
            sortable: true,
            header: CMS.i18n('Status'),
            renderer: {
                fn: function (value, metaData, record) {
                    var msg;
                    switch (value) {
                        case plupload.QUEUED:
                            msg = '';
                            break;
                        case plupload.UPLOADING:
                            msg = String.format(CMS.i18n('wird hochgeladen… ({0}%)'), record.get('percent'));
                            break;
                        case plupload.FAILED:
                            msg = record.get('msg') || CMS.i18n('Fehler');
                            msg = '<span class="error">' + msg + '</span>';
                            break;
                        case plupload.DONE:
                            msg = '<span class="success">' + CMS.i18n('Übertragung abgeschlossen') + '</span>';
                            break;
                    }

                    return msg;
                },
                scope: this
            }
        }];

        this.bbar = {
            items: [{
                /**
                 * @name progressBar
                 * @type Ext.ProgressBar
                 * @memberOf CMS.form.ImportButton
                 * @property
                 */
                xtype: 'progress',
                ref: '../progressBar',
                animate: true,
                height: 40,
                width: 250
            }, '->', {
                /**
                 * @name cancelButton
                 * @type Ext.Button
                 * @memberOf CMS.form.ImportButton
                 * @property
                 */
                text: CMS.i18n('Abbrechen'),
                handler: this.onCancel,
                scope: this,
                disabled: true,
                ref: '../cancelButton',
                iconCls: 'cancel'
            }, {
                /**
                 * @name startButton
                 * @type Ext.Button
                 * @memberOf CMS.form.ImportButton
                 * @property
                 */
                text: CMS.i18n('Hochladen'),
                handler: this.onUpload,
                scope: this,
                disabled: true,
                ref: '../startButton',
                cls: 'primary',
                iconCls: 'upload'
            }]
        };

        this.tbar = {
            items: [{
                /**
                 * @name addButton
                 * @type Ext.Button
                 * @memberOf CMS.form.ImportButton
                 * @property
                 */
                text: this.singleFile ? CMS.i18n('Datei auswählen', 'mediadb.selectFile') : CMS.i18n('Dateien auswählen', 'mediadb.selectFiles'),
                ref: '../addButton',
                iconCls: 'addfile',
                disabled: true
            }, '->', {
                /**
                 * @name deleteButton
                 * @type Ext.Button
                 * @memberOf CMS.form.ImportButton
                 * @property
                 */
                text: CMS.i18n('Entfernen'),
                tooltip: {
                    text: CMS.i18n('Aus Liste entfernen')
                },
                handler: this.onDelete,
                scope: this,
                disabled: true,
                ref: '../deleteButton',
                iconCls: 'delete'
            }]
        };

        this.on('afterRender', this.initUploader, this);

        CMS.CMSimporter.superclass.initComponent.apply(this, arguments);
    },

    destroy: function () {
        this.uploader.destroy(); //this also removes the events set in this.initUploader()
        this.store.destroy();

        CMS.CMSimporter.superclass.destroy.apply(this, arguments);
    },

    /**
     * @private
     */
    initUploader: function () {
        this.uploader = new plupload.Uploader({
            url: this.url,
            runtimes: 'html5',
            required_features: 'multipart',
            browse_button: this.addButton.getEl().dom.id,
            browse_button_hover: 'x-btn-focus x-btn-over',
            browse_button_active: 'x-btn-focus x-btn-over x-btn-click',
            container: this.getTopToolbar().getEl().dom.id,
            flash_swf_url: CMS.config.urls.pluploadFlash,
            multi_selection: false,
            chunk_size: '2048kb',
            urlstream_upload: true, //forces URLStream method, otherwise cookie won't be send (see http://www.plupload.com/punbb/viewtopic.php?id=249)
            multipart: true,
            drop_element: this.body.dom.id
        });

        //listen to plupload events
        Ext.each(['Init', 'FilesAdded', 'FilesRemoved', 'FileUploaded', 'Refresh', 'StateChanged', 'UploadFile', 'UploadProgress', 'UploadComplete', 'Error'], function (method) {
            this.uploader.bind(method, this.uploaderListeners[method], this);
        }, this);

        this.uploader.init();
    },

    /**
     * Returns true or false, whether the uploader is currently running
     */
    isUploading: function () {
        return this.uploader.state == plupload.UPLOADING;
    },

    /**
     * @private
     */
    onDelete: function () {
        Ext.each(this.getSelectionModel().getSelections(), function (record) {
            var id = record.get('id');
            var fileObj = this.uploader.getFile(id);

            if (fileObj) {
                this.uploader.removeFile(fileObj);
            } else {
                this.store.remove(this.store.getById(id));
            }
        }, this);
    },

    /**
     * @private
     */
    onUpload: function () {
        this.uploader.start();
    },

    /**
     * @private
     */
    onCancel: function () {
        this.uploader.stop();
    },

    /**
     * @private
     */
    updateProgressBar: function () {
        var t = this.uploader.total;
        var speed = Ext.util.Format.fileSize(t.bytesPerSec);
        var total = this.store.getCount();
        var failed = this.failed.length;
        var success = this.success.length;
        var sent = failed + success;
        var queued = total - success - failed;

        if (total) {
            var progressText = String.format(CMS.i18n('{0} von {1} ({5}/s)'), sent, total, success, failed, queued, speed);
            var percent = t.percent / 100;

            this.progressBar.updateProgress(percent, progressText);
        } else {
            this.progressBar.updateProgress(0, ' ');
        }
    },

    /**
     * @private
     */
    updateStore: function (file) {
        if (!file.msg) {
            file.msg = '';
        }
        var record = this.store.getById(file.id);
        if (record) {
            record.data = file;
            record.commit();
        } else {
            this.store.loadData([file], true);
        }
    },

    /**
     * @private
     */
    onStoreLoad: function (store, record, operation) {
        this.updateProgressBar();
    },

    /**
     * @private
     */
    onStoreRemove: function (store, record, operation) {
        if (!store.getCount()) {
            this.deleteButton.setDisabled(true);
            this.startButton.setDisabled(true);
            this.uploader.total.reset();
        }
        var id = record.get('id');

        Ext.each(this.success, function (file) {
            if (file && file.id == id) {
                this.success.remove(file);
            }
        }, this);

        Ext.each(this.failed, function (file) {
            if (file && file.id == id) {
                this.failed.remove(file);
            }
        }, this);

        this.updateProgressBar();
    },

    /**
     * @private
     */
    onStoreUpdate: function (store, record, operation) {
        this.updateProgressBar();
    },



    /*
     * methods which are bind to plupload events
     * list of available events: http://www.plupload.com/plupload/docs/api/index.html#class_plupload.Uploader.html
     */
    uploaderListeners: {
        Init: function (uploader, data) {
            console.log('[Uploader] initialized uploader with runtime: ', data.runtime);

            //HACK SBCMS-708 Disabled the file filter in the select dialog for HTML5 runtime; https://github.com/moxiecode/plupload/issues/352#issuecomment-2453096
            if (data.runtime == plupload.runtimes.Html5.name) {
                (function () {
                    Ext.get(uploader.id + '_html5').set({accept: '*'});
                }).defer(1);
            }

            if (this.uploader.features.dragdrop) {
                var view = this.getView();
                view.emptyText = [
                    '<span class="empty-msg">',
                    this.singleFile ? CMS.i18n('Datei hier hineinziehen oder oben auswählen…', 'mediadb.emptyTextDropSingle') :
                        CMS.i18n('Dateien hier hineinziehen oder oben auswählen…', 'mediadb.emptyTextDrop'),
                    '</span>'
                ].join('');

                if (view.rendered) {
                    view.refresh();
                }
            }
            this.addButton.setDisabled(false);
        },

        FilesAdded: function (uploader, files) {
            if (this.singleFile) {
                if (uploader.files.length > 0) {
                    // there is already one file
                    return false;
                } else if (files.length > 1) {
                    // allow only a single file to be added
                    return false;
                }
                // hide add button
                this.addButton.hide();
            }

            this.deleteButton.setDisabled(false);
            this.startButton.setDisabled(false);

            Ext.each(files, function (file) {
                this.updateStore(file);
            }, this);
            //this.uploader.start();
        },

        FilesRemoved: function (uploader, files) {

            if (this.singleFile) {
                this.addButton.show();
            }

            Ext.each(files, function (file) {
                this.store.remove(this.store.getById(file.id));
            }, this);
        },

        FileUploaded: function (uploader, file, status) {
            var response = Ext.decode(status.response);
            if (response.success === true) {
                file.server_error = 0;
                this.success.push(file);
            } else {
                if (response.error) {
                    var error = '';
                    Ext.each(response.error, function (oneError) {
                        error += oneError.text + '<br>';
                    });
                    file.msg = '<span class="error">' + error + '</span>';
                }
                file.server_error = 1;
                this.failed.push(file);
            }
            this.updateStore(file);

        },

        Refresh: function (uploader) {
            Ext.each(uploader.files, function (file) {
                this.updateStore(file);
            }, this);
        },

        StateChanged: function (uploader) {
            if (uploader.state == plupload.STARTED) {
                //this.fireEvent('uploadstarted', this);
                this.cancelButton.setDisabled(false);
                this.startButton.setDisabled(true);
            } else {
                this.fireEvent('uploadcomplete', this, this.success, this.failed);
                this.cancelButton.setDisabled(true);
                this.startButton.setDisabled(false);
            }
        },

        UploadFile: function (uploader, file) {
            this.updateStore(file);
        },

        UploadProgress: function (uploader, file) {
            if (file.server_error) {
                file.status = plupload.FAILED;
            }
            this.updateStore(file);
        },
                /**
         * Fires 'allfilesuploaded' event if upload is complete or shows an error message
         */
        UploadComplete: function (uploader, files) {
            var success = true;
            Ext.each(this.store.getRange(), function (file) {
                if (file.get('status') == plupload.FAILED) {
                    success = false;
                    return false;
                }
            });

            if (success) {
                this.fireEvent('CMSallfilesuploaded');
                console.log('uploadcomplete');
            } else {
                Ext.MessageBox.show({
                    title: CMS.i18n('Fehler beim Hochladen'),
                    msg: CMS.i18n('Es konnten nicht alle Dateien erfolgreich hochgeladen werden. Fehlerhafte Dateien wurden in der Liste gekennzeichnet.'),
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        },

        Error: function (uploader, data) {
            data.file.status = plupload.FAILED;

            switch (data.code) {
                case plupload.FILE_SIZE_ERROR:
                    data.file.msg = '<span class="error">' + CMS.i18n('Fehler: Datei ist zu groß (max. {maxsize})').replace('{maxsize}', CMS.config.media.maxFileSize.toUpperCase()) + '</span>';
                    break;
                case plupload.FILE_EXTENSION_ERROR:
                    data.file.msg = '<span class="error">' + CMS.i18n('Fehler: Ungültiger Dateityp') + '</span>';
                    break;
                default:
                    data.file.msg = String.format('<span class="error">{2} ({0}: {1})</span>', data.code, data.details, data.message);
            }

            this.updateStore(data.file);
        }
    }
});

Ext.reg('CMSimporter', CMS.CMSimporter);