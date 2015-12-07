Ext.ns('CMS');

/**
 * @class       CMS.home.PublishWindow
 * @extends     CMS.MainWindow
 *
 * The wrapper window for the CMS.home.PublishPanel
 *
 */
CMS.home.PublishWindow = Ext.extend(CMS.MainWindow, {

    /**
     * @cfg {Object} website
     * The website for which the colorscheme should be adapted
     */
    website: undefined,

    minWidth: 650,
    minHeight: 300,
    widthViewPortPercent: 0.4,
    heightViewPortPercent: 0.3,

    layout: 'card',

    initComponent: function () {
        this.title = CMS.i18n('Publizieren');

        this.activeItem = this.website.get('publishingEnabled') ? 0 : 1;

        this.items = [{
            id: 'publish-content',
            ref: 'content',
            xtype: 'CMSpublishpanel',
            listeners: {
                close: this.close,
                scope: this
            }
        }, {
            id: 'publish-welcome',
            ref: 'welcome',
            xtype: 'CMSpublishchoosetypepanel',
            listeners: {
                publishingEnabled: this.handlePublishingEnabled,
                scope: this
            }
        }];

        CMS.home.PublishWindow.superclass.initComponent.call(this);

        this.content.setSite(this.website);
        this.welcome.setSite(this.website);
    },

    /**
     * handle publishing enabled event from chooser
     * @private
     */
    handlePublishingEnabled: function (enabled, type) {
        this.showPublishPanel();
        if (type == 'internal') {
            this.content.fireEvent('buildAndPublishWebsite');
        } else if (type == 'external') {
            this.content.fireEvent('showPublishConfig');
        }
    },

    /**
     * Switch from Welcome (ChooseType) to Content Panel
     * @private
     */
    showPublishPanel: function () {
        this.getLayout().setActiveItem('publish-content');
    }

});

