Ext.ns('CMS.data');

CMS.data.websiteFields = [{
    name: 'id',
    type: 'string',
    allowBlank: false
}, {
    name: 'name',
    type: 'string',
    defaultValue: 'Ohne Titel',
    allowBlank: false
}, {
    name: 'description',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'navigation',
    type: 'array',
    defaultValue: [],
    allowBlank: true
}, {
    name: 'publishInfo',
    type: 'json',
    defaultValue: {},
    allowBlank: false
}, {
    name: 'publish',
    type: 'json',
    defaultValue: {},
    allowBlank: false
}, {
    name: 'colorscheme',
    type: 'array',
    defaultValue: [],
    allowBlank: false
}, {
    name: 'privileges',
    type: 'json',
    defaultValue: {},
    allowBlank: false
}, {
    name: 'screenshot',
    type: 'string',
    defaultValue: ''
}, {
    name: 'resolutions',
    type: 'json',
    defaultValue: CMS.config.defaultWebsiteResolutions,
    allowBlank: false
}, {
    name: 'publishingEnabled',
    type: 'boolean',
    defaultValue: false
}];

/**
* @class CMS.data.WebsiteRecord
* @extends Ext.data.Record
*/
CMS.data.WebsiteRecord = CMS.data.Record.create(CMS.data.websiteFields);

CMS.data.isWebsiteRecord = function (record) {
    return record && (record.constructor == CMS.data.WebsiteRecord);
};

CMS.data.WebsiteStore = (function () {
    var instance;
    return {
        /**
        * (Class method)
        */
        getInstance: function () {
            if (!instance) {
                instance = new CMS.data.JsonStore({
                    storeId: 'websites',
                    idProperty: 'id',
                    url: CMS.config.urls.getAllWebsites,
                    baseParams: SB.util.cloneObject(CMS.config.params.getAllWebsites),
                    root: CMS.config.roots.getAllWebsites,
                    fields: CMS.data.WebsiteRecord,
                    getPublishedWebsitesCount: function () {
                        var count = 0;
                        this.each(function (record) {
                            if (record.get('publishingEnabled')) {
                                count++;
                            }
                        });
                        return count;
                    }
                });
            }
            return instance;
        },
        /**
         * Update a single record of the website (ASYNC)
         * (Class method)
         *
         * @param {String} websiteId
         *
         * @param {String} [section] key which should be updated (e.g. 'navigation'), if falsy all is updated
         * @param {Function} [callback]
         * @param {Object} [scope]
         *
         * @returns {Boolean} true if the update XHR call was performed (does not mean is was successful)
         */
        refreshWebsiteRecord: function (websiteId, section, callback, scope) {
            var record = this.getInstance().getById(websiteId);

            if (!record) {
                return false;
            }

            CMS.app.trafficManager.sendRequest({
                action: 'getWebsite',
                data: {
                    id: websiteId
                },
                successCondition: 'data',
                success: function (response) {
                    var responseKeys = Object.keys(response.data);
                    var recordKeys = Object.keys(record.data);
                    // update record
                    record.beginEdit();
                    // iterate over all known keys of the record
                    if (section) {
                        record.set(section, response.data[section]);
                    } else {
                        // update all
                        recordKeys.forEach(function (k) {
                            // response contains data
                            if (responseKeys.indexOf(k) !== -1) {
                                // update record with response data
                                record.set(k, response.data[k]);
                            }
                        });
                    }
                    record.endEdit();

                    if (callback) {
                        callback.call(scope || this, response);
                    }

                },
                scope: this,
                failureTitle: CMS.i18n('Fehler beim Laden der Website')
            });

            return true;
        }
    };
})();
