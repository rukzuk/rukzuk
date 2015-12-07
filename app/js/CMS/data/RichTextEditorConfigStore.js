Ext.ns('CMS.data');

CMS.data.richTextEditorConfigFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'config',
    type: 'object'
}];

/**
* @class CMS.data.AlbumRecord
* @extends Ext.data.Record
*/
CMS.data.RichTextEditorConfigRecord = CMS.data.Record.create(CMS.data.richTextEditorConfigFields);

/**
* @class CMS.data.AlbumStore
* @extends CMS.data.JsonStore
*/
CMS.data.RichTextEditorConfigStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.RichTextEditorConfigStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            fields: CMS.data.richTextEditorConfigFields
        }));
    }
});
