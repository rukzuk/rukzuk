Ext.ns('CMS');

/**
 * A plugin which enables resizing of the iframe and shows buttons for the resolutions defined in the current website.
 * Wraps the iframe in a few containers to allow scrolling of high resolutions with a smaller viewport.
 * @class CMS.IframeResolutionSwitcherPlugin
 */
CMS.IframeResolutionSwitcherPlugin = Ext.extend(Object, {
    editMode: true,
    forceInitialSetSrc: false,
    invisibleEmptySrc: false,

    init: function (cmp) {
        this.cmp = cmp;

        // hide edit button when editing a page
        if (cmp.mode && cmp.mode == 'page') {
            this.editMode = false;
        }

        this.wrapIframe();

        this.resizeInfoEl = Ext.getBody().child('div.CMSresizeinfo'); // use existing element if possible
        if (!this.resizeInfoEl) {
            // ... or create a new one if not
            this.resizeInfoEl = Ext.getBody().createChild('<div class="CMSresizeinfo"></div>');
        }

        cmp.on('destroy', this.destroy, this);
    },

    /**
     * Wraps the iframe in some containers to allow scrolling. Also inserts the resolutionSwitcherToolbar.
     * @private
     */
    wrapIframe: function () {
        var cmp = this.cmp;
        var iframe = cmp.iframe;

        iframe.on('afterrender', this.initResizable, this);
        iframe.readyClassForParentComponent = true;
        iframe.forceInitialSetSrc = this.forceInitialSetSrc;
        iframe.invisibleEmptySrc = this.invisibleEmptySrc;

        // wrap the iframe in a few containers
        cmp.remove(iframe, false);
        cmp.layout = 'border';
        cmp.insert(0, {
            xtype: 'CMSiframewrapper',
            ref: 'viewport',
            bodyStyle: 'overflow-x: auto; overflow-y: none;',
            layout: 'fit',
            items: {
                xtype: 'container',
                ref: '../iframeContainer',
                cls: 'CMSiframeResizeContainer',
                items: iframe
            },
            region: 'center',
            listeners: {
                CMSiframewrapperscrollleft: this.handleIframeWrapperScrollLeft,
                scope: this
            },
            flex: 1
        }, {
            xtype: 'CMSiframeresolutionswitchertoolbar',
            ref: 'resolutionSwitcherToolbar',
            websiteId: cmp.websiteId,
            bubbleEvents: ['CMSvisualhelpers', 'CMSview', 'CMSshowqrcode', 'CMSresolutionchanged'],
            editMode: this.editMode,
            listeners: {
                CMSresolutionchange: this.handleResolutionChange,
                scope: this
            },
            region: 'east'
        }, {
            xtype: 'CMSrulerpanel',
            ref: 'rulerPanel',
            height: 24,
            websiteId: cmp.websiteId,
            bubbleEvents: ['CMSresolutionchanged'],
            listeners: {
                CMSresolutionchange: this.handleResolutionChange,
                scope: this
            },
            region: 'north',
        });

        cmp.iframe = iframe;
    },

    handleIframeWrapperScrollLeft: function (scrollLeft) {
        this.cmp.rulerPanel.setScrollLeft(scrollLeft);
    },

    /**
     * init all the resize logic
     * @private
     */
    initResizable: function () {
        var el = this.cmp.iframeContainer.getEl();
        var toolbar = this.cmp.resolutionSwitcherToolbar;

        this.resizer = new Ext.Resizable(el, {
            handles: 's e se',
            dynamic: true
        });

        toolbar.setViewport(this.cmp.viewport);

        // update the toolbar (show dimensions and activate the respective button) while resizing
        this.resizer.resizeElement = function () {
            var box = Ext.Resizable.prototype.resizeElement.call(this);
            toolbar.syncResolutionView(box.width, box.height);
            return box;
        };

        // update the toolbar after resizing
        this.resizer.on('resize', function (r, width, height) {
            this.cmp.resolutionSwitcherToolbar.syncResolutionView(width, height);
        }, this);
    },

    /**
     * will get called when the user clicks on a resolution button
     * @private
     */
    handleResolutionChange: function (resolution) {
        if (!this.resizer) {
            return;
        }

        var width = resolution.width;
        var clientWidth = this.cmp.viewport.getEl().getWidth();
        if (width === '100%') {
            width = clientWidth;
        } else if (width === Number.POSITIVE_INFINITY) { // 'Default' button
            var biggestRes = this.cmp.resolutionSwitcherToolbar.resolutions[1];
            if (biggestRes && clientWidth <= biggestRes.width) {
                width = biggestRes.width + 42;
            } else {
                width = clientWidth;
            }
        }

        var height = resolution.height;
        var vpHeight = this.cmp.viewport.getEl().getHeight();

        // no height? use viewport height
        if (!height) {
            height = vpHeight;
        }
        // do not change height to a bigger value than the viewport (prevents vertical scrollbars)
        if(height > vpHeight) {
            height = vpHeight - Ext.getPreciseScrollBarWidth();
        }

        this.resizer.resizeTo(width, height);
    },

    destroy: function () {
        if (this.resizer) {
            this.resizer.destroy();
        }
        if (this.resizeInfoEl) {
            this.resizeInfoEl.remove();
            this.resizeInfoEl = null;
        }
        delete this.cmp;
    }
});
Ext.preg('CMSiframeresolutionswitcher', CMS.IframeResolutionSwitcherPlugin);
