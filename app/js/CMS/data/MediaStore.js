Ext.ns('CMS.data');

CMS.data.mediaFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'websiteId',
    type: 'text'
}, {
    name: 'albumId',
    type: 'text'
}, {
    name: 'name',
    type: 'text'
}, {
    name: 'url',
    type: 'text'
}, {
    name: 'icon',
    type: 'text'
}, {
    name: 'type',
    type: 'text'
}, {
    name: 'extension',
    type: 'text'
}, {
    name: 'filesize',
    type: 'int'
}, {
    name: 'dateUploaded',
    type: 'int'
}];

/**
* @class CMS.data.MediaRecord
* @extends Ext.data.Record
*/
CMS.data.MediaRecord = CMS.data.Record.create(CMS.data.mediaFields);

CMS.data.isMediaRecord = function (record) {
    return record && (record.constructor == CMS.data.MediaRecord);
};

/**
* @class CMS.data.MediaStore
* Store for media files
*/
CMS.data.MediaStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.MediaStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllMedia,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllMedia),
            paramNames: CMS.config.params.getAllMediaParamNames,
            root: CMS.config.roots.getAllMedia,
            fields: CMS.data.MediaRecord,
            remoteSort: true,
            sortInfo: { field: 'name', direction: 'ASC' },
            totalProperty: CMS.config.roots.getAllMediaTotal
        }));
    },

    fireMediachanged: function () {
        this.fireEvent('CMSmediachanged');
    }
});
