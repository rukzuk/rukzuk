Ext.ns('CMS.data');

CMS.data.buildFields = [{
    name: 'id',
    type: 'text'
}, {
    name: 'version',
    type: 'int'
}, {
    name: 'timestamp',
    type: 'int'
}, {
    name: 'comment',
    type: 'text'
}, {
    name: 'status',
    type: 'text',
    mapping: 'lastPublished.status'
}, {
    name: 'message',
    type: 'text',
    mapping: 'lastPublished.msg'
}, {
    name: 'published',
    type: 'int',
    mapping: 'lastPublished.timestamp'
}, {
    name: 'percent',
    type: 'int',
    mapping: 'lastPublished.percent'
}, {
    name: 'remaining',
    type: 'int',
    mapping: 'lastPublished.remaining'
}];

/**
* @class CMS.data.BuildRecord
* @extends Ext.data.Record
*/
CMS.data.BuildRecord = CMS.data.Record.create(CMS.data.buildFields);

CMS.data.isBuildRecord = function (record) {
    return record && (record.constructor == CMS.data.BuildRecord);
};

/**
* @class CMS.data.BuildStore
* Store for build files
*/
CMS.data.BuildStore = Ext.extend(CMS.data.JsonStore, {
    constructor: function (config) {
        CMS.data.BuildStore.superclass.constructor.call(this, Ext.apply(config, {
            idProperty: 'id',
            url: CMS.config.urls.getAllWebsiteBuilds,
            fields: CMS.data.BuildRecord,
            root: CMS.config.roots.getAllWebsiteBuilds
        }));
    },

    /**
     * Checks if there is a build which is being published and returns the
     * build record
     *
     * @return {CMS.data.BuildRecord} The record that is being published
     *      <code>undefined</code> if there is none
     */
    isPublishing: function () {
        var isPublishing = false;
        this.each(function (record) {
            if (record.get('status') === 'INPROGRESS') {
                // we've found a build which is being published right now...
                isPublishing = record;
                // ...so exit loop
                return false;
            }
        });
        return isPublishing;
    },

    /**
     * Checks if there is at least a single successful published build
     * @returns {boolean}
     */
    hasBeenSuccesfulllyPublished: function () {
        var isPublished = false;
        this.each(function (record) {
            if (record.get('status') === 'FINISHED') {
                // we've found a build which is being published right now...
                isPublished = true;
                // ...so exit loop
                return false;
            }
        });
        return isPublished;
    },

    /**
     * Returns the build with the newest publication date
     *
     * @return {CMS.data.BuildRecord} The buil record with the newest publication
     *      date; <code>undefined</code> if there is no published build so far
     */
    getLatestPublishedBuild: function () {
        var latestPublishedBuild;
        this.each(function (record) {
            if (!record.get('published')) {
                // build is not publised yet
                // -> continue with next build
                return;
            }
            if (!latestPublishedBuild || record.get('published') > latestPublishedBuild.get('published')) {
                // there is another build with a newer publish timestamp
                latestPublishedBuild = record;
            }
        });
        return latestPublishedBuild;
    }
});
