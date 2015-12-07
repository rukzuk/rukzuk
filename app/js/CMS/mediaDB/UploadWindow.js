Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.UploadWindow
* @extends Ext.Window
* This is a true singleton. Use getInstance() to access its instance.
*/
CMS.mediaDB.UploadWindow = Ext.extend(Ext.Window, {
    modal: true,
    width: 600,
    height: 500,
    layout: 'fit',
    singleFile: false,
    constrainHeader: true,
    initComponent: function () {
        this.title = this.title ? this.title : CMS.i18n('Dateien hochladen', 'mediadb.uploadWindowTitle');
        this.items = {
            layout: 'vbox',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            border: false,
            items: [{
                flex: 1,
                xtype: 'CMSuploader',
                ref: '../uploadPanel',
                url: CMS.config.urls.upload + '?runId=' + CMS.app.runId + '&websiteId=' + this.websiteId + '&albumId=' + this.albumId + (this.mediaId ? ('&id=' + this.mediaId) : ''),
                singleFile: this.singleFile,
                listeners: {
                    scope: this,
                    CMSallfilesuploaded: this.destroy
                }
            }]
        };

        CMS.mediaDB.UploadWindow.superclass.initComponent.apply(this, arguments);
    },

    close: function () {
        if (this.uploadPanel.isUploading()) {
            Ext.MessageBox.show({
                closable: false,
                title: CMS.i18n('Hochladen abbrechen?'),
                msg: CMS.i18n('Soll die laufende Übertragung wirklich abgebrochen werden?'),
                buttons: {yes: true, no: true},
                fn: function (btnId) {
                    if (btnId == 'yes') {
                        this.uploadPanel.onCancel();
                        this.destroy();
                    }
                },
                scope: this
            });
        } else {
            this.destroy();
        }
    }
});
