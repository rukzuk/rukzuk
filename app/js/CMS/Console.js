Ext.ns('CMS');

/**
* @singleton
* Wrapper object for console calls.
* This is to make sure
* - we don't log in production mode
* - console.log calls from liveView iframes don't interfere with the application's log
*
* For logging in production mode, use CMS.console.log().
* Calls to console.log() will be ignored, and work only in debug mode.
*/
CMS.Console = {
    /**
    * Create CMS.console, which maps to original console,
    * and overwrite window.console with emptyFn in production mode
    */
    init: function () {
        var realConsole = window.console || {};
        var dummyConsole = {};
        var consoleMethods = 'assert,clear,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,profile,profileEnd,table,time,timeEnd,trace,warn'.split(',');
        Ext.each(consoleMethods, function (method) {
            dummyConsole[method] = Ext.emptyFn;
        });

        if (!Ext.isFunction(realConsole.exception)) {
            realConsole.exception = realConsole.error;
        }

        var getter = function f() {
            return realConsole;
        };
        if (!CMS.config.debugMode) {
            getter = function () {
                return dummyConsole;
            };
        }

        try {
            Object.defineProperty(window, 'console', {
                get: getter,
                set: function (x) {
                    realConsole = x;
                }
            });
        } catch (e) {
            try {
                console.log('Error initializing console wrapper:', e);
            } catch (ie8isShit) {
                try {
                    window.console = dummyConsole;
                } catch (safari) {}
            }
        }

        try {
            Object.defineProperty(CMS, 'console', {
                get: function f() {
                    return realConsole;
                }
            });
        } catch (ex) {
            console.log('Error initializing CMS.console:', ex);
            CMS.console = dummyConsole;
        }
    }
};
