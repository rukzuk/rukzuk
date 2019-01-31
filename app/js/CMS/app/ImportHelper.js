Ext.ns('CMS.app');

/**
* @class CMS.app.ImportHelper
* @extends Object
* @singleton
* Helper for importing
*/
CMS.app.ImportHelper = {

    /**
    * Set active website so later calls to the ImportHelper know which website is currently opened for editing
    * @param {CMS.data.WebsiteRecord} website The currently active website
    */
    setActiveWebsite: function (website) {
        this.activeWebsite = website;
    },

    /**
    * Set selected website so later calls to the ImportHelper know which website is currently selected
    * @param {CMS.data.WebsiteRecord} website The currently selected website
    */
    setSelectedWebsite: function (website) {
        this.selectedWebsite = website;
    },

    /**
     * Start the import procedure for the given website.
     * This will set a lock and open the actual import dialog
     *
     * @param {CMS.data.WebsiteRecord} website
     *      The website to import into
     *
     * @param {Object} cfg
     *      Optional. An additional configuration object that allows to modify:
     *      <ul>
     *        <li>title: The window title</li>
     *        <li>text: The text of the import dialg</li>
     *        <li>allowedType: The allowed import types (see CMS.config.importTypes)
     *      </ul>
     */
    startImport: function (website, cfg) {

        if (website === null)
        {
            // -> import a new website
            this.openFileSelection(Ext.apply({
                title: CMS.i18n('Website importieren'),
                text: '<p>' + CMS.i18n('Bitte Datei mit Website auswählen.') + '</p>'
            }, cfg || {}));
        } else {
            this.website = website || this.activeWebsite || this.selectedWebsite;
            if (this.website) {
                this.openFileSelection(Ext.apply({
                    title: CMS.i18n('Importieren'),
                    text: [
                        '<p>', CMS.i18n('Es wird in die Website „{website}“ importiert.').replace('{website}', this.website.get('name')), '</p>',
                        '<p>', CMS.i18n('Während des Importvorgangs ist die Website für Bearbeitungen gesperrt.'), '</p>'
                    ].join('')
                }, cfg || {}));
            }
        }
    },

    /**
    * @private
    */
    openFileSelection: function (cfg) {
        this.win = new Ext.Window({
            title: cfg.title,
            modal: true,
            cls: 'CMSimportwindow',
            width: 600,
            border: false,
            layout: 'card',
            activeItem: 0,
            resizable: false,
            items: [{
                xtype: 'panel',
                autoHeight: true,
                border: false,
                layout: 'fit',
                items: [{
                    xtype: 'label',
                    html: cfg.text
                }, {
                    xtype: 'CMSimporter',
                    url: CMS.config.urls.importFile + '?runId=' + CMS.app.runId + '&websiteid=' + (this.website ? this.website.id : null),
                    singleFile: true,
                    buttonText: CMS.i18n('Datei auswählen'),
                    ref: '../importButton',
                    iconCls: 'importwebsite',
                    wrapperCls: 'CMSimortwebsite',
                    siteId: this.website ? this.website.id : null,
                    allowedType: cfg.allowedType,
                    logFailure: false,
                    height: 375,
                    listeners: {
                        scope: this,
                        CMSallfilesuploaded: this.uploadHandler.createDelegate(this)
                    },
                    success: this.uploadHandler.createDelegate(this),
                    failure: this.uploadFailureHandler.createDelegate(this)
                }]
            }, {
                xtype: 'panel',
                autoHeight: true,
                border: false,
                cls: 'CMSprogress',
                html: '<span class="spinner">' + CMS.i18n('Datei wird hochgeladen') + '</span>'
            }]
        });
        this.win.show();
    },

    /**
     * Handler for upload from import button
     * @private
     */
    uploadHandler: function (response) {
        if (this.win) {
            this.win.destroy();
        }
        this.win = null;

        var m = Ext.getBody().mask(CMS.i18n('Die Anwendung wird aktualisiert'), 'CMSmaskall');
        m.addClass('CMSmaskall');
        var callback = function () {
            CMS.app.viewport.refresh(function () {
                Ext.getBody().unmask();
            });

            if (this.uploadCallback) {
                this.uploadCallback.fn.call(this.uploadCallback.scope, response);
                this.uploadCallback = null;
            }
        };

        var websiteStore = CMS.data.WebsiteStore.getInstance();
        if (this.website) {
            // import of modules, templates, ... into an existing website
            var moduleStore = CMS.data.StoreManager.get('module', this.website.id);
            var templateStore = CMS.data.StoreManager.get('template', this.website.id);
            var templateSnippetStore = CMS.data.StoreManager.get('templateSnippet', this.website.id);
            var mediaStore = CMS.data.StoreManager.get('media', this.website.id);

            this.reloadStores([websiteStore, moduleStore, templateStore, templateSnippetStore, mediaStore], callback, this);
        } else {
            // import of a new website
            this.reloadStores([websiteStore], callback, this);
        }
    },


    /**
     * Helper to load multiple stores ar once and execute a single callback after loading all stores
     * @private
     */
    reloadStores: function (stores, callback, scope) {
        var counter = stores.length;
        var loadCB = function () {
            counter--;
            if (counter === 0) {
                callback.call(scope);
            }
        };
        for (var i = 0; i < stores.length; i++) {
            stores[i].reload({
                callback: loadCB
            });
        }
    },

    /**
    * @private
    * Handler for failed uplaod from import button
    */
    uploadFailureHandler: function (response, error) {
        if (this.win) {
            this.win.destroy();
        }
        this.win = null;
        var genericError = false;
        if (Ext.isArray(response.error) && response.error.length == 1 && response.error[0].code == CMS.config.specialErrorCodes.importConflict) {
            this.importId = SB.util.getObjectByIndexPath(response, 'data.importId');
            var tpl = (SB.util.getObjectByIndexPath(response, 'data.conflict.templates') || [])[0];
            if (!this.importId) {
                genericError = true;
            } else {
                (new CMS.imp.ImportConflictWindow({
                    templateConflict: tpl,
                    moduleConflicts: SB.util.getObjectByIndexPath(response, 'data.conflict.modules'),
                    templateSnippetConflicts: SB.util.getObjectByIndexPath(response, 'data.conflict.templatesnippets'),
                    mediaConflicts: SB.util.getObjectByIndexPath(response, 'data.conflict.media'),
                    importId: this.importId,
                    listeners: {
                        'cancel': this.conflictCancelHandler,
                        'success': this.conflictSuccessHandler,
                        scope: this
                    }
                })).show();
            }
        } else {
            genericError = true;
        }
        if (genericError) {
            // we borrow some logic from trafficManager's private methods
            CMS.app.ErrorManager.push(error.verbose);
            CMS.Message.error(CMS.i18n('Fehler beim Import'), error.formatted || CMS.i18nTranslateMacroString(CMS.config.errorTexts.generic));
        }
    },


    /**
    * @private Handler for ImportConflictWindow's cancel event
    */
    conflictCancelHandler: function (win) {
        CMS.app.trafficManager.sendRequest({
            action: 'cancelImport',
            data: {
                importId: this.importId
            }
        });
    },

    /**
    * @private Handler for ImportConflictWindow's success event
    */
    conflictSuccessHandler: function (win) {
        this.uploadHandler(null, null);
    }
};
