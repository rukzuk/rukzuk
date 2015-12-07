Ext.ns('CMS.app');

/**
* @class CMS.app.UIDManager
* Helper for UUID generation
* This is a true singleton. Use {@link #getInstance} to access its instance.
*/

CMS.app.UIDManager = (function () {

    var instance;

    var Class = Ext.extend(Ext.util.Observable, {

        supportedTypes: ['unit', 'template', 'site', 'module'],

        constructor: function () {
            Ext.util.Observable.prototype.constructor.call(this);
            this.enableBubble(this.bubbleEvents);
        },

        /**
        * @param {String} type
        * One of 'unit', 'module', 'template', 'site'
        * @return {String} a UUID
        */
        getId: function (type) {
            if (this.supportedTypes.indexOf(type) < 0) {
                throw 'Type "' + type + '" not supported.';
            }
            var identifier;
            switch (type) {
            case 'unit':
                identifier = 'm' + type;
                break;
            case 'module':
                identifier = type;
                break;
            case 'template':
                identifier = 'tpl';
                break;
            case 'site':
                identifier = type;
                break;
            }
            identifier = identifier.toUpperCase();
            return identifier + '-' + SB.util.UUID() + '-' + identifier;
        },

        /**
        * Retrieves a specified amount of UUIDs.
        * @param {String} type
        * One of 'unit', 'module', 'template', 'site'
        * @param {Integer} amount
        * Number of required UUIDs
        * @return {Array} An array containing the specified
        * amount of UUIDs
        */
        getIdSet: function (type, amount) {
            var uuids = [];
            for (var i = 0; i < amount; i++) {
                uuids.push(this.getId(type));
            }
            return uuids;
        }
    });

    return {
        /**
         * (Class method)
         */
        getInstance: function () {
            if (!instance) {
                instance = new Class();
            }
            return instance;
        }
    };
})();
