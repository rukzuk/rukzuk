Ext.ns('CMS');

/**
* @class CMS.Message
* Helper class for displaying alert boxes
* @singleton
*/
CMS.Message = (function () {
    return {
        /**
        * Display an error message
        * @param {String} title The message box's title
        * @param {String} body The message to be displayed (HTML formatted)
        */
        error: function (title, body) {

            if (typeof body == 'undefined') {
                body = title;
                title = '&#160;';
            }

            if (CMS.config.debugMode) {
                body += '<br><br><span style="display: inline-block; text-align: center;">' + 'Debug mode active';
                body += '<br>' + 'Press {key} to show futher details'.replace('{key}', CMS.config.debugKey.description) + '</span>';
            }

            Ext.MessageBox.show({
                title: title,
                msg: body,
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        },

        /**
        * Display a warning. Same as <code>info(title, body, Ext.MessageBox.WARNING)</code>
        */
        warn: function (title, body) {
            this.info(title, body, Ext.MessageBox.WARNING);
        },

        /**
        * Display an info message
        * @param {String} title The message box's title
        * @param {String} [body] The message to be displayed (HTML formatted)
        * @param {String} [iconCls] A CSS class for the icon. Defaults to Ext.MessageBox.INFO
        */
        info: function (title, body, iconCls) {

            if (typeof body == 'undefined') {
                body = title;
                title = '&#160;';
            }

            Ext.MessageBox.show({
                title: title,
                msg: body,
                buttons: Ext.MessageBox.OK,
                icon: iconCls || Ext.MessageBox.INFO
            });
        },

        /**
        * Display a "toast" pop-up message
        * @param {String} title The message box's title
        * @param {String} [body] The message to be displayed (HTML formatted)
        */
        toast: function (title, body) {
            if (typeof body == 'undefined') {
                body = title;
                title = CMS.i18n('Hinweis');
            }
            Ext.ux.Toast.msg(title, body);
        },

        /**
        * Displays a prompt dialog
        * @param {String} title The message box's title
        * @param {String} title The message box's Message
        * @param {Function} fn callback function
        * @param {Object} scope the scope in which the callback is executed
        * @param {boolean} multiline (NYI!) true to display a multiline field
        * @param {String} value Default value
        * @param {Object} [fieldconfig] textfield params (e.g. validator, regexp...)
        */
        prompt: function (title, msg, fn, scope, multiline, value, fieldconfig) {
            var config = {
                title: title,
                msg: msg,
                callback: {
                    scope: scope,
                    fn: fn
                },
                value: value,
                fieldconfig: fieldconfig
            };
            (new CMS.PromptWindow(config)).show();
        }
    };
})();
