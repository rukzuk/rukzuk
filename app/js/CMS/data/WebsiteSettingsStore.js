Ext.ns('CMS.data');

CMS.data.websiteSettingsFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'websiteId',
    type: 'text'
}, {
    name: 'name',
    type: 'json'
}, {
    name: 'description',
    type: 'json'
}, {
    name: 'version',
    type: 'string'
}, {
    name: 'form',
    type: 'json'
}, {
    name: 'formValues',
    type: 'json',
    defaultValue: {},
    allowBlank: true
}];

/**
 * @class CMS.data.WebsiteSettingsRecord
 * @extends Ext.data.Record
 */
CMS.data.WebsiteSettingsRecord = CMS.data.Record.create(CMS.data.websiteSettingsFields);

CMS.data.isWebsiteSettingsRecord = function (record) {
    return record && (record.constructor == CMS.data.WebsiteSettingsRecord);
};

/**
 * @class CMS.data.WebsiteSettingsStore
 * @extends CMS.data.JsonStore
 */
CMS.data.WebsiteSettingsStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.WebsiteSettingsStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllWebsiteSettings,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllWebsiteSettings),
            root: CMS.config.roots.getAllWebsiteSettings,
            fields: CMS.data.WebsiteSettingsRecord
        }));
    }
});
