Ext.ns('CMS.form');

/**
 * @class CMS.form.KeyValueTextAreaConfigurable
 * A TextArea which allows a Key-Value with multiple values input. If one line contains only a key,
 * the key will be used as key and value. Multiple values are supported!
 *
 */
CMS.form.KeyValueTextAreaConfigurable = Ext.extend(Ext.form.TextArea, {

    /**
     * @cfg separator separates key with value1, value2 etc.
     */
    separator: ':',

    /**
     * @cfg number of allowed values, note: the key is not a value!
     */
    numOfValues: 1,

    initComponent: function () {
        CMS.form.KeyValueTextAreaConfigurable.superclass.initComponent.call(this);
    },


    /**
     * Generates a array which holds key-value arrays which are generated from the entered text
     * @return {Array} key/value array e.g.: [[k,v1_1,v1_2,...],[k2,v2_1, v2_2,...]]
     */
    getValue: function () {
        var text = CMS.form.KeyValueTextAreaConfigurable.superclass.getValue.apply(this, arguments);

        var result = [];
        var lines = text.split('\n');

        Ext.each(lines, function (curLine) {
            var key,
                value;

            if (/^\s*$/.test(curLine)) {
                return false; // ignore blank lines
            }
            if (curLine.indexOf(this.separator) != -1) {
                var lineArr = curLine.split(this.separator, this.numOfValues + 1);
                key = lineArr[0];
                value = lineArr.slice(1);
            } else {
                key = curLine;
                value = curLine;
            }

            //set key to empty string if key is empty
            if (key.trim().length === 0) {
                key = '';
            }
            //set value to non breaking space if value is empty
            //if (value.trim().length === 0) {
            //    value = '\u00A0';
            //}

            result.push(Ext.flatten([key, value]));
        }, this);
        return result;
    },

    /**
     * Method which fills the textarea with 'a key:value1:value2' list
     */
    setValue: function (rawvalue) {
        var valueArr = [];
        Ext.each(rawvalue, function (curSet) {
            // join by separator char
            var  value = curSet.join(this.separator);
            valueArr.push(value);
        }, this);
        CMS.form.KeyValueTextAreaConfigurable.superclass.setValue.call(this, valueArr.join('\n'));
    }
});

Ext.reg('CMSkeyvaluetextareaconfigurable', CMS.form.KeyValueTextAreaConfigurable);
