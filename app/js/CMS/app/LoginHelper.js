Ext.ns('CMS.app');

/**
* @class CMS.app.LoginHelper
* @extends Ext.util.Observable
* Responsible for displaying a login panel or window, and re-sending requests that had failed
* due to insufficient credentials
*
* See login.html for the actual form and ajax calls
*/
CMS.app.LoginHelper = Ext.extend(Ext.util.Observable, {

    isInitialLogin: true,

    constructor: function () {
        CMS.app.LoginHelper.superclass.constructor.apply(this, arguments);
        this.pendingOptions = [];
    },

    /**
     * @private
     * handles all postMessages of the embedded login iframe
     * @param event
     */
    handlePostMessageEvent: function (event) {
        if (event.origin == location.protocol + '//' + location.host) {
            if (Ext.isObject(event.data) && event.data.hasOwnProperty('action') && event.data.hasOwnProperty('successful')) {
                if (event.data.action === 'login' && event.data.successful) {
                    this.successfulLogin();
                } else if (event.data.action === 'pwrecovery' && event.data.successful) {
                    this.successfulPasswordRecovery();
                }
            }
        }
    },

    /**
    * @private
    * Generate login URL with added cachebuster parameter
    * @return String
    */
    getLoginUrl: function (username, pwToken) {
        username = username || CMS.app.urlParameterHelper.getParameter('u');
        var usernamePart = username ? '&username=' + encodeURIComponent(username) : '';
        var simple = this.isInitialLogin ? '' : 'mode=simple';
        var resetPw = pwToken ? 'pwToken=' + pwToken : '';
        return 'login.html?' + resetPw + simple + usernamePart + '&action=embedded&nocache=' + (+new Date()); // prevent caching
    },

    /**
     * @private
     * Shows the iframe with all the login logic
     * @param username - the usename if we know it (i.e we lost our session cookie)
     * @param [pwToken] - the pwToken is used for pwRecovery
     */
    showLoginIframe: function (username, pwToken) {

        // make sure there is only one iframe
        if (this.win) {
            this.win.destroy();
        }

        var loginUrl = this.getLoginUrl(username, pwToken);

        if (this.isInitialLogin) {
            this.win = this.createIframe(loginUrl, true);
        } else {
            // wrap panel by window if this is not the initial login
            this.win = new Ext.Window({
                cls: 'CMSloginwindow CMSloginwindowhidden',
                width: 350,
                height: 250,
                title: CMS.i18n('Sitzung abgelaufen, bitte erneut anmelden'),
                closeAction: 'destroy',
                closable: false,
                modal: true,
                resizable: false,
                border: false,
                layout: 'fit',
                items: [this.createIframe(loginUrl, false)],
                renderTo: Ext.getBody(),
                focus: Ext.emptyFn
            });
        }

        this.win.show();
        CMS.app.Application.hideInitialLoadingIndicator();
    },


    /**
     * Shows the unsupported Browser Window
     */
    showUnsupportedBrowserDialog: function () {
        var cmp = this.createIframe('unsupported_browser.html', true);
        cmp.show();
        CMS.app.Application.hideInitialLoadingIndicator();
    },

    /**
     * @private
     * Creates an Fullscreen IFrame
     * @param {String} src
     * @param {Boolean} [fullscreen]
     * @returns {Ext.Container}
     */
    createIframe: function (src, fullscreen) {
        var cmp = new Ext.Container({
            autoEl: {
                tag: 'iframe',
                id: 'logindummy',
                height: '100%',
                width: '100%',
                style: 'width:100%; height:100%; border:0; margin: 0; padding: 0;' + (fullscreen ? ' position: fixed; top:0; left:0; ' : ''),
                scrolling: 'no',
                src: src
            },
            renderTo: Ext.getBody()
        });

        return cmp;
    },

    /**
    * Show the login window and send a request
    * @param {String} username (optional) The userName to prefill into the login form
    * @param {Object} options (optional) An Ext.Ajax config object used to repeat a failed request upon successful login
    */
    startLogin: function (username, options) {
        this.pendingOptions.push(options);
        if (this.loggingIn) {
            return;
        }
        this.loggingIn = true;
        CMS.app.heartbeat.stopBeating();

        if (!username) {
            Ext.getBody().addClass(['CMSlogin']);
        }

        // register postMessage handler
        this.handlePostMessageEventDelegate =  this.handlePostMessageEvent.createDelegate(this);
        window.addEventListener('message', this.handlePostMessageEventDelegate, false);

        this.showLoginIframe(username);
    },

    /**
     * Called after successful login postMessage received
     */
    successfulLogin: function () {
        // hide window
        this.win.hide();
        // trigger destroy in 2 secs
        this.win.destroy.defer(2 * 1000, this.win); // Chrome needs to wait until the iframe has loaded to show the password manager. 2s should be enough
        Ext.getBody().removeClass(['CMSlogin']);
        this.loggingIn = false;
        this.isInitialLogin = false;

        // do pending requests
        while (this.pendingOptions.length) {
            var o = this.pendingOptions.shift();
            console.log('[LoginHelper] re-sending previous request', o);
            if (o.scope && o.scope.conn && o.scope.conn.store) {
                o.scope.conn.store.reload();
            } else {
                Ext.Ajax.request(o);
            }
        }
        if (!CMS.app.initializing) {
            CMS.app.heartbeat.startBeating();
        }

        // remove event listener for post message
        window.removeEventListener('message', this.handlePostMessageEventDelegate, false);
    },


    /**
     * Shows the password recovery window
     * @param pwToken - the pwToken sent by email
     * @param callback - callback function called after successful action
     */
    startPasswordRecovery: function (pwToken, callback) {

        // register postMessage handler
        this.handlePostMessageEventDelegate =  this.handlePostMessageEvent.createDelegate(this);
        window.addEventListener('message', this.handlePostMessageEventDelegate, false);

        this.showLoginIframe(null, pwToken);
        this.passwordRecoveryCallback = callback;
    },

    /**
     * Called after successful pw recovery postMessage received
     */
    successfulPasswordRecovery: function () {
        if (this.passwordRecoveryCallback) {
            this.passwordRecoveryCallback();
        }

        // remove event listener for post message
        window.removeEventListener('message', this.handlePostMessageEventDelegate, false);
        this.win.destroy();
    },

    /**
    * Logout the current user and restart the application
    */
    logout: function () {
        CMS.app.trafficManager.sendRequest({
            action: 'logout',
            modal: true,
            success: function () {
                if (CMS.app.FullScreenHelper.fullScreenSupported) {
                    CMS.app.FullScreenHelper.toggleFullScreen(false);
                }
                window.location.reload();
            },
            scope: this
        });
    },

    destroy: function () {
        if (this.win && this.win.destroy) {
            this.win.destroy();
        }
        Ext.iterate(this.connectionListeners, function (event, listener) {
            Ext.data.Connection.un(event, listener, this);
        }, this);
        delete this.pendingOptions;
        this.purgeListeners();
    }
});
