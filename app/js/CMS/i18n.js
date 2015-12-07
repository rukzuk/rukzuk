Ext.ns('CMS');

(function () {

    /**
     * Helper method to determine the current language dictionary
     * based on the current application language
     *
     * @return {Object} The dictionary
     * @private
     */
    var getDictionary = function () {
        var libs = CMS.language.libs;
        var langKey = CMS.app.lang || (CMSSERVER && CMSSERVER.data && CMSSERVER.data.language) || 'en';
        var dict = libs[langKey];

        if (!dict) {
            // first fallback, e.g. "de-AT" -> "de"
            dict = libs[langKey.split('-')[0]];
        }
        if (!dict) {
            // second fallback, e.g. "fr" -> "en"
            dict = libs.en;
        }
        return dict.dictionary;
    };

    /**
     * Helper method to translates the given input using the current
     * dictionary
     *
     * @param {String} input The raw untranslated text
     * @param {String} [key] The language key which will be used translate the
     *      input; If omitted the input will act as key
     * @return {String} the translated text
     * @private
     */
    var translate = function (input, key) {
        var output;
        var dictionary = getDictionary();

        if (!CMS.app.lang) {
            console.error('[i18n] Call to i18n("' + input + '") before app language was determined');
        }

        if (typeof key !== 'undefined') {
            output = dictionary[key];
        } else {
            output = dictionary[input];
        }

        if (output) {
            return output;
        } else {
            return input || key;
        }
    };

    /**
     * Global translation method for (static) UI texts
     *
     * @memberOf CMS
     * @param {String} input The untranslated string
     * @param {String} [key] The translation key (should be something like: component.shortMsg; eg. users.addUser)
     * @return {String} The translation, if there is no translation the input will be returned
     */
    CMS.i18n = function (input, key) {
        if (!CMS.app.initializing && !CMS.app.initialized) {
            console.trace();
            throw 'Call to i18n("' + input + '") before app initialization';
        }

        // initialize actual i18n function on first call
        if (CMS.config.useI18n === false) {
            console.info('[i18n] disabled in config');
            CMS.i18n = function (input) {
                return input;
            };
        } else if (!CMS.language) {
            console.warn('[i18n] Translation database missing');
            CMS.i18n = function (input) {
                return input;
            };
        } else {
            CMS.i18n = translate;
        }
        // call newly created i18n function (on first call)
        return translate(input, key);
    };

    /**
     * Translate by macro string
     *
     * @param {String} macroString - string in the format: __i18n_KEY
     * @returns {*} Translation in current language or original object if its
     * not a string or the string does not start with macro magic prefix
     */
    CMS.i18nTranslateMacroString = function (macroString) {
        if (Ext.isString(macroString) && macroString.indexOf('__i18n_') === 0) {
            return translate('[TRANSLATION NOT FOUND]', macroString.replace(/^__i18n_/, ''));
        }
        return macroString;
    };

    /**
     * Global translation method for dynamic texts (e.g. module names, descriptions, ...)
     *
     * @memberOf CMS
     * @param {String|Object} input A translation object or its json representation. A translation
     *      object should have the following form:
     *      <code>{de: 'Hallo Welt!', en: 'Hello World!', ...}</code>
     * @return {String} The translation or the input if translation was not possible
     */
    CMS.translateInput = function (input) {
        var output;
        if (typeof input === 'string') {
            // assume it is a json encoded translation object
            // -> try to decode it
            try {
                input = Ext.decode(input);
            } catch (e) {}
        }

        if (Ext.isObject(input)) {
            // assume it is a translation object
            var langKey = CMS.app.lang || 'en-US';
            if (!input[langKey]) {
                // there is no translation for region specific lanuage
                // -> fall back to the general case (e.g. there may not
                // be a translation for "de-AT" there may be a translation
                // for "de")
                langKey = langKey.split('-')[0];
            }
            if (!input[langKey]) {
                // there is no translation for the given language at all
                // -> use the first existing translation
                langKey = Object.keys(input)[0];
            }
            output = input[langKey];
        } else {
            // no valid translation object (e.g. malform json string)
            // -> return input
            output = input;
        }

        return output;
    };
}());
