Ext.ns('CMS.home');

/**
* @class CMS.home.BuildGrid
* @extends Ext.grid.GridPanel
*
* Show builds of a website with publish and download buttons
*
*/
CMS.home.BuildGrid = Ext.extend(Ext.grid.GridPanel, {
    cls: 'CMSbuildgrid',

    /**
    * @cfg {String} websiteId
    */
    websiteId: null,

    initComponent: function () {
        this.viewConfig = this.viewConfig || {};
        this.viewConfig.deferEmptyText = false;
        this.viewConfig.emptyText = CMS.i18n('Es wurden noch keine Versionen von dieser Website angelegt.');

        var config = {
            store: CMS.data.StoreManager.get('build', this.websiteId || -1, { disableLoad: !this.websiteId }),
            header: true,
            title: CMS.i18n('Die letzten 3 Versionen dieser Website'),
            disableSelection: true,
            border: false,
            enableHdMenu: false,
            columns: [{
                id: 'version',
                dataIndex: 'version',
                width: 60,
                sortable: true,
                header: CMS.i18n('Version')
            }, {
                id: 'status',
                dataIndex: 'status',
                width: 200,
                header: CMS.i18n('Status'),
                sortable: false,
                renderer: this.statusRenderer
            }, {
                id: 'timestamp',
                dataIndex: 'timestamp',
                width: 125,
                sortable: true,
                renderer: function (value) {
                    return Ext.util.Format.date(SB.date.dateFromUnixTimeStamp(value), CMS.config.date.format + ' ' + CMS.config.date.timeFormat);
                },
                header: CMS.i18n('Datum')
            }, {
                id: 'comment',
                dataIndex: 'comment',
                width: 200,
                sortable: true,
                header: CMS.i18n('Kommentar')
            }, {
                id: 'download',
                dataIndex: '',
                header: '&#160;',
                renderer: this.downloadActionRenderer,
                resizable: false,
                width: 26
            }, {
                id: 'publish',
                dataIndex: '',
                header: '&#160;',
                renderer: this.publishActionRenderer,
                resizable: false,
                width: 26
            }],
            autoExpandColumn: 'comment'
        };
        Ext.apply(this, config);

        CMS.home.BuildGrid.superclass.initComponent.apply(this, arguments);

        this.on('cellclick', this.cellclickHandler, this);
        this.mon(this.getView(), 'refresh', function (view) {
            var latestPublishedBuild = this.store.getLatestPublishedBuild();
            if (latestPublishedBuild) {
                // there is a build which is being published or has been published
                // -> visualize the the status
                var index = this.store.indexOf(latestPublishedBuild);
                var row = Ext.fly(view.getRow(index));
                if (row) {
                    if (latestPublishedBuild.get('status') === 'FINISHED') {
                        row.addClass('success');
                    } else if (latestPublishedBuild.get('status') === 'FAILED') {
                        row.addClass('error');
                    }
                }
            }

            // hide the "publish" column if there is a build which is currently
            // being published; it is not allowed to publish the same website more
            // than once at the same time
            var cm = this.getColumnModel();
            var isPublishing = !!this.store.isPublishing();
            cm.setHidden(cm.getIndexById('publish'), isPublishing);
        }, this);
    },

    /**
     * Cell renderer for the "status" column
     * @private
     */
    statusRenderer: function (status, meta, record, rowIndex, colIndex, store) {
        var statusText = '';
        var latestPublishedBuild = store.getLatestPublishedBuild();
        var isCurrentVersion = latestPublishedBuild && latestPublishedBuild === record;

        if (isCurrentVersion) {
            switch (status) {
            case 'INPROGRESS':
                statusText = CMS.i18n('Wird gerade publiziert…');
                break;
            case 'FINISHED':
                statusText = CMS.i18n('Live');
                break;
            case 'FAILED':
                statusText = CMS.i18n('Fehler');
                if (record.get('message')) {
                    statusText += ': ' + record.get('message');
                }
                statusText += '; ' + CMS.i18n('Bitte prüfe die Live-Server-Konfiguration.');
                meta.attr = 'ext:qtip="' + statusText + '"';
                break;
            }
        }

        return statusText;
    },

    /**
     * Cell renderer for the "download" column
     * @private
     */
    downloadActionRenderer: function (value, meta, record, rowIndex, colIndex, store) {
        meta.attr = 'ext:qtip="' + CMS.i18n('Version als ZIP-Datei herunterladen') + '"';
        return '<img class="action download" src="' +  Ext.BLANK_IMAGE_URL + '" width="16">';
    },

    /**
     * Cell renderer for the "publish" column
     * @private
     */
    publishActionRenderer: function (value, meta, record, rowIndex, colIndex, store) {
        meta.attr = 'ext:qtip="' + CMS.i18n('Version auf den Live-Server publizieren') + '"';
        return '<img class="action publish" src="' +  Ext.BLANK_IMAGE_URL + '" width="16">';
    },

    /**
    * @private
    */
    cellclickHandler: function (grid, rowIndex, colIndex, evt) {
        var downloadIndex = this.getColumnModel().getIndexById('download');
        var publishIndex = this.getColumnModel().getIndexById('publish');
        if (colIndex === downloadIndex) {
            this.fireEvent('CMSdownloadbuild', this.store.getAt(rowIndex));
        } else if (colIndex === publishIndex) {
            this.fireEvent('CMSpublishbuild', this.store.getAt(rowIndex));
        }
    },

    /**
    * Open the specified site
    * @param {CMS.data.WebsiteRecord} record The site to be opened
    */
    setSite: function (record) {
        if (record && record.id) {
            this.websiteId = record.id;
            this.reconfigure(CMS.data.StoreManager.get('build', this.websiteId), this.getColumnModel());
        } else {
            delete this.websiteId;
            this.reconfigure(CMS.data.StoreManager.get('build', -1, { disableLoad: true }), this.getColumnModel());
        }
    },

    destroy: function () {
        if (this.store) {
            this.store.destroy();
        }
        CMS.home.BuildGrid.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSbuildgrid', CMS.home.BuildGrid);
