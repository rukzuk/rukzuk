Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.UserManagementPanel
* @extends CMS.home.ManagementPanel
*
* Panel for managing all users.
*
*/

CMS.userManagement.UserManagementPanel = Ext.extend(CMS.home.ManagementPanel, {
    /**
    * @cfg websiteId
    * @type String
    * The currently opened website's id (only if a website is open - otherwise null)
    */
    websiteId: null,

    initComponent: function () {
        this.items = [{
            ref: 'usergrid',
            xtype: 'CMSallusersgrid',
            listeners: {
                rowSelected: this.rowSelectedHandler,
                rowDeselected: this.rowDeselectedHandler,
                scope: this
            }
        }];

        this.bbar = [{
            text: CMS.i18n('Bearbeiten'),
            ref: '../editButton',
            iconCls: 'edit edituser',
            disabled: true,
            handler: this.editButtonHandler,
            scope: this
        }, {
            text: CMS.i18n('Löschen'),
            ref: '../deleteButton',
            iconCls: 'delete deleteuser',
            disabled: true,
            handler: this.deleteButtonHandler,
            scope: this
        }, {
            text: CMS.i18n('Passwort-E-Mail senden'),
            ref: '../sendPasswordMailButton',
            iconCls: 'email emailsendpw',
            disabled: true,
            handler: this.sendPasswordMailButtonHandler,
            scope: this
        }, '->', {
            text: CMS.i18n('Neu'),
            cls: 'primary',
            iconCls: 'add adduser',
            handler: this.newButtonHandler,
            scope: this
        }];

        CMS.userManagement.UserManagementPanel.superclass.initComponent.apply(this, arguments);
        this.mon(this.usergrid, 'rowdblclick', this.rowDblClickHanlder, this);
    },

    /**
    * @private
    * Passes the currently selected user from the grid to the user property form which
    * in addition is enabled
    * enable
    * @param {Ext.grid.RowSelectionModel} selectionModel The selection model of the grid
    * @param {Number} rowIndex The index of the currently selected row
    * @param {CMS.data.UserRecord} record The currently selected user
    */
    rowSelectedHandler: function (selectionModel, rowIndex, record) {
        this.editButton.enable();
        if (record && record.get('readonly')) {
            return;
        }
        this.deleteButton.enable();
        this.sendPasswordMailButton.enable();
    },

    /**
    * @private
    * Disables the user property form since no user is currently selected
    */
    rowDeselectedHandler: function () {
        this.editButton.disable();
        this.deleteButton.disable();
        this.sendPasswordMailButton.disable();
    },

    /**
     * @private
     * Row Double Click Handler - opens the user edit window
     * @param usergrid
     * @param rowIndex
     * @param e
     */
    rowDblClickHanlder: function (usergrid, rowIndex, e) {
        this.openEditUserWindow(usergrid.getStore().getAt(rowIndex));
    },

    /**
     * @private
     * Edit button clicked, opens the edit user window
     */
    editButtonHandler: function () {
        var userRecord = this.usergrid.getSelectedUser();
        this.openEditUserWindow(userRecord);
    },

    /**
    * @private
    * Shows the {@link CMS.userManagement.EditUserWindow} which enables the current user
    * to edit existing users.
    */
    openEditUserWindow: function (userRecord) {
        if (userRecord.get('readonly')) {
            CMS.Message.info(CMS.i18n(null, 'userManagementPanel.userReadOnlyEditNotPossible'));
            return;
        }
        var editUserWindow = new CMS.userManagement.EditUserWindow({
            userRecord: userRecord
        });
        editUserWindow.show();
    },

    /**
     * @private
     * Shows the {@link CMS.userManagement.CreateUserWindow} which enables the current user
     * to create new users
     */
    newButtonHandler: function () {
        var createWindow = new CMS.userManagement.CreateUserWindow({
            websiteId: this.websiteId
        });
        createWindow.show();
        createWindow.on('userCreated', this.userCreatedHandler, this);
    },

    /**
    * @private
    * Presents the user with a dialog which will ask him/her whether he/she really has the audacity
    * to delete the currently selected user. If the current user shows the required boldness, the
    * delete request is consequently send to the server and the deleted user is removed from the grid
    */
    deleteButtonHandler: function () {
        var record = this.usergrid.getSelectedUser();
        if (record.get('readonly')) {
            return;
        }
        var msg = CMS.i18n('Soll der Benutzer „{name}“ wirklich aus dem System gelöscht werden?').replace('{name}', (record.data.firstname || '') + ' ' + (record.data.lastname || ''));
        Ext.MessageBox.confirm(CMS.i18n('Aus dem System löschen?'), msg, function (btnId) {
            if (btnId === 'yes') {
                CMS.app.trafficManager.sendRequest({
                    action: 'deleteUser',
                    data: {
                        id: record.id
                    },
                    scope: this,
                    success: function (response) {
                        CMS.Message.toast(CMS.i18n('Benutzer wurde gelöscht.'));
                        this.usergrid.removeUser(record);
                        CMS.data.StoreManager.get('user', null).reload();
                        this.rowDeselectedHandler();
                    },
                    failureTitle: CMS.i18n('Fehler beim Löschen des Benutzers')
                }, this);
            }
        }, this);
    },

    /**
    * @private
    * Sends an email to the selected user so the user is able to set a new password.
    */
    sendPasswordMailButtonHandler: function () {
        var record = this.usergrid.getSelectedUser();
        var msg = String.format(CMS.i18n('Soll dem Benutzer „{0}“ eine E-Mail gesendet werden, mit welcher er sich ein Passwort setzen kann?\nDie E-Mail wird an die Adresse „{1}“ gesendet.'), (record.data.firstname || '') + ' ' + (record.data.lastname || ''), record.data.email);
        Ext.MessageBox.confirm(CMS.i18n('Password-E-Mail senden?'), msg, function (btnId) {
            if (btnId === 'yes') {
                CMS.app.trafficManager.sendRequest({
                    action: 'sendPasswordMail',
                    data: {
                        ids: [record.id]
                    },
                    scope: this,
                    success: function (response) {
                        CMS.Message.toast(CMS.i18n('Passwort-E-Mail wurde an den Benutzer gesendet.'));
                    },
                    failureTitle: CMS.i18n('Fehler beim Senden der Passwort-E-Mail')
                }, this);
            }
        }, this);
    },

    /**
    * @private
    * Adds the newly created (and stored) user to the grid
    * @param {CMS.userManagement:CreateUserWindow} window The CreateUserWindow which has trigger the event
    * @param {Object} userRecord The freshly created user
    */
    userCreatedHandler: function (window, userRecord) {
        this.changeHandler();
    },

    /**
    * @private
    * Reloads user store
    */
    changeHandler: function () {
        CMS.data.StoreManager.get('user', null).reload();
    }
});

Ext.reg('CMSusermanagementpanel', CMS.userManagement.UserManagementPanel);
