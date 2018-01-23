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
        console.log('hallo');
        console.log(field);
        console.log(api.getWebsiteSettings(websiteSettingsId, name + 'Default'));
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
            if (field.type == 'ComboSelect') {
                var text = api.getWebsiteSettings(websiteSettingsId, name + 'Default');
                if (text == '') {
                    field.remove = true;
                } else {
                    var result = [];
                    var lines = text.split('\n');

                    Ext.each(lines, function (curLine) {
                        var key,
                            value,
                            hierarchy = 0;

                        if (/^\s*$/.test(curLine)) {
                            return false; // ignore blank lines
                        }
                        if (curLine.indexOf(':') != -1) {
                            var lineArr = curLine.split(':');

                            key = lineArr[0];
                            value = lineArr.slice(1).join(':');

                            // hierarchy support with dashes (-)
                            var keyDashMatch = key.match(/^(\-+) (.*)/);
                            if (keyDashMatch) {
                                hierarchy = keyDashMatch[1].length;
                                key = keyDashMatch[2];
                            }

                        } else {
                            key = curLine;
                            value = curLine;
                        }

                        //set key to empty string if key is empty
                        if (key.trim().length === 0) {
                            key = '';
                        }
                        //set value to non breaking space if value is empty
                        if (value.trim().length === 0) {
                            value = '\u00A0';
                        }

                        result.push([key, value, hierarchy]);
                    });
                    field.options = result;
                }
            } else if (!pageAttributes[name]) {
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
            adjustCustomTextField(api, pageType, pageAttributes, 'customTextfield3');
            adjustCustomTextField(api, pageType, pageAttributes, 'customTextfield4');
            adjustCustomTextField(api, pageType, pageAttributes, 'customTextfield5');
        }
    };
}());