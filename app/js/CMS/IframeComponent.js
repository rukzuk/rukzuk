Ext.ns('CMS');

/**
 * An Component wich displays an iframe. Can communicate with the iframe via postMessage.
 *
 * @class CMS.IframeComponent
 * @extends Ext.Component
 */
CMS.IframeComponent = Ext.extend(Ext.BoxComponent, {

    /**
     * @cfg {String} src
     * The inital src attribute when creating the iframe element.
     * Defaults to <tt>about:blank</tt>.
     */
    src: Ext.SSL_SECURE_URL,

    /**
     * @cfg {String} bodyCls
     */
    bodyCls: undefined,

    initComponent: function () {
        this.autoEl = {
            tag: 'iframe',
            src: this.src,
            cls: this.bodyCls,
            style: {
                border: '0px none'
            }
        };
        this.on('afterrender', function () {
            this.applyListeners();
        }, this);
        CMS.IframeComponent.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Set the src which should be shown in the Iframe
     * @param {String} src The URL to show
     */
    setSrc: function (src) {
        if (src == this.src) {
            return;
        }
        this.src = src;
        this.getIframeEl().src = src;
    },

    getIframeEl: function () {
        return this.el.dom;
    },

    /**
     * The event handler for the "message" event of the iframe; The parameter
     * are defined by the event emitter; To be overwritten
     * @function
     * @protected
     */
    handleMessage: undefined,

    /**
     * Registers an event listener to recieve the messages from the store page within the iframe
     * @private
     */
    applyListeners: function () {
        this.removeListener();
        if (!this.handleMessageDelegate && this.handleMessage) {
            this.handleMessageDelegate = this.handleMessage.createDelegate(this);
            window.addEventListener('message', this.handleMessageDelegate, false);
        }
    },

    /**
     * Removes the listener (if there is one) from global window object
     * @private
     */
    removeListener: function () {
        if (this.handleMessageDelegate) {
            window.removeEventListener('message', this.handleMessageDelegate, false);
            delete this.handleMessageDelegate;
        }
    },

    /**
     * @override
     */
    destroy: function () {
        this.removeListener();
        this.setSrc(Ext.SSL_SECURE_URL);
        CMS.IframeComponent.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSiframecomponent', CMS.IframeComponent);