Ext.ns('CMS.home');

/**
* @class CMS.home.ColorSchemeDefinitionPanel
* @extends Ext.Panel
* A panel for defining color schemes, using several {@link CMS.form.ClearableColorField}s.
*/
CMS.home.ColorSchemeDefinitionPanel = Ext.extend(CMS.home.ManagementPanel, {
    /**
    * @cfg {Array} values
    * The color values to be initially rendered.
    */
    values: [],

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    cls: 'CMScolorschemedefinitionpanel',

    initComponent: function () {
        Ext.apply(this, {
            layout: 'vbox',
            layoutConfig: {
                align: 'stretch',
                pack: 'start',
                defaultMargins: '10 ' + (10 + Ext.getPreciseScrollBarWidth()) + ' 0 10'
            }
        });
        this.items = [];
        if (this.values && this.values.length) {
            Ext.each(this.values, this.addField, this);
        }

        this.bbar = [{
            xtype: 'button',
            iconCls: 'add addcolor',
            text: CMS.i18n('Farbe hinzufügen'),
            handler: this.addBtnHandler,
            scope: this
        }, '->', {
            text: CMS.i18n('Speichern'),
            iconCls: 'save savecolorscheme',
            cls: 'primary',
            handler: this.saveBtnHandler,
            scope: this
        }];
        var websiteId = this.websiteId;
        delete this.websiteId;
        this.setSite(websiteId);

        CMS.home.ColorSchemeDefinitionPanel.superclass.initComponent.apply(this, arguments);
    },

    addBtnHandler: function () {
        this.addField();
    },

    saveBtnHandler: function () {
        var cfg = [];
        this.items.each(function (item) {
            if (item.xtype !== 'CMSclearablecolorfield') {
                return;
            }
            cfg.push(item.getValue());
        }, this);

        CMS.app.trafficManager.sendRequest({
            action: 'editColorScheme',
            data: {
                id: this.websiteId,
                colorscheme: cfg
            },
            scope: this,
            success: function () {
                CMS.Message.toast(CMS.i18n('Farbschema erfolgreich gespeichert.'));
                this.updateWebsiteRecord(cfg);
                /**
                * @event savesuccess
                * Fired when the color scheme was successsfully saved
                * @param {CMS.home.ColorSchemeDefinitionPanel} this This panel
                */
                this.fireEvent('savesuccess', this);
            },
            failureTitle: CMS.i18n('Fehler beim Speichern des Farbschemas')
        });
    },

    doLayout: function () {
        CMS.home.ColorSchemeDefinitionPanel.superclass.doLayout.apply(this, arguments);
        this.getContentTarget().dom.firstChild.style.overflowY = 'auto';
    },

    /**
    * Updates the websiteRecord 'colorscheme' attribute
    * @param {Array} colorSchemes Array which contains all defined colorschemes
    */
    updateWebsiteRecord: function (colorSchemes) {
        var store = Ext.StoreMgr.lookup('websites');
        var websiteRecord = store.getById(this.websiteId);
        if (!websiteRecord) {
            return;
        }
        websiteRecord.set('colorscheme', colorSchemes);
    },

    removeBtnHandler: function (cmp) {
        Ext.MessageBox.confirm(CMS.i18n('Farbe entfernen?'), CMS.i18n('Farbe „{name}“ wirklich entfernen?').replace('{name}', cmp.getValue().name || CMS.i18n('(unbenannt)')), function (btnId) {
            if (btnId === 'yes') {
                this.remove(cmp);
                this.doLayout();
            }
        }, this);
    },

    /**
    * Add a color field
    * @param {String} value (optional) The new field's initial value
    */
    addField: function (value, doLayout) {
        value = value || '#FFFFFF';
        var fieldCfg = {
            xtype: 'CMSclearablecolorfield',
            pickerCfg: {
                enableAlpha: true,
                fireChangeOnDrag: false
            },
            fireChangeOnDrag: false,
            setTransparentOnClear: false,
            value: value,
            listeners: {
                clear: this.removeBtnHandler,
                scope: this
            }
        };
        if (this.rendered) {
            var newField = this.add(fieldCfg);
            if (doLayout !== false) {
                this.doLayout();
            }
            newField.textField.setValue(CMS.i18n('Farbe {index}').replace('{index}', this.items.indexOf(newField) + 1));
            newField.textField.focus(true);
        } else {
            this.items.unshift(fieldCfg);
        }
    },

    /**
    * Open the specified site
    * @param {CMS.data.WebsiteRecord|String} record The site to be opened
    */
    setSite: function (record) {
        if (Ext.isString(record)) {
            record = Ext.StoreMgr.lookup('websites').getById(record);
        }
        if (!record || record.id === this.websiteId) {
            return;
        }
        CMS.home.ColorSchemeDefinitionPanel.superclass.setSite.call(this, record);
        var scheme = record.get('colorscheme');
        var currentFieldCount = (this.items.getCount ? this.items.getCount() : this.items.length);
        while (scheme.length < currentFieldCount) {
            if (this.rendered) {
                this.remove(0);
            } else {
                this.items.shift();
            }
            currentFieldCount--;
        }
        while (scheme.length > currentFieldCount) {
            this.addField(null, false);
            currentFieldCount++;
        }
        Ext.each(scheme, function (val, i) {
            if (this.rendered) {
                this.items.get(i).setValue(val);
            } else {
                this.items[i].value = val;
            }
        }, this);
        if (this.rendered) {
            this.doLayout();
        }
    }
});

Ext.reg('CMScolorschemedefinitionpanel', CMS.home.ColorSchemeDefinitionPanel);
