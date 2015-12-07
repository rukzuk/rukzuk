Ext.ns('CMS');

/**
 * A window which displays an iframe
 * @class CMS.IframeWindow
 * @extends CMS.MainWindow
 */
CMS.IframeWindow = Ext.extend(CMS.MainWindow, {

    /**
    * @cfg {String} src
    * The inital src attribute when creating the iframe element.
    * Defaults to <tt>about:blank</tt>.
    */
    src: Ext.SSL_SECURE_URL,

    /**
     * The event handler for the "message" event of the iframe; The parameter
     * are defined by the event emitter; To be overwritten
     * @function
     * @protected
     */
    handleMessage: undefined,


    closeAction: 'destroy',
    modal: true,

    initComponent: function () {
        this.items = [{
            xtype: 'CMSiframecomponent',
            ref: 'iframeCmp',
            src: this.src,
            bodyCls: this.bodyCls,
            handleMessage: this.handleMessage ? this.handleMessage.createDelegate(this) : Ext.emptyFn
        }];
        CMS.IframeWindow.superclass.initComponent.apply(this, arguments);
    },

    onRender: function () {
        CMS.IframeWindow.superclass.onRender.apply(this, arguments);
    },

    /**
     * Set the src which should be shown in the Iframe
     * @param {String} src The URL to show
     */
    setSrc: function (src) {
        this.iframeCmp.setSrc(src);
    },

    getIframeCmp: function () {
        return this.iframeCmp;
    },

    /**
     * @override
     */
    destroy: function () {
        this.iframeCmp.destroy();
        CMS.IframeWindow.superclass.destroy.apply(this, arguments);
    }
});
