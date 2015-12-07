Ext.ns('CMS.data');

CMS.data.pageTypeFields = [{
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
    name: 'previewImageUrl',
    type: 'string',
    defaultValue: ''
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
* @class CMS.data.PageTypeRecord
* @extends Ext.data.Record
*/
CMS.data.PageTypeRecord = CMS.data.Record.create(CMS.data.pageTypeFields);

CMS.data.isPageTypeRecord = function (record) {
    return record && (record.constructor == CMS.data.PageTypeRecord);
};


/**
 * @class CMS.data.PageTypeStore
 * @extends CMS.data.JsonStore
 */
CMS.data.PageTypeStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.PageTypeStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllPageTypes,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllPageTypes),
            root: CMS.config.roots.getAllPageTypes,
            fields: CMS.data.PageTypeRecord
        }));
    }
});
