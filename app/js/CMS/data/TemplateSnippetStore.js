Ext.ns('CMS.data');

CMS.data.templateSnippetFields = [{
    name: 'id',
    type: 'string',
    allowBlank: false
}, {
    name: 'websiteId',
    type: 'string',
    allowBlank: false
}, {
    name: 'name',
    type: 'string',
    defaultValue: 'Ohne Titel',
    allowBlank: false
}, {
    name: 'description',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'sourceType',
    type: 'string',
    defaultValue: ''
}, {
    name: 'readonly',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'baseLayout',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'pageTypes',
    type: 'array',
    defaultValue: []
}, {
    name: 'previewImageUrl',
    type: 'string',
    defaultValue: ''
}, {
    name: 'overwritten',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'category',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'content',
    type: 'json',
    defaultValue: {},
    allowBlank: false
}];

/**
* @class CMS.data.TemplateSnippetRecord
* @extends Ext.data.Record
*/
CMS.data.TemplateSnippetRecord = CMS.data.Record.create(CMS.data.templateSnippetFields);

CMS.data.isTemplateSnippetRecord = function (record) {
    return record && (record.constructor == CMS.data.TemplateSnippetRecord);
};

/**
* @class CMS.data.TemplateSnippetStore
* This is a true singleton. Use {@link #getInstance} to access its instance.
*/
CMS.data.TemplateSnippetStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.TemplateSnippetStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            autoLoad: false,
            url: CMS.config.urls.getAllTemplateSnippets,
            root: CMS.config.roots.getAllTemplateSnippets,
            fields: CMS.data.TemplateSnippetRecord,
            reader: new Ext.data.JsonReader(config)
        }));
    }
});
