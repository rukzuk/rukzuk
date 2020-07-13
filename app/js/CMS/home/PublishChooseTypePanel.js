Ext.ns('CMS.home');

/**
 * Panel which lets choose the user between internal and external hosting
 *
 * @class       CMS.home.PublishChooseTypePanel
 * @extends     CMS.home.ManagementPanel
 */
CMS.home.PublishChooseTypePanel = Ext.extend(CMS.home.ManagementPanel, {

    initComponent: function () {
        Ext.apply(this, {
            layout: 'table',
            cls: 'CMSpublishChooseTypePanel',
            layoutConfig: {
                // The total column count must be specified here
                columns: 2
            },
            style: 'padding: 10px 25px',
            items: [
                {
                    html: '<h2>' + CMS.i18n(null, 'publish.chooseType.introText.title') + '</h2>',
                    colspan: 2,
                    style: 'margin-bottom: 25px',
                },
                {
                    xtype: 'button',
                    text: CMS.i18n(null, 'publish.chooseType.external'),
                    handler: this.enableExternalHosting,
                    style: 'margin-bottom: 55px',
                    cls: 'primary',
                    width: 230,
                    scope: this
                },
                {
                    html: CMS.i18n(null, 'publish.chooseType.introText.external'),
                    style: 'padding-left: 30px; line-height: 145%',
                },
                {
                    xtype: 'button',
                    text: CMS.i18n(null, 'publish.chooseType.internal'),
                    handler: this.enableInternalHosting,
                    style: 'margin-bottom: 55px',
                    width: 230,
                    scope: this

                },
                {
                    html: CMS.i18n(null, 'publish.chooseType.introText.internal'),
                    style: 'padding-left: 30px; line-height: 145%',
                }
            ]
        });

        CMS.home.PublishChooseTypePanel.superclass.initComponent.call(this);

    },

    setSite: function (record) {
        CMS.home.PublishChooseTypePanel.superclass.setSite.apply(this, arguments);
        if (!record.get('publishingEnabled')) {
            this.autoEnableBySupportedTypes();
        }
    },

    /**
     * auto enable publish - if we only have one type
     */
    autoEnableBySupportedTypes: function () {
        var supportedPublishTypes = CMSSERVER.data.supportedPublishTypes;
        if (supportedPublishTypes.length === 1) {
            switch (supportedPublishTypes[0]) {
                case 'internal':
                    this.enableInternalHosting();
                    break;
                case 'external:':
                    this.enableExternalHosting();
                    break;
            }
        }
    },

    enableExternalHosting: function () {
        this.enableHosting('external', function () {
            this.fireEvent('publishingEnabled', true, 'external');
        });
    },

    enableInternalHosting: function () {
        this.enableHosting('internal', function () {
            this.fireEvent('publishingEnabled', true, 'internal');
        });
    },


    /**
     * Enables the Webhosting
     * @param hostingType internal / external
     */
    enableHosting: function (hostingType, cb) {

        var websiteStore = CMS.data.WebsiteStore.getInstance();
        var websiteRecord = websiteStore.getById(this.websiteId);

        CMS.app.trafficManager.sendRequest({
            action: 'editWebsite',
            data: {
                'id': this.websiteId,
                'publishingEnabled': true,
                'publish': {
                    'type': hostingType
                }
            },
            modal: true,
            scope: this,
            success: function (response) {

                websiteRecord.beginEdit();
                websiteRecord.set('publishingEnabled', response.data.publishingEnabled);
                websiteRecord.set('publish', response.data.publish);
                websiteRecord.set('publishInfo', response.data.publishInfo);
                websiteRecord.endEdit();

                if (cb && cb.apply) {
                    cb.apply(this, arguments);
                }
            },
            failureTitle: CMS.i18n(null, 'publish.chooseType.failureTitle')
        }, this);
    }
});

Ext.reg('CMSpublishchoosetypepanel', CMS.home.PublishChooseTypePanel);
