Ext.ns('CMS');

/**
 * @class CMS.Popover
 * @extends Ext.ToolTip
 * @constructor
 * Create a new popover
 * @param {Object} config The configuration options
 */
CMS.Popover = Ext.extend(Ext.ToolTip, {

    closable: true,
    draggable: false,
    autoHide: false,
    anchor: 'left',
    cls: 'CMSpopover',
    offsets: {
        top: [0, 0],
        bottom: [0, 0],
        right: [0, 0],
        left: [0, 0]
    },

    destroyOnDocMouseDown: true,

    initComponent: function () {
        CMS.Popover.superclass.initComponent.apply(this, arguments);
        this.show();

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

    /**
     * Overwriting to set special offsets
     */
    getTargetXY: function () {
        var axy = CMS.Popover.superclass.getTargetXY.call(this);
        var ap = this.getAnchorPosition();
        if (this.offsets[ap]) {
            axy[0] += this.offsets[ap][0];
            axy[1] += this.offsets[ap][1];
        }
        return axy;
    },

    /**
     * Overwriting to destroy popover on clicking somewhere on the document
     * @param e
     */
    onDocMouseDown: function (e) {
        if (this.destroyOnDocMouseDown && this.el && !e.within(this.el.dom)) {
            this.destroy();
        }
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
        this.destroy();
    },

    /**
     * Destroy this popover
     */
    onDestroy: function() {
        Ext.EventManager.removeListener(window, 'resize', this.reposition, this);
        CMS.Popover.superclass.onDestroy.call(this);
    }

});
