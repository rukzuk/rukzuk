Ext.ns('CMS.pageType.api');

/**
 * API pass to the page type methods
 *
 * @class CMS.pageType.api.API
 * @version 1.1 (2016-12-22)
 */
CMS.pageType.api.API = Ext.extend(Ext.util.Observable, /** @lends CMS.pageType.api.API.prototype */ {

    /**
     * @property websiteId
     * @type String
     * @private
     */
    websiteId: null,

    constructor: function (websiteId) {
        this.websiteId = websiteId;
        CMS.pageType.api.API.superclass.constructor.apply(this, arguments);
        this.console = CMS.console;

    },

    /**
     * Returns the website setting values.
     * @param {String} id The id of the website setting
     * @return {Object} The values of the website setting
     */
    getWebsiteSettings: function (id, elementName) {
        try {
            var store = CMS.data.StoreManager.get('websiteSettings', this.websiteId);
            return store.getById(id).data.formValues[elementName];
        } catch (e) {
            return null;
        }
    },

    getPageTypeFormElementByName: function (pageType, name) {
        var element = null;
        if (pageType && pageType.data && pageType.data.form) {
            Ext.each(pageType.data.form, function (child) {
                if (child.CMSvar && child.CMSvar === name) {
                    element = child;
                }
            });
        }
        return element;
    },

    /**
     * destroys the internal variables
     */
    destroy: function () {
        this.purgeListeners();
        this.websiteId = null;
    }
});
