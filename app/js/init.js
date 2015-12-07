if (CMS.config.debugMode) {
    window.alert = function (msg) {
        Ext.MessageBox.alert('alert', msg);
    };
}

// init console wrapper
CMS.Console.init();

// legacy xtype
Ext.reg('fileuploadfield', Ext.ux.form.FileUploadField);


// Improve garbage collection
Ext.Component.prototype.destroy = (function (destroy) {
    return function () {
        destroy.apply(this, arguments);
        SB.util.cleanupObject(this, ['isDestroyed', 'id']);
    };
})(Ext.Component.prototype.destroy);

Ext.data.Store.prototype.destroy = (function (destroy) {
    return function () {
        destroy.apply(this, arguments);
        delete this.proxy;
    };
})(Ext.data.Store.prototype.destroy);

Ext.onReady(function () {

    // set default config options for Ext components
    Ext.iterate(CMS.config['ext-defaults'], function (key, value) {
        Ext.override(Ext.ns(key), value);
    });

    Ext.QuickTips.init();

    // setup application
    CMS.app.Application.init();
});
