Ext.ns('CMS.form');

/**
* @class CMS.form.ClearableMediaButton
* @extends Ext.Container
* A {@link CMS.mediaDB.MediaDBButton} with a "clear" button. Can be used as a form field.
* @requires CMS.mediaDB.MediaDBButton
*/
CMS.form.ClearableMediaButton = Ext.extend(Ext.Container, {
    /**
    * @cfg {Boolean} showText (For use as a form field)
    * <tt>false</tt> to not show the current value as a text next to the button.
    * Defaults to <tt>true</tt>.
    */
    showText: true,

    /**
    * @cfg {String} type (optional)
    * The type of the MediaDBWindow to show.
    * Will be passed to {@link CMS.mediaDB.MediaDBWindow#filterMediaType}
    * See there for valid values
    */
    filterType: '',

    /**
    * @cfg {Boolean} multiSelect
    * <tt>true</tt> to make this component manage an array of media rather than a single medium.
    * Defaults to <tt>false</tt>
    */
    multiSelect: false,

    iconCls: 'mediaDB',

    layoutConfig: {
        defaultMargins: {top: 0, right: 1, bottom: 0, left: 0}
    },

    autoHeight: true,

    value: null,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    initComponent: function () {

        // support for legacy type prop
        if (this.type == 'image' && !this.filterType) {
            this.filterType = this.type;
        }

        if (!this.multiSelect) {
            this.layout = 'hbox';
        }

        this.items = [{
            xtype: 'CMSmediadbbutton',
            filterType: this.filterType,
            iconCls: this.iconCls,
            disabled: this.disabled,
            text: false,
            tooltip: this.text,
            ref: 'mediaDbButton',
            multiSelect: this.multiSelect,
            websiteId: this.websiteId,
            isSelector: true,
            value: this.value,
            listeners: {
                change: this.changeHandler,
                scope: this
            }
        }, {
            xtype: 'button',
            ref: 'clearButton',
            disabled: this.disabled || !this.value,
            handler: this.clearButtonHandler,
            tooltip: CMS.i18n('zurücksetzen'),
            scope: this,
            iconCls: 'delete',
            hidden: this.multiSelect
        }, {
            xtype: 'ux-multilinelabel',
            cls: 'CMSmedialabel x-form-item' + (this.multiSelect ? ' CMSmedialabel-multiselect' : ''),
            ref: 'textEl',
            flex: 1,
            autoHeight: true,
            text: ''
        }];

        CMS.form.ClearableMediaButton.superclass.initComponent.apply(this, arguments);

        this.setValue(this.value);

        this.getRawValue = this.getValue;
        this.setRawValue = this.setValue;

        // update preview if media has been changed
        try {
            var mediaStore = CMS.data.StoreManager.get('media', this.websiteId, {disableLoad: true});
            this.mon(mediaStore, 'CMSmediachanged', function () {
                if (this.rendered) {
                    this.renderValue();
                }
            }, this);
        } catch(e) {
            console.warn('[ClearableMediaButton] could not register updates of website', e);
        }
    },

    setValue: function (value) {
        var oldVal = this.value;
        this.clearButton.setDisabled(!value);
        if (!this.multiSelect && Ext.isArray(value)) {
            this.value = value[0];
        } else if (this.multiSelect && !Ext.isArray(value)) {
            this.value = value ? [value] : [];
        } else {
            this.value = value;
        }
        this.mediaDbButton.setValue(this.value);
        this.renderValue();
        if (oldVal != this.value) {
            this.fireEvent('change', this, this.value, oldVal);
        }
    },

    getValue: function () {
        return this.value;
    },

    /**
    * @private
    */
    renderValue: function () {
        this.textEl.setText(' ');
        if (this.requestId) {
            CMS.app.trafficManager.abortRequest(this.requestId);
        }
        if (this.value === '' || this.value === undefined || this.value === null || (this.multiSelect && !this.value.length) || !this.showText) {
            return;
        }
        if (this.multiSelect) {
            this.requestId = CMS.app.trafficManager.sendRequest({
                action: 'getMultipleMedia',
                data: {
                    websiteId: this.websiteId,
                    ids: this.value
                },
                successCondition: CMS.config.roots.getMultipleMedia,
                scope: this,
                success: function (resp) {
                    var response = SB.util.getObjectByIndexPath(resp, CMS.config.roots.getMultipleMedia);
                    var renderValues = [];
                    Ext.each(this.value, function (id) {
                        if (response[id]) {
                            renderValues.push([response[id].icon, response[id].name]);
                        } else {
                            renderValues.push([CMS.config.urls.errorImg, CMS.i18n('DATEI NICHT GEFUNDEN: {name}').replace('{name}', id)]);
                        }
                    });
                    var text = '';
                    Ext.each(renderValues, function (val) {
                        text += '<img class="selected-img multi" src="' + val[0] + '" alt="" title="' + val[1] + '" >';
                    });
                    this.textEl.setText(text, false);
                },
                failure: function () {
                    this.textEl.setText(CMS.i18n('Fehler beim Laden der Vorschau'));
                },
                callback: function () {
                    this.requestId = null;
                    this.textEl.setHeight('auto');
                    this.doLayout();
                }
            });
        } else {
            this.requestId = CMS.app.trafficManager.sendRequest({
                action: 'getMedium',
                data: {
                    websiteId: this.websiteId,
                    id: this.value
                },
                successCondition: 'data.name',
                scope: this,
                success: function (resp) {
                    var url = resp.data.icon;
                    var text = resp.data.name;
                    if (this.textEl) {
                        this.textEl.setText('<img class="selected-img" src="' + url + '" alt="" title="' + text + '"><span class="selected-text">' + text + '</span>', false);
                    }
                },
                failure: function () {
                    this.textEl.setText(CMS.i18n('DATEI NICHT GEFUNDEN: {name}').replace('{name}', this.value));
                },
                callback: function () {
                    this.requestId = null;
                }
            });
        }
    },

    /**
    * @private
    * Handler for the change event from mediaDbButton
    */
    changeHandler: function (mediaDbButton, newVal, oldVal) {
        this.setValue(newVal);
    },

    /**
    * @private
    * Handler for the "clear" button
    */
    clearButtonHandler: function () {
        this.setValue(null);
        if (this.multiSelect) {
            this.textEl.setHeight('auto');
            this.doLayout();
        }
    },

    destroy: function () {
        if (this.requestId) {
            CMS.app.trafficManager.abortRequest(this.requestId);
        }
        CMS.form.ClearableMediaButton.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSclearablemediabutton', CMS.form.ClearableMediaButton);
