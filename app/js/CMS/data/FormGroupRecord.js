Ext.ns('CMS.data');

CMS.data.formGroupFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'name',
    type: 'text'
}, {
    name: 'icon',
    type: 'text'
}, {
    name: 'formGroupData',
    type: 'array',
    defaultValue: []
}];

/**
* @class CMS.data.FormGroupRecord
* @extends Ext.data.Record
*/
CMS.data.FormGroupRecord = CMS.data.Record.create(CMS.data.formGroupFields);
