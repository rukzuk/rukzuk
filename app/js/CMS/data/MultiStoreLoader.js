Ext.ns('CMS.data');

/**
* @class CMS.data.MultiStoreLoader
* Class which makes sure all required stores are loading before callback is fired
*/
CMS.data.MultiStoreLoader =  Ext.extend(Ext.util.Observable, {

    /**
    * @cfg {Array} storeTypes
    * Array which contains the type of the requested stores e.g. ['module', 'template']
    */
    storeTypes: null,

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    /**
    * @cfg {function} callback
    * Method which will be called if all stores have been loaded
    */
    callback: null,

    /**
    * @cfg {obj} scope
    * scope for the callback method
    */
    scope: null,

    /**
    * @private
    * @type Number
    * Internal variable to store how many stores have been loaded
    */
    storesLoaded: 0,

    /**
    * @private
    * @type Ext.LoadMask
    * Holds a reference to the loadMask, if configured
    */
    mask: null,

    constructor: function (cfg) {
        Ext.apply(this, cfg);
        CMS.data.MultiStoreLoader.superclass.constructor.call(this);
    },


    /**
    * Retrieves the requested stores using the CMS.data.StoreManager
    * The callback fn is set to registerLoaded fn which will execute the callback
    * method if all stores are loaded
    * @param showMask {Boolean} true to show a modal loading mask
    */
    load: function (showMask) {
        if (Ext.isArray(this.storeTypes) && this.storeTypes.length > 0) {
            if (showMask) {
                var bodyMask = Ext.getBody().mask(CMS.i18n('Bitte warten…'));
                bodyMask.addClass('CMSwait');
                bodyMask.setStyle('z-index', 20000);
                this.mask = bodyMask;
                var activeWindow = Ext.WindowMgr.getActive();
                if (activeWindow) {
                    activeWindow.setDisabled(true);
                }
            }
        }

        Ext.each(this.storeTypes, function (curType) {
            CMS.data.StoreManager.get(curType, this.websiteId, {
                scope: this,
                callback: this.registerLoaded
            });
        }, this);
    },

    /**
    * @private
    * Method is called as StoreManager callback
    * Checks if all required stores are loaded and executes the callback method
    */
    registerLoaded: function () {
        this.storesLoaded++;
        console.log('[MultiStoreManager] ', this.storesLoaded, ' of ', this.storeTypes.length, ' stores loaded');
        if (this.storesLoaded == this.storeTypes.length) {
            console.log('[MultiStoreManager] calling callback method');
            if (this.mask) {
                var activeWindow = Ext.WindowMgr.getActive();
                if (activeWindow) {
                    activeWindow.setDisabled(false);
                }
                Ext.getBody().unmask();
            }
            this.callback.call(this.scope || window);
        }
    }
});
