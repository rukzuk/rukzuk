Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.MediaDBButton
* @extends Ext.Button
* A dedicated button for opening a {@link CMS.mediaDB.MediaDBWindow}
* @requires CMS.mediaDB.MediaDBWindow
*/
CMS.mediaDB.MediaDBButton = Ext.extend(Ext.Button, {

    /**
    * @cfg {String} type (optional)
    * The type of the MediaDBWindow to show.
    * Will be passed to {@link CMS.mediaDB.MediaDBWindow#filterMediaType}
    * See there for valid values
    */
    filterType: '',

    /**
    * @cfg {Boolean} isSelector (optional)
    * Defines if the button is used as a media selector; if <tt>true</tt> (default), an "ok" button will be shown in the MediaDBWindow
    * Will be passed to {@link CMS.mediaDB.MediaDBWindow#setIsSelector}
    */
    isSelector: true,

    /**
    * @cfg {Boolean} multiSelect
    * <tt>true</tt> to make this component manage an array of media rather than a single medium.
    * Defaults to <tt>false</tt>
    */
    multiSelect: false,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @private
    * Mapping for button's type to MediaDBWindow type
    * Types not present in this config object will not be modified
    */
    typeMapping: {
        'download': ''
    },

    initComponent: function () {

        // support for legacy type prop
        if (this.type == 'image' && !this.filterType) {
            this.filterType = this.type;
        }

        if (this.text !== false) {
            this.text = this.text || CMS.i18n('MediaDB');
        }
        this.iconCls = this.iconCls || 'mediaDB';
        if (this.filterType) {
            this.iconCls = this.iconCls + ' ' + this.iconCls + '-' + this.filterType;
        }
        this.mappedType = this.filterType;
        if (this.typeMapping.hasOwnProperty(this.filterType)) {
            this.mappedType = this.typeMapping[this.filterType];
        }
        CMS.mediaDB.MediaDBButton.superclass.initComponent.apply(this, arguments);
        this.setValue(this.value);
    },

    handler: function (btn) {
        var win = CMS.mediaDB.MediaDBWindow.getInstance(this.websiteId, this.multiSelect);
        win.filterMediaType(this.mappedType);
        win.setIsSelector(this.isSelector);
        win.setSelectedFiles(this.value);
        win.show();
        this.mon(win, 'select', btn.selectHandler, btn, { single: true });
        this.mon(win, 'hide', function () { win.un('select', btn.selectHandler); win.setIsSelector(false); }, win, { single: true });
        this.mon(win, 'close', function () { win.un('select', btn.selectHandler); win.setIsSelector(false); }, win, { single: true });
    },


    // for use as a form element
    setValue: function (value) {
        if (!this.multiSelect && Ext.isArray(value)) {
            this.value = value[0];
        } else if (this.multiSelect && !Ext.isArray(value)) {
            this.value = [value];
        } else {
            this.value = value;
        }
    },

    // for use as a form element
    getValue: function (value) {
        if (this.multiSelect && !this.value) {
            return [];
        }
        return this.value;
    },

    /**
    * @private
    * Handler for mediaDBwindow's <tt>select</tt> event
    */
    selectHandler: function (records) {
        var oldValue = this.getValue();
        var newValue;
        if (this.multiSelect) {
            newValue = Ext.pluck(records, 'id');
        } else {
            newValue = records[0].id;
        }

        var equal = true;
        if (this.multiSelect) {
            if (oldValue.length != newValue.length) {
                equal = false;
            } else {
                for (var i = 0, l = newValue.length; i < l; i++) {
                    if (newValue[i] != oldValue[i]) {
                        equal = false;
                        break;
                    }
                }
            }
        } else {
            equal = (newValue == oldValue);
        }
        if (equal) {
            return;
        }
        this.setValue(newValue);
        this.fireEvent('change', this, newValue, oldValue);
    }

});

Ext.reg('CMSmediadbbutton', CMS.mediaDB.MediaDBButton);
