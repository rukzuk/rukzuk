Ext.ns('CMS.app');

/**
 * @extend Ext.util.MixedCollection
 * @class CMS.app.UserInfo
 */
CMS.app.UserInfo = Ext.extend(Ext.util.MixedCollection, {
    /** @lends CMS.app.UserInfo.prototype */

    /**
     * Determines whether the current user can edit any page of the given website
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canEditPages: function (website) {
        var result = !!this.get('superuser') || this.hasPrivilege(website, 'allpagerights');
        if (!result) {
            // the user has no general rights
            // -> check each page of the website until we find one which the user can edit
            var pages = this.getAttribute(website, 'navigation');
            Ext.each(pages, function (page) {
                if (this.canEditPage(page) || this.canCreateChildPages(page)) {
                    result = true; // we found an editable page
                    return false; // -> exit loop
                }
            }, this);
        }
        return result;
    },

    /**
     * Determines whether the current user can edit a page tree node
     * @param {Object} page A data object (tree node, record or simple object) representing the page to be tested
     * @return {Boolean}
     */
    canEditPage: function (page) {
        return !!this.get('superuser') || this.hasPrivilege(page, 'edit');
    },

    /**
     * Determines whether the current user can delete a page tree node
     * @param {Object} page A data object (tree node, record or simple object) representing the page to be tested
     * @return {Boolean}
     */
    canDeletePage: function (page) {
        return !!this.get('superuser') || this.hasPrivilege(page, 'delete');
    },

    /**
     * Determines whether the current user can add a page to the top level of a website (beneath the tree's virtual root node)
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canCreateRootPages: function (website) {
        if (!website) {
            return false;
        }
        if (typeof website == 'string') {
            website = CMS.data.WebsiteStore.getInstance().getById(website);
        }
        return this.get('superuser') || this.hasPrivilege(website, 'allpagerights');
    },

    /**
     * Determines whether the current user can add a child page to an existing page tree node
     * @param {Object} page A data object (tree node, record or simple object) representing the page to be tested
     * @return {Boolean}
     */
    canCreateChildPages: function (page) {
        if (this.get('superuser')) {
            return true;
        }
        if (!page || page.id === 'root') {
            return false;
        }
        return this.hasPrivilege(page, 'createChildren');
    },

    /**
     * Determines whether the current user can export entities
     * @return {Boolean}
     */
    canExport: function () {
        return !!this.get('superuser');
    },

    /**
     * Determines whether the current user can import entities
     * @return {Boolean}
     */
    canImport: function () {
        return !!this.get('superuser');
    },

    /**
     * Determines whether the current user can view the log file of a given website
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canViewLogFile: function (website) {
        return !!this.get('superuser') || this.hasPrivilege(website, 'readlog');
    },

    /**
     * Determines whether the current user can publish websites
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canPublish: function (website) {
        return !!this.get('superuser') || this.hasPrivilege(website, 'publish');
    },

    /**
     * Determines whether the current user can edit the publishing
     * configuration (e.g. live server host, user, password, ...)
     * @return {Boolean}
     */
    canEditPublishingConfig: function () {
        return !!this.get('superuser');
    },

    /**
     * Determines whether the current user can edit modules
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canEditModules: function (website) {
        return !!this.get('superuser') || this.hasPrivilege(website, 'modules');
    },

    /**
     * Determines whether the current user can edit modules
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canEditTemplates: function (website) {
        return !!this.get('superuser') || this.hasPrivilege(website, 'templates');
    },

    /**
     * Determines whether the current user can edit website settings
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canEditWebsiteSettings: function (website) {
        return !!this.get('superuser') || this.hasPrivilege(website, 'templates');
    },

    /**
     * Determines whether the current user can create/delete websites
     * @return {Boolean}
     */
    canManageSites: function () {
        return !!this.get('superuser');
    },

    /**
     * Determines whether the current user can change the colorscheme
     * @param {Object} website A data object (tree node, record or simple object) representing the website to be tested
     * @return {Boolean}
     */
    canManageColors: function (website) {
        return !!this.get('superuser') || this.hasPrivilege(website, 'colorscheme');
    },

    /**
     * Determines whether the current user can manage users
     * @return {Boolean}
     */
    canManageUsers: function () {
        return !!this.get('superuser');
    },

    /**
     * Determines whether the current user can manage user groups
     * @return {Boolean}
     */
    canManageGroups: function () {
        return !!this.get('superuser');
    },


    //
    //
    // private helper
    //
    //

    /**
     * Retrives the value of a given attribute from different data
     * objects (i.e. tree nodes, records and simple objects)
     * @private
     */
    getAttribute: function (data, prop) {
        if (!data) {
            return undefined;
        } else if (data.attributes) {
            // a tree node
            return data.attributes[prop];
        } else if (Ext.isFunction(data.get)) {
            // a record
            return data.get(prop);
        } else {
            // a simple data Object
            return data[prop];
        }
    },

    /**
     * Checks if a given privilege is set
     * @private
     */
    hasPrivilege: function (data, privilege) {
        var privileges = this.getAttribute(data, 'privileges');
        return privileges && privileges[privilege];
    }
});
