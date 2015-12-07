Ext.ns('CMS.form');

/**
* @class CMS.form.AddRecordComboBox
* @extends Ext.form.ComboBox
*
* Provides basic functionality for adding records via a combobox.
*
*/
CMS.form.AddRecordComboBox = Ext.extend(Ext.form.ComboBox, {

    /**
    * @cfg {String} storeType
    * The store which should be associated with the combobox.
    */
    storeType: '',

    /**
    * @cfg {String} websiteId
    * The id of the currently opened website
    */
    websiteId: null,

    bubbleEvents: ['recordSelected', 'noRecordSelected'],
    initComponent: function () {
        var config = {
            triggerAction: 'all',
            enableKeyEvents: true,
            forceSelection: true,
            bubbleEvent: ['select'],
            mode: 'local',
            minChars: 2,
            listeners: {
                focus: this.focusHandler,
                select: this.selectHandler,
                blur: this.blurHandler,
                scope: this
            }
        };

        Ext.apply(this, config);
        CMS.form.AddRecordComboBox.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * The focusHandler instantiates a UserStore object and hereby triggers the request
    * which will load all users from the server.
    */
    focusHandler: function () {
        // If the store is still the dummy Ext.data.ArrayStore
        // used during initialization of the store we have
        // to reload it.
        if (!this.store || this.store.constructor === Ext.data.ArrayStore) {
            this.initStore();
        }
    },

    /**
    * @private
    * Initializes the store of the combo box.
    */
    initStore: function () {
        if (this.initializingStore) {
            return;
        }
        this.storeLoaded = false;
        this.clearValue();
        this.bindStore(CMS.data.StoreManager.get(this.storeType, this.websiteId, {
            callback: function () {
                this.initializingStore = false;
                (this.storeLoadedHandler || Ext.emptyFn).apply(this, arguments);
            },
            scope: this
        }));
        this.blurHandler(this);
        this.initializingStore = true;
    },

    /**
    * @private
    * The selectHandler fires the "recordSelected" event to which the
    * other controls can subscribe.
    * @param {Ext.form.ComboBox} combo The combobox
    * @param {Ext.data.Record} record The currently selected record
    * @param {Number} index The index of the currently selected record
    */
    selectHandler: function (combo, record, index) {
        this.fireEvent('recordSelected', this, record);
    },

    /**
    * @private
    * The blurHandler fires the "noRecordSelected" event to which the
    * other controls can subscribe if no value is currently selected.
    * @param {Ext.form.ComboBox} combo The combobox
    */
    blurHandler: function (combo) {
        if (!combo.getValue()) {
            this.fireEvent('noRecordSelected', this);
        }
    },

    /**
    * Will remove the record who is passed to the method from
    * the store object of the combobox and additionally
    * clears the combobox field.
    * @param {Object} record The record which should be removed.
    */
    removeRecord: function (record) {
        var index = this.store.data.indexOf(record);
        if (index > -1) { // remove record from filtered store
            record.join(null);
            this.store.data.removeAt(index);
        }
        this.clearValue();
    },

    /**
    * Will add the record who is passed to the method to
    * the store object of the combobox
    * @param {Object} record The record which should be added
    */
    addRecord: function (record) {
        this.store.data.add(record);
        record.join(this.store);
        this.store.fireEvent('add', this.store, record, this.store.data.length);
    },

    /**
    * Will fetch the records associated with the current website to fill the combobox
    * @param {String} id The id of the current website
    */
    setSite: function (id) {
        var oldId = this.websiteId;
        this.websiteId = id;
        if (id !== oldId) {
            this.initStore();
        }
    }
});

Ext.reg('CMSaddrecordcombobox', CMS.form.AddRecordComboBox);
