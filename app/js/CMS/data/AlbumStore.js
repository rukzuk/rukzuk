Ext.ns('CMS.data');

CMS.data.albumFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'websiteId',
    type: 'text'
}, {
    name: 'name',
    type: 'string'
}, {
    name: 'url',
    type: 'string',
    //TODO remove demo thumbnail
    defaultValue: 'images/icons/filetypes/Box Full.png'
}];

/**
* @class CMS.data.AlbumRecord
* @extends Ext.data.Record
*/
CMS.data.AlbumRecord = CMS.data.Record.create(CMS.data.albumFields);

CMS.data.isAlbumRecord = function (record) {
    return record && (record.constructor == CMS.data.AlbumRecord);
};

/**
* @class CMS.data.AlbumStore
* @extends CMS.data.JsonStore
*/
CMS.data.AlbumStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.AlbumStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllAlbums,
            baseParams: SB.util.cloneObject(CMS.config.params.getAllAlbums),
            root: CMS.config.roots.getAllAlbums,
            fields: CMS.data.AlbumRecord
        }));
    }
});
