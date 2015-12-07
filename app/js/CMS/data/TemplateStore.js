Ext.ns('CMS.data');

CMS.data.templateFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'name',
    type: 'text'
}, {
    name: 'websiteId',
    type: 'text'
}, {
    name: 'content',
    type: 'array',
    allowBlank: false,
    defaultValue: []
}, {
    name: 'screenshot',
    type: 'string',
    defaultValue: ''
}, {
    name: 'pageType',
    type: 'string',
}];

/**
* @class CMS.data.TemplateRecord
* @extends Ext.data.Record
*/
CMS.data.TemplateRecord = CMS.data.Record.create(CMS.data.templateFields);

CMS.data.isTemplateRecord = function (record) {
    return record && (record.constructor == CMS.data.TemplateRecord);
};

CMS.data.TemplateStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.TemplateStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllTemplates,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllTemplates),
            root: CMS.config.roots.getAllTemplates,
            fields: CMS.data.TemplateRecord
        }));
    }
});
