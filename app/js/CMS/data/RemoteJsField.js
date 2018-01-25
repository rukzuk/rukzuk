/**
 * This data type means that the raw data is converted into an Object before it is placed into a Record, using {@link Ext.decode}.
 *
 * @property JSON
 * @member Ext.data.Types
 */
Ext.data.Types.REMOTEJS = {
    convert: function (v) {
        if (typeof v === 'string') {
            var js = {
                loaded: false
            };
            if (v && v !== '') {
                console.log("[Ext.data.Types.REMOTEJS] load remote javascript", v);
                Ext.Loader.load([v], function () {
                    js.loaded = true;
                }, this);
            }
            return js;
        }
        return v;
    },
    sortType: Ext.data.SortTypes.none,
    type: 'remotejs'
};
