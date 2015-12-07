Ext.ns('CMS.app');

/**
* @class CMS.app.FullScreenHelper
* @extends Object
* @singleton
* A helper to toggle fullscreen mode in supported browsers.
*/
CMS.app.FullScreenHelper = {
    init: function () {
        if (this.fullScreenSupported) {
            Ext.lib.Event.addListener(window, 'keydown', function (e) {
                if (e.keyIdentifier == 'F11') {
                    // this does seemingly nothing at all, but actually the webkitfullscreenchange event
                    // is only fired when toggling fullscreen programmatically, d'oh!
                    e.stopPropagation();
                    e.preventDefault();
                    CMS.app.FullScreenHelper.toggleFullScreen();
                    return false;
                }
            });
        } else if (CMS.config.debugMode) {
            console.info('[FullScreenHelper] Fullscreen mode is not supported in this browser');
        }
        /*
        if (this.isFullScreen()) {
            console.warn('Fullscreen mode detected. Fullscreen button may not work on first click. This is a bug in webkit.'); // https://bugs.webkit.org/show_bug.cgi?id=83272
        }
        */
    },

    /**
    * @property fullScreenSupported
    * @type Boolean
    * Determines whether or not the browser supports manually toggling fullscreen.
    */
    fullScreenSupported: !!(document.fullscreenEnabled || document.mozFullScreenEnabled || document.webkitFullscreenEnabled),

    /**
    * Determines whether or not the browser is currently in fullscreen mode
    */
    isFullScreen: function () {
        return !!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement);
    },

    /**
    * Toggle fullscreen mode
    * Currently only working in webkit.
    * @param {Boolean} on (optional) <tt>true</tt> to enter fullscreen mode, <tt>false</tt> to exit.
    * If omitted, toggle depending on the current state
    */
    toggleFullScreen: function (on) {
        console.log('[FullScreenHelper] toggleFullScreen', on);
        if (!CMS.app.FullScreenHelper.fullScreenSupported) {
            throw 'Not supported by this browser';
        }
        if (typeof on == 'undefined') {
            on = !this.isFullScreen();
        }
        if (on) {
            var el = document.documentElement;
            if (el.requestFullscreen) {
                el.requestFullscreen();
            } else if (el.mozRequestFullScreen) {
                el.mozRequestFullScreen();
            } else if (el.webkitRequestFullScreen) {
                /*global Element:false*/
                el.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            }
        }
    }
};
