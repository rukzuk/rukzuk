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
    type: 'string'
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
            fields: CMS.data.TemplateRecord,
            sortInfo: { field: 'name', direction: 'ASC'}
        }));
    },

    createSortFunction: function(field, direction) {
        console.log('sorting template store');
        direction = direction || "ASC";
        var directionModifier = direction.toUpperCase() == "DESC" ? -1 : 1;

        var sortType = this.fields.get(field).sortType;

        //create a comparison function. Takes 2 records, returns 1 if record 1 is greater,
        //-1 if record 2 is greater or 0 if they are equal
        return function(r1, r2) {
            var v1 = sortType(r1.data[field]),
                v2 = sortType(r2.data[field]);

            // move templates that start with '_' to end of list
            if (v1.startsWith('_') && !v2.startsWith('_')) {
                return directionModifier * 1;
            }
            if (!v1.startsWith('_') && v2.startsWith('_')) {
                return directionModifier * -1;
            }

            // To perform case insensitive sort
            if (v1.toLowerCase) {
                v1 = v1.toLowerCase();
                v2 = v2.toLowerCase();
            }

            return directionModifier * (v1 > v2 ? 1 : (v1 < v2 ? -1 : 0));
        };
    }
});
