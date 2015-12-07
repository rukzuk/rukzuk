Ext.ns('CMS.data');

CMS.data.JsonReader = Ext.extend(Ext.data.JsonReader, {
    // dirty little hack to inform error handler that root was not found. This is needed because ExtJS suckx.
    extractData: function (root, returnRecord) {
        if (!root) {
            throw 'ROOT_NOT_FOUND';
        }
        return CMS.data.JsonReader.superclass.extractData.apply(this, arguments);
    }
});

/**
* @class CMS.data.JsonStore
* @extends Ext.data.JsonStore
* @requires CMS.data.HttpProxy
* A JsonStore that submits its load request via a {@link CMS.data.HttpProxy}
*/
CMS.data.JsonStore = Ext.extend(Ext.data.JsonStore, {
    constructor: function (config) {
        if (!config) {
            config = {};
        }
        if (!config.proxy) {
            config.proxy = new CMS.data.HttpProxy(new Ext.data.Connection({
                url: config.url,
                api: config.api,
                store: this
            }));
        }
        config.reader = new CMS.data.JsonReader(config);
        // call Ext.data.Store, since we want to bypass Ext.data.JsonStore's constructor
        Ext.data.Store.prototype.constructor.call(this, config);
    }
});
