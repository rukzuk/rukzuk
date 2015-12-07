Ext.ns('CMS.form');

/**
* @class CMS.form.ClearableColorField
* @extends Ext.Container
* An {@link Ext.ux.form.ColorPickerField} with a "clear" button. Can be used as a form field.
*/
CMS.form.ClearableColorField = Ext.extend(Ext.Container, {

    /**
    * @cfg {Boolean} setTransparentOnClear
    * <tt>false</tt> to prevent setting the field value to <tt>transparent</tt> when the clear button is pressed.
    * Defaults to <tt>true</tt>
    */
    setTransparentOnClear: true,

    /**
    * @cfg {Object} pickerCfg
    * (optional) Additional config parameters passed to the ColorPickerField
    */
    pickerCfg: {},

    layout: 'hbox',

    initComponent: function () {
        this.items = [{
            xtype: 'textfield',
            ref: 'textField',
            selectOnFocus: true,
            name: 'name',
            emptyText: CMS.i18n('Bezeichnung'),
            margins: '0 5 0 0',
            flex: 1
        }, Ext.apply({
            xtype: 'ux-colorpickerfield',
            ref: 'pickerField',
            name: 'color',
            flex: 1
        }, this.pickerCfg), {
            xtype: 'button',
            handler: this.clearButtonHandler,
            text: '&#160;&#160;&#160;',
            tooltip: CMS.i18n('Farbe entfernen'),
            scope: this,
            cls: 'CMSclearbutton',
            width: 25,
            margins: '0 0 0 5'
        }];

        CMS.form.ClearableColorField.superclass.initComponent.apply(this, arguments);

        this.setValue(this.value);
    },

    setValue: function (value) {
        if (typeof value == 'string') {
            value = {
                value: value
            };
        }
        var oldVal = this.getValue();
        if (oldVal.value == value.value && oldVal.id == value.id && oldVal.name == value.name) {
            return;
        }
        this.pickerField.setValue(value.value);
        this.textField.setValue(value.name || '');
        this.colorId = value.id || ('COLOR-' + SB.util.UUID() + '--000000000000--COLOR');
        this.fireEvent('change', this, value, oldVal);
    },

    getValue: function () {
        return {
            id: this.colorId,
            value: this.pickerField.getValue(),
            name: this.textField.getValue()
        };
    },

    /**
    * @private
    * Handler for the "clear" button
    */
    clearButtonHandler: function () {
        /**
        * @event clear
        * Fired when the clear button is pressed
        * @param field This component
        */
        this.fireEvent('clear', this);
        if (!this.isDestroyed && this.setTransparentOnClear) {
            this.pickerField.setValue('transparent');
        }
    }
});

Ext.reg('CMSclearablecolorfield', CMS.form.ClearableColorField);
