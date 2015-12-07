Ext.ns('CMS.app');

/**
 * BrowserHelper detects Browser product and version
 * required because Ext 3.x Browser detection is broken!
 */
CMS.app.BrowserHelper = Ext.extend(Object, {

    /**
     * Get Browser Info Object
     * @returns {Object}
     */
    getInfo: function () {
        /* global bowser */
        return bowser;
    },
});
