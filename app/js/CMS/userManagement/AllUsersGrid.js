Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.AllUsersGrid
* @extends Ext.grid.GridPanel
*
* Shows all users of the system and provides functionality for removing users.
*
*/
CMS.userManagement.AllUsersGrid = Ext.extend(Ext.grid.GridPanel, {

    initComponent: function () {
        this.viewConfig = this.viewConfig || {};
        this.viewConfig.deferEmptyText = false;
        this.viewConfig.emptyText = CMS.i18n('Keine Benutzer in dieser Website vorhanden');
        var config = {
            header: false,
            border: false,
            store: CMS.data.StoreManager.get('user', null),
            columns: [{
                id: 'lastname',
                dataIndex: 'lastname',
                sortable: true,
                width: 250,
                header: CMS.i18n('Nachname', 'users.lastName')
            }, {
                id: 'firstname',
                dataIndex: 'firstname',
                sortable: true,
                width: 250,
                header: CMS.i18n('Vorname', 'users.firstName')
            }, {
                id: 'email',
                dataIndex: 'email',
                sortable: true,
                width: 300,
                header: CMS.i18n('E-Mail-Adresse')
            }, {
                id: 'flags',
                sortable: false,
                width: 100,
                renderer: this.renderUserFlags,
                scope: this,
                header: CMS.i18n('allUsersGrid.flagsHeaderText')
            }],
            autoExpandColumn: 'lastname',
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            })
        };
        Ext.apply(this, config);

        this.addEvents(
            /**
             * @event rowSelected
             * Fires when the user has select one or more rows of the grid.
             * @param {Ext.grid.RowSelectionModel} selectionModel The selection model of the grid
             * @param {Number} rowIndex The index of the selected row
             * @param {Ext.data.Record} record The currently selected record
             */
            'rowSelected',
            /**
             * @event rowDeselected
             * Fires when the user has deselect all rows of the grid.
             * @param {Ext.grid.RowSelectionModel} selectionModel The selection model of the grid
             * @param {Number} rowIndex The index of the last selected row
             * @param {Ext.data.Record} record The last selected record
             */
            'rowDeselected'
        );
        CMS.userManagement.AllUsersGrid.superclass.initComponent.apply(this, arguments);

        this.getSelectionModel().on('rowselect', function (selectionModel, rowIndex, record) {
            this.fireEvent('rowSelected', selectionModel, rowIndex, record);
        }, this);

        this.getSelectionModel().on('rowdeselect', function (selectionModel, rowIndex, record) {
            this.fireEvent('rowDeselected', selectionModel, rowIndex, record);
        }, this);
    },

    /**
     * Renders the Flags cloumn
     * @param value
     * @param meta
     * @param record
     * @returns {String}
     */
    renderUserFlags: function (value, meta, record) {
        if (record.get('owner')) {
            return CMS.i18n(null, 'allUsersGrid.ownerText');
        }

        if (record.get('readonly')) {
            return CMS.i18n(null, 'allUsersGrid.readonlyText');
        }
    },

    /**
    * Adds the newly created user to the store of the grid which will inevitable lead to
    * to an update of the display of the grid
    * @param {Ext.data.Record} userRecord A user
    */
    addNewUser: function (userRecord) {
        this.store.add(new Ext.data.Record(userRecord, userRecord.id));
        var sortState = this.store.getSortState();
        if (sortState) {
            this.store.sort(sortState.field, sortState.direction);
        } else {
            this.store.sort('lastname', 'ASC');
        }
    },

    /**
    * Removes the currently selected user from the system. It will display a dialog which ask the user
    * whether he/she really wants to delete the user. In case of
    */
    removeUser: function (record) {
        this.store.remove(record);
    },

    /**
    * Returns the currently selected user
    * @return - The currently selected user as an Ext.data.Record object
    */
    getSelectedUser: function () {
        return this.getSelectionModel().getSelected();
    }
});

Ext.reg('CMSallusersgrid', CMS.userManagement.AllUsersGrid);
