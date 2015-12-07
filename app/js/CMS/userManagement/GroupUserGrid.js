Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.GroupUserGrid
* @extends Ext.grid.GridPanel
*
* Shows all users of a user group and provides functionality to remove
* individual users from the group
*
*/
CMS.userManagement.GroupUserGrid = Ext.extend(Ext.grid.GridPanel, {
    /**
    * @cfg bubbleEvents
    * @type Array
    * The names of the events which will bubble up
    */

    bubbleEvents: ['removeuser'],
    /**
    * @cfg {CMS.data.UserGroupRecord} record
    * The userGroup that is being displayed
    */
    record: null,

    initComponent: function () {
        this.viewConfig = this.viewConfig || {};
        this.viewConfig.deferEmptyText = false;
        this.viewConfig.emptyText = CMS.i18n('Dieser Gruppe sind keine Benutzer zugeordnet');

        var config = {
            store: new Ext.data.JsonStore({
                fields: CMS.data.userFields
            }),
            header: true,
            title: CMS.i18n('Zugewiesene Benutzer'),
            columns: [{
                id: 'lastname',
                dataIndex: 'lastname',
                sortable: true,
                header: CMS.i18n('Nachname', 'users.lastName')
            }, {
                id: 'firstname',
                dataIndex: 'firstname',
                sortable: true,
                width: 150,
                header: CMS.i18n('Vorname', 'users.firstName')
            }, {
                id: 'email',
                dataIndex: 'email',
                sortable: true,
                width: 250,
                header: CMS.i18n('E-Mail-Adresse')
            }, {
                id: 'remove',
                dataIndex: '',
                header: '&#160;',
                hideable: false,
                renderer: function (value, meta, record, rowIndex, colIndex, store) {
                    meta.attr = 'ext:qtip="' + CMS.i18n('Benutzer aus der Gruppe entfernen') + '"';
                    return '<img class="action delete" src="' +  Ext.BLANK_IMAGE_URL + '" width="16">';
                },
                resizable: false,
                menuDisabled: true,
                width: 26
            }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            autoExpandColumn: 'lastname'
        };
        Ext.apply(this, config);

        this.reloadUserGrid(this.record);

        CMS.userManagement.GroupUserGrid.superclass.initComponent.apply(this, arguments);
        this.on({
            'cellclick': {
                fn: this.cellclickHandler,
                scope: this
            }
        });
    },

    /**
    * Fills the userStore with the latest data from the group record
    * @param {CMS.data.UserGroupRecord} record The group record
    */
    reloadUserGrid: function (record) {
        if (record) {
            this.record = record;
            this.store.loadData(record.data.users);
        }
    },

    /**
    * @private
    * Checks if the cell which has been clicked by the user was
    * the 'remove user' one. If this is the case, it fires the
    * 'removeuser' event passing the row record object along
    */
    cellclickHandler: function (grid, rowIndex, colIndex, evt) {
        var removeIndex = this.getColumnModel().getIndexById('remove'),
            record = this.store.getAt(rowIndex);
        if (colIndex === removeIndex) {
            this.fireEvent('removeuser', record);
        }
    },

    /**
    * Removes the user which is passed to the method as a record object
    * from the store and therefore from the grid.
    * @param {Object} record The user which should be removed.
    */
    removeUser: function (record) {
        this.store.remove(record);
    },

    /**
    * Adds the user which is passed to the method as a record object
    * to the store and therefore to the grid.
    * @param {Object} record The user which should be added.
    */
    addUser: function (record) {
        this.store.add(record);
        var sortState = this.store.getSortState();
        if (sortState) {
            this.store.sort(sortState.field, sortState.direction);
        } else {
            this.store.sort('lastname', 'ASC');
        }
    },

    destroy: function () {
        if (this.store) {
            this.store.destroy();
        }
        CMS.userManagement.GroupUserGrid.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSgroupusergrid', CMS.userManagement.GroupUserGrid);
