Ext.ns('CMS.app');

/**
* @class CMS.app.PreviewTicketHelper
* @extends Ext.util.Observable
* A helper class for creating preview tickets for pages or templates; requests a preview ticket from the server so
* external users can preview a page/template without the need of having a regular user account.
*/
CMS.app.PreviewTicketHelper = Ext.extend(Ext.util.Observable, {

    /**
    * Creates a preview ticket for a page or template so external users can preview
    * a page/template without the need of having a regular user account.
    * @param {String} websiteId
    * @param {String} type 'page' or 'template'
    * @param {String} id The id of the page/template
    * @param {Function} callback The callback function to be called after the ticket has been created
    * @param {Object} options (Optional) Configuration of the ticket, e.g. to protect it with credentials
    * e.g. {
    *          protect: false,
    *          credentials: {
    *              username: 'test',
    *              password: 'test'
    *          },
    *          ticketLifetime: 60,
    *          sessionLifetime: 60,
    *          remainingCalls: 1
    *      }
    * @param {Boolean} scope (Optional) The object in whose scope the callback function should be executed
    */
    createPreviewTicket: function (websiteId, type, id, callback, options, scope) {
        if (!Ext.isFunction(callback)) {
            return;
        }

        var data = {
            websiteId: websiteId,
            type: type,
            id: id
        };

        this.requestId = CMS.app.trafficManager.sendRequest({
            action: 'createPreviewTicket',
            data: Ext.applyIf(data, options),
            successCondition: 'data.id',
            success: function (json) {
                if (Ext.isObject(scope)) {
                    callback.call(scope, json.data);
                } else {
                    callback(json.data);
                }
            },
            callback: function () {
                this.requestId = null;
            },
            scope: this
        });
    },

    destroy: function () {
        CMS.app.trafficManager.abortRequest(this.requestId);
    }

});
