Ext.ns('CMS.app');

/**
* @class CMS.app.Heartbeat
* @extends Ext.util.Observable
* Sends a "heartbeat" to the backend.
*/
CMS.app.Heartbeat = Ext.extend(Ext.util.Observable, {

    constructor: function () {
        this.runner = new Ext.util.TaskRunner();
        this.openItems = {};
        return CMS.app.Heartbeat.superclass.constructor.apply(this, arguments);
    },

    /**
    * Add an item that will be sent with each heartbeat
    * @param {Object} cfg A config object with the following properties:
    * <ul><li><strong>websiteId</strong>: (String) The item's website's id</li>
    * <li><strong>id</strong>: (String) The item's id</li>
    * <li><strong>type</strong>: (String) The item's type</li>
    * </ul>
    */
    addItem: function (cfg) {
        var type = cfg.type;
        var websiteId = cfg.websiteId;
        var itemId = cfg.id;
        if (!/module|page|template/.test(type)) {
            throw '[Heartbeat] Invalid item type';
        }
        this.openItems[websiteId] = this.openItems[websiteId] || {};
        var array = this.openItems[websiteId][type + 's'];
        if (array) {
            if (array.indexOf(itemId) == -1) {
                array.push(itemId);
            } else {
                console.warn('[Heartbeat] Tried to add already present item', websiteId, type, itemId);
            }
        } else {
            this.openItems[websiteId][type + 's'] = [itemId];
        }
    },

    /**
    * Remove an item from the heartbeat.
    * @param {Object} cfg A config object with the following properties:
    * <ul><li><strong>websiteId</strong>: (String) The item's website's id</li>
    * <li><strong>id</strong>: (String) The item's id</li>
    * <li><strong>type</strong>: (String) The item's type</li>
    * </ul>
    * @param {Boolean} silent (optional) Suppress warnings. Defaults to <tt>false</tt>
    */
    removeItem: function (cfg, silent) {
        var type = cfg.type;
        var websiteId = cfg.websiteId;
        var itemId = cfg.id;
        var websiteItems = this.openItems[websiteId];
        if (!websiteItems || !websiteItems[type + 's'] || websiteItems[type + 's'].indexOf(itemId) == -1) {
            if (!silent) {
                console.warn('[Heartbeat] Tried to remove non-existing item', websiteId, type, itemId);
            }
            return;
        }
        websiteItems[type + 's'].remove(itemId);
        if (!websiteItems[type + 's'].length) {
            delete websiteItems[type + 's'];
        }
        if (!SB.util.getKeys(websiteItems).length) {
            delete this.openItems[websiteId];
        }
    },

    /**
    * Remove all items from the heartbeat
    */
    removeAll: function () {
        this.openItems = {};
    },

    /**
    * Start sending hearbeats
    */
    startBeating: function () {
        if (this.beating) {
            return;
        }
        this.runner.start({
            run: this.sendBeat,
            interval: CMS.config.ajaxIntervals.heartbeat,
            scope: this
        });
        this.beating = true;
    },

    /**
    * Stop sending hearbeats
    * @param {Boolean} (optional) Remove all items. Defaults to <tt>false</tt>
    */
    stopBeating: function (clear) {
        this.cancelCurrentBeat();
        this.runner.stopAll();
        if (clear) {
            this.removeAll();
        }
        this.beating = false;
    },

    /**
    * @private
    * Send one individual heartbeat
    */
    sendBeat: function () {

        this.cancelCurrentBeat();
        if (CMS.app.unloading) {
            return;
        }
        this.requestId = CMS.app.trafficManager.sendRequest({
            action: 'heartbeat',
            data: {
                openItems: this.openItems
            },
            success: function (json) {
                this.requestId = null;
                if (json.data) {
                    this.checkResponse(json.data);
                }
            },
            scope: this
        });
    },

    /**
    * Cancels a currently sent heartbeat. Does nothing if no heartbeat is active.
    * This can be used to avoid rare race conditions together with unlock requests
    */
    cancelCurrentBeat: function () {
        if (!this.requestId) {
            return;
        }
        CMS.app.trafficManager.abortRequest(this.requestId);
        this.requestId = null;
    },

    /**
    * Checks the JSON response for expired/invalid entries
    * @param {Object} data The response data
    */
    checkResponse: function (data) {
        var expired = [];
        var invalid = [];
        // "abgefahrenes Teil, ey!"
        Ext.each([[data.expired, expired], [data.invalid, invalid]], function (args) {
            Ext.iterate(args[0], function (site, conflict) {
                Ext.iterate(conflict, function (type, items) {
                    Ext.each(items, function (itemId) {
                        args[1].push({
                            type: type.replace(/s$/, ''),
                            websiteId: site,
                            id: itemId
                        });
                    });
                });
            });
        });
        var closed = [];
        var toBeClosed = expired.concat(invalid);
        Ext.each(toBeClosed, function (exp) {
            var title = CMS.app.viewport.removePanel(exp.id, true);
            if (title) {
                closed.push(title);
            }
        }, this);
        var toBeRemoved = toBeClosed;
        Ext.each(toBeRemoved, function (inv) {
            this.removeItem(inv, true);
        }, this);
        if (closed.length) {
            CMS.Message.warn(CMS.i18n('Bearbeitungssitzung abgelaufen'), CMS.i18n('Folgende Tabs sind geschlossen worden:') + '<br><br>- ' + closed.join('<br>- ') + '<br><br>' + CMS.i18n('Die Bearbeitung wurde durch einen Administrator beendet oder es wurde zu lange nicht gespeichert.'));
        }
        closed = null;
        toBeClosed = null;
        toBeRemoved = null;
    },

    destroy: function () {
        this.stopBeating(true);
        this.runner = null;
    }
});
