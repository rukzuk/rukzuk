Ext.ns('CMS.form');

/**
* @class CMS.form.KeyValueTextArea
* A TextArea which allows Key-Value input. If one line contains only a key,
* the key will be used as key and value
*
* e.G:
* Key1:Value1
* Key2:Value2
* Key3
*/
CMS.form.KeyValueTextArea = Ext.extend(Ext.form.TextArea, {

    initComponent: function () {
        CMS.form.KeyValueTextArea.superclass.initComponent.call(this);
    },


    /**
     * Generates a array which holds key-value arrays which are generated from the entered text
     * @return {Array} key/value array e.g.: [[k,v],[k2,v2]]
     */
    getValue: function () {
        var text = CMS.form.KeyValueTextArea.superclass.getValue.apply(this, arguments);

        var result = [];
        var lines = text.split('\n');

        Ext.each(lines, function (curLine) {
            var key,
                value,
                hierarchy = 0;

            if (/^\s*$/.test(curLine)) {
                return false; // ignore blank lines
            }
            if (curLine.indexOf(':') != -1) {
                var lineArr = curLine.split(':');

                key = lineArr[0];
                value = lineArr.slice(1).join(':');

                // hierarchy support with dashes (-)
                var keyDashMatch = key.match(/^(\-+) (.*)/);
                if (keyDashMatch) {
                    hierarchy = keyDashMatch[1].length;
                    key = keyDashMatch[2];
                }

            } else {
                key = curLine;
                value = curLine;
            }

            //set key to empty string if key is empty
            if (key.trim().length === 0) {
                key = '';
            }
            //set value to non breaking space if value is empty
            if (value.trim().length === 0) {
                value = '\u00A0';
            }

            result.push([key, value, hierarchy]);
        });
        return result;
    },

    /**
     * Method which fills the textarea with 'a key:value' list
     * support for '- key:value' number of dashes (-) are defined by rawvalue[2]
     */
    setValue: function (rawvalue) {
        var valueArr = [];
        Ext.each(rawvalue, function (curSet) {

            // join [0] and [1]
            var  value = curSet[0] + ':' + curSet[1];

            // add dashes
            if (curSet[2] && curSet[2] > 0) {
                value = new Array(curSet[2] + 1).join('-') + ' ' + value; // crazy form of String.repeat which does not exist so far
            }
            valueArr.push(value);

        });
        CMS.form.KeyValueTextArea.superclass.setValue.call(this, valueArr.join('\n'));
    }
});

Ext.reg('CMSkeyvaluetextarea', CMS.form.KeyValueTextArea);
