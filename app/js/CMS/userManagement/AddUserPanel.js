Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.AddUserPanel
* @extends Ext.Container
*
* Panel for managing the members of a user group:
* - add users to a group
* - remove users from a group
*
*/
CMS.userManagement.AddUserPanel = Ext.extend(Ext.Container, {
    /**
    * @cfg websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg groupId
    * @type String
    * The currently opened group's id
    */
    groupId: '',

     /**
    * @cfg {CMS.data.UserGroupRecord} record
    * The userGroup that is being displayed
    */
    record: null,

    selectedUser: null,

    initComponent: function () {
        this.header = false;
        this.border = false;
        this.layout = 'vbox';
        this.layoutConfig = {
            align: 'stretch'
        };

        this.items = [{
            xtype: 'CMSgroupusergrid',
            record: this.record,
            cls: 'CMSDeleteGrid',
            ref: 'usergrid',
            flex: 1,
            style: {
                padding: '0 10px 10px 10px'
            },
            listeners: {
                removeuser: this.removeUserHandler,
                scope: this
            }
        }, {
            layout: 'toolbar',
            items: [{
                xtype: 'CMSaddusercombobox',
                width: 350,
                ref: '../usercombo',
                group: this.record,
                listeners: {
                    recordSelected: this.recordSelectedHandler,
                    noRecordSelected: this.noRecordSelectedHandler,
                    scope: this
                }
            }, {
                xtype: 'tbspacer',
                width: 10
            }, {
                xtype: 'button',
                cls: 'primary',
                iconCls: 'add addusertogroup',
                disabled: true,
                ref: '../adduserbutton',
                text: CMS.i18n('Hinzufügen'),
                listeners: {
                    click: this.buttonClickHandler,
                    scope: this
                }
            }]
        }];

        CMS.userManagement.AddUserPanel.superclass.initComponent.apply(this, arguments);

        var groupStore = CMS.data.StoreManager.get('group', this.websiteId);
        this.mon(groupStore, 'datachanged', function () {
            this.usergrid.reloadUserGrid(groupStore.getById(this.record.id));
        }, this);
    },

    /**
    * @private
    * Shows a message box to the user and ask whether he/she really wants to remove the selected
    * user from the current group. If the user input is yes, a request to remove this user is sent.
    * @param {Object} record The user which should be removed.
    */
    removeUserHandler: function (record) {
        Ext.MessageBox.confirm(CMS.i18n('Aus der Gruppe entfernen?'), CMS.i18n('Soll der Benutzer „{name}“ wirklich aus der Gruppe entfernt werden?')
            .replace('{name}', (record.data.firstname || '') + ' ' + (record.data.lastname || '')), function (btnId) {
                if (btnId === 'yes') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'removeGroups',
                        data: {
                            websiteId: this.websiteId,
                            id: record.id,
                            groupIds: [this.groupId]
                        },
                        scope: this,
                        success: function (response) {
                            //this.usergrid.removeUser(record);
                            //this.usercombo.addRecord(record);
                            this.fireEvent('CMSusergroupchanged');

                            CMS.Message.toast(CMS.i18n('Benutzer wurde aus der Gruppe entfernt.'));
                        },
                        failureTitle: CMS.i18n('Fehler beim Entfernen des Benutzers aus einer Gruppe')
                    }, this);
                }
            }, this);
    },

    /**
    * @private
    * Gets called whenever the user clicks on the 'Add user' button.
    * It will request that the user is added to the current group.
    * In case of success, it will add the selected
    * user to the grid and remove him/her from the combobox.
    */
    buttonClickHandler: function () {
        CMS.app.trafficManager.sendRequest({
            action: 'addUsersToGroup',
            data: {
                websiteId: this.websiteId,
                id: this.groupId,
                userids: [this.selectedUser.id]
            },
            scope: this,
            success: function (response) {
                //this.usergrid.addUser(this.selectedUser);
                //this.usercombo.removeRecord(this.selectedUser);
                this.fireEvent('CMSusergroupchanged');
            },
            failureTitle: CMS.i18n('Fehler beim Hinzufügen des Benutzers')
        }, this);
    },

    /**
    * @private
    * Gets called whenever the user has selected a user from the combobox.
    * It will enable the previously disabled 'Add user' button and
    * store the currently selected user in a property.
    * @param {Ext.form.ComboBox} combo The combobox
    * @param {Ext.data.Recod} record The currently selected recordcord
    */
    recordSelectedHandler: function (combo, record) {
        this.adduserbutton.enable();
        this.selectedUser = record;
    },

    /**
    * @private
    * Gets called whenever the user has selected a user from the combobox.
    * It will enable the previously disabled 'Add user' button and
    * store the currently selected user in a property.
    * @param {Ext.form.ComboBox} combo The combobox
    */
    noRecordSelectedHandler: function (combo) {
        this.adduserbutton.disable();
        this.selectedUser = null;
    }
});

Ext.reg('CMSadduserpanel', CMS.userManagement.AddUserPanel);
