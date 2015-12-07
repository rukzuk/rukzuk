Ext.ns('CMS.form');

/**
 * Abstract class to show a ComboBox for selecting store records
 * This makes a copy of the bound store to allow for filtering
 *
 * @class CMS.form.Chooser
 * @extends Ext.form.ComboBox
 * @requires Ext.ux.EncodingDataView
 */
CMS.form.Chooser = Ext.extend(Ext.form.ComboBox, {
    /** @lends CMS.form.Chooser.prototype */

    /**
     * The store which holds the records to fill the ComboBox from.
     * The contained records will be copied to a local store
     * @property originalStore
     * @type Ext.data.Store
     */
    originalStore: null,

    /**
     * The value to return when nothing is selected
     * Defaults to <tt>null</tt>.
     * @property noSelectionValue
     * @type String
     */
    noSelectionValue: null,

    /**
     * The text to be displayed by default
     * Defaults to '-'.
     * @property noSelectionText
     * @type String
     */
    noSelectionText: undefined,

    forceSelection: true,
    editable: false,
    triggerAction: 'all',
    mode: 'local',
    displayField: 'text',
    valueField: 'id',

    /** @protected */
    initComponent: function () {

        this.store = new Ext.data.ArrayStore({
            fields: ['id', 'text'],
            idIndex: 0,
            autoDestroy: true
        });

        if (this.originalStore) {
            this.mon(this.originalStore, 'datachanged', this.datachangedHandler, this);
            this.mon(this.originalStore, 'update', this.datachangedHandler, this);

            this.datachangedHandler();
        }

        CMS.form.Chooser.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Will get called after initialization and every time the originalStore fires the datachanged event
     * @private
     */
    datachangedHandler: function () {
        var records = this.originalStore.getRange();

        var data = [];
        Ext.each(records, function (record) {
            data.push([record.get('id'), CMS.translateInput(record.get('name'))]);
        });

        this.syncComboBoxStore(data);
    },

    /**
     * Load the internal store with data from the passed store
     * @private
     */
    syncComboBoxStore: function (data) {
        if (this.allowBlank) {
            data.unshift([this.noSelectionValue, this.noSelectionText || '-']);
        }
        this.hasFocus = false; // Workaround to prevent combobox from re-expanding after loading store.
        this.store.loadData(data);

        //refresh the view
        this.setValue(this.getValue());
    },

    // nasty hack to use EncodingDataView
    initList: function () {
        var origDataView = Ext.DataView;
        Ext.DataView = Ext.ux.EncodingDataView;
        Ext.DataView.superclass = origDataView.superclass;
        CMS.form.Chooser.superclass.initList.apply(this, arguments);
        Ext.DataView = origDataView;
        Ext.ux.EncodingDataView.superclass = Ext.DataView;
    },

    destroy: function () {
        this.originalStore = null;

        CMS.form.Chooser.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSchooser', CMS.form.Chooser);
