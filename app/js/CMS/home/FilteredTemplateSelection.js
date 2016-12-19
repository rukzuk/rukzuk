Ext.ns('CMS.home');

/**
 * @class CMS.home.FilteredTemplateSelection
 * @extends CMS.home.ThumbnailView
 */
CMS.home.FilteredTemplateSelection = Ext.extend(CMS.home.TemplateThumbnailView, {
    /** @lends CMS.home.FilteredTemplateSelection.prototype */

    showInsertTemplates: true,

    /** @protected */
    initComponent: function () {
        CMS.home.TemplateSelection.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Returns filtert template stor
     * @param {String} websiteId A string identifying the desired website
     * @param {Object} [options]
     * @protected
     */
    getTemplateStore: function (websiteId, options) {
        var filteredStore = new Ext.data.JsonStore({
            fields: CMS.data.templateFields
        });
        filteredStore.add(CMS.data.StoreManager.get('template', websiteId, options).getRange());
        if (!this.showInsertTemplates) {
            filteredStore.filterBy(function(tpl) {
                return (!tpl.get('name').startsWith('_'));
            }, this);
        }
        return filteredStore;
    },

});

Ext.reg('CMSfilteredtemplateselection', CMS.home.FilteredTemplateSelection);
