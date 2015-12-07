Ext.ns('Ext.ux');

/**
* @class Ext.ux.UnixDateField
* @extends Ext.form.DateField
* A datefield that accepts a unix timestamp as a value
*/
Ext.ux.UnixDateField = Ext.extend(Ext.form.DateField, {

    initComponent: function () {
        if (!this.value) {
            this.value = new Date();
        }

        Ext.ux.UnixDateField.superclass.initComponent.apply(this, arguments);

        // fire change event on select to have simpler logic in GeneratedFormPanel (SBCMS-996)
        this.on('select', function (cmp) {
            if (String(this.getValue()) !== String(this.startValue)) {
                this.fireEvent('change', this, this.getValue(), this.startValue);
            }
        }, this);
    },

    setValue: function (v) {
        if (Ext.isNumber(v) || (typeof v == 'string' && +v > 0)) {
            v = this.formatDate(SB.date.dateFromUnixTimeStamp(v));
        } else if (Ext.isDate(v)) {
            v = this.formatDate(v);
        }
        if (this.rendered) {
            return Ext.form.DateField.superclass.setValue.call(this, v);
        } else {
            this.value = v;
        }
    },

    getValue: function () {
        var date = Ext.ux.UnixDateField.superclass.getValue.call(this);
        return SB.date.unixTimeStampFromDate(date);
    },

    onTriggerClick: function () {
        var myGetValue = this.getValue;
        this.getValue = Ext.ux.UnixDateField.superclass.getValue;
        Ext.ux.UnixDateField.superclass.onTriggerClick.apply(this, arguments); // calls this.getValue()
        this.getValue = myGetValue;

        // the getValue remapping before sets startValue to a string, so we need to set it again to be a timestamp
        this.startValue = this.getValue();
    }
});

Ext.reg('ux-unixdatefield', Ext.ux.UnixDateField);
