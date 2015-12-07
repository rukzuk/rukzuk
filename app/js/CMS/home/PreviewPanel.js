Ext.ns('CMS.home');

/**
 * Panel that contains an iframe to show a preview of a template or
 * a page of a given website
 *
 * @class       CMS.home.PreviewPanel
 * @extends     Ext.Panel
 */
CMS.home.PreviewPanel = Ext.extend(Ext.Panel, {
    /** @lends CMS.home.PreviewPanel.prototype */

    disabled: true,
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },

    /**
     * The currently used content parameter
     *
     * @property currentPreviewParams
     * @type Object
     * @private
     */
    currentPreviewParams: undefined,

    /**
     * The wrapped iframe instance
     *
     * @property iframe
     * @type SB.TheIframe
     * @protected
     */
    iframe: undefined,

    /**
     * An Ext task configuration to monitor the iframe location href
     * an trigger the "locationChanged" event on changes
     *
     * @property iframeMonitor
     * @type Object
     * @private
     */
    iframeMonitor: undefined,

    initComponent: function () {
        this.plugins = ['CMSpreviewpaneliframeresolutionswitcher'];

        this.iframe = new CMS.home.PreviewIframe({
            suppressFirstReadyEvent: false,
            cls: 'CMSiframe',
            loadMask: true
        });
        this.items = [this.iframe];

        CMS.home.PreviewPanel.superclass.initComponent.apply(this, arguments);

        // start a task to observe the current location of the iframe
        this.iframeMonitor = {
            run: function () {
                try {
                    var url = this.iframe.getFrameWindow().location.href;
                    if (url !== this.lastUrl) {
                        this.lastUrl = url;
                        this.handleLocationChange(url);
                    }
                } catch (e) {}
            },
            scope: this,
            interval: 200
        };
        Ext.TaskMgr.start(this.iframeMonitor);

        // reload the page/template if the websites colorscheme, resolutions where changed
        this.mon(CMS.data.WebsiteStore.getInstance(), 'update', function (store, record) {
            if (record.isModified('colorscheme') ||
                record.isModified('resolutions') ||
                record.isModified('navigation')) {
                this.refresh();
            }
        }, this);

        // listen to event fired by CMSiframeresolutionswitcher plugin
        this.on('CMSshowqrcode', this.showQrCodeHandler, this);

        // prevent navigation to external links after loading the iframe
        this.mon(this.iframe, 'domready', this.preventExternalNavigation, this);
    },

    /**
     * Changes the content of the iframe according to the given parameters
     *
     * @param {Object} params
     *      The parameter to identify the item to render (e.g. a website id and
     *      a page id to show the preview for a website page);
     *      Leave empty to switch the iframe to "about:blank"
     */
    setContent: function (params) {
        var baseUrl, url = 'about:blank';
        if (params) {
            if (params.pageId) {
                params = Ext.apply(params, CMS.config.params.previewPageById);
                baseUrl = CMS.config.urls.previewPageById;
            } else {
                params = Ext.apply(params, CMS.config.params.previewTemplateById);
                baseUrl = CMS.config.urls.previewTemplateById;
            }
            // store params for qrCode
            this.updatePreviewParams(params);

            params = CMS.app.trafficManager.createPostParams(params);
            url = Ext.urlAppend(baseUrl, Ext.urlEncode(params));

            this.enable();
        } else {
            this.disable();
        }
        this.iframe.setSrc(url);
    },


    /**
     * Refeshes the iframe content
     */
    refresh: function () {
        var src = this.iframe.src;
        this.iframe.setSrc('about:blank');
        this.iframe.setSrc(src);
    },

    /**
     * override superclass to destroy iframe
     */
    destroy: function () {
        this.iframe.destroy();
        Ext.TaskMgr.stop(this.iframeMonitor);

        CMS.home.PreviewPanel.superclass.destroy.apply(this, arguments);

        this.iframe = null;
        this.iframeMonitor = null;
        this.currentPreviewParams = null;
    },

    //
    //
    // private helper
    //
    //

    /**
     * Handler for showqrcode event fired by CMSiframeresolutionswitcher plugin
     * @private
     */
    showQrCodeHandler: function () {
        var params = this.currentPreviewParams;
        if (params) {
            var mode;
            var recordId;

            if (params.pageId) {
                mode = 'page';
                recordId = params.pageId;
            } else {
                mode = 'template';
                recordId = params.templateId;
            }

            var websiteId = params.websiteId;

            (new CMS.QrCodeWindow({
                websiteId: websiteId,
                mode: mode,
                recordId: recordId
            })).show();
        }
    },

    /**
     * Handler for changes of the iframe location (triggered by the iframe monitor);
     * Fires the "contentChanged" event if there is new a websiteId and/or a new pageId
     * displayed within the iframe
     * @private
     */
    handleLocationChange: function (url) {
        var params = CMS.app.trafficManager.extractPostParams(url);
        var pageId, websiteId;

        // SB.TheIframe caches the src url; But this value is not updated when using
        // links within the iframe
        // -> we have to do this manually or the next switch back will be ignored
        this.iframe.src = url;

        if (params) {
            // the backend "lowercases" the parameter names within the link urls when
            // rendering the page preview
            for (var key in params) {
                if (params.hasOwnProperty(key)) {
                    if (key.toLowerCase() === 'pageid') {
                        pageId = params[key];
                    }
                    if (key.toLowerCase() === 'websiteid') {
                        websiteId = params[key];
                    }
                }
            }
        }
        if (pageId && websiteId) {

            /**
             * Fired when the location of the preview iframe has switched to a new website
             * and/or a new page
             * NOTICE: {@link #setContent} will also trigger this event
             * @event
             * @name locationChanged
             *
             * @param {CMS.home.PreviewPanel} this This panel
             * @param {Object} location The location data providing <code>pageId</code>
             *      and <code>websiteId</code>
             */
            this.fireEvent('locationChanged', this, {
                websiteId: websiteId,
                pageId: pageId
            });

            // store params for qrCode
            this.updatePreviewParams({
                websiteId: websiteId,
                pageId: pageId
            });
        }
    },

    /**
     * Update the params (also websiteId)
     * @param params
     */
    updatePreviewParams: function (params) {
        // change of website or first time
        if (!this.currentPreviewParams || params.websiteId !== this.currentPreviewParams.websiteId) {
            this.listenForDataUpdates(params.websiteId);
        }
        this.currentPreviewParams = params;
    },

    /**
     * Add listeners to data stores which affect the preview
     * @param websiteId
     */
    listenForDataUpdates: function (websiteId) {
        this.mon(CMS.data.StoreManager.get('websiteSettings', websiteId, {disableLoad: true}), 'update', function (store, record, type) {
            if (type == Ext.data.Record.COMMIT) {
                this.refresh();
            }
        }, this);
    },

    /**
     * Force external links in the iframe to open in new window
     * @private
     */
    preventExternalNavigation: function (iframe) {
        var openNewWindow = function (event) {
            event.preventDefault();
            event.stopPropagation();
            window.open(this.getAttribute('href'), '_blank');
            return false;
        };
        var links = Ext.DomQuery.jsSelect('a', iframe.getFrameDocument());
        for (var i = 0; i < links.length; i++) {
            var linkDomEl = links[i];
            if (/[http|https|ftp]\:\/\//.test(linkDomEl.getAttribute('href'))) {
                // external link -> deactivate
                linkDomEl.addEventListener('click', openNewWindow);
            }
        }
    }
});

Ext.reg('CMSpreviewpanel', CMS.home.PreviewPanel);
