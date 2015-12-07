Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.AddGroupComboBox
* @extends CMS.form.AddRecordComboBox
*
* Provides functionality to add groups to the currently selected user
*
*/
CMS.userManagement.AddGroupComboBox = Ext.extend(CMS.form.AddRecordComboBox, {

    /**
    * @cfg {String} websiteId
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {String} groups
    * The ids of all groups of which the currently selected user happens to be a member
    */
    groups: [],

    initComponent: function () {
        var config = {
            valueField: 'id',
            emptyText: CMS.i18n('In Gruppe aufnehmen'),
            displayField: 'name',
            storeType: 'group'
        };

        Ext.apply(this, config);
        CMS.userManagement.AddGroupComboBox.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * The storeLoadedHandler removes the groups which are already associated with the currently selected
    * user. This makes sure that only group who are not part of the users
    * can be associated with him/her.
    */
    storeLoadedHandler: function () {
        //TODO filtering of already added groups is temporarily disabled: causes problems since store is used multiple times
        /*
        (function () {
            var groupIds = Ext.pluck(this.groups, 'id');
            this.store.filterBy(function (record) {
                return groupIds.indexOf(record) == -1;
            });
        }).defer(1, this); // defer is required since this.store will be defined only after loading
        */
    }
});

Ext.reg('CMSaddgroupcombobox', CMS.userManagement.AddGroupComboBox);
