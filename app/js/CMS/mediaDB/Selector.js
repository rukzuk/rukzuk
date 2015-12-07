Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.Selector
* @extends Ext.grid.GridPanel
* A component that presents a list of entities
*/
CMS.mediaDB.Selector = Ext.extend(Ext.grid.GridPanel, {

    loadMask: true,

    /**
    * @private
    * @param {Boolean} openOnDoubleClick <tt>true</tt> to fire the CMSopen event
    * when a row is double clicked. Defaults to <tt>false</tt>
    */
    setupEvents: function (openOnDoubleClick) {
        /**
        * @event mark
        * Fired when a list entry is highlighted.
        * @param {CMS.data.PageRecord} record The highlighted record
        * @param {CMS.mediaDB.Selector} this This component
        * @param {Ext.grid.AbstractSelectionModel} sm This component's selectionModel
        */
        this.mon(this.sm, 'rowselect', function (sm, rowIndex, record) {
            this.fireEvent('mark', record, this, sm);
        }, this);
        /**
        * @event unmark
        * Fired when a list entry is being unselected.
        * @param {CMS.data.WebsiteRecord} record The previously highlighted record
        * @param {CMS.mediaDB.Selector} this This component
        * @param {Ext.grid.AbstractSelectionModel} sm This component's selectionModel
        */
        this.mon(this.sm, 'rowdeselect', function (sm, rowIndex, record) {
            this.fireEvent('unmark', record, this, sm);
        }, this);

        if (openOnDoubleClick) {
            this.on('rowdblclick', function (grid, rowIndex, event) {
                var record = this.store.getAt(rowIndex);
                console.log('[Selector] firing event CMSopen');
                this.fireEvent('CMSopen', record);
            }, this);
            this.bubbleEvents = ['CMSopen'];
        }
    },

    /**
    * Reloads the attached store
    */
    reload: function () {
        this.fireEvent('unmark', null, this, this.sm);
        this.store.reload();
    }
});
