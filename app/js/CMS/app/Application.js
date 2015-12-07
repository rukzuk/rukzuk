Ext.ns('CMS.app');

/**
 * Bootstrap class for application initialization. Not reusable.
 * @class CMS.app.Application
 * @extends Object
 * @singleton
 */
CMS.app.Application = {

    /**
     * Initialize CMS application
     * @param {Object} config May contain a <tt>plugins</tt> attribute. See {@link Ext.Component#plugins}
     */
    init: function (cfg) {
        /** @lends CMS.app */
        var serverData = (CMSSERVER && CMSSERVER.data) || {};

        /**
        * Indicates whether the app is currently initializing. (Viewport not ready yet)
        * @property initializing
        * @type Boolean
        */
        CMS.app.initializing = true;

        /**
         * The global browserHelper instance
         * @property browserHelper
         * @type CMS.app.BrowserHelper
         */
        CMS.app.browserHelper = new CMS.app.BrowserHelper();

        /**
         * The global urlParameterHelper instance
         * @property urlParameterHelper
         * @type CMS.app.UrlParameterHelper
         */
        CMS.app.urlParameterHelper = new CMS.app.UrlParameterHelper();

        /**
         * Application mode: 'full' (was used for deprecated 'trial' mode)
         * @property mode
         * @type String
         */
        CMS.app.mode = serverData.mode || 'full';

        /**
         * This array is used to whitelist Ext.Ajax connections which should not be canceled on unload.
         * Push transactionIds of any connections that shall continue during shutdown.
         * @property connectionWhitelist
         * @type Array
         */
        CMS.app.connectionWhitelist = [];

        /**
        * An ID that is unique to the browser tab
        * @property runId
        * @type String
        */
        if (!/^CMSRUNID-/.test(window.name)) {
            window.name = 'CMSRUNID-' + SB.util.UUID() + '-CMSRUNID';
        }
        CMS.app.runId = window.name;

        /**
        * The global trafficManager instance
        * @property trafficManager
        * @type CMS.app.TrafficManager
        */
        CMS.app.trafficManager = new CMS.app.TrafficManager();


        CMS.app.setDevModeEnabled = this.setDevModeEnabled.createDelegate(this);
        CMS.app.isDevModeEnabled = this.isDevModeEnabled.createDelegate(this);

        var subtitles = [];
        if (CMS.config.debugMode) {
            console.info('Debug-Modus enabled');
            subtitles = ['debug mode'];
        } else {
            // disable the default browser context menu in non-debug mode
            window.oncontextmenu = function (e) {
                var el = e.srcElement || e.target;
                if (!(/textarea|input/i).test(el.nodeName)) {
                    e.preventDefault();
                }
            };
        }

        this.setProductName(CMS.i18nTranslateMacroString(CMS.config.productName), subtitles);

        CMS.app.ErrorManager.init();

        CMS.app.FullScreenHelper.init();

        if (CMS.config.debugMode) {
            this.wrapUnloadHandler();
        } else {
            // ignore shutdown errors
            Ext.lib.Event.addListener(window, 'unload', this.shutdown);
        }
        Ext.lib.Event.addListener(window, 'beforeunload', this.onbeforeunload);

        this.initPlugins(cfg);
        this.afterInitPlugins();
    },

    /**
    * Set document title and CSS containing the product title, and optionally additional subtitles
    * @param {String} name The application name. If left blank, the currently set name is reused.
    * @param {Array} subtitles Subtitles to be appended. If omitted, the currently set subtitles are reused.
    * @param {String} [prefix] Prefix to be prepended. If left blank, any existing prefix is removed.
    * @private
    */
    setProductName: function (name, subtitles, prefix) {
        subtitles = subtitles || this.productSubtitles || [];
        name = name || this.productName;
        if (typeof subtitles == 'string') {
            subtitles = [subtitles];
        }
        /**
        * @property productName
        * @type String
        * The currently used product name
        */
        this.productName = name;
        /**
        * The currently used product subtitles
        * @property productSubtitles
        * @type Array
        */
        this.productSubtitles = subtitles;
        var titleText = name;
        if (prefix) {
            titleText = prefix + ' - ' + titleText;
        }
        if (subtitles.length) {
            titleText += ' (' + subtitles.join(', ') + ')';
        }

        document.title = titleText;
    },

    /**
     * Process optionally given plugins, like in Ext.Component
     * @param {Object} cfg The config object passed to this app
     * @private
     */
    initPlugins: function (cfg) {
        if (cfg && cfg.plugins) {
            var plugins = Ext.isArray(cfg.plugins) ? cfg.plugins : [cfg.plugins];
            Ext.each(plugins, function (plugin) {
                Ext.Component.prototype.initPlugin.call(this, plugin);
            }, this);
        }
    },

    /**
     * Initialize the languge dependent settings (e.g. {@link CMS.app.lang}) accoding
     * to the current requested langugae or user language
     * @param {Object} response The response of the "user/info"-request
     * @private
     */
    initApplicationLanguage: function (response) {
        var lang = CMS.app.urlParameterHelper.getParameter('hl') || '';
        var libs = CMS.language.libs;

        // determine application language
        if (!libs[lang]) {
            // requested language is not available
            // -> fallback to simple format (e.g. "de-AT" -> "de")
            lang = lang.split('-')[0];
        }
        if (!libs[lang]) {
            // requested language is still not available
            // -> fallback to user language
            lang = response.data.userInfo.language;
        }
        if (!libs[lang]) {
            // still no valid language found (e.g. new user without language setting)
            // -> fallback to system language
            lang = CMSSERVER.data.language;
        }
        if (!libs[lang]) {
            // still no luck...
            // -> just use english for god's sake
            lang = 'en';
        }

        // apply language dependent configurations (e.g. help window locations, tutorial links, ...)
        if (libs[lang].config) {
            Ext.apply(CMS.config, libs[lang].config);
        }

        /**
         * Application language (e.g. "en-US", "de-DE"); Defaults to "en-US"
         * @property lang
         * @type String
         */
        CMS.app.lang = lang;

        // internationalize Ext components based on provided language
        CMS.i18nExtOverrides();
    },

    /**
     * Executed after {@link #initPlugins}. A plugin may decide to overwrite this method
     * @private
     */
    afterInitPlugins: function () {
        /**
         * The global Heartbeat instance
         * @property heartbeat
         * @type CMS.app.Heartbeat
         */
        CMS.app.heartbeat = new CMS.app.Heartbeat();

        /**
         * The global LockManager instance
         * @property lockManager
         * @type CMS.app.LockManager
         */
        CMS.app.lockManager = new CMS.app.LockManager();

        /**
         * The global downloadHelper instance
         * @property downloadHelper
         * @type CMS.app.DownloadHelper
         */
        CMS.app.downloadHelper = new CMS.app.DownloadHelper();

        /**
         * The global loginHelper instance
         * @property loginHelper
         * @type CMS.app.LoginHelper
         */
        CMS.app.loginHelper = new CMS.app.LoginHelper();

        /**
         * The global clipboard instance
         * @property clipboard
         * @type Ext.util.MixedCollection
         */
        CMS.app.clipboard = new Ext.util.MixedCollection();


        // start the app
        this.preStartApp();

    },

    /**
     * Checks for supported browser and pwToken
     * @private
     */
    preStartApp: function () {
        // show unsupported browser page
        if (!this.checkSupportedBrowser()) {
            CMS.app.loginHelper.showUnsupportedBrowserDialog();
            return; // end here
        }

        if (this.checkBrowserMobileAndTable()) {
            alert(CMS.i18n('application.mobileBrowserWarning'));
        }

        // handle pw token if any
        var pwToken = CMS.app.urlParameterHelper.getParameter('pwToken') || CMS.app.urlParameterHelper.getParameter('t');
        if (pwToken) {
            this.handlePwToken(pwToken, function () {
                this.getUserInfoAndStartApp();
            });
        } else {
            // no pwToken, get user info (session) and start app
            this.getUserInfoAndStartApp();
        }
    },

    /**
     * Checks if the browser is supported
     * @private
     * @returns {boolean}
     */
    checkSupportedBrowser: function () {
        var browserInfo = CMS.app.browserHelper.getInfo();
        return ((browserInfo.chrome && browserInfo.version >= 28)
            || (browserInfo.safari && browserInfo.version >= 8)
            || (browserInfo.firefox && browserInfo.version >= 29)
            || (browserInfo.ios && browserInfo.osversion >= 8)
            || (browserInfo.phantom));
    },

    /**
     * Checks if the
     * @returns {boolean}
     */
    checkBrowserMobileAndTable: function () {
        var browserInfo = CMS.app.browserHelper.getInfo();
        return !!(browserInfo.mobile || browserInfo.tablet);
    },

    /**
     * Shows the "Change Password" panel and calls the callback after success or if pwToken is invalid
     * @param {String} pwToken
     * @param {Function} callback
     * @private
     */
    handlePwToken: function (pwToken, callback) {
        var self = this;
        CMS.app.trafficManager.sendRequest({
            action: 'validateOptin',
            data: {
                code: pwToken
            },
            success: function () {
                // create a login helper because this plugin stops app.afterInitPlugins
                CMS.app.loginHelper = new CMS.app.LoginHelper();
                // show password recovery panel
                CMS.app.loginHelper.startPasswordRecovery(pwToken, function () {
                    CMS.Message.toast(CMS.i18n('Passwort geändert', 'app.pwRecoveryToastSuccessTitle'), CMS.i18n('Dein Passwort wurde geändert.', 'app.pwRecoveryToastSuccessBody'));
                    //show login page without reload
                    callback.call(self);
                });
            },
            failure: function () {
                callback.call(self);
            }
        });
    },

    /**
     * @private
     * Get user info, and initialize viewport on success
     */
    getUserInfoAndStartApp: function () {
        // load user info (usually the first call - creates the session)
        CMS.app.userInfo = new CMS.app.UserInfo();
        CMS.app.trafficManager.sendRequest({
            action: 'getCurrentUserInfo',
            success: function (response) {
                this.initApplicationLanguage(response);
                this.startApp(response);
            },
            successCondition: 'data.userInfo',
            failure: function () {
                CMS.Message.error(CMS.i18n('Server nicht erreichbar. Anwendung kann nicht gestartet werden.'));
            },
            scope: this
        });
    },

    hideInitialLoadingIndicator: function () {
        Ext.getBody().removeClass('CMSloading');
    },

    setInitialLoadingIndicatorText: function (text) {
        Ext.util.CSS.removeStyleSheet('initialLoadText');
        Ext.util.CSS.createStyleSheet('body.CMSloading:before { content: "' + text + '" !important; }', 'initialLoadText');
    },

    /**
    * @private
    */
    startApp: function (response) {

        this.hideInitialLoadingIndicator();

        /**
         * The application viewport
         * @property userInfo
         * @type CMS.app.UserInfo
         */
        CMS.app.userInfo.addAll(response.data.userInfo);

        CMS.app.loginHelper.isInitialLogin = false;

        /**
         * The application viewport
         * @property viewport
         * @type CMS.layout.ApplicationViewport
         */
        CMS.app.viewport = new CMS.layout.ApplicationViewport({
            plugins: [new CMS.app.SwallowKeyboardPlugin({
                key: Ext.EventObject.BACKSPACE
            })]
        });

        /* -> SBCMS-1331 cause performance issues
        if (!CMS.config.debugMode) {
            try {
                SB.util.hideObjectsFromFrames(window, 'CMS,Ext,SB');
            } catch (ignore) {}
        }
        */

        CMS.app.heartbeat.startBeating();

        CMS.app.initializing = false;

        /**
         * Indicates whether the app is fully initialized (Viewport ready)
         * @property initialized
         * @type Boolean
         */
        CMS.app.initialized = true;
    },

    onbeforeunload: function () {
        /**
         * Indicates whether the app received a "beforeunload" event.
         * This happens before iframes receive the "beforeunload", and before the unload event is fired.
         * @property willunload
         * @type Boolean
         */
        CMS.app.willunload = true;

        // The user may cancel unloading. If he does, we reset the will unload flag
        window.setTimeout(function () {
            if (window.CMS) {
                CMS.app.willunload = !!(CMS.app.unloading);
            }
        }, 100); // 100ms should be enough
    },

    /**
    * @private
    * For debugging purposes only
    * Wrap unload handler in try-catch and log any occurring errors to localStorage
    * On next startup, display those errors
    */
    wrapUnloadHandler: function () {
        var err = window.localStorage.getItem('CMSShutdownError');
        if (err && err.length) {
            err = JSON.parse(err);
            CMS.app.ErrorManager.push(err);
            CMS.Message.error('Error during last shutdown', err.message);
            console.error('[Application] Error during last shutdown:', err.message + '\n' + err.stack);
        }
        window.localStorage.removeItem('CMSShutdownError');
        var self = this;
        Ext.lib.Event.addListener(window, 'unload', function shutdownwrapper() {
            Ext.lib.Event.removeListener(window, 'unload', shutdownwrapper);
            try {
                self.shutdown();
            } catch (e) {
                // store shutdown errors for next init
                window.localStorage.setItem('CMSShutdownError', JSON.stringify({
                    name: e.name,
                    message: e.message,
                    fileName: e.fileName,
                    lineNumber: e.lineNumber,
                    stack: e.stack,
                    isCMSError: true
                }));
            } finally {
                self = null;
            }
        });
    },

    /**
    * @private
    * Do some memory cleanup
    */
    shutdown: function shutdown() {
        CMS.app.willunload = true;

        /**
         * Indicates whether the app is currently being shut down. (Memory cleanup in progress after unload event)
         * @property unloading
         * @type Boolean
         */
        CMS.app.unloading = true;

        Ext.lib.Event.removeListener(window, 'unload', shutdown);
        Ext.lib.Event.removeListener(window, 'beforeunload', CMS.app.Application.onbeforeunload);
        CMS.app.Application.setProductName(CMS.i18nTranslateMacroString(CMS.config.productName), []); // for browser history
        Ext.each(['ErrorManager', 'viewport', 'loginHelper', 'downloadHelper', 'lockManager', 'heartbeat'], function (cmp) {
            if (CMS.app[cmp]) {
                CMS.app[cmp].destroy();
            }
        });
        CMS.app.trafficManager.purgeListeners();
        Ext.iterate(Ext.lib.Ajax.conn, function (tId, conn) { // this is neccessary to cancel all pending Ajax requests without running callbacks. Ext.lib.Ajax is crap.
            if (conn && (CMS.app.connectionWhitelist.indexOf(+tId) == -1)) {
                conn.abort();
                clearInterval(this.poll[tId]);
                delete this.poll[tId];
                clearTimeout(this.timeout[tId]);
                delete this.timeout[tId];
            }
        }, Ext.lib.Ajax);
        delete CMS.app.connectionWhitelist;
        Ext.lib.Ajax.conn = {}; // override built-in connection canceling, to make sure whitelisted requests continue
        if (CMS.app.clipboard) {
            CMS.app.clipboard.clear();
        }
        Ext.WindowMgr.each(Ext.destroy);
        Ext.menu.MenuMgr.hideAll();
        Ext.QuickTips.getQuickTip().destroy();
        Ext.StoreMgr.clear();
        Ext.ComponentMgr.all.clear();
        if (Ext.Element.collectorThreadId) {
            window.clearInterval(Ext.Element.collectorThreadId);
        }
        if (window.console && !CMS.config.debugMode) {
            console.clear();
        }
        Ext.iterate(CMS, function (key) {
            CMS[key] = null;
        });
        // Note that Ext.lib.Event runs Ext.EventManager._unload()
        // after all unload handlers have been called
    },

    /**
     * Sets whether the module development mode is enabled or not
     * Useability tests have shown that new users often get lost in
     * module editing dialogs
     *
     * @param {Boolean} enable <code>true</code> to enable, <code>false</code> to
     *      disable module development mode
     */
    setDevModeEnabled: function (enable) {
        this.devModeEnabled = !!enable;
    },

    /**
     * Returns whether the module development mode is enabled or not
     *
     * @return {Boolean}
     */
    isDevModeEnabled: function () {
        return !!this.devModeEnabled;
    }
};
