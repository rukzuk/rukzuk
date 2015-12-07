Ext.ns('CMS');

/**
* @class CMS.TreeLoader
* @extends Ext.tree.TreeLoader
* A treeloader that uses {@link CMS.app.trafficManager} to send its load requests, thus benefiting from error handling
*/
CMS.TreeLoader = Ext.extend(Ext.tree.TreeLoader, {
    /**
    * @cfg {String} action
    * Instead of {@link Ext.tree.TreeLoader#dataUrl}, this loader uses action, which will be passed to the trafficManager
    */
    action: null,

    /**
    * @cfg {String} dataRoot
    * the subroot of the response that contains the tree data. If not present, will be read from CMS.config
    */
    dataRoot: undefined,

    constructor: function () {
        Ext.tree.TreeLoader.apply(this, arguments);
        if (!this.dataRoot) {
            this.dataRoot = CMS.config.roots[this.action];
        }
        this.url = this.url || true;
    },

    requestData: function (node, callback, scope) {
        if (this.fireEvent('beforeload', this, node, callback) !== false) {
            var requestOptions = {
                action: this.action,
                data: this.getParams(node),
                success: function (response) {
                    var rawResponse = response;
                    if (this.dataRoot) {
                        response = SB.util.getObjectByIndexPath(response, this.dataRoot);
                    }
                    // nasty HACK to work around Ext.tree.TreeLoader's bizarre callback stack
                    this.processResponse({ responseData: response }, node, callback, scope);
                    this.fireEvent('load', this, node, response, rawResponse);
                },
                failure: function (response) {
                    this.fireEvent('loadexception', this, node, response);
                    this.runCallback(callback, scope || node, [node]);
                },
                failureTitle: CMS.i18n('Fehler beim Laden der Dateien'),
                scope: this
            };
            if (this.dataRoot) {
                if (Ext.isArray(requestOptions.successCondition)) {
                    requestOptions.successCondition.push(this.dataRoot);
                } else if (requestOptions.successCondition) {
                    requestOptions.successCondition = [requestOptions.successCondition, this.dataRoot];
                } else {
                    requestOptions.successCondition = this.dataRoot;
                }
            }
            CMS.app.trafficManager.sendRequest(requestOptions);
        } else {
            this.runCallback(callback, scope || node, []);
        }
    }
});
