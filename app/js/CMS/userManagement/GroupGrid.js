Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.GroupGrid
* @extends Ext.grid.GridPanel
*
* The grid which is used to add or remove a single user to/from different groups
*
*/
CMS.userManagement.GroupGrid = Ext.extend(Ext.grid.GridPanel, {

    /**
    * @cfg websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {CMS.data.UserRecord} user
    * The currently selected user
    */
    user: null,

    initComponent: function () {
        this.viewConfig = this.viewConfig || {};
        this.viewConfig.deferEmptyText = false;
        this.viewConfig.emptyText = CMS.i18n('Dieser Benutzer ist keiner Gruppe zugeordnet');
        this.viewConfig.scrollOffset = 0;
        this.cls = (this.cls || '') + ' CMSgroupgrid';

        var config = {
            title: CMS.i18n('Mitglied in diesen Gruppen:'),
            store: new Ext.data.JsonStore({
                fields: CMS.data.userGroupFields
            }),
            columns: [{
                id: 'name',
                dataIndex: 'name',
                header: CMS.i18n('Gruppenname'),
                sortable: true,
                hideable: false,
                menuDisabled: true,
                resizable: false
            }, {
                id: 'remove',
                dataIndex: '',
                header: '&#160;',
                sortable: false,
                hideable: false,
                menuDisabled: true,
                resizable: false,
                renderer: function (value, meta, record, rowIndex, colIndex, store) {
                    meta.attr = 'ext:qtip="' + CMS.i18n('entfernen') + '"';
                    return '<img class="action remove" src="' +  Ext.BLANK_IMAGE_URL + '" width="16">';
                },
                width: 46
            }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            autoExpandColumn: 'name',
            bubbleEvents: [
                'CMSusergroupchanged'
            ]
        };
        Ext.apply(this, config);
        CMS.userManagement.GroupGrid.superclass.initComponent.apply(this, arguments);

        this.on({
            'cellclick': {
                fn: this.cellclickHandler,
                scope: this
            }
        });
    },

    /**
    * Sets the website id property.
    * @param {String} id The id of the current website
    */
    setSite: function (id) {
        this.websiteId = id;

        this.allGroupsStore = CMS.data.StoreManager.get('group', this.websiteId);
        this.mon(this.allGroupsStore, 'datachanged', function () {
            if (this.user) {
                this.setUser(this.user); //to refresh the grid
            }
        }, this);

        /*
            var sm = this.getSelectionModel();
            if (!sm.hasSelection()) {
                sm.selectFirstRow();
            } else {
                sm.selectRecords(sm.getSelected());
            }
        */
    },

    /**
    * Fills the grid by taking all those groups from the store which the currently selected user
    * is a member of.
    * @param {Ext.data.Record} record The currently selected user
    */
    setUser: function (record) {
        this.user = record;
        var groupsOfUser = [];

        this.store.removeAll();

        if (record) {
            this.allGroupsStore.data.each(function (group) {
                Ext.each(group.data.users, function (user) {
                    if (user.id == record.id) {
                        groupsOfUser.push(group);
                    }
                });
            });
            this.store.add(groupsOfUser);
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
            this.removeGroup(record);
        }
    },

    /**
    * Removes the group which is passed to the method as a record object
    * from the store and therefore from the grid.
    * @param {Object} record The group which should be removed.
    */
    removeGroup: function (record) {
        if (this.user) {
            var self = this;
            Ext.MessageBox.confirm(CMS.i18n('Aus Gruppe entfernen?'), CMS.i18n('Soll der Benutzer wirklich aus der Gruppe „{name}“ entfernt werden?').replace('{name}', record.get('name')), function (btnId) {
                if (btnId === 'yes') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'removeUserFromGroup',
                        data: {
                            id: record.id,
                            websiteId: self.websiteId,
                            userIds: [self.user.id]
                        },
                        modal: true,
                        failureTitle: CMS.i18n('Fehler beim Löschen'),
                        success: function () {
                            self.fireEvent('CMSusergroupchanged');
                        }
                    });
                }
            });
        } else {
            // No user is set -> simply remove group from grid
            this.store.remove(record);
        }
    },

    /**
    * Adds the group which is passed to the method as a record object
    * to the store and therefore to the grid.
    * @param {Object} groupRecord The group which should be added.
    */
    addGroup: function (groupRecord) {
        if (this.store.indexOf(groupRecord) == -1) {
            if (this.user) {

                CMS.app.trafficManager.sendRequest({
                    action: 'addGroupsToUser',
                    data: {
                        id: this.user.id,
                        websiteId: this.websiteId,
                        groupIds: [groupRecord.id]
                    },
                    modal: true,
                    failure: function (json, error) {
                        Ext.MessageBox.show({
                            title: CMS.i18n('Fehler beim Zuweisen der Gruppe'),
                            msg: error.formatted,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });
                    },
                    scope: this,
                    success: function () {
                        this.fireEvent('CMSusergroupchanged');
                    }
                });
            } else {
                // No user is set -> simply add group to grid without adding a user to it.
                this.store.add(groupRecord);
            }
        }
    },

    destroy: function () {
        if (this.store) {
            this.store.destroy();
        }
        CMS.userManagement.GroupGrid.superclass.destroy.apply(this, arguments);
    }

});

Ext.reg('CMSgroupgrid', CMS.userManagement.GroupGrid);
