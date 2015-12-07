/* global unescape: false */
Ext.ns('CMS.app.ErrorManager');

CMS.app.ErrorManager.init = function () {
    var errors = [];
    var errorHistory = [];
    var errorsShowing = false;

    var hotKeyListener = function (e) {
        if (CMS.config.debugMode && SB.util.compareObjectProperties(e, CMS.config.debugKey, 'keyCode,shiftKey,ctrlKey,altKey')) {
            if (e.preventDefault) {
                e.preventDefault();
            } else {
                e.returnValue = false;
            }
            CMS.app.ErrorManager.showErrors();
        } else if (SB.util.compareObjectProperties(e, CMS.config.nan, 'keyCode,shiftKey,ctrlKey')) {
            (new Ext.Window({
                width: 750,
                height: 650,
                title: unescape('%4e%79a%6e!'),
                items: {
                    bodyStyle: 'background-color: transparent;',
                    html: '<iframe height="100" width="100" style="width: 100%; height:100%" scrolling="no" frameborder="0" src="' + unescape(CMS.config.nan.url) + '"></iframe>'
                },
                renderTo: Ext.getBody()
            })).show();
        }
    };

    var errorListener = function (text, url, line) {
        if (CMS.app.unloading) {
            return true;
        }
        CMS.Message.error(CMS.i18n('JS-Fehler'), Ext.util.Format.ellipsis(text, 100));
        CMS.app.ErrorManager.push(CMS.i18n('Scriptfehler:\n{description}\n\n----\n\nZeile {line} in {file}').replace('{description}', text).replace('{line}', line).replace('{file}', url));
        return true; // returning true(!) prevents default
    };

    /**
    * A utility class for storing and displaying error messages in a convenient way
    * @class CMS.app.ErrorManager
    * @extends Object
    */
    CMS.app.ErrorManager = {
        /**
        * Initialize error manager
        */
        init: Ext.emptyFn,

        /**
        * Store an error text or an error object
        * @param {String|Error} error A plain text to be displayed or an Error object. This can contain an expando,
        * <tt>cmstitle</tt> of type String, which will be used as the error title if present
        * Alternatively, you may pass an error-like object which contains a truthy <tt>isCMSError</tt> attribute, for example
        * <tt>{isCMSError: true, message: 'foo', fileName: 'nowhere'}</tt>
        */
        push: function (error) {
            var text = error;
            if (Object.prototype.toString.call(error).indexOf('Error') != -1 || (typeof error == 'object' && error.isCMSError)) {
                text = (error.cmstitle || 'JS Exception:') + '\n' + error.name;
                text += '\n\n----\n\nMessage:\n' + error.message;
                if (error.stack) {
                    text += '\n\n----\n\nStack:\n{stack}' + error.stack;
                }
            }
            errors.push({
                text: text,
                date: new Date()
            });
        },

        /**
        * Remove all stored error texts
        */
        clear: function () {
            errorHistory = errorHistory.concat(errors);
            errors = [];
        },

        /**
        * Get the history of all errors
        */
        getErrorHistory: function () {
            return errors.concat(errorHistory);
        },

        /**
        * Show all stored error texts
        */
        showErrors: function () {
            if (errorsShowing) {
                return;
            }
            var win;
            if (!errors.length) {
                CMS.Message.toast(CMS.i18n('Keine Fehler') + ' <font size="+3" style="line-height: .1;">&#9786;</font>');
                return;
            }
            var windowCfg = {
                iconCls: 'errorlog',
                width: 815,
                border: false,
                resizable: false,
                autoHeight: true,
                closeAction: 'destroy',
                buttons: [{
                    text: CMS.i18n('Log zurücksetzen'),
                    iconCls: 'clearerrorlog',
                    handler: function () {
                        this.clear();
                        win.destroy();
                    },
                    scope: this
                }, '->', {
                    text: CMS.i18n('Schließen'),
                    iconCls: 'ok',
                    handler: function () {
                        win.destroy();
                    }
                }],
                buttonAlign: 'left',
                hide: function () {
                    errorsShowing = false;
                    Ext.Window.prototype.hide.apply(this, arguments);
                }
            };

            var userAgent = '\n\n----\n\n' + navigator.userAgent + ' (' + navigator.platform + ')';

            switch (errors.length) {
            case 1:
                Ext.apply(windowCfg, {
                    title: errors[0].date.toLocaleTimeString(),
                    html: '<textarea style="width: 800px; height: 500px;">' + errors[0].text + userAgent + '</textarea>'
                });
                break;
            default:
                var items = [];
                for (var i = errors.length - 1; i >= 0; i--) {
                    items.push({
                        title: errors[i].date.toLocaleTimeString(),
                        html: '<textarea style="width: 800px; height: 500px;">' + errors[i].text + userAgent + '</textarea>'
                    });
                }
                Ext.apply(windowCfg, {
                    title: CMS.i18n('{i} Fehler').replace('{i}', errors.length),
                    layout: 'fit',
                    items: {
                        xtype: 'tabpanel',
                        enableTabScroll: true,
                        activeItem: 0,
                        height: 527,
                        items: items
                    }
                });
            }
            win = new Ext.Window(windowCfg);
            errorsShowing = true;
            win.show();
            Ext.MessageBox.hide();
        },

        /**
        * Detects wether or not Firebug is enabled.
        * @property firebugEnabled
        * @type Boolean
        */
        firebugEnabled: !!(window.console && console.exception),

        destroy: function () {
            CMS.app.ErrorManager.clear();
            Ext.lib.Event.removeListener(Ext.isIE ? document : window, 'keydown', hotKeyListener);
            window.onerror = errorListener = null;
            hotKeyListener = null;
            delete CMS.app.ErrorManager;
        }
    };

    Ext.lib.Event.addListener(Ext.isIE ? document : window, 'keydown', hotKeyListener);
    if (!CMS.app.ErrorManager.firebugEnabled) {
        window.onerror = errorListener;
    }

    if (CMS.config.debugKey.keyCode == Ext.EventObject.F1 && Ext.isIE) {
        document.onhelp = function () {
            return false;
        };
    }

};
