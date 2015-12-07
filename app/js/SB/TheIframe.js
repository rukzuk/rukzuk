Ext.ns('SB');

/**
* @class SB.TheIframe
* @extends Ext.BoxComponent
* A component wrapping an iframe HTMLelement.
* This does not work with cross-domain frames.
*/
SB.TheIframe = Ext.extend(Ext.BoxComponent, {

    /**
    * @cfg {Boolean} suppressFirstReadyEvent
    * <tt>false</tt> to fire {@link domready} when the initial page is loaded.
    * Defaults to <tt>true</tt>. This is useful if <tt>src</tt> is initially <tt>about:blank</tt>,
    * And you don't want the event to fire for the blank page.
    */
    suppressFirstReadyEvent: true,

    /**
    * @cfg {Number} pollInterval
    * Interval (in ms) for polling used to determine the loaded state of the
    * content document in browsers which are not supporting the "DOMContentLoaded"
    * event (i.e. IE < 9).
    * Defaults to 10.
    */
    pollInterval: 10,

    /**
    * @cfg {Number} pollTimeout
    * Timeout (in ms) for polling (see {@link #pollInterval}). When this timeout is
    * exceeded, this component will fire an {@link #timeout} event
    * Defaults to 5000.
    */
    pollTimeout: 5000,

    /**
    * @cfg {String} src
    * The inital src attribute when creating the iframe element.
    * Note that this component does not work with cross-domain src attributes.
    * Defaults to <tt>about:blank</tt>.
    */
    src: Ext.SSL_SECURE_URL,

    /**
    * @cfg {Array} proxyEvents
    * An array of events that should be caught from the iframe contents and re-fired by this component.
    * Defaults to <tt>['scroll']</tt>
    */
    proxyEvents: ['scroll'],

    /**
    * @property loading
    * @type Boolean
    * <tt>true</tt> if the iframe is currently loading, <tt>false</tt> if not
    */
    loading: false,

    /**
    * @property isOldIE
    * @type Boolean
    * <tt>true</tt> if the current browser is a version of the Internet
    * Explorer prior to 9, <tt>false</tt> if not
    */
    isOldIE: Ext.isIE6 || Ext.isIE7 || Ext.isIE8,


    /**
     * Flag for DomReady management
     */
    lastDomReadyWasSent: false,

    /**
     * Adds the iframeReady class also the the ownerCt (parent component)
     * Useful for background loading masks etc.
     */
    readyClassForParentComponent: false,

    /**
     * Force initial set src, helps to prevent <iframe> without a src attr
     */
    forceInitialSetSrc: false,

    /**
     * Makes empty (about:blank) iframes "invisible")
     */
    invisibleEmptySrc: false,

    initComponent: function () {
        if (!Ext.isDefined(this.loadMask)) {
            this.loadMask = {
                msg: CMS.i18n(null, 'SB.TheIframe.defaultLoadingText')
            };
        }

        SB.TheIframe.superclass.initComponent.apply(this, arguments);
    },

    onRender: function (container) {
        this.autoEl = {
            tag: 'iframe',
            frameborder: 0
        };

        SB.TheIframe.superclass.onRender.apply(this, arguments);

        if (this.invisibleEmptySrc) {
            this.addClass('invisibleEmptySrc');
        }

        var ifrEl = this.el.dom;

        var self = this;
        this.eventProxy = function (event) {
            var e = Ext.EventObject.setEvent(event);
            var args = Ext.toArray(arguments);
            args.shift();
            args.unshift(self);
            args.unshift(e.type);
            self.fireEvent.apply(self, args);
        };

        // iFrame DOMReady detection
        /**
         * Its not trivial to get an event if an iframe has loaded its DOM contents (i.e. domready) - even in recent
         * versions of WebKit (as of 02-2013) there is not possibility to get this kind of information
         * (see: https://bugs.webkit.org/show_bug.cgi?id=33604)
         *
         */

        // onload or onreadystatechange (IE Support)
        if (Ext.isIE) {
            ifrEl.onreadystatechange = this.handleReadyStateChange.createDelegate(this);
        } else {
            ifrEl.onload = this.handleLoad.createDelegate(this, ['onload']);
        }

        ifrEl.onerror = this.handleError.createDelegate(this);

        ifrEl = null;

        // Although there is an HTML5 Standard Event called 'DOMContentLoaded' which bubbles[1], it can't be used within iFrames
        // html5 specs: "propagation will continue up to and including the Document."
        // It is important to understand that an iframe has an own document element.
        // specs: "the embedded document remains independent of the main document"
        // [1] http://www.whatwg.org/specs/web-apps/current-work/multipage/the-end.html#the-end

        // use the non-standard Gecko-specific DOMFrameContentLoaded which fires when a (i)Frame has gotten the  DOMContentLoaded
        // other browsers (at least some versions of firefox) used to (!) bubble the DOMContentLoaded also for iframes - this is not valid for Firefox 17
        //
        // Testcase for DOMFrameContentLoaded:                       https://bug431833.bugzilla.mozilla.org/attachment.cgi?id=319075
        // Testcase for DOMContentLoaded on iframe.contentDocument:  https://bug-33604-attachments.webkit.org/attachment.cgi?id=158581

        if (Ext.isGecko) {
            this.domReadyHandler = this.handleDomFrameContentLoaded.createDelegate(this);
            window.addEventListener('DOMFrameContentLoaded', this.domReadyHandler, false);
        }

        // register to own event using cross document postMessage (workaround mainly for webkit)
        // NOTE: This is a workaround which can be removed as soon as all supported browsers
        //       correctly implement DOMFrameContentLoaded (and it is adopted by the WHATWG-HTML5?)
        this.handlePostMessageEventDelegate =  this.handlePostMessageEvent.createDelegate(this);
        window.addEventListener('message', this.handlePostMessageEventDelegate, false);

        this.setSrc(this.src || Ext.SSL_SECURE_URL, this.forceInitialSetSrc);
    },

    listeners: {
        beforedestroy: function (self) {
            try {
                var ifrEl = self.el.dom;
                ifrEl.onload = ifrEl.onerror = ifrEl.onreadystatechange = null;
                self.onBeforeReplace();
                self.stopPolling();
                if (Ext.isGecko) {
                    window.removeEventListener('DOMFrameContentLoaded', self.domReadyHandler, false);
                }
                window.removeEventListener('message', self.handlePostMessageEventDelegate, false);
            } catch (e) { // self.el might be unaccessible if the user has navigated away
                return;
            }
        }
    },

    handlePostMessageEvent: function (event) {

        // check for correct data format
        if (Ext.isObject(event.data) && event.data.hasOwnProperty('action') && event.data.hasOwnProperty('id')) {
            // is the action and id as expected?
            var iframeElId = this.el && this.el.id;
            if (event.data.action === 'emulatedDOMFrameContentLoaded' && event.data.id === iframeElId) {
                this.handleLoad('emulatedDOMFrameContentLoaded');
            }
        }

    },

    /**
    * @private
    * Initiate polling process to determine ready state in browsers other than Firefox.
    */
    startPolling: function () {
        if (!this.pollingId) {
            this.pollCounter = 0;
            this.pollingId = window.setInterval(this.poller.createDelegate(this), this.pollInterval);
            this.poller();
        }
    },

    /**
    * @private
    * Cancel currently running polling process
    */
    stopPolling: function () {
        if (this.pollingId) {
            window.clearInterval(this.pollingId);
        }
    },

    /**
    * @private
    * Detect the load state of the iframe contents
    */
    poller: function () {
        this.pollCounter += this.pollInterval;
        if (this.pollCounter > this.pollTimeout) {
            this.stopPolling();
            if (!this.isDestroyed) {
                /**
                * @event timeout
                * Fired when the frame contents have not loaded until {@link pollTimeout} has exceeded
                * @param {Ext.BoxComponent} this
                */
                this.fireEvent('timeout', this);
            }
        }
        var doc = this.getFrameDocument();
        if (doc && doc.body && doc.body.innerHTML && doc.body.innerHTML.length) {
            this.stopPolling();
            this.handleLoad('IEpolling');
        }
    },

    /**
    * @private
    * Gecko
    */
    handleDomFrameContentLoaded: function (evt) {
        if (evt.target !== this.el.dom) {
            return;
        }

        this.handleLoad('DOMFrameContentLoaded');
    },

    /**
    * @private
    * Generalized handler for different ready events from all browsers
    */
    handleLoad: function (eventName) {

        // make sure we don't fire multiple times
        var iframeWindow = this.getFrameWindow();
        if (this.suppressFirstReadyEvent && iframeWindow && iframeWindow.location.href == Ext.SSL_SECURE_URL) {
            console.log('[TheIframe] handleLoad suppressFirstReadyEvent e.g. (about:blank)');
            return; // end here!
        }

        // make sure we don't fire multiple
        if (this.domreadyFired) {
            console.log('[TheIframe] handleLoad domreadyFired=true ignore event:', eventName);
        } else {
            console.log('[TheIframe] handleLoad event:', eventName);

            // check if we can access the iframe (SOP rules - i.e. external content)
            if (!this.getFrameWindow() || !this.getFrameDocument()) {
                this.handleError(new Error('Could not access frame contents'));
                return;
            }

            // remember domready event has been send (this method could be called multiple times!)
            this.domreadyFired = true;

            console.log('[TheIframe] handleLoad iframe fire beforedomready');
            /**
             * @event beforedomready
             * Fired just before the domready event is fired - can be used to cancle domready
             * @param {Ext.BoxComponent} this
             * @param {Error} error The error object created by the browser.
             */
            var sendDomReady = this.fireEvent('beforedomready', this, this.el);

            // remember domready cancel status
            this.lastDomReadyWasSent = sendDomReady;

            if (sendDomReady) {
                console.log('[TheIframe] handleLoad iframe fire domready');
                /**
                 * @event domready
                 * Fired when the DOM is ready
                 * @param {Ext.BoxComponent} this
                 * @param {Error} error The error object created by the browser.
                 */
                this.fireEvent('domready', this, this.el);

                this.onReady();

                if (this.callback) {
                    this.callback(this.el);
                }
            } else {
                console.log('[TheIframe] handleLoad iframe domready was CANCELED!');
            }
        }

        // fire onload event, so other components can react - even if the domready is already sent
        // (NOTE: domready and onload is not necessarily the same!)
        // -> onload means all resources have been loaded and rendered
        // -> dont sent the onload event if domready is cancled by beforedomready!
        if (eventName === 'onload' && this.lastDomReadyWasSent) {
            console.log('[TheIframe] handleLoad iframe loading completed (onload)');
            this.fireEvent('onload', this, this.el);
        }
    },

    /**
    * @private
    * IE
    */
    handleReadyStateChange: function () {
        var state = this.el.dom.readyState;
        if (state === 'complete') {
            this.handleLoad('onreadystatechange');
        }
    },

    /**
    * @private
    */
    handleError: function (error) {
        /**
        * @event error
        * Fired when a load error occurs
        * @param {Ext.BoxComponent} this
        * @prarm {Error} error The error object created by the browser.
        */
        this.fireEvent('error', this, error);
        delete this.callback;
    },

    /**
    * Get the iframe's <tt>window</tt> object
    */
    getFrameWindow: function () {
        return this.el && this.el.dom.contentWindow;
    },

    /**
    * Get the iframe's <tt>document</tt> object
    */
    getFrameDocument: function () {
        return this.el && this.el.dom.contentDocument;
    },

    /**
    * Get the iframe's <tt>body</tt> object if present, or <tt>null</tt> otherwise
    */
    getFrameBody: function () {
        var result = null;
        try {
            var doc = this.getFrameDocument();
            if (!doc.body) {
                doc.open();
                doc.write('<html><head><title></title></head><body></body></html>');
                doc.close();
            }
            result = doc.body;
        } catch (ignore) {}
        return result;
    },

    /**
    * Get DOM elements by attribute/value
    * @param {HTMLElement} node The DOM node to search. Defaults to the iframe's content document
    * @parram {String} attrName The attribute name to search
    * @param {String} attrValue The attribute value that results must contain
    * @param {String} tagName If this parameter is given, only elements matching this tag name are searched.
    * Defaults to <tt>'*'</tt>.
    */
    getElementsByAttribute: function (node, attrName, attrValue, tagName) {
        var result = [];
        var els;
        var i, l;
        node = node || this.getFrameDocument();
        if (node.document) {
            node = node.document; // sometimes node==frameWindow in IE8
        }
        tagName = tagName || '*';
        attrName = attrName.toLowerCase();
        if (attrName === 'class') {
            if (node.getElementsByClassName) {
                return Ext.toArray(node.getElementsByClassName(attrValue));
            }
            attrName = 'className';
        }
        if (!!attrValue) {
            if (node.querySelectorAll && !Ext.isIE8) {
                return node.querySelectorAll('[' + attrName + '~="' + attrValue + '"]');
            }
            var pattern = new RegExp('(^|\\s)' + attrValue + '(\\s|$)');
            els = node.getElementsByTagName(tagName);
            for (i = 0, l = els.length; i < l; i++) {
                if (pattern.test(els[i][attrName])) {
                    result.push(els[i]);
                }
            }
        } else {
            if (node.querySelectorAll) {
                return node.querySelectorAll('[' + attrName + ']');
            }
            els = node.getElementsByTagName(tagName);
            for (i = 0, l = els.length; i < l; i++) {
                if (els[i].hasAttribute(attrName)) {
                    result.push(els[i]);
                }
            }
        }
        return result;
    },

    /**
     * Change the iframe's src attribute
     * @param {String} src The new src to be set. This should be same-origin.
     * @param {Boolean} [force] - change even the current (assumed) state is the same
     */
    setSrc: function (src, force) {
        if (!force && src == this.src) {
            return;
        }
        this.src = src;
        this.onBeforeReplace();
        this.el.dom.src = src;
    },

    /**
    * @private
    * Creates a form with all params as a form field and submit it
    * @param {Object} options see {@link submitAsTarget}
    */
    buildAndSubmitForm: function (options) {
        options = Ext.apply({
            method: 'POST',
            encoding: 'multipart/form-data'
        }, options);

        var d = this.getFrameDocument();

        var body = this.getFrameBody(); // IE needs this before createElement. Don't ask.
        var form = d.createElement('form');

        Ext.copyTo(form, options, 'method,encoding');
        form.action = options.url || options.action;
        form.style.cssText = 'display: none !important';

        Ext.iterate(options.params, function (name, value) {
            var field = d.createElement('textarea');
            field.name = name;
            field.innerHTML = value;
            form.appendChild(field);
        });

        body.appendChild(form);

        if (options.callback) {
            this.callback = options.callback.createDelegate(options.scope || this);
        }

        this.onBeforeReplace();

        form.submit();
    },

    /**
    * Use the frame as a target for form submission. This is useful for sending POST data.
    * The iframe's contents will be replaced by the server response.
    * @param {Object} options The following options can be provided:<ul>
        <li><tt>method</tt>: <tt>String</tt>
        <li><tt>encoding</tt>: <tt>String</tt><br> defaults to <tt>'multipart/form-data'</tt>
        <li><tt>url</tt>: <tt>String</tt><br> the URL to send the form to
        <li><tt>action</tt>: <tt>String</tt><br> same as <tt>url</tt>
        <li><tt>params</tt>: <tt>Object</tt><br> the actual form data to be sent
        </ul>
    */
    submitAsTarget: function (options) {
        // Compress request-params if compression enabled
        if (CMS.app.trafficManager.requestCompression && options.params[CMS.config.postKeyName].length >= CMS.config.requestCompressionMinSize) {
            var self = this;
            CMS.app.trafficManager.compressData(options.params[CMS.config.postKeyName], function (paramsCompressed, headers) {
                Ext.apply(options.params, headers);

                options.params[CMS.config.postKeyName] = paramsCompressed;

                self.buildAndSubmitForm(options);
                self = null;
            }, function () {
                self.buildAndSubmitForm(options);
                self = null;
            }, true);
        } else {
            this.buildAndSubmitForm(options);
        }

    },

    /**
    * @private
    * Called internally prior to setSrc() and submitAsTarget()
    */
    onBeforeReplace: function () {
        /**
         * @event beforereplace
         * Fired before replacing the iframe url
         *
         * @param {Object} this
         *      the current TheIframe instance
         */
        this.fireEvent('beforereplace', this);

        this.removeClass('iframeReady');
        if (this.readyClassForParentComponent) {
            this.ownerCt.removeClass('iframeReady');
        }

        if (this.loading) {
            //this.getFrameWindow().stop();
        } else {
            this.removeListeners();
        }
        this.domreadyFired = false;
        if (this.isOldIE) {
            // HACK: this MAY fail if polling starts prior to setting src
            this.startPolling.defer(1, this);
        }
        this.loading = true;

        //mask the Iframe
        if (this.loadMask) {
            this.el.parent().mask(this.loadMask.msg, this.loadMask.msgCls);
        }
    },


    /**
    * @private
    * Called internally when DOM is ready (just before 'domready' fires)
    */
    onReady: function () {
        this.loading = false;
        this.addListeners();

        //hide mask
        this.addClass('iframeReady');
        if (this.readyClassForParentComponent) {
            this.ownerCt.addClass('iframeReady');
        }
        if (this.loadMask) {
            this.el.parent().unmask();
        }
    },

    /**
    * @private
    * Helper method to add event listeners
    */
    addListeners: function () {
        try {
            for (var i = 0; i < this.proxyEvents.length; i++) {
                var name = this.proxyEvents[i];
                var target = (Ext.isIE && name == 'scroll') ? this.getFrameWindow() : this.getFrameDocument();
                Ext.lib.Event.addListener(target, name, this.eventProxy);
            }
        } catch (e) {
            throw 'iFrame component could not initialize events. SOP issue?';
        }
    },

    /**
    * @private
    * Helper method to remove eventlisteners
    */
    removeListeners: function () {
        for (var i = 0; i < this.proxyEvents.length; i++) {
            var name = this.proxyEvents[i];
            var target = (Ext.isIE && name == 'scroll') ? this.getFrameWindow() : this.getFrameDocument();
            Ext.lib.Event.removeListener(target, name, this.eventProxy);
        }
    },

    destroy: function () {
        this.stopPolling();
        this.removeListeners();
        this.el.dom.src = Ext.SSL_SECURE_URL;
        SB.TheIframe.superclass.destroy.apply(this, arguments);
        delete this.proxyEvents;
        delete this.eventProxy;
        delete this.domReadyHandler;
        delete this.callback;
    }
});
