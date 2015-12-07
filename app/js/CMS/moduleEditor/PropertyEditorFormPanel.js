Ext.ns('CMS.moduleEditor');

/**
* Formpanel which allows moduleform property editing
*
* @class CMS.moduleEditor.PropertyEditorFormPanel
* @extends Ext.FormPanel
*/
CMS.moduleEditor.PropertyEditorFormPanel = Ext.extend(Ext.FormPanel, {
    disabled: true,
    monitorValid: true,

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    /**
    * @property {Ext.form.Field} cmp
    * Current selected form component
    */
    cmp: null,

    initComponent: function () {
        Ext.apply(this, {
            bodyStyle: {
                padding: '5px 10px'
            },
            defaults: {
                anchor: '-' + Ext.getScrollBarWidth()
            },
            title: CMS.i18n('Feldeigenschaften')
        });
        CMS.moduleEditor.PropertyEditorFormPanel.superclass.initComponent.apply(this, arguments);

        this.on('CMSpropertychange', function () {
            this.changeHandler.defer(10, this); // wait for validation task
        }, this);
    },

    /**
    * Open the config of a form element
    * @param {Array} params
    */
    loadFormElData: function (cmp, callback) {

        if (!cmp) {
            this.clear();
            return;
        }

        this.origParams = SB.util.cloneObject(cmp.params);

        if (this.disabled) {
            this.enable();
        }
        this.cmp = cmp;
        this.callback = callback;

        if (!this.refreshingComponent) {
            this.removeAll(true);
            var contentCfg = [];
            var convertedObject = CMS.form.LegacyComponentConverter.convert(cmp.params);
            if (convertedObject.errors) {
                Ext.each(convertedObject.errors, function (error) {
                    switch (error.type) {
                    case 'converted':
                        console.warn('[PropertEditorFormPanel] legacy xtype "', error.xtypes.before, '" in form config. Converting to "', error.xtypes.after, '".');
                        break;
                    case 'unknown':
                        var warningText = '[PropertEditorFormPanel] Unknown xtype " ' + error.xtypes.before + ' " in form config. Obsolete database entry? Falling back to "' + error.xtypes.after + '".';
                        console.warn(warningText);
                        CMS.Message.error(CMS.i18n('Ungültiges Modul'), CMS.i18n('Nicht kompatibel mit aktueller CMS-Version'));
                        CMS.app.ErrorManager.push(CMS.i18n('Ungültiges Feld:\n----------------\n{source}').replace('{source}', JSON.stringify(cmp.params, undefined, 2)));
                        CMS.app.ErrorManager.push(warningText + CMS.i18n('\n\n----\n\nConfig:\n{config}\n\n----\n\nForm Element Description:\n{descr}').replace('{config}', Ext.encode(cmp.params)).replace('{descr}', Ext.encode(cmp.descr)));
                        break;
                    }
                });
            }
            Ext.each(convertedObject.parameters, function (param) {
                if (param.xtype) {
                    param.name = Ext.id();
                    param.websiteId = this.websiteId;

                    // i18n Support via __i18n_ macro
                    this.translateParam(param);

                    contentCfg.push(param);
                }
            }, this);
            if (!contentCfg.length) {
                this.disable();
            } else {
                this.enable();
                this.add(contentCfg);
            }
            this.doLayout();

            this.items.each(function (item) {
                var changeEvent = 'change';
                if (item.isXType('checkbox')) {
                    changeEvent = 'check';
                } else if (item.isXType('combo')) {
                    changeEvent = 'select';
                }
                item.on(changeEvent, function (field) {
                    this.fireEvent('CMSpropertychange');
                }, this);
            }, this);

            //if its a new field, focus the first property field (should always be CMSvar)
            if (this.items.getCount() > 0) {
                var firstItem = this.get(0);
                if (!firstItem.getValue()) {
                    this.get(0).focus(true, true);
                }
            }
        } else {
            this.refreshingComponent = false;
        }
    },

    /**
    * Returns the edited formfield data
    * or the original data if form is not dirty
    */
    getData: function () {
        var result = this.origParams;
        var formItems = this.items.getRange();
        for (var i = 0, j = 0, l = result.length; i < l; i++) {
            if (result[i].xtype) { // skip non-configurable values
                result[i].value = formItems[j].getValue();
                j++;
            }
        }
        return result;
    },

    /**
    * Removes all formfields and disables the cmp
    */
    clear: function () {
        this.removeAll(true);
        this.disable();
    },

    /**
    * @private
    */
    changeHandler: function () {
        this.refreshingComponent = true;
        this.callback(this.cmp, 'changed', this.getData());
    },

    /**
     * Translates all strings in a param object with macros
     * @param param
     * @private
     */
    translateParam: function (param) {
        // find and translate first level string properties
        Object.keys(param).forEach(function (key) {
            param[key] = CMS.i18nTranslateMacroString(param[key]);
        });

        // handle array stores ([[id, val],[id,val]])
        if (Ext.isArray(param.store)) {
            param.store.forEach(function (storeItems) {
                if (Ext.isArray(storeItems)) {
                    storeItems.forEach(function (item, idx) {
                        // exclude record ids
                        if (idx !== 0) {
                            storeItems[idx] = CMS.i18nTranslateMacroString(storeItems[idx]);
                        }
                    });
                }
            });
        }
    }

});
Ext.reg('CMSpropertyeditorformpanel', CMS.moduleEditor.PropertyEditorFormPanel);
