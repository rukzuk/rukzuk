Ext.ns('CMS.pageType.Type');

(function () {
    if (CMS.pageType.Type.page) {
        // already initialized
        return;
    }

    var websiteSettingsId = 'custom_page_properties';

    /**
     * Adjust a textfield configured at website settings
     *
     * @param {CMS.pageType.api.API} api
     * @param {CMS.data.PageTypeRecord} pageType
     * @param {Object} pageAttributes
     * @param {String} name
     * @returns {null}
     */
    var adjustCustomTextField = function (api, pageType, pageAttributes, name) {
        var field = api.getPageTypeFormElementByName(pageType, name);
        if (!field) {
            return null;
        }
        if (api.getWebsiteSettings(websiteSettingsId, name + 'Enabled') === true) {
            // show field with custom label and default value if no page value exists
            field.remove = false;
            var customFieldLabel = api.getWebsiteSettings(websiteSettingsId, name + 'Label');
            if (customFieldLabel) {
                field.fieldLabel = customFieldLabel;
            }
            if (!pageAttributes[name]) {
                pageAttributes[name] = api.getWebsiteSettings(websiteSettingsId, name + 'Default');
            }
        } else {
            // hide field and reset page value of this field
            field.remove = true;
            pageAttributes[name] = null;
        }
    };

    CMS.pageType.Type.page = {
        /**
         * Called before page type form panel rendered
         *
         * @param {CMS.pageType.api.API} api
         * @param {CMS.data.PageTypeRecord} pageType
         * @param {Object} pageAttributes
         */
        buildFormPanel : function (api, pageType, pageAttributes) {
            adjustCustomTextField(api, pageType, pageAttributes, 'customTextfield1');
            adjustCustomTextField(api, pageType, pageAttributes, 'customTextfield2');
        }
    };
}());
