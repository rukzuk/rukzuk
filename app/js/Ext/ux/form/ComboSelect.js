Ext.ns('Ext.ux.form');

/**
* @class Ext.ux.form.ComboSelect
* @extends Ext.form.ComboBox
* A combobox simulating an ordinary select field, but allowing manual typeahead input
* @requires Ext.ux.EncodingDataView
*/
Ext.ux.form.ComboSelect = Ext.extend(Ext.form.ComboBox, {

    forceSelection: true,

    isAfterSelect: false, // needed to prevent change event firing after the select event

    constructor: function (config) {
        Ext.apply(this, {
            typeAhead: true,
            triggerAction: 'all',
            mode: 'local',
            store: {
                xtype: 'arraystore',
                fields: ['value', 'text'],
                data: config.options
            },
            displayField: 'text',
            valueField: 'value',
            listeners: {
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        if (!this.isAfterSelect) {
                            // update value of the field if enter is pressed (needed for key handling in GeneratedFormPanel)
                            this.assertValue();
                        } else {
                            // stop event propagation
                            return false;
                        }
                    }
                },
                scope: this
            }
        });
        Ext.ux.form.ComboSelect.superclass.constructor.call(this, config);
        
        this.on('select', function () {
            this.isAfterSelect = true;
        }, this);
        
        // also fire the select event if the value was typed in manually (SBCMS-516)
        this.on('change', function () {
            if (!this.isAfterSelect) {
                this.fireEvent('select', this, null, null);
            }
        }, this);
    },

    // nasty hack to use EncodingDataView
    initList: function () {
        var origDataView = Ext.DataView;
        Ext.DataView = Ext.ux.EncodingDataView;
        Ext.DataView.superclass = origDataView.superclass;
        Ext.ux.form.ComboSelect.superclass.initList.apply(this, arguments);
        Ext.DataView = origDataView;
        Ext.ux.EncodingDataView.superclass = Ext.DataView;
    },
    
    // overwriting onKeyUp to prevent change event firing after the select event
    onKeyUp: function (e) {
        Ext.ux.form.ComboSelect.superclass.onKeyUp.call(this, e);
        this.isAfterSelect = false;
    }
});

Ext.reg('ux-comboselect', Ext.ux.form.ComboSelect);
