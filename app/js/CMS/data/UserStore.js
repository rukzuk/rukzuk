CMS.data.userFields = [{
    name: 'id',
    type: 'string'
}, {
    name: 'lastname',
    type: 'string'
}, {
    name: 'firstname',
    type: 'string'
}, {
    name: 'email',
    type: 'string'
}, {
    name: 'language',
    type: 'string'
}, {
    name: 'superuser',
    type: 'boolean'
}, {
    name: 'owner',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'readonly',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'display',
    convert: function (v, rec) {
        return rec.firstname + ' ' + rec.lastname + ' (' + rec.email + ')';
    }
}];

/**
* @class CMS.data.UserRecord
* @extends Ext.data.Record
*/
CMS.data.UserRecord = CMS.data.Record.create(CMS.data.userFields);

/**
* @class CMS.data.UserStore
* @extends CMS.data.JsonStore
*/
CMS.data.UserStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.UserStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllUsers,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllUsers),
            root: CMS.config.roots.getAllUsers,
            fields: CMS.data.UserRecord,
            autoLoad: false,
            sortInfo: {
                field: 'lastname',
                direction: 'ASC'
            }
        }));
    }
});

/**
* @class CMS.data.FilteredUserStore
* @extends CMS.data.JsonStore
*/
CMS.data.FilteredUserStore = Ext.extend(CMS.data.UserStore, {});
