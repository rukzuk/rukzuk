Ext.ns('CMS.data');

CMS.data.userGroupFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'websiteId',
    type: 'text'
}, {
    name: 'name',
    type: 'string'
}, {
    name: 'rights',
    type: 'json'
}, {
    name: 'users',
    type: 'array'
}];

/**
* @class CMS.data.UserGroupRecord
* @extends Ext.data.Record
*/
CMS.data.UserGroupRecord = CMS.data.Record.create(CMS.data.userGroupFields);

/**
* @class CMS.data.UserGroupStore
* @extends CMS.data.JsonStore
*/
CMS.data.UserGroupStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.UserGroupStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllUserGroups,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllUserGroups),
            root: CMS.config.roots.getAllUserGroups,
            fields: CMS.data.UserGroupRecord
        }));
    }
});
