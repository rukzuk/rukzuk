Ext.ns('CMS.form');

/**
* @class CMS.form.ToggleButton
* @extends Ext.Button
* A button that can be configured to be 'toggleable'. If enableToggle is <tt>true</tt>, it will save its value.
* Otherwise it will return <tt>null</tt> as its value.
* Can be used as a form field.
*/
CMS.form.ToggleButton = Ext.extend(Ext.Button, {
    value: false,

    initComponent: function () {

        this.addClass('CMStogglebutton');
        this.addClass('smallBtn');

        CMS.form.ToggleButton.superclass.initComponent.apply(this, arguments);

        this.getRawValue = this.getValue;
        this.setRawValue = this.setValue;

        if (this.enableToggle) {
            this.pressed = this.value;
            this.on('toggle', this.toggleHandler, this);
        } else {
            this.on('click', this.clickHandler, this);
        }
    },

    clickHandler: function () {
        this.fireEvent('change', this, null);
    },

    toggleHandler: function () {
        this.setValue(this.pressed);
    },

    setValue: function (value) {
        if (!this.enableToggle) {
            return;
        }

        var oldVal = this.value;
        this.value = value;

        if (oldVal != this.value) {
            if (this.enableToggle) {
                this.toggle(this.value, true);
            }
            this.fireEvent('change', this, this.value, oldVal);
        }
    },

    getValue: function () {
        if (!this.enableToggle) {
            return null;
        }

        return this.value;
    }
});

Ext.reg('CMStogglebutton', CMS.form.ToggleButton);
