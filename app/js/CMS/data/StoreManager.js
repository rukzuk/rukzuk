Ext.ns('CMS.data');

/**
* @singleton
* @class CMS.data.StoreManager
* Helper class to retrieve and automatically load (if required) stores
*/
CMS.data.StoreManager = {

    /**
    * By default, the {@link #get} method creates a store from class <tt>CMS.data.[storeType]Store</tt>.
    * Use this config option to overwrite that behaviour.
    * example: <tt>constructors: { 'foo': 'Bar.Baz.FooStore' }</tt>
    */
    constructors: {
        'group': 'CMS.data.UserGroupStore',
        'filteredusers': 'CMS.data.FilteredUserStore',
        'template': 'CMS.data.TemplateStore'
    },

    /**
    * retrieve and loads(if required) a store
    * using a id combination which consists the storetype and websiteId helps
    * us to retrieve the store using the Ext.StoreMgr
    * @param {String} storeType See {@link #constructors}
    * @param {String|CMS.data.JSONStore|Ext.data.Record} websiteId A string identifying the desired website,
    * or a store from the same website, or a record from such a store
    * @param {Object} [options]
    * @param {Function} [options.callback] Method which will be called if the store has been loaded
    *   The following parameter will be passed:
    *   <ul>
    *       <li>store: The loaded store</li>
    *       <li>records: An Array with the stores records</li>
    *  </ul>
    * @param {Object} [options.scope:scope] for the callback method
    * @param {Boolean} [options.disableLoad] if true, the storemanager will skip reloading the store if store is empty
    * @param {String} [options.idSuffix] Set the template / page id if the store is only relevant for one template / page.
    */
    get: function (storeType, websiteId, options) {
        if (typeof websiteId == 'object' && websiteId !== null) {
            websiteId = this.getWebsiteId(websiteId);
        }

        if (!storeType || typeof websiteId == 'undefined') {
            console.warn('[StoreManager] No stores found for storetype', storeType, ', websiteId', websiteId);
            return null;
        }

        options = options || {};

        var storeId = storeType + '-' + websiteId;

        //Add the additional id (Template- or pageId) if idSuffix param is set
        if (options && options.idSuffix) {
            storeId += '-';
            storeId += options.idSuffix;
        }

        var result = Ext.StoreMgr.get(storeId);
        if (!result) {
            var Constructor = this.constructors[storeType] ? Ext.ns(this.constructors[storeType]) : CMS.data[SB.util.ucFirst(storeType) + 'Store'];
            result = new Constructor({
                storeId: storeId
            });
            //websiteId will be used in getWebsiteId method.
            result.websiteId = websiteId;
        }
        if (websiteId !== null) {

            result.setBaseParam('websiteId', websiteId);
        }
        var status = this.getStoreStatus(result);
        switch (status) {
        case 'loading':
            if (options.callback) {
                //Add load listener to store
                result.on('load', options.callback, options.scope || window, {
                    single: true
                });

                //Remove listener if an exception occures
                result.on('exception', function () {
                    this.removeListener('load', options.callback);
                }, result, {
                    single: true
                });
            }
            break;

        case 'loaded':
            if (options.callback) {
                //Call callback method
                options.callback.call(options.scope || window, result, (result && result.getRange()));
            }
            break;

        case 'not loaded':
            //Reload store if disableLoad is not set
            if (!options.disableLoad) {
                result.reload.defer(1, result, [{ // timeout required for loadmask
                    callback: function () {
                        // we have to use a wrapper function because the bloody Ext framework
                        // passes different parameter to the load callback and to load event
                        // handler
                        if (options.callback) {
                            options.callback.call(options.scope || window, result, (result && result.getRange()));
                        }
                    },
                    websiteId: websiteId
                }]);
            }
            break;
        }
        return result;
    },

    /**
    * Returns the current store state
    * May be one of: 'loading', 'loaded' or 'not loaded'
    * @param {Ext.data.Store} store
    */
    getStoreStatus: function (store) {

        if (store.lastOptions && store.proxy.getConnection().isLoading()) {
            return 'loading';
        }

        if (store.lastOptions && !store.proxy.getConnection().isLoading()) {
            return 'loaded';
        }

        //Store is neither loaded or currently loading
        return 'not loaded';
    },


    /**
    * Returns the websiteId of a store which has been created using the StoreManager,
    * or of a record belonging to such a store
    * @param {Ext.data.Store|Ext.data.Record} inp
    */
    getWebsiteId: function (inp) {
        return (inp.store && inp.store.websiteId) || inp.websiteId;
    }
};
