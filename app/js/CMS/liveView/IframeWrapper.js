Ext.ns('CMS.liveView');

/**
 * A panel that outlines the iframe
 *
 * @class CMS.liveView.IframeWrapper
 * @extends Ext.Panel
 */
CMS.liveView.IframeWrapper = Ext.extend(Ext.Panel, {
    /** @lends CMS.liveView.IframeWrapper.prototype */

    cls: 'CMSiframewrapper',
    flex: 1,
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },

    initComponent: function () {
        CMS.liveView.IframeWrapper.superclass.initComponent.call(this);
        this.handleScrollDelegate = this.handleScroll.createDelegate(this);
        this.on('afterrender', function () {
            this.body.dom.addEventListener('scroll', this.handleScrollDelegate);
        }, this);
    },

    destroy: function () {
        try {
            this.body.dom.removeEventListener('scroll', this.handleScrollDelegate);
        } catch(e) {}
        CMS.liveView.IframeWrapper.superclass.destroy.call(this);
    },

    /**
     *
     * @param domEvent
     */
    handleScroll: function (domEvent) {
        this.fireEvent('CMSiframewrapperscrollleft', domEvent.target.scrollLeft);
    },
});

Ext.reg('CMSiframewrapper', CMS.liveView.IframeWrapper);

