Ext.ns('CMS.app');

/**
* @class CMS.app.SwallowKeyboardPlugin
* @extend Object
* A plugin that swallows given keyboard combinations on the component
*/
CMS.app.SwallowKeyboardPlugin = Ext.extend(Object, {
    /**
    * @constructor
    * @param {Array|Object} cfg
    * An key config or an array thereof. The syntax is similar to {@link Ext.KeyMap}, but
    * only <tt>key</tt>, <tt>ctrl</tt>, <tt>shift</tt> and <tt>alt</tt> are supported
    */
    constructor: function (cfg) {
        if (!cfg || cfg.swallow) {
            throw 'SwallowKeyboardPlugin must be passed a config';
        }
        if (!Ext.isArray(cfg)) {
            cfg = [cfg];
        }
        this.maps = [];
        Ext.each(cfg, function (cfgItem) {
            this.maps.push({
                key: cfgItem.key,
                ctrl: !!cfgItem.ctrl,
                alt: !!cfgItem.alt,
                shift: !!cfgItem.shift
            });
        }, this);
    },

    init: function (self) {
        var target = self.el.dom;
        if (target == document.body) {
            target = Ext.isIE ? document : window;
        }
        var maps = this.maps;
        var handler = function (evt) {
            evt = Ext.EventObject.setEvent(evt);
            // do nothing in input fields/textareas
            if (document.activeElement && /^(input|textarea)$/i.test(document.activeElement.tagName)) {
                return;
            }
            Ext.each(maps, function (keymap) {
                if (keymap.key == evt.keyCode && keymap.ctrl == evt.ctrlKey && keymap.shift == evt.shiftKey && keymap.alt == evt.altKey) {
                    evt.stopEvent();
                    evt.preventDefault();
                    return false;
                }
            });
        };
        Ext.lib.Event.addListener(Ext.isIE ? document : window, 'keydown', handler);
        self.on('destroy', function () {
            maps = null;
            target = null;
            Ext.lib.Event.addListener(Ext.isIE ? document : window, 'keydown', handler);
        });
    }
});

