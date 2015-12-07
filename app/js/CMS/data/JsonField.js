/**
 * This data type means that the raw data is converted into an Object before it is placed into a Record, using {@link Ext.decode}.
 *
 * @property JSON
 * @member Ext.data.Types
 */
Ext.data.Types.JSON = {
    convert: function (v) {
        if (typeof v === 'string') {
            return Ext.decode(v);
        }
        return v;
    },
    sortType: Ext.data.SortTypes.none,
    type: 'json'
};
