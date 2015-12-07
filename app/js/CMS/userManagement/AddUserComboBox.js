Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.AddUserComboBox
* @extends CMS.form.AddRecordComboBox
*
* Provides functionality to add users to a user group
*
*/
CMS.userManagement.AddUserComboBox = Ext.extend(CMS.form.AddRecordComboBox, {

     /**
    * @cfg {CMS.data.UserGroupRecord} group
    * The userGroup that is being displayed
    */
    group: null,

    initComponent: function () {
        var config = {
            valueField: 'id',
            emptyText: CMS.i18n('Benutzer hinzufügen'),
            displayField: 'display',
            storeType: 'user'
        };

        Ext.apply(this, config);
        CMS.userManagement.AddUserComboBox.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * The storeLoadedHandler removes the users who are already part of the current user group
    * from the UserStore object. This makes sure that only users who are not part of the group
    * can be added to it.
    */
    storeLoadedHandler: function () {
        (function () {
            //TODO filtering of already added groups is temporarily disabled: causes problems since store is used multiple times
            /*
            var userIds = Ext.pluck(this.group.data.users, 'id');
            this.store.filterBy(function (record) {
                return userIds.indexOf(record.id) == -1;
            });
            */
            this.storeFiltered = true;

            if (this.view) {
                this.view.refresh();
                this.restrictHeight();
                this.expand();
            }
        }).defer(1, this); // defer is required since this.store will be defined only after loading
    },

    expand: function () {
        if (!this.storeFiltered) {
            return;
        }
        CMS.userManagement.AddUserComboBox.superclass.expand.apply(this, arguments);
    }
});

Ext.reg('CMSaddusercombobox', CMS.userManagement.AddUserComboBox);
