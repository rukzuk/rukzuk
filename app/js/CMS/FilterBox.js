Ext.ns('CMS');

/**
* @class CMS.FilterBox
* @extends SB.form.TwinTriggerComboBox
* A TwinTriggerComboBox that can be used for filtering a store
*/

CMS.FilterBox = Ext.extend(SB.form.TwinTriggerComboBox, {

    /**
    * @cfg {Ext.data.Store} store
    * The store to be used for filtering
    */
    store: null,

    /**
    * @cfg {String} field
    * The field to filter for
    */
    field: '',


    editable: false,
    mode: 'local',
    triggerAction: 'all',

    initComponent: function () {
        // dirty little hack to prevent filtering the original store
        this.referenceStore = this.store;
        this.store = new Ext.data.ArrayStore({
            fields: ['id'],
            idIndex: 0
        });
        this.syncData();
        this.displayField = this.valueField = 'id';
        CMS.FilterBox.superclass.initComponent.apply(this, arguments);
        this.on('select', this.onSelectEvt, this);
        this.on('clear', this.onClear, this);
    },

    onTrigger2Click: function () {
        this.syncData();
        CMS.FilterBox.superclass.onTriggerClick.apply(this, arguments);
    },

    /**
    * @private
    */
    syncData: function () {
        this.store.suspendEvents();
        this.store.removeAll();
        var ids = [];
        this.referenceStore.data.each(function (record) {
            var val = record.get(this.field);
            if (val) {
                ids.push(val);
            }
        }, this);
        this.store.loadData(Ext.zip(Ext.unique(ids)), false);
        this.store.resumeEvents();
    },

    onSelectEvt: function (combo, record, index) { // don't overwrite private "onSelect"
        /**
        * @event filter
        * Fired when a filter is selected
        * @param {String} self This component
        * @param {String} value The selected filter value
        */

        this.fireEvent('filter', this, record ? record.id : null);
    },

    onClear: function () {
        /**
        * @event clearfilter
        * Fired when the filter has been reset
        * @param {String} self This component
        */
        this.fireEvent('clearfilter', this);
    },

    destroy: function () {
        this.store.destroy();
        CMS.FilterBox.superclass.destroy.apply(this, arguments);
    }

});

Ext.reg('CMSfilterbox', CMS.FilterBox);
