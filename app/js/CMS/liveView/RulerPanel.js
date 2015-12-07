Ext.ns('CMS.liveView');

/**
 * A panel that outlines its content with a ruler
 *
 * @class CMS.liveView.RulerPanel
 * @extends Ext.Panel
 */
CMS.liveView.RulerPanel = Ext.extend(Ext.Panel, {
    /** @lends CMS.liveView.RulerPanel.prototype */

    websiteId: null,
    cls: 'CMSrulerpanel',

    initComponent: function () {
        this.items = [{
            xtype: 'container',
            cls: 'CMSrulertopbar',
            ref: 'resolutionButtons',
            width: 4500,
            style: {
                overflow: 'hidden'
            },
            height: 24,
            layout: 'hbox',
            defaults: {
                tooltipType: 'ext:qtip',
                xtype: 'button',
                handler: this.resolutionButtonHandler,
                scope: this
            }
        }, {
            xtype: 'button',
            cls: 'CMSbtnsmall switch-btn-' + CMS.config.theDefaultResolution.id,
            tooltip: {
                text: CMS.i18n(null, 'rulerPanel.defaultResolution'),
                width: 120,
                align: 't-b?'
            },
            iconCls: 'maxWidth',
            text: '',
            width: 40,
            style: {
                position: 'absolute',
                right: -1,
                top: 0
            },
            resolutionData: CMS.config.theDefaultResolution,
            handler: this.resolutionButtonHandler,
            scope: this
        }];
        CMS.liveView.RulerPanel.superclass.initComponent.call(this);
        this.websiteStore = CMS.data.WebsiteStore.getInstance();
        this.mon(this.resolutionButtons, 'afterrender', function () {
            this.updateResolutionButtons();
        }, this);

        if (this.websiteId) {
            this.setSite(this.websiteId);
        }
    },

    setScrollLeft: function (scrollLeft) {
        var resBtnEl = this.resolutionButtons.getEl();
        if (resBtnEl) {
            resBtnEl.setStyle('margin-left', -scrollLeft);
        }
    },

    /**
     * Button Handler for resolution changes
     * @param button
     */
    resolutionButtonHandler: function (button) {
        this.fireEvent('CMSresolutionchange', button.resolutionData);
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
        if (!this.rendered) {
            return;
        }

        this.resolutionButtons.removeAll();

        if (!this.websiteId) {
            return;
        }

        var resolutions = this.loadResolutionData();

        var items = [];

        var calcRulerButtonWidth = function (resolution, index) {
            if (index === 0) {
                return resolution.width;
            }
            return (resolution.width - resolutions[index - 1].width);
        };

        // add a button for each resolution
        var resolutionsAsc = resolutions ? resolutions.reverse() : [];
        Ext.each(resolutionsAsc, function (resolution, index) {
            var btnWidth = calcRulerButtonWidth(resolution, index);
            var displayTextOnButton = (btnWidth > 34);
            items.push({
                xtype: 'button',
                text: displayTextOnButton ? resolution.width : '',
                resolutionData: resolution,
                cls: 'CMSbtnsmall ruler-btn switch-btn-' + resolution.id + (displayTextOnButton ? '' : ' ruler-btn-empty'),
                width: btnWidth,
                tooltip: {
                    text: resolution.name,
                    width: 120,
                    align: 't-b?'
                }
            });
        });

        // add the buttons
        this.resolutionButtons.add(items);

        // add default button
        this.resolutionButtons.add({
            text: '',
            resolutionData: CMS.config.theDefaultResolution,
            cls: 'CMSbtnsmall ruler-btn switch-btn-' + CMS.config.theDefaultResolution.id,
            tooltip: {
                text: CMS.i18n(null, 'rulerPanel.defaultResolution'),
                width: 120,
                align: 't-b?'
            },
            flex: 1,
        });

        this.doLayout();
    },

});

Ext.reg('CMSrulerpanel', CMS.liveView.RulerPanel);

