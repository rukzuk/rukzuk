/**
* ExtLint
* This file provides some debugging helpers that are designed to detect
* common errors
*/

(function () {

Ext.override(Ext.Container, {
    initItems: function () {
        if (!this.items) {
            this.items = new Ext.util.MixedCollection(false, this.getComponentId);
            this.getLayout();

        } else if (!this.items.add) {
            var xtypes = [];
            Ext.each(this.items, function (it) {
                xtypes.push(it.xtype);
            });
            console.error('[ext-lint] Items (' + xtypes.join(', ') + ') must not be defined in the class, but in the instance.');
        }
    },
    afterRender: function () {
        Ext.Container.superclass.afterRender.call(this);
        if (!this.layout) {
            this.layout = 'auto';
            if (this.items) {
                console.group();
                console.warn('[ext-lint] No layout specified. Falling back to auto layout.');
                console.log('Component: ', this);
                console.log('DOM: ', this.el.dom);
                console.groupEnd();
            }
        }
        if (Ext.isObject(this.layout) && !this.layout.layout) {
            this.layoutConfig = this.layout;
            this.layout = this.layoutConfig.type;
        }
        if (Ext.isString(this.layout)) {
            this.layout = new Ext.Container.LAYOUTS[this.layout.toLowerCase()](this.layoutConfig);
        }
        this.setLayout(this.layout);
        if (this.activeItem !== undefined) {
            var item = this.activeItem;
            delete this.activeItem;
            this.layout.setActiveItem(item);
        }
        if (!this.ownerCt) {
            this.doLayout(false, true);
        }
        if (this.monitorResize === true) {
            Ext.EventManager.onWindowResize(this.doLayout, this, [false]);
        }
    }
});

Ext.reg = Ext.ComponentMgr.registerType = (function (registerType) {
    return function (xtype, cls) {
        if (typeof cls != 'function') {
            console.error('[ext-lint] Tried to register xtype "' + xtype +  '" with invalid Class argument');
        } else {
            registerType(xtype, cls);
        }
    };
})(Ext.ComponentMgr.registerType);

Ext.create = Ext.ComponentMgr.create = (function (types) {
    return function (config, defaultType) {
        if (config.render) {
            return config;
        }
        var xtype = config.xtype || defaultType;
        if (typeof types[xtype] !== 'function') {
            console.error('[ext-lint] "' + xtype + '" is not a registered xtype');
        }
        return new types[xtype](config);
    };
})(Ext.ComponentMgr.types);

Ext.override(Ext.grid.Column, {
    // override setEditor, so we don't have to c&p the whole Ext.grid.Column definition
    setEditor: function (editor) {
        if (typeof this.dataIndex == 'undefined') {
            console.error('[ext-lint] column ' + (this.id ? '"' + this.id + '"' : '') + ' is missing a dataIndex property');
        }
        var ed = this.editor;
        if (ed) {
            if (ed.gridEditor) {
                ed.gridEditor.destroy();
                delete ed.gridEditor;
            } else {
                ed.destroy();
            }
        }
        this.editor = null;
        if (editor) {
            if (!editor.isXType) {
                editor = Ext.create(editor, 'textfield');
            }
            this.editor = editor;
        }
    }
});

Ext.override(Ext.menu.Menu, {
    lookupComponent: function (c) {
        if (Ext.isString(c)) {
            c = (c == 'separator' || c == '-') ? new Ext.menu.Separator() : new Ext.menu.TextItem(c);
            this.applyDefaults(c);
        } else {
            if (!c) {
                console.error('[ext-lint] Falsy value passed as a menu item config');
            }
            if (Ext.isObject(c)) {
                c = this.getMenuItem(c);
            } else if (c.tagName || c.el) {
                c = new Ext.BoxComponent({
                    el: c
                });
            }
        }
        return c;
    }
});

var origExecute = Ext.data.Store.prototype.execute;

Ext.override(Ext.data.Store, {
    execute: function (action, rs, options, batch) {
        if (action === 'read' && !this.url) {
            console.error('[ext-lint] No url parameter provided for Store.');
        }
        return origExecute.apply(this, arguments);
    }
});

Ext.util.Observable.prototype.on = Ext.util.Observable.prototype.addListener = function (eventName, fn, scope, o) {
    var me = this,
        e,
        oe,
        isF,
        ce;
    if (typeof eventName == 'object') {
        o = eventName;
        for (e in o) {
            oe = o[e];
            if (!me.filterOptRe.test(e)) {
                if (typeof oe == 'undefined' || oe === null) {
                    console.error('[ext-lint] Tried to add invalid "' + e + '" listener in ' + this.ctype + '/' + this.xtype);
                }
                me.addListener(e, oe.fn || oe, oe.scope || o.scope, oe.fn ? oe : o);
            }
        }
    } else {
        eventName = eventName.toLowerCase();
        ce = me.events[eventName] || true;
        if (typeof ce == 'boolean') {
            me.events[eventName] = ce = new Ext.util.Event(me, eventName);
        }
        if (typeof fn == 'undefined' || fn === null) {
            console.error('[ext-lint] Tried to add invalid "' + eventName + '" listener in ' + this.ctype + '/' + this.xtype);
        }
        ce.addListener(fn, scope, typeof o == 'object' ? o : {});
    }
};


var origExtend = Ext.extend;
Ext.extend = function (a) {
    if (typeof a != 'function') {
        return console.error('[ext-lint] Ext.extend called on invalid class');
    }
    return origExtend.apply(this, arguments);
};

var origOverride = Ext.override;
Ext.override = function (a) {
    if (typeof a != 'function') {
        return console.error('[ext-lint] Ext.override called on invalid class');
    }
    return origOverride.apply(this, arguments);
};

})();
