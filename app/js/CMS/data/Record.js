Ext.ns('CMS.data');

/**
* @class CMS.data.Record
* @extends Ext.data.Record
* Improved version of Ext.data.Record that applies the defaultValue properties of the corresponding fields
* @singleton
*/
CMS.data.Record = Ext.extend(Ext.data.Record, {

    constructor: function (data, id) {
        this.id = (id || id === 0) ? id : Ext.data.Record.id(this);
        this.data = data || {};
        if (!!this.fields) {
            this.fields.each(function (field) {
                // apply defaultValue if necessary
                if (!field.allowBlank && !this.data.hasOwnProperty(field.name)) {
                    if (typeof field.defaultValue == 'function') {
                        this.data[field.name] = field.defaultValue();
                    } else if (field.hasOwnProperty('defaultValue')) {
                        this.data[field.name] = SB.util.cloneObject(field.defaultValue);
                    }
                }
            }, this);
        }
        this.cleanup(); // remove legacy data from DB garbage
    },

    /**
     * Make sure the record's data contains only the fields defined in the record constructor.
     * All other fields will be removed.
     * @private
     */
    cleanup: function () {
        var superfluousKeys = SB.util.setDifference(SB.util.getKeys(this.data), this.fields.keys);
        Ext.each(superfluousKeys, function (key) {
            delete this.data[key];
        }, this);

        // HACK for SBCMS-415
        // can be removed when Zend JSON encoder has been fixed (SBCMS-307)
        if (Ext.isArray(this.data.formValues)) {
            CMS.console.warn('(SBCMS-415) Converting formValues:[] to formValues:{}');
            this.data.formValues = {};
        }
    },

    // @private override to handle special types of fields
    // -> Can be removed when SBCMS-307 is solved
    /*
    get: function (name) {
        var result = Ext.data.Record.prototype.get.apply(this, arguments);
        var field = this.fields.get(name);
        var type = result && field && field.type;
        switch (type && type.type) {
        case 'array':
        case 'json':
            return type.convert(result);
            break;
        default:
            return result;
            break;
        }
    },
    */

    /**
    * Create a normalized JSON representation of the record's data.
    * It can be used to compare two records for data equivalence.
    */
    dataToJSON: function () {
        var result = '{';
        var keys = SB.util.getKeys(this.data);
        keys.sort();
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            if (i > 0) {
                result += ',';
            }
            // HACK for SBCMS-307: we need to call get() here which will convert strings if required
            result += ('"' + key + '":' + this.get(key));
        }
        result += '}';
        return result;
    }
});

/**
* Just like Ext.data.Record.create, this method allows to create record constructors
* Calling such a constructor applies the defaultValue properties of the corresponding fields
*/
CMS.data.Record.create = function (o) {
    var f = Ext.extend(CMS.data.Record, {});
    var p = f.prototype;
    p.fields = new Ext.util.MixedCollection(false, function (field) {
        return field.name;
    });
    for (var i = 0, len = o.length; i < len; i++) {
        p.fields.add(new Ext.data.Field(o[i]));
    }
    f.getField = function (name) {
        return p.fields.get(name);
    };
    return f;
};
