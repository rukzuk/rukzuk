Ext.ns('CMS.data');

CMS.data.pageFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'websiteId',
    type: 'text'
}, {
    name: 'templateId',
    type: 'text'
}, {
    name: 'name',
    type: 'text'
}, {
    name: 'description',
    type: 'text'
}, {
    name: 'inNavigation',
    type: 'boolean'
}, {
    name: 'date',
    type: 'integer'
}, {
    name: 'mediaId',
    type: 'string'
}, {
    name: 'screenshot',
    type: 'string'
}, {
    name: 'navigationTitle',
    type: 'string'
}, {
    name: 'content',
    type: 'array',
    defaultValue: []
}, {
    name: 'pageType',
    type: 'string',
    defaultValue: 'page',
}, {
    name: 'pageAttributes',
    type: 'json',
    defaultValue: {}
}];


/**
* @class CMS.data.PageRecord
* @extends Ext.data.Record
*/
CMS.data.PageRecord = CMS.data.Record.create(CMS.data.pageFields);

CMS.data.isPageRecord = function (record) {
    return record && (record.constructor == CMS.data.PageRecord);
};
