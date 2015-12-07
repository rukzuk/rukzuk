
/**
 * Sorts a translatable text according to the current application language
 * @property asTranslatedText
 * @type {Function}
 * @memberOf Ext.data.SortTypes
 */
Ext.data.SortTypes.asTranslatedText = function (s) {
    return CMS.translateInput(s);
};
