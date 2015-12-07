Ext.ns('CMS.app');

/**
 * UrlParameterHelper parses GET parameter
 * @type {*}
 */
CMS.app.UrlParameterHelper = Ext.extend(Object, {

    constructor: function () {
        // parse url params
        this.params = Ext.urlDecode(window.location.search.replace(/^\?/, ''));

        // try to remove query string (html5)
        if (window.history && window.history.replaceState && !this.getParameter('debug')) {
            window.history.replaceState(null, '', window.location.pathname);
        }
    },

    /**
     * @public
     * URL-GET Parameters
     * @param name
     * @returns {String} - ?key=value returns value; ?key returns "undefined" as string (which is true-ish) otherwise null is returned
     */
    getParameter: function (name) {
        var param = this.params[name];
        return (Ext.isString(param) && param.length > 0) ? param : null;
    }
});
