/**
 * This data type means that the raw data is converted into an Array before it is placed into a Record.
 * First, {@link Ext.decode} is used to try to decode. If that fails, the input is parsed as a comma-separated list.
 *
 * @property ARRAY
 * @member Ext.data.Types
 */
Ext.data.Types.ARRAY = {
    convert: function (v, rec) {
        var result;
        if (!v) {
            return [];
        }
        if (Ext.isArray(v)) {
            return v;
        }
        if (/^\[/.test(v) && /\]$/.test(v)) {
            // looks like an array. try to decode.
            try {
                result = Ext.decode(v);
                return result;
            } catch (e) {}
        }
        // manual interpretation
        v = v.replace(/^\[(.*)\]$/, '$1');
        result = v.split(',');
        for (var i = 0; i < result.length; i++) {
            var word = result[i];
            if (/^"/.test(word)) {
                result[i] = result[i].replace(/^"(.*)"$/, '$1');
            } else if (word == 'true') {
                result[i] = true;
            } else if (word == 'false') {
                result[i] = false;
            } else if (word == 'null') {
                result[i] = null;
            } else if (/^[0-9.\-]/.test(word)) {
                result[i] = parseFloat(i);
            }
        }
        return result;
    },
    sortType: Ext.data.SortTypes.none,
    type: 'array'
};
