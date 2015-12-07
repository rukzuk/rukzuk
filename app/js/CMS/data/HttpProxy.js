Ext.ns('CMS.data');

/**
* @class CMS.data.HttpProxy
* @extends Ext.data.HttpProxy
* A HttpProxy that submits requests as a JSON-encoded <tt>data</tt> parameter,
* using {@link CMS.app.TrafficManager}'s <tt>createPostParams()</tt> method
*/
CMS.data.HttpProxy = Ext.extend(Ext.data.HttpProxy, {

    constructor: function (cfg) {
        CMS.data.HttpProxy.superclass.constructor.call(this, cfg);
        if (cfg.hasOwnProperty('paramName')) {
            this.paramName = cfg.paramName;
        }
    },

    doRequest: function (action, rs, params, reader, cb, scope, arg) {
        var o = {
            method: (this.api[action]) ? this.api[action].method : undefined,
            request: {
                callback: cb,
                scope: scope,
                arg: arg
            },
            reader: reader,
            callback: this.createCallback(action, rs),
            scope: this
        };

        if (params.xmlData) {
            o.xmlData = params.xmlData;
        } else {
            o.params = CMS.app.trafficManager.createPostParams(params || {});
        }
        this.conn.url = this.buildUrl(action, rs);

        if (this.useAjax) {

            Ext.applyIf(o, this.conn);

            // If a currently running request is found for this action, abort it.
            if (this.activeRequest[action]) {
                ////
                // Disabled aborting activeRequest while implementing REST.  activeRequest[action] will have to become an array
                // TODO ideas anyone?
                //
                //Ext.Ajax.abort(this.activeRequest[action]);
            }
            this.activeRequest[action] = Ext.Ajax.request(o);
        } else {
            this.conn.request(o);
        }
        // request is sent, nullify the connection url in preparation for the next request
        this.conn.url = null;
    }
});
