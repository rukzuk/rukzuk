/**
* @class CMS.app.LockManager
* @extends Ext.util.Observable
* A helper for sending lock/unlock requests.
*/
CMS.app.LockManager = Ext.extend(Ext.util.Observable, {

    constructor: function () {
        /**
        * @property releaseTransactionIds
        * @type Array
        * Holds the ids of all lock/release requests sent
        */
        this.releaseTransactionIds = [];
        this.pendingReleases = [];
        return CMS.app.LockManager.superclass.constructor.apply(this, arguments);
    },


    /**
    * Send a lock request for an item
    * @param {Object} cfg Config object
    * It can have the following properties:
    * <ul><li><strong>websiteId</strong>: (String) The item's website's id</li>
    * <li><strong>id</strong>: (String) The item's id</li>
    * <li><strong>type</strong>: (String) The item's type</li>
    * <li><strong>modal</strong>: (Boolean) Mask the screen while locking</li>
    * <li><strong>success</strong>: (Function) (optional) A callback function to be run on success</li>
    * <li><strong>callback</strong>: (Function) (optional) A callback function to be run on success or failure</li>
    * </ul>
    * @param {Boolean} override
    * If present, this flag is added to the request data to indicate an override
    */
    requestLock: function (cfg, override) {

        var self = this;

        CMS.app.trafficManager.sendRequest({
            action: 'itemLock',
            modal: cfg.modal,
            data: Ext.copyTo({
                override: override || false
            }, cfg, 'websiteId,id,type'),
            success: cfg.success,
            failure: function (json, error) {
                if (!override && json.data && json.data.overridable) {
                    self.confirmOverride(cfg, json, error);
                } else {
                    CMS.Message.error(CMS.i18n('Fehler beim Sperren des Dokuments:'), error.formatted);
                    CMS.app.ErrorManager.push(error.verbose);
                }
            },
            callback: cfg.callback,
            scope: cfg.scope
        });
    },

    /**
    * @private
    * Ask the user if they want to override an existing lock.
    * @param {Object} cfg See {@link #requestLock}
    * @param {Object} response The response object as recieved by the lock request
    * @param {Object} error The error object as recieved by the lock request
    */
    confirmOverride: function (cfg, response, error) {
        var errorCode = SB.util.getObjectByIndexPath(response, 'error.0.code');
        var msg = SB.util.getObjectByIndexPath(response, 'error.0.text');
        if (msg && CMS.config.specialErrorCodes.lockRelatedErrors.test(errorCode)) {
            msg += '<br><br>' + CMS.i18n('Sperre aufheben? Ungespeicherte Änderungen gehen dabei verloren.');
            Ext.MessageBox.confirm(CMS.i18n('Dokument gesperrt'), msg, function (btnId) {
                if (btnId == 'yes') {
                    this.requestLock(cfg, true);
                }
            }, this);
        } else {
            CMS.Message.error(CMS.i18n('Fehler beim Sperren des Dokuments:'), error.formatted);
            CMS.app.ErrorManager.push(error.verbose);
        }
    },

    /**
    * Release an existing lock. This is a "fire & forget" request. No callbacks or failure handlers will be run.
    * @param{Object} cfg Config object
    * It can have the following properties:
    * <ul><li><strong>websiteId</strong>: (String) The item's website's id</li>
    * <li><strong>id</strong>: (String) The item's id</li>
    * <li><strong>type</strong>: (String) The item's type</li>
    * </ul>
    */
    releaseLock: function (cfg) {
        var data = Ext.copyTo({}, cfg, 'websiteId,id,type');
        this.pendingReleases.push(data);
        if (!this.callTask) {
            this.callTask = new Ext.util.DelayedTask(this.sendReleaseRequest, this);
        }
        this.callTask.delay(0);
    },

    /**
     * Release an existing lock.
     * @param{Object} cfg Config object
     * @param cfg.id item id
     * @param cfg.websiteId website id
     * @param cfg.type item type
     */
    releaseLockImmediately: function (cfg, cb, scope) {
        CMS.app.heartbeat.cancelCurrentBeat();
        this.releaseTransactionIds.push(CMS.app.trafficManager.sendRequest({
            action: 'itemUnlock',
            data: {
                items: [cfg]
            },
            success: function () {
                cb.apply(scope, arguments);
            },
            scope: this,
        }).tId);
    },

    /**
    * @private
    * If several releaseLock calls are done within the same callstack, there is only one request with the cumulated data
    * This works like Ext.direct.RemotingProvider.combineAndSend
    */
    sendReleaseRequest: function () {
        if (!this.pendingReleases.length) {
            return;
        }
        CMS.app.heartbeat.cancelCurrentBeat();
        this.releaseTransactionIds.push(CMS.app.trafficManager.sendRequest({
            action: 'itemUnlock',
            data: {
                items: this.pendingReleases
            },
            fireAndForget: true
        }).tId);
        this.pendingReleases = [];
    },

    destroy: function () {
        if (this.callTask) {
            this.callTask.cancel();
        }
        this.callTask = null;
        this.sendReleaseRequest();
        [].push.apply(CMS.app.connectionWhitelist, this.releaseTransactionIds);
        delete this.releaseTransactionIds;
        this.purgeListeners();
        this.pendingReleases = null;
    }
});
