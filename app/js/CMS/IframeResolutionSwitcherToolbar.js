Ext.ns('CMS');

/**
 * @class CMS.IframeResolutionSwitcherToolbar
 * @extends Ext.Panel
 */
CMS.IframeResolutionSwitcherToolbar = Ext.extend(Ext.Panel, {
    /** @lends CMS.IframeResolutionSwitcherToolbar.prototype */

    cls: 'CMSiframeresolutionswitchertoolbar',
    border: false,
    layout: 'vbox',

    editMode: true,

    suggestedResolutions: [
        [1920, 1080, 'Full HD'],
        [1680, 1050, 'Notebook WSXGA+'],
        [1440, 900, 'Mac Book Pro 15"'],
        [1280, 800, 'Mac Book Pro 13"/Xperia Tablet Z'],
        [1024, 768, 'iPad'],
        [768, 1024, 'iPad portrait'],
        [568, 320, 'iPhone 5 landscape'],
        [480, 320, 'iPhone 4 landscape'],
        [414, 736, 'iPhone 6 Plus'],
        [375, 667, 'iPhone 6'],
        [320, 568, 'iPhone 5'],
        [320, 480, 'Generic Smartphone/iPhone 4'],
        [360, 640, 'Galaxy S4'],
    ],

    resolutions: [CMS.config.theDefaultResolution],

    currentResolution: CMS.config.theDefaultResolution,

    visualHelperState: true,
    visualHelperToolbarState: true,

    initComponent: function () {

        this.items = [{
            xtype: 'container',
            items: [{
                xtype: 'button',
                cls: 'CMSdefaultResolutions',
                ref: '../resolutionMenu',
                tooltip: {
                    text: CMS.i18n('Standardauflösungen testen'),
                    align: 'l-r?'
                },
                menu: {
                    cls: 'resolutionMenu',
                    xtype: 'menu',
                    items: [],
                    listeners: {
                        itemclick: this.resolutionButtonHandler,
                        scope: this
                    }
                },
                menuAlign: 'tr-br?'
            }, {
                xtype: 'box',
                autoEl: 'div',
                cls: 'CMSresolutioninfo',
                ref: '../resolutionInfo'
            }, {
                xtype: 'button',
                iconCls: 'editResolutions',
                ref: '../editResolutionButton',
                tooltip: {
                    text: CMS.i18n('Auflösungen bearbeiten'),
                    align: 'l-r?'
                },
                tooltipType: 'ext:qtip',
                handler: this.resolutionEditButtonHandler,
                scope: this,
                hidden: !this.editMode
            }],
            flex: 1
        }, {
            cls: 'CMSvisualHelperButtons',
            xtype: 'button',
            iconCls: 'visualHelpersToolbar',
            tooltip: {
                text: CMS.i18n(null, 'iframeresolutionswitchertoolbar.visualHelperToolbarBtn'),
                align: 'l-r?'
            },
            tooltipType: 'ext:qtip',
            width: '100%',
            enableToggle: true,
            pressed: true,
            handler: this.visualHelpersToolbarButtonHandler,
            scope: this,
            hidden: !this.editMode
        }, {
            cls: 'CMSvisualHelperButtons',
            xtype: 'button',
            iconCls: 'visualHelpers',
            tooltip: {
                text: CMS.i18n('Hilfsmarkierungen anzeigen'),
                align: 'l-r?'
            },
            tooltipType: 'ext:qtip',
            width: '100%',
            enableToggle: true,
            pressed: true,
            handler: this.visualHelpersButtonHandler,
            scope: this,
            hidden: !this.editMode
        }, {
            xtype: 'button',
            iconCls: 'view',
            tooltip: {
                text: CMS.i18n('Vorschau in neuem Fenster öffnen'),
                align: 'l-r?'
            },
            tooltipType: 'ext:qtip',
            width: '100%',
            handler: this.viewButtonHandler,
            scope: this
        }, {
            xtype: 'button',
            iconCls: 'qrCode',
            tooltip: {
                text: CMS.i18n('Vorschau auf mobilem Gerät öffnen'),
                align: 'l-r?'
            },
            tooltipType: 'ext:qtip',
            width: '100%',
            handler: this.qrCodeButtonHandler,
            scope: this
        }];

        CMS.IframeResolutionSwitcherToolbar.superclass.initComponent.apply(this, arguments);

        this.websiteStore = CMS.data.WebsiteStore.getInstance();

        if (this.websiteId) {
            this.setSite(this.websiteId);
        }
    },

    /**
     * Sets the viewport container in which the iframe is placed
     * @param {Ext.Container} viewport
     */
    setViewport: function (viewport) {
        this.viewport = viewport;

        // sync buttons also when whole viewport was resized (e.g. main browser window resized)
        this.mon(viewport, 'resize', function (cmp, width, height) {
            this.fireEvent('CMSresolutionchange', this.currentResolution);
        }, this);
    },

    /**
     * Updates the resolution buttons to the resolutions defined in the current website
     * @param {String} websiteId
     */
    setSite: function (websiteId) {
        this.websiteId = websiteId;
        this.updateResolutionButtons();

        this.mon(this.websiteStore, 'datachanged', this.updateResolutionButtons, this);
        this.mon(this.websiteStore, 'update', this.updateResolutionButtons, this);

        // always start with default resolution
        this.fireEvent('CMSresolutionchange', CMS.config.theDefaultResolution);
    },

    /**
     * Load resolution data from store
     * @private
     * @returns {Object|*}
     */
    loadResolutionData: function () {
        if (!this.websiteId) {
            return;
        }

        var websiteRecord = this.websiteStore.getById(this.websiteId);
        var resolutions = websiteRecord.get('resolutions');
        if (!resolutions || !resolutions.enabled) {
            return;
        }

        // add id if there is none (legacy resolution)
        Ext.each(resolutions.data, function (resolution, index) {
            if (!resolution.id) {
                resolution.id = 'res' + (index + 1);
                console.debug('[IframeResolutionSwitcherToolbar] legacy resolution data found: ', resolution);
            }
        });
        websiteRecord.set('resolutions', resolutions);

        // copy resolutions to break references
        resolutions = SB.util.cloneObject(resolutions.data);

        // add default resolution object
        this.resolutions = [CMS.config.theDefaultResolution].concat(resolutions);

        return resolutions;

    },

    /**
     * Updates the resolution buttons to the resolutions defined in the current website
     * @private
     */
    updateResolutionButtons: function () {
        this.resolutionMenu.menu.removeAll();

        if (!this.websiteId) {
            return;
        }


        // add suggested
        var items = [];
        Ext.each(this.suggestedResolutions, function (resolution) {
            items.push({
                text: resolution[2] + ' (' + resolution[0] + '×' + resolution[1] + ')',
                resolutionData: {
                    width: resolution[0],
                    height: resolution[1]
                }
            });
        });


        // add defined resolutions
        var resolutions = this.loadResolutionData();
        Ext.each(resolutions, function (resolution, index) {
            items.push({
                text: resolution.name + ' (' + resolution.width + ')',
                resolutionData: resolution,
                cls: 'switch-btn switch-btn-' + resolution.id,
            });
        });

        items.sort(function (a, b) {
            return b.resolutionData.width - a.resolutionData.width ;
        });

        this.resolutionMenu.menu.add(items);

        var websiteRecord = this.websiteStore.getById(this.websiteId);
        this.editResolutionButton.setVisible(this.editMode && CMS.app.userInfo.canEditTemplates(websiteRecord));

        // sync with current width of the iframe
        if (this.viewport) {
            var ctEl = this.viewport.findByType('container')[0].getEl();
            this.syncResolutionView(ctEl.getWidth(), ctEl.getHeight());
        }
        this.doLayout();
    },

    /**
     * Handler of the resolution buttons
     * @private
     */
    resolutionButtonHandler: function (button) {
        this.fireEvent('CMSresolutionchange', button.resolutionData);
    },

    /**
     * Handler of the view button
     * @private
     */
    visualHelpersButtonHandler: function (button) {
        this.visualHelperState = button.pressed;
        this.fireVisualHelperChange();
    },

    /**
     * Handler of the toolbar button
     * @private
     */
    visualHelpersToolbarButtonHandler: function (button) {
        this.visualHelperToolbarState = button.pressed;
        this.fireVisualHelperChange();
    },

    /**
     * @private
     */
    fireVisualHelperChange: function () {
        this.fireEvent('CMSvisualhelpers', this.visualHelperState, this.visualHelperToolbarState);
    },

    /**
     * Handler of the view button
     * @private
     */
    viewButtonHandler: function () {
        this.fireEvent('CMSview');
    },

    /**
     * Handler of the qrCode button
     * @private
     */
    qrCodeButtonHandler: function () {
        this.fireEvent('CMSshowqrcode');
    },

    /**
     * Handler of the resolution edit button
     * @private
     */
    resolutionEditButtonHandler: function (button) {
        (new CMS.IframeResolutionEditWindow({
            websiteRecord: this.websiteStore.getById(this.websiteId),
            resolutionWidth: this.resolutionWidth,
            callback: this.updateResolutionButtons,
            scope: this
        })).show();
    },

    /**
     * Syncs the resolutions
     * @param {Number} resolutionWidth
     * @param {Number} resolutionHeight
     */
    syncResolutionView: function (resolutionWidth, resolutionHeight) {
        this.resolutionWidth = resolutionWidth;
        this.resolutionInfo.update(resolutionWidth + '<br>×<br>' + resolutionHeight);

        // check which resolution is currently active
        var resolutionFound = false;
        for (var i = this.resolutions.length - 1; i >= 0; i--) {
            if (resolutionWidth <= this.resolutions[i].width) {
                this.updateCurrentResolution(this.resolutions[i]);
                resolutionFound = true;
                break;
            }
        }

        // default resolution
        if (!resolutionFound) {
            this.updateCurrentResolution(CMS.config.theDefaultResolution);
        }

        return resolutionFound;
    },

    /**
     * Updates current resolution (if changed) and fires the event
     * @private
     * @param resData
     */
    updateCurrentResolution: function (resData) {
        if (this.currentResolution !== resData) {
            this.fireEvent('CMSresolutionchanged',  resData, this.resolutions);
            this.resolutionInfo.getEl().set({'data-resolution': resData.id});
            this.currentResolution = resData;
        }
    },

    destroy: function () {
        this.websiteStore = null;
        this.viewport = null;

        CMS.IframeResolutionSwitcherToolbar.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSiframeresolutionswitchertoolbar', CMS.IframeResolutionSwitcherToolbar);
