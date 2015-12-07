/* global mockstar */
(function (global) {
    function getItemFromArray(id, array) {
        if (array && array.length > 0) {
            for (var i = 0, l = array.length; i < l; i++) {
                var item = array[i];
                if (item && item.id === id) {
                    return item;
                }
            }
        }
        return null;
    }

    var stubs = ['on', 'un', 'get', 'set', 'getModule', 'getResolutions', 'getCurrentResolution'];
    var CMS = mockstar.define(stubs, {
        /**
         *
         * @param id
         * @param [includeFormValues]
         * @returns {*}
         */
        get: function (id, includeFormValues) {
            return getItemFromArray(id, this.units);
        },

        getModule: function (id) {
            return getItemFromArray(id, this.modules);
        }
    });

    define('CMS', [], function () {
        return CMS;
    });
    global.CMS = CMS;

}(window));
