Ext.ns('CMS');

/**
 * @class CMS.GettingStartedPopover
 * @extends Ext.ToolTip
 * @constructor
 * Create the getting started popover
 * @param {Object} config The configuration options
 */
CMS.GettingStartedPopover = Ext.extend(Ext.ToolTip, {

    closable: false,
    draggable: false,
    autoHide: false,
    anchor: 'top',
    cls: 'CMSgettingstartedpopover',
    measureWidth: false,
    destroyOnHide: true,

    initComponent: function () {

        this.items = [{
            xtype: 'CMSiframecomponent',
            ref: 'iframeCmp',
            src: CMS.config.gettingStartedUrl + '?hl=' + CMS.app.lang,
            bodyCls: 'CMSgettingstartedpopoveriframe',
            region: 'center',
        }];

        CMS.GettingStartedPopover.superclass.initComponent.apply(this, arguments);

        Ext.EventManager.addListener(window, 'resize', this.reposition, this, {buffer: 500});
    },

    /**
     * Overwriting to disable binding the popover to the specified element.
     * @param {Mixed} t The Element, HtmlElement, or ID of an element to bind to
     */
    initTarget: function (target) {
        var t;
        if ((t = Ext.get(target))) {
            this.target = t;
        }
        if (this.anchor) {
            this.anchorTarget = this.target;
        }
    },

    getAnchorPosition: function() {
        this.tipAnchor = 't';
        return 'top';
    },

    /**
     * Overwriting to set special offsets
     */
    getTargetXY: function () {
        var offsets = this.getOffsets(),
            xy = this.el.getAlignToXY(this.anchorTarget, this.getAnchorAlign()),
            dw = Ext.lib.Dom.getViewWidth() - 5,
            sz = this.getSize();

        this.anchorEl.removeClass(this.anchorCls);

        var pos = [
            dw - sz.width + offsets[0],
            xy[1] + offsets[1]
        ];

        this.anchorCls = 'x-tip-anchor-'+this.getAnchorPosition();
        this.anchorEl.addClass(this.anchorCls);
        return pos;
    },

    getOffsets : function() {
        return [-20, 25];
    },

    /**
     * set the anchor position
     */
    syncAnchor : function(){
        var tx = this.anchorTarget.getXY()[0],
            tw = this.anchorTarget.getSize().width,
            x = this.el.getXY()[0],
            w = this.getSize().width;

        var maxOffsetX = -25,
            minOffsetX = (w - 25) * -1,
            tc = tx + (tw / 2);

        var offsetX = (x + w - tc) * -1;

        var hideAnchor = false;
        if (offsetX > maxOffsetX) {
            offsetX = maxOffsetX;
            hideAnchor = true;
        }
        if (offsetX < minOffsetX) {
            offsetX = minOffsetX;
            hideAnchor = true;
        }

        if (hideAnchor) {
            this.anchorEl.setXY([-1000,-1000]);
        } else {
            this.anchorEl.alignTo(this.el, 'b-tr', [offsetX, 0]);
        }
    },

    /**
     * Overwriting to destroy popover on clicking somewhere on the document
     * @param e
     */
    onDocMouseDown: function (e) {
        if (!this.el) {
            return;
        }
        if (e.within(this.el.dom)) {
            return;
        }
        if (this.anchorTarget && e.within(this.anchorTarget.dom)) {
            return;
        }
        this.hide();
    },

    /**
     * reposition the popover if visible
     */
    reposition: function () {
        if (this.isVisible()) {
            this.show();
        }
    },

    /**
     * Overwriting to destroy popover on hide
     */
    hide: function () {
        if (this.destroyOnHide) {
            this.destroy();
        } else {
            CMS.GettingStartedPopover.superclass.hide.call(this);
        }
    },

    /**
     * Destroy this popover
     */
    onDestroy: function() {
        Ext.EventManager.removeListener(window, 'resize', this.reposition, this);
        CMS.GettingStartedPopover.superclass.onDestroy.call(this);
    }

});
