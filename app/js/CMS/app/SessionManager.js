Ext.ns('CMS.app');

/**
* @singleton
* @class CMS.app.SessionManager
* @extends Object
* Helper class for generating a session id,
* This id is persistent per browser window/tab, i.e. it will be restored if the user reloads.
*/
CMS.app.SessionManager = {
    /**
    * Generates a session id
    * @param {Boolean} force <tt>true</tt> to force a new session
    */
    initSession: function (force) {
        if (force || !this.getSessionId()) {
            window.name = 'CMSSSID-' + SB.util.UUID();
        }
        return this.getSessionId();
    },

    /**
    * Returns the current window's session id if present, <tt>null</tt> otherwise
    */
    getSessionId: function () {
        if (!/^CMSSSID-/.test(window.name)) {
            return null;
        }
        return window.name;
    }
};
