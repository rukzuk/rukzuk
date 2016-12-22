Ext.ns('CMS.home');

/**
 * @class CMS.home.TemplateThumbnailView
 * @extends CMS.ThumbnailView
 */
CMS.home.TemplateThumbnailView = Ext.extend(CMS.ThumbnailView, {
    /** @lends CMS.home.TemplateThumbnailView.prototype */

    /**
     * The id of the current website
     *
     * @property websiteId
     * @type String
     * @readonly
     */
    websiteId: undefined,

    startWithDummyTpl: false,

    /** @protected */
    initComponent: function () {
        this.emptyText = '<div class="CMSemptytext">' + CMS.i18n(null, 'templateSelection.emptyText') + '</div>';
        this.store = this.getTemplateStore(this.websiteId);
        this.pageTypeStore = CMS.data.StoreManager.get('pageType', this.websiteId);

        // a dummy template without images to avoid requesting screenshot if not visible
        this.dummyTpl = new Ext.XTemplate('<tpl for="."><div class="wrap"></div></tpl>');

        // the actual fancy template with screenshots and everything
        var that = this;
        this.actualTpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="wrap templates">',
                    '<div class="thumb">',
                        '<img class="screenshot" src="{[values.screenshot || Ext.BLANK_IMAGE_URL]}" title="{name}" width="75" height="56">',
                    '</div>',
                    '<div class="titlebar">',
                    '<span class="text">{name}</span>',
                    '{[this.getPageTypePreviewImageUrl(values.pageType)]}',
                    '</div>',
                '</div>',
            '</tpl>',
            {
                getPageTypePreviewImageUrl: function(pageType) {
                    var url = '';
                    var title = '';
                    var ptRecord = that.pageTypeStore.getById(pageType);
                    if (ptRecord) {
                        url = ptRecord.get('previewImageUrl') || 'images/pagetype-page.svg';
                        title = CMS.translateInput(ptRecord.get('name'));
                    }
                    return [
                        '<span class="pageTypePreview fillHeight" title="',
                        title,
                        '" style="background-image: url(',
                        url,
                        ');">',
                        '</span>'
                    ].join('');
                },
            }
        );

        Ext.apply(this, {
            itemSelector: 'div.wrap',
            overClass: 'hover',
            selectedClass: 'selected',
            singleSelect: true,
            tpl: this.startWithDummyTpl ? this.dummyTpl : this.actualTpl,
            trackOver: true,
            containerDeselect: false,
            scrollOffset: 10
        });

        CMS.home.TemplateThumbnailView.superclass.initComponent.apply(this, arguments);
        this.mon(this.dataView, 'selectionchange', this.onSelectionChange, this);
    },

    setWebsiteId: function (websiteId) {
        if (this.dataView.rendered) {
            this.setSite({
                id: websiteId
            });
        } else {
            this.mon(this.dataView, 'afterrender', function () {
                this.setSite({
                    id: websiteId
                });
            }, this, {single: true});
        }
    },

    /**
     * Open the specified site
     * @param {CMS.data.WebsiteRecord} record The site to be opened
     */
    setSite: function (record) {
        if (record && record.id) {
            this.websiteId = record.id;
            this.store = this.getTemplateStore(this.websiteId);
        } else {
            this.website = null;
            this.store = this.getTemplateStore(-1, {
                disableLoad: true
            });
        }
        this.bindStore(this.store);
    },

    onSelectionChange: function () {
        var record = this.dataView.getSelectedRecords()[0];
        this.fireEvent('select', record);
    },

    /**
     * Selects and scrolls a template into view
     * @private
     */
    selectTemplate: function (templateId) {
        this.selectItem(templateId, true);
    },

    /**
     * Select the topmost template
     */
    selectFirstTemplate: function () {
        var firstTemplate = this.store.getAt(0);
        if (firstTemplate) {
            this.selectItem(firstTemplate.id, true);
        }
    },

    /**
     * Returns the template store
     * @param {String} websiteId A string identifying the desired website
     * @param {Object} [options]
     * @private
     */
    getTemplateStore: function (websiteId, options) {
        return CMS.data.StoreManager.get('template', websiteId, options);
    },

});

Ext.reg('CMStemplatethumbnailview', CMS.home.TemplateThumbnailView);
