Ext.ns('CMS.websiteSettings');

/**
 * @class CMS.websiteSettings.WebsiteSettingsSectionSidebar
 * @extends CMS.ThumbnailView
 */
CMS.websiteSettings.WebsiteSettingsSectionSidebar = Ext.extend(CMS.ThumbnailView, {
    /** @lends CMS.websiteSettings.WebsiteSettingsSectionSidebar.prototype */

    deferEmptyText: true,

    /**
     * The id of the current website
     *
     * @property websiteId
     * @type String
     * @readonly
     */
    websiteId: undefined,

    settingsId: '',

    /** @protected */
    initComponent: function () {
        this.emptyText = '<div class="CMSemptytext">' + CMS.i18n(null, 'websiteSettingsSectionSidebar.emptyText') + '</div>';

        // template
        var actualTpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="wrap">',
            '<div class="titlebar"><span>{[CMS.translateInput(values.name)]}</span></div>',
            '</div>',
            '</tpl>'
        );

        Ext.apply(this, {
            itemSelector: 'div.wrap',
            overClass: 'hover',
            selectedClass: 'selected',
            singleSelect: true,
            tpl: actualTpl,
            trackOver: true,
            containerDeselect: false,
            scrollOffset: 10
        });

        CMS.websiteSettings.WebsiteSettingsSectionSidebar.superclass.initComponent.apply(this, arguments);
        this.mon(this.dataView, 'selectionchange', this.handleSelectionChange, this);

        // select first item on open
        this.mon(this.dataView, 'afterrender', this.initialItemSelect, this, {single: true});
        this.mon(this.store, 'load', this.initialItemSelect, this, {single: true});

    },

    /**
     * @private
     */
    handleSelectionChange: function () {
        var selectedRecords = this.dataView.getSelectedRecords();
        if (selectedRecords.length) {
            this.fireEvent('settingsSelectionChange', selectedRecords[0]);
        } else {
            this.fireEvent('settingsSelectionChange', null);
        }
    },

    /**
     * @private
     */
    initialItemSelect: function () {
      if (this.dataView.getSelectedIndexes().length === 0) {
          var idx = 0;
          var settingsRecord = this.store.getById(this.settingsId);
          if (settingsRecord) {
              idx = this.dataView.indexOf(settingsRecord);
          }
          this.dataView.selectRange(idx, idx, false);
      }
    },


});

Ext.reg('CMSwebsitesettingssectionsidebar', CMS.websiteSettings.WebsiteSettingsSectionSidebar);
