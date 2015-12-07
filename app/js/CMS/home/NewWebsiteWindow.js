Ext.ns('CMS');

/**
 * Create a new Website empty or based on exports (examples)
 *
 * @class CMS.home.NewWebsiteWindow
 * @extends CMS.MainWindow
 */
CMS.home.NewWebsiteWindow = Ext.extend(CMS.MainWindow, {
    /** @lends CMS.NewWebsiteWindow.prototype */

    maxWidth: 1400,
    maxHeight: 925,

    heightViewPortPercent: 0.95,

    header: false,

    cls: 'CMSnewwebsitewindow',

    /**
     * A callback function which is executed when the import is finished
     * (defaults to <code>undefined</code>)
     *
     * @property callback
     * @type {Function}
     */
    callback: undefined,

    /**
     * The excution scope for the callback function
     * (defaults to <code>undefined</code>)
     *
     * @property scope
     * @type {Object}
     */
    scope: undefined,

    layout: {
        type: 'hbox',
        align: 'stretch',
        pack: 'start',
    },

    /**
     * @type Ext.data.Store
     */
    store: null,

    /**
     * Animate visibility of websites
     */
    animateShow: false,

    /**
     * Full Modal Mode (no header, hard mask etc.)
     */
    fullModal: false,

    /**
     * Static Category Data for Empty Website
     */
    emptyWebsiteData: {
        categories: []
    },

    initComponent: function () {

        if (this.fullModal) {
            this.animateShow = true;
            this.closable = false;
            this.draggable = false;
            this.addClass('hideHeader');
        }

        this.emptyWebsiteData.categories.push({
            id: 'empty',
            name: CMS.i18n(null, 'newWebsiteWindow.emptyPageTitleText'),
            description: '',
            websites: [
                {
                    id: 'empty',
                    name: CMS.i18n(null, 'newWebsiteWindow.emptyPageButton'),
                    description: '',
                    preview: Ext.BLANK_IMAGE_URL
                },
            ]
        });

        // '__i18n_newWebsiteWindow.callToActionButton'
        this.tpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="wrap">',
                '<div class="overlay">',
                    '<h3>{[CMS.translateInput(values.name)]}</h3>',
                    '<div class="description">{[CMS.translateInput(values.description)]}</div>',
                    '<div class="ctaBtn">{[CMS.i18n(null, "newWebsiteWindow.callToActionButton")]}</div>',
                '</div>',
                '<div class="thumb" style="background-image: url({[ values.preview || Ext.BLANK_IMAGE_URL ]})"></div>',
            '</div>',
            '</tpl>'
        );

        this.store = new Ext.data.JsonStore({
            url: CMS.config.urls.newWebsiteJson,
            root: 'categories',
            fields: [
                'name', 'description',
                {name:'websites', type: 'array'},
            ]
        });

        this.title = CMS.i18n(null, 'newWebsiteWindow.windowTitle');

        this.items = [{
            flex: 1,
            autoScroll: true,
            cls: 'CMSnewwebsitewindowpanel',
            ref: 'contentContainer',
            items: [{
                autoEl: {
                    tag: 'h1',
                    html: CMS.i18n(null, 'newWebsiteWindow.windowTitle')
                },
                hidden: !this.fullModal
            }]
        }];

        CMS.home.NewWebsiteWindow.superclass.initComponent.apply(this, arguments);
        if (this.animateShow) {
            this.addClass('animateShow');
        }
        this.initStore();
    },

    /**
     * @overrides
     */
    beforeShow: function () {
        CMS.home.NewWebsiteWindow.superclass.beforeShow.apply(this, arguments);
        if (this.fullModal) {
            this.mask.addClass('mask-full');
        }
    },

    initStore: function () {
        this.store.load({
            callback: function () {
                this.store.loadData(this.emptyWebsiteData, true);
                this.store.getRange().forEach(function (item) {
                    this.addCategoryToItems(item.data);
                }, this);
            },
            scope: this,
        });

    },

    /**
     *
     * @param category
     */
    addCategoryToItems: function (category) {

        Ext.each(category.websites, function(ws) {
            if (!ws.preview) {
                ws.preview = String.format(CMS.config.urls.newWebsitePreviewImageUrlTpl, ws.id);
            }
        });

        var wsStore = new Ext.data.JsonStore({
            fields: [
                'id',
                'name',
                'description',
                'preview',
            ],
            data: category.websites
        });

        var added = this.contentContainer.add({
            xtype: 'container',
            cls: 'CMSnewwindowthumbviewcontainer',
            flex: 1,
            items: [{
                xtype: 'container',
                autoEl: {
                    tag: 'h2',
                    html: CMS.translateInput(category.name),
                }
            }, {
                xtype: 'CMSthumbview',
                cls: 'CMSnewwindowthumbview',
                itemSelector: 'div.wrap',
                overClass: 'hover',
                selectedClass: 'selected',
                singleSelect: true,
                tpl: this.tpl,
                store: wsStore,
                ref: 'thumbView',
                trackOver: true,
                scrollOffset: 10,
                listeners: {
                    click: function (dview, itemIdx) {
                        var item = dview.store.getAt(itemIdx);
                        this.newWebsiteHandler(item.id);
                    },
                    scope: this
                }
            }]
        });

        this.doLayout();
        if (this.animateShow) {
            (function () {
                added.addClass('show');
            }).defer(200);
        }
    },


    /**
     * Handler for DataView Click Action
     * @param id
     */
    newWebsiteHandler: function (id) {
        if (id === 'empty') {
            this.newEmptyPage();
            return;
        }
        this.importLocal(id);
    },

    /**
     * Import from Local
     * @param localId
     */
    importLocal: function (localId) {
        CMS.Message.prompt(CMS.i18n(null, 'newWebsiteWindow.websiteCreateTitle'), CMS.i18n(null, 'newWebsiteWindow.websiteCreateLabel'), function (btnId, title, msgbox) {
            if (btnId == 'ok') {
                CMS.app.trafficManager.sendRequest({
                    action: 'importLocal',
                    modal: true,
                    data: {
                        websiteName: title,
                        localId: localId
                    },
                    scope: this,
                    success: function () {
                        this.callback.apply(this.scope, arguments);
                        this.close();
                    },
                    failureTitle: CMS.i18n(null, 'newWebsiteWindow.errorWhileCreateWebsite')
                });
            }
        }, this, false, CMS.i18n(null, 'newWebsiteWindow.newWebsiteTitle'), CMS.config.validation.websiteName);
    },

    /**
     * New Empty Page
     * @protected
     */
    newEmptyPage: function () {
        CMS.Message.prompt(CMS.i18n(null, 'newWebsiteWindow.websiteCreateTitle'), CMS.i18n(null, 'newWebsiteWindow.websiteCreateLabel'), function (btnId, title, msgbox) {
            if (btnId == 'ok') {
                CMS.app.trafficManager.sendRequest({
                    action: 'createWebsite',
                    data: {
                        name: title,
                        resolutions: CMS.config.defaultWebsiteResolutions,
                    },
                    successCondition: 'data.id',
                    scope: this,
                    success: function () {
                        this.callback.apply(this.scope, arguments);
                        this.close();
                    },
                    failureTitle: CMS.i18n(null, 'newWebsiteWindow.errorWhileCreateWebsite')
                });
            }
        }, this, false, CMS.i18n(null, 'newWebsiteWindow.newWebsiteTitle'), CMS.config.validation.websiteName);
    },

});
