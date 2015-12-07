Ext.ns('CMS.form');

/**
 * A SB.form.ColorSchemePicker combined with an edit button
 * @class CMS.form.ColorSchemePickerWithEditButton
 * @extends Ext.Container
 */
CMS.form.ColorSchemePickerWithEditButton = Ext.extend(Ext.Container, {
    /** @lends CMS.form.ColorSchemePickerWithEditButton.prototype */

    layout: 'hbox',
    layoutConfig: {
        align: 'middle'
    },

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    initComponent: function () {

        this.items = [{
            xtype: 'ux-colorschemepicker',
            ref: 'pickerField',
            emptyText: CMS.i18n('Bitte wählen…'),
            unknownColor: CMS.i18n('Unbekannte Farbe'),
            name: 'color',
            flex: 1,
            websiteId: this.websiteId
        }];

        try {
            var website = CMS.data.WebsiteStore.getInstance().getById(this.websiteId);
            if (CMS.app.userInfo.canManageColors(website)) {
                this.items.push({
                    xtype: 'button',
                    ref: 'editButton',
                    iconCls: 'edit',
                    cls: 'smallBtn lightBtn',
                    tooltip: CMS.i18n('Farbschema bearbeiten'),
                    handler: this.editButtonHandler,
                    scope: this,
                    margins: '0 0 0 8'
                });
            }
        } catch(e) {
            console.warn('[ColorSchemePickerWithEditButton] could not get website rights', e);
        }

        CMS.form.ColorSchemePickerWithEditButton.superclass.initComponent.apply(this, arguments);

        this.setValue(this.value);
        this.relayEvents(this.pickerField, ['change', 'select']);
    },

    isXType: function (xtype) { // this is required to convince the GeneratedFormPanel that this is a combo box
        if (xtype === 'combo') {
            return true;
        }
        return Ext.Component.prototype.isXType.apply(this, arguments);
    },

    setValue: function (value) {
        var oldVal = this.getValue();
        if (oldVal == value) {
            return;
        }
        this.pickerField.setValue(value);
    },

    getValue: function () {
        return this.pickerField.getValue();
    },

    /**
    * @private
    * Handler for the "edit" button
    */
    editButtonHandler: function () {
        var win = new Ext.Window({
            title: CMS.i18n('Farbschema bearbeiten'),
            modal: true,
            width: 600,
            height: 500,
            layout: 'fit',
            items: {
                xtype: 'CMScolorschemedefinitionpanel',
                websiteId: this.websiteId,
                listeners: {
                    savesuccess: function () {
                        win.destroy();
                    },
                    destroy: function () {
                        win = null;
                    }
                }
            }
        });
        win.show();
    }
});

Ext.reg('CMScolorschemepickerwitheditbutton', CMS.form.ColorSchemePickerWithEditButton);
