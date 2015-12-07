Ext.ns('CMS.layout');

/**
 * This is the main CMS viewport.
 * It manages the main content area and a tabbar which has default buttons assigned.
 *
 * @class       CMS.layout.ApplicationViewport
 * @extends     SB.TaskbarViewport
 */
CMS.layout.ApplicationViewport = Ext.extend(SB.TaskbarViewport, {
    /** @lends CMS.layout.ApplicationViewport.prototype */

    taskbarRegion: 'north',

    /**
     * The id if currently active page or template, null if there has
     * no page or template been opened
     *
     * @property activeItem
     * @type Object
     * @private
     */
    activeItem: undefined,

    /**
     * A set of configurations to restore the currently open panels
     *
     * @property activePanel
     * @type Object
     * @private
     */
    activePanel: undefined,

    /**
     * The getting started popover
     *
     * @property gettingStartedPopover
     * @type Object
     * @private
     */
    gettingStartedPopover: undefined,

    /** @protected */
    initComponent: function () {
        var allowFullscreen = CMS.app.FullScreenHelper.fullScreenSupported;

        this.centerComponents = [{
            id: 'sitesPanel',
            xtype: 'CMSwebsiteselection',
            listeners: {
                select: this.setSite,
                render: function (cmp) {
                    cmp.store.reload();
                },
                scope: this
            }
        }];

        this.taskbarItems = [{
            /**
             * The main menu button
             * @name menuButton
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            tooltip: {
                text: CMS.i18n(null, 'applicationViewport.mainMenuButtonTooltip')
            },
            iconCls: 'menu',
            ref: '../menuButton',
            toggleGroup: false,
            enableToggle: false,
            menuAlign: 'tr-br',
            width: 40,
            menu: {
                cls: 'CMSMainMenu',
                items: [{
                    iconCls: 'help',
                    text: CMS.i18n('Hilfe & Tutorials'),
                    scope: this,
                    hidden: !(CMSSERVER && CMSSERVER.data && CMSSERVER.data.urls && CMSSERVER.data.urls.linkResolver),
                    handler: this.helpMenuButtonHandler,
                    targetUrl: CMS.config.helpDeskUrl
                }, '-', {
                    iconCls: 'websiteSettings',
                    text: CMS.i18n(null, 'applicationViewport.websiteSettings'),
                    requireWebsite: true,
                    disabled: true,
                    hidden: true,
                    onSetSite: function (website, button) {
                        button.setVisible(website && CMS.app.userInfo.canEditWebsiteSettings(website));
                    },
                    handler: this.websiteSettingsButtonHandler,
                    scope: this
                }, {
                    iconCls: 'colors',
                    text: CMS.i18n('Farbschema'),
                    requireWebsite: true,
                    disabled: true,
                    hidden: true,
                    onSetSite: function (website, button) {
                        button.setVisible(website && CMS.app.userInfo.canManageColors(website));
                    },
                    handler: this.colorSchemesHandler,
                    scope: this
                }, {
                    iconCls: 'media',
                    text: CMS.i18n('Mediendatenbank'),
                    requireWebsite: true,
                    disabled: true,
                    handler: this.mediaDBHandler,
                    scope: this
                }, {
                    iconCls: 'templatesnippets',
                    text: CMS.i18n('Snippetverwaltung'),
                    requireWebsite: true,
                    disabled: true,
                    hidden: true,
                    onSetSite: function (website, button) {
                        button.setVisible(website && CMS.app.userInfo.canEditTemplates(website));
                    },
                    handler: this.templateSnippetsButtonHandler,
                    scope: this
                }, {
                    iconCls: 'groups',
                    text: CMS.i18n('Gruppen und Rechte'),
                    requireWebsite: true,
                    disabled: true,
                    hidden: !CMS.app.userInfo.canManageGroups(),
                    handler: this.groupsButtonHandler,
                    scope: this
                }, {
                    iconCls: 'modules',
                    text: CMS.i18n('Modulentwicklung aktivieren'),
                    requireWebsite: true,
                    disabled: true,
                    hidden: true,
                    onSetSite: function (website, button) {
                        button.setVisible(website && CMS.app.userInfo.canEditModules(website));
                    },
                    handler: this.openModuleManagement,
                    scope: this
                }, {
                    iconCls: 'publish',
                    text: CMS.i18n('Publizieren'),
                    tooltip: {
                        text: CMS.i18n('Website veröffentlichen')
                    },
                    requireWebsite: true,
                    disabled: true,
                    hidden: true,
                    onSetSite: function (website, button) {
                        button.setVisible(
                                website && (CMS.app.userInfo.canEditPublishingConfig() ||
                                (CMS.app.userInfo.canPublish(website)  && website.get('publishingEnabled')))
                        );
                    },
                    handler: this.publishHandler,
                    scope: this
                }, {
                    xtype: 'menuseparator',
                    hidden: !CMS.app.userInfo.canManageUsers()
                }, {
                    iconCls: 'store',
                    text: CMS.i18n('Kundenbereich', 'ApplicationViewport.customerArea'),
                    handler: this.openCustomerDashboardHandler,
                    hidden: !CMS.app.userInfo.get('dashboardUrl'),
                    scope: this
                }, {
                    iconCls: 'users',
                    text: CMS.i18n('Benutzerverwaltung'),
                    hidden: !CMS.app.userInfo.canManageUsers(),
                    handler: this.usersButtonHandler,
                    scope: this
                }, '-', {
                    iconCls: 'fullscreen',
                    text: CMS.i18n('Vollbildmodus aktivieren'),
                    ref: 'fullscreenButton',
                    checked: false,
                    hidden: !allowFullscreen,
                    checkHandler: function (btn, checked) {
                        CMS.app.FullScreenHelper.toggleFullScreen(checked);
                    }
                }, {
                    iconCls: 'password',
                    text: CMS.i18n('Passwort ändern'),
                    handler: this.changePasswordButtonHandler,
                    hidden: (CMS.app.userInfo.get('readonly') === true),
                    scope: this
                }, {
                    iconCls: 'logout',
                    text: CMS.app.userInfo.get('email') ? CMS.i18n('Abmelden ({user})').replace('{user}', CMS.app.userInfo.get('email')) : CMS.i18n('Abmelden'),
                    handler: this.logoutButtonHandler,
                    scope: this
                }]
            }
        }, {
            xtype: 'tbspacer',
            ctCls: 'spacer'
        },{
            /**
             * An info "button" without any functionality to display name of the
             * current website, page or template
             *
             * @name overviewBtn
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            id: 'overviewBtn',
            ref: '../overviewBtn',
            hidden: false,
            enabled: false,
            iconCls: 'project-summary',
            text: CMS.i18n(null, 'ApplicationViewport.overviewBtn.text'),
            enableToggle: false,
            toggleGroup: false,
            handler: this.summaryButtonHandler
        }, {
            /**
             * An info "button" without any functionality to display name of the
             * current website
             *
             * @name websiteBtn
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            id: 'websiteBtn',
            ref: '../websiteBtn',
            hidden: true,
            iconCls: 'breadcrumbtrail-arrow',
            enableToggle: false,
            toggleGroup: false,
            handler: this.websiteButtonHandler
        }, {
            /**
             * An info "button" without any functionality to display name of the
             * current website, page or template
             *
             * @name infoBtn
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            id: 'infoBtn',
            ref: '../infoBtn',
            iconCls: 'breadcrumbtrail-arrow',
            hidden: true,
            enableToggle: false,
            toggleGroup: false
        }, {
            /**
             * The button to close a selected website/template/page
             *
             * @name backButton
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            id: 'backButton',
            iconCls: 'close',
            ref: '../backButton',
            hidden: true,
            enableToggle: false,
            toggleGroup: false,
            handler: this.backButtonHandler,
            scope: this
        }, {
            xtype: 'tbspacer',
            ctCls: 'spacer'
        }, {
            /**
             * The button to open the getting started window
             *
             * @name openGettingStartedWindowButton
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            id: 'openGettingStartedWindowButton',
            ref: '../openGettingStartedWindowButton',
            text: CMS.i18n(null, 'ApplicationViewport.openGettingStartedWindowBtn.text'),
            hidden: !CMS.app.userInfo.canManageSites() ||
            !(CMSSERVER && CMSSERVER.data && CMSSERVER.data.urls && CMSSERVER.data.urls.linkResolver),
            enableToggle: false,
            toggleGroup: false,
            handler: this.openGettingStartedPopoverHandler,
            scope: this
        }, {
            /**
             * The button to open the upgrade view of the customer area
             *
             * @name openUpgradeUrlButton
             * @type Ext.Button
             * @memberOf CMS.layout.ApplicationViewport
             * @property
             * @private
             */
            id: 'openUpgradeUrlButton',
            ref: '../openUpgradeUrlButton',
            text: CMS.i18n(null, 'ApplicationViewport.openUpgradeUrlBtn.text'),
            hidden: !CMS.app.userInfo.get('upgradeUrl'),
            enableToggle: false,
            toggleGroup: false,
            handler: this.openUpgradeUrlButtonHandler,
            scope: this
        }];

        CMS.layout.ApplicationViewport.superclass.initComponent.call(this);

        this.on('CMSopenworkbench', this.loadStoresAndCreatePanel, this);
        this.on('CMScloseworkbench', this.removePanel, this);
        this.on('CMSbeforeimport', this.beforeImportHandler, this);
        this.on('CMSopentemplate', this.openTemplateHandler, this);

        this.on('CMSclosepageandopentemplate', this.closePageAndOpenTemplate, this);

        this.mon(CMS.data.WebsiteStore.getInstance(), 'load', this.handleStartSequence, this, {
            single: true
        });

        this.on('CMScurrentpagenamechange', function (name) {
            this.updatePageOrTemplateNameBreadcrumb(name, 'page');
        }, this);

        this.on('resize', this.repositionGettingStartedPopover, this);

        var fullscreenButton = this.menuButton.menu.fullscreenButton;
        if (allowFullscreen) {
            this.fullscreenToggleHandler = function () {
                window.setTimeout(function () {
                    var isFullScreen = CMS.app.FullScreenHelper.isFullScreen();
                    fullscreenButton.setChecked(isFullScreen, true);
                    if (isFullScreen) {
                        fullscreenButton.setText(CMS.i18n('Vollbildmodus deaktivieren'));
                    } else {
                        fullscreenButton.setText(CMS.i18n('Vollbildmodus aktivieren'));
                    }
                }, 1);
            };
            Ext.lib.Event.addListener(window, 'resize', this.fullscreenToggleHandler);
            this.fullscreenToggleHandler();
        }
    },

    /**
     * Handler for the fist load event of the website store to trigger the start sequences
     * @private
     */
    handleStartSequence: function (store, records) {
        var websiteCount = records.length;

        // check website quota
        var maxWebsiteCount = CMSSERVER.data.quota.website.maxCount;
        if (websiteCount > maxWebsiteCount) {
            var hintText = CMS.i18n(null, 'websiteQuota.maxCount.hint.text').replace('{websiteCount}', websiteCount).replace('{quotaCount}', maxWebsiteCount).replace('{deleteCount}', websiteCount-maxWebsiteCount);
            CMS.Message.warn(CMS.i18n(null, 'websiteQuota.maxCount.hint.title'), hintText);
        }

        // Try to open website automatically
        var canManageSites = CMS.app.userInfo.canManageSites();
        var canImport = CMS.app.userInfo.canImport();
        var canExport = CMS.app.userInfo.canExport();

        if (websiteCount === 1) {
            // there is only one available ...
            if ((!canManageSites && !canImport && !canExport)) {
                // ... and the user has no further rights to manage website
                // -> we assume the user is an editor and we can open it automatically
                this.setSite(records[0]);
            }
        }

        // init tracking for the first three sessions
        if (window.localStorage) {
            var trackingCount = parseInt(window.localStorage.getItem('CMStCount'), 10) || 0;
            if (trackingCount <= 3 && (trackingCount > 0 || websiteCount === 0)) {
                window.localStorage.setItem('CMStCount', (trackingCount + 1));
                (new CMS.TrackingHelper()).injectTracking();
            }
        }

        this.openUpgradePopoverDelayed();
    },

    /**
     * Checks if panelConfig has requiredStores param set and calls multiStoreLoader to
     * to load store dependencies. Calls openPanel if required stores have been loaded or
     * <code>panelConfig.requiredStores</code> is not set
     * @private
     *
     * @param {Object} panelConfig Configuration object for the panel that will be created.
     * @param {String} panelConfig.xtype panel xtype
     * @param {String} panelConfig.cls panel cls
     * @param {String} panelConfig.title panel title
     * @param {Object} panelConfig.record data record e.g. TemplateRecord or PageRecord
     * @param {String} panelConfig.before opening the panel (e.g. ['module', 'unit'])
     * @param {String} [panelConfig.templateId] (opt) templateId if panel = page- or templateEditTemplate
     * @param {String} [panelConfig.websiteId] (Opt) data websiteId if available
     * @param {Array} [panelConfig.requiredStores] (opt) array which contains the store names which should be loaded
     * @param {String} [panelConfig.requiredUIDs] (opt)
     * @param {String} [panelConfig.isTemplate] (opt) true if panel is a templateEditPanel
     *
     * @param {Ext.Button} [delegateButton] (Optional) An existing button that will
     *       be connected to the new panel. If not present, a new button is created
     */
    loadStoresAndCreatePanel: function (panelConfig, delegateButton) {
        if (panelConfig.requiredStores || panelConfig.requiredUIDs) {
            var multiStoreLoader = new CMS.data.MultiStoreLoader({
                storeTypes: panelConfig.requiredStores,
                websiteId: panelConfig.websiteId,
                scope: this,
                callback: function () {
                    this.openPanel(panelConfig, delegateButton);
                }
            });
            multiStoreLoader.load(true);
        } else {
            this.openPanel(panelConfig, delegateButton);
        }
    },

    /**
     * Handler for WebsiteSelection's select event
     * @private
     */
    setSite: function (record) {
        if (!this.beforeWebsiteClose(this.setSite, this, [record])) {
            return;
        }

        var unselect = !record; // true if unselecting a website

        // enable/disable main tabs and menu items
        this.menuButton.menu.items.each(function (menuItem) {
            if (menuItem.requireWebsite) {
                // disable menu items which will work only if website is selected
                menuItem.setDisabled(unselect);
            }
            if (Ext.isFunction(menuItem.onSetSite)) {
                menuItem.onSetSite.call(this, record, menuItem);
            }
        });

        if (record) {
            // pre load stores and show pages and template mgmt panel
            var multiStoreLoader = new CMS.data.MultiStoreLoader({
                storeTypes: ['template', 'pageType', 'media'],
                websiteId: record.get('id'),
                scope: this,
                callback: function () {

                    this.viewport.add({
                        id: 'pagesAndTemplatesPanel',
                        xtype: 'CMSpageandtemplatemanagementpanel',
                        website: record
                    });

                    CMS.app.Application.setProductName(null, null, record.get('name'));
                    CMS.app.ImportHelper.setActiveWebsite(record);

                    this.openGettingStartedPopoverAtOpenWebsite();

                    this.website = record;

                    // initially switch to the templates if the user has sufficient rights or fall back to pages
                    this.setActiveItem(null);

                }
            });
            multiStoreLoader.load(true);

        } else {
            // remove pages and template mgmt panel
            this.viewport.remove('pagesAndTemplatesPanel', true);

            CMS.app.Application.setProductName(null, null, null);
            CMS.app.ImportHelper.setActiveWebsite(null);

            this.website = record;

            // initially switch to the templates if the user has sufficient rights or fall back to pages
            this.setActiveItem(null);
        }
    },

    /**
     * Override superclass to apply default web-app behavior over desktop taskbar like behavior
     */
    openPanel: function (panelConfig) {
        if (!panelConfig || !panelConfig.record) {
            return;
        }
        // grab record's id, since we don't want to open several panels for the same record
        panelConfig.id = panelConfig.record.id;

        var newPanel = this.viewport.add(panelConfig);
        newPanel.on('destroy', function (p) {
            this.removePanel(p, true);
        }, this);

        this.setActiveItem(panelConfig.record);

        // a new panel was actually created
        // -> store config to all restoring open tabs after app refresh
        this.activePanel = panelConfig;

        return newPanel;
    },

    /**
     * Sets the active (visible) item in the layout.
     */
    setActiveItem: function (record) {
        this.activeItem = null;

        if (this.website) {
            var websiteName = this.website.get('name');

            if (record) {
                this.overviewBtn.setIconClass("project-summary depth-two");
                // add website button
                var websiteBtnCap = CMS.i18n(null, 'ApplicationViewport.btnCap.text.website').replace(/{name}/g, websiteName);
                var websiteBtnTip = CMS.i18n(null, 'ApplicationViewport.backBtn.tip.backToPages');
                var overviewBtnTip = CMS.i18n(null, 'ApplicationViewport.backBtn.tip.backToWebsites');
                this.websiteBtn.show();
                this.websiteBtn.setText(websiteBtnCap);
                this.websiteBtn.setTooltip(websiteBtnTip);
                this.overviewBtn.setTooltip(overviewBtnTip);

                var type = CMS.data.isTemplateRecord(record) ? 'template' : 'page';
                this.updatePageOrTemplateNameBreadcrumb(record.get('name'), type);

                this.viewport.getLayout().setActiveItem(record.id);
                this.activeItem = record.id;
            } else {
                this.overviewBtn.setIconClass("project-summary depth-one");
                this.websiteBtn.hide();
				this.activePanel = null;

                var btnCap = CMS.i18n(null, 'ApplicationViewport.btnCap.text.website').replace(/{name}/g, websiteName);
                var btnTip = CMS.i18n(null, 'ApplicationViewport.backBtn.tip.backToWebsites');
                this.overviewBtn.setTooltip(btnTip);
                this.backButton.setTooltip(btnTip);
                this.infoBtn.setText(btnCap);

                this.viewport.getLayout().setActiveItem('pagesAndTemplatesPanel');
            }
            this.infoBtn.show();
            this.backButton.show();
        } else {
            this.infoBtn.hide();
            this.overviewBtn.setIconClass("project-summary");
            this.overviewBtn.setTooltip("");
            this.websiteBtn.hide();
            this.backButton.hide();
            this.viewport.getLayout().setActiveItem('sitesPanel');
        }
    },

    updatePageOrTemplateNameBreadcrumb: function (name, type) {
        // Button Text (Active Item: Template or Page)
        var btnCap = type === 'template' ?
            CMS.i18n(null, 'ApplicationViewport.btnCap.text.template') :
            CMS.i18n(null, 'ApplicationViewport.btnCap.text.page');
        btnCap = btnCap.replace(/{name}/g, name);
        this.infoBtn.setText(btnCap);

        // Close Button Tooltip
        this.backButton.setTooltip(CMS.i18n(null, 'ApplicationViewport.backBtn.tip.backToPages'));
    },

    /**
     * override superclass to update css classes of tabs (fancy edges)
     * @protected
     */
    removePanel: function (panel, force) {
        if (Ext.isString(panel)) {
            panel = Ext.getCmp(panel);
        }
        if (!panel) {
            return null;
        }

        var result = CMS.layout.ApplicationViewport.superclass.removePanel.call(this, panel, force);
        if (result) {
            this.activePanel = null;
        }
        return result;
    },

    /**
     * Refreshes all open tabs
     *
     * @param {Function} callback A callback function which will be called after refresh is complete
     * @param {Object} scope The execution scope for the callback
     */
    refresh: function (callback, scope) {
        // remember currently selected website
        var website = this.website;

        // remember open tab
        var activePanel = this.activePanel;

        // close all tabs...
        this.processOpenTabs(/* close -> */ true, /* force -> */ true);
        if (website) {
            // ...and close website to destroy all old data
            this.setSite(null);
        }

        // re-open website and tabs with fresh data
        if (website) {
            this.setSite(website);
        }

        if (activePanel) {
            // re-activate previously selected panel
            (function () {
                this.restorePanel(activePanel, callback, scope);
            }).defer(1000, this); // delay the opening to so unlock requests from closing have enough time
        } else {
            callback.call(scope || this);
        }
    },


    /**
     * Updates the tab records and re-opens the tabs again
     * @private
     */
    restorePanel: function (panelConfig, callback, scope) {
        var record = panelConfig.record;
        var lockType = null;

        if (record) {
            var websiteId = record.get('websiteId');
            var templateStore = CMS.data.StoreManager.get('template', websiteId);
            var openPanelAndTriggerCallback = function (panelConfig) {
                this.openPanel(panelConfig);
                callback.call(scope || this);
            };

            if (CMS.data.isPageRecord(record)) {
                // record represents page
                lockType = 'page';
                // unfortunately there is no page store
                // -> we have to load the current data from server
                CMS.app.trafficManager.sendRequest({
                    action: 'getPage',
                    data: {
                        id: record.id,
                        websiteId: websiteId
                    },
                    success: function (response) {
                        panelConfig.record = new CMS.data.PageRecord(Ext.apply(response.data, {
                            websiteId: websiteId
                        }), record.id);
                        openPanelAndTriggerCallback.call(this, panelConfig);
                    },
                    scope: this
                });
            } else if (CMS.data.isTemplateRecord(record)) {
                // record represents a template
                lockType = 'template';
                // -> get updated record from store
                panelConfig.record = templateStore.getById(record.id);
                openPanelAndTriggerCallback.call(this, panelConfig);
            }

            if (lockType) {
                CMS.app.lockManager.requestLock({
                    id: record.id,
                    websiteId: websiteId,
                    type: lockType
                }, true);
            }
        }
    },

    /**
     * Click handler for the "back" button
     * Switches to "pages and templates" overview if a page or template is active
     * or to the website overview otherwise
     * @private
     */
    backButtonHandler: function () {
        var activePanel = this.activeItem && Ext.getCmp(this.activeItem);
        if (activePanel) {
            activePanel.conditionalDestroy({
                success: function () {
                    this.setActiveItem(null);
                },
                scope: this
            });
        } else {
            this.setSite(null);
        }
    },

    websiteButtonHandler: function () {
        var activePanel = this.activeItem && Ext.getCmp(this.activeItem);
        if (activePanel) {
            activePanel.conditionalDestroy({
                success: function () {
                    this.setActiveItem(null);
                },
                scope: this
            });
        } else {
            this.setActiveItem(null);
        }
    },

    summaryButtonHandler: function () {
        var activePanel = this.activeItem && Ext.getCmp(this.activeItem);
        if (activePanel) {
            activePanel.conditionalDestroy({
                success: function () {
                    this.setSite(null);
                },
                scope: this
            });
        } else {
            this.setSite(null);
        }
    },

    //
    //
    // Handler for main menu items
    //
    //

    usersButtonHandler: function () {
        (new CMS.userManagement.UserManagementWindow({
            websiteId: this.website ? this.website.id : null
        })).show();
    },

    groupsButtonHandler: function () {
        (new CMS.userManagement.UserGroupManagementWindow({
            website: this.website
        })).show();
    },

    templateSnippetsButtonHandler: function () {
        (new CMS.home.TemplateSnippetManagementWindow({
            websiteId: this.website.id
        })).show();
    },

    websiteSettingsButtonHandler: function () {
        (new CMS.websiteSettings.WebsiteSettingsWindow({
            websiteId: this.website.id
        })).show();
    },

    mediaDBHandler: function () {
        CMS.mediaDB.MediaDBWindow.getInstance(this.website.id, false).show();
    },

    publishHandler: function () {
        // abort if website has no pages
        if (!this.website.get('navigation').length) {
            CMS.Message.info(CMS.i18n(null, 'ApplicationViewport.menu.publishNoPages'));
            return;
        }

        if (!this.website.get('publishingEnabled') &&
            CMS.data.WebsiteStore.getInstance().getPublishedWebsitesCount() >= CMSSERVER.data.quota.webhosting.maxCount) {
            (new CMS.home.PublishingMarketingWindow()).show();
            return;
        }

        // show publish window
        (new CMS.home.PublishWindow({
            website: this.website
        })).show();
    },

    helpMenuButtonHandler: function (btn) {
        window.open(btn.targetUrl);
    },

    colorSchemesHandler: function () {
        (new CMS.home.ColorSchemeDefinitionWindow({
            website: this.website
        })).show();
    },

    changePasswordButtonHandler: function () {
        (new CMS.PasswordChangeWindow()).show();
    },

    openCustomerDashboardHandler: function () {
        window.open(SB.util.addParameterToUrl(CMS.app.userInfo.get('dashboardUrl'), 'email', CMS.app.userInfo.get('email')), 'dashboard');
    },

    openModuleManagement: function (btn) {
		if (!CMSSERVER.data.quota.module.enableDev) {
			(new CMS.moduleEditor.ModuleMarketingWindow()).show();
		} else {
			(new CMS.moduleEditor.ModuleManagementWindow({
				website: this.website
			})).show();
		}
    },

    logoutButtonHandler: function () {

        //Check if user has to close some tabs first
        if (!this.beforeWebsiteClose(this.logoutButtonHandler, this)) {
            return;
        }
        Ext.MessageBox.confirm(
            /* title -> */ CMS.i18n('Benutzer abmelden'),
            /* message -> */ CMS.i18n('Wirklich vom System abmelden?'),
            /* callback -> */ function (btnId) {
                if (btnId === 'yes') {
                    CMS.app.loginHelper.logout();
                }
            },
            /* scope -> */ this
        );
    },

    openUpgradeUrlButtonHandler: function() {
        window.open(SB.util.addParameterToUrl(CMS.app.userInfo.get('upgradeUrl'), 'email', CMS.app.userInfo.get('email')), 'upgrade');
    },

    //
    //
    // private helper methods
    //
    //

    /**
     * Get an array containing the currently opened tab's titles
     *
     * @param {Boolean} close <code>true</code> to close a tab if it is not dirty
     * @param {Boolean} force <code>true</code> to close even dirty tabs
     * @return {Array} An array of the names of the dirty tabs
     * @private
     */
    processOpenTabs: function (close, force) {
        var tabList = [];
        Ext.each(this.taskbar.items, function (oneButton) {
            if (oneButton.isXType('splitbutton')) {
                var panel = Ext.getCmp(oneButton.connectedPanel);
                if (panel && panel.isDirty() && !force) {
                    tabList.push('- ' + oneButton.text);
                } else {
                    if (close) {
                        this.removePanel(panel, force);
                    }
                }
            }
        }, this);
        return tabList;
    },

    /**
     * Saves all open tab with unsaved changes
     *
     * @param {Boolean} [close] Optional. Close tab/panel after saving (defaults to false)
     * @param {Function} [cb] Optional. A callback function which is executed after saving
     * @param {Object} [scope] Optional. The execution context for the callback method
     * @param {Array} [args] Optional. The arguments for the callback
     * @private
     */
    saveOpenTabs: function (close, callback, scope, args) {
        var pending = 0; // the number of tabs with a running save request

        Ext.each(this.taskbar.items, function (oneButton) {
            if (oneButton.isXType('splitbutton')) {
                var panel = Ext.getCmp(oneButton.connectedPanel);
                var cb = (function () { // callback for saving a single tab
                    pending--;

                    if (close) {
                        this.removePanel(panel);
                    }

                    if (pending === 0 && Ext.isFunction(callback)) {
                        // no further request in progress
                        // -> enable GUI
                        Ext.getBody().unmask();
                        // -> and trigger callback
                        callback.apply(scope, args);
                    }
                }).bind(this);

                if (panel && panel.isDirty() && Ext.isFunction(panel.save)) {
                    pending++;
                    panel.save(/* callback -> */ cb, /* silent -> */ true);
                }
            }
        }, this);

        if (pending > 0) {
            // there are tab which should be saved
            // -> show load mask until all are saved
            Ext.getBody().mask(CMS.i18n('Bitte warten…'));
        } else if (Ext.isFunction(callback)) {
            // nothing to save/close
            // -> trigger callback
            callback.apply(scope, args);
        }
    },

    /**
     * Handler for beforeclose event
     *
     * @param {Function} [cb] Optional. A callback function which is executed if the user wants to continue
     *      in spite of the unsaved changes warning
     * @param {Object} [scope] Optional. The execution context for the callback method
     * @param {Array} [args] Optional. The arguments for the callback
     * @private
     */
    beforeWebsiteClose: function (cb, scope, args) {
        var openTabs = this.processOpenTabs(true);
        if (openTabs.length > 0) {
            var msg = CMS.i18n('Es konnten nicht alle Tabs automatisch geschlossen werden, da es ungespeicherte Änderungen gibt:') + '<br/>' + openTabs.join('<br/>');

            Ext.MessageBox.show({
                closable: false,
                title: CMS.i18n('Ungespeicherte Änderungen'),
                msg: msg,
                buttons: {
                    yes: CMS.i18n('Alles speichern'),
                    no: CMS.i18n('Alle Änderungen verwerfen'),
                    cancel: true
                },
                icon: Ext.MessageBox.INFO,
                fn: function (btnId) {
                    if (btnId === 'yes') {
                        this.saveOpenTabs(true, cb, scope, args);
                    } else if (btnId === 'no') {
                        this.processOpenTabs(/* close -> */ true, /* force -> */ true);
                        if (Ext.isFunction(cb)) {
                            cb.apply(scope, args);
                        }
                    }
                },
                scope: this
            });
            return false;
        }
        return true;
    },

    /**
     * Handler for the "CMSbeforeimport" event
     * Cancels import if there are unsaved changes and shows message
     * @private
     */
    beforeImportHandler: function () {
        var unsavedTabs = this.processOpenTabs(false);
        if (unsavedTabs.length > 0) {
            CMS.Message.info(
                /* title -> */ CMS.i18n('Ungespeicherte Änderungen'),
                /* message -> */ CMS.i18n('Alle Änderungen müssen gespeichert oder verworfen werden bevor ein Import durchgeführt werden kann. Folgende Tabs sind betroffen:') + '<br/>' + unsavedTabs.join('<br/>'));
            return false;
        }
        return true;

    },

    // override superclass to switch back to the corresponding tab when closing the last panel in history
    removeItemFromHistory: function (panel) {
        var lastActiveItem = CMS.layout.ApplicationViewport.superclass.removeItemFromHistory.call(this, panel);
        if (!lastActiveItem) {
            lastActiveItem = 'pagesAndTemplatesPanel';
        }
        return lastActiveItem;
    },

    /**
     * @private
     */
    openGettingStartedPopoverAtOpenWebsite: function() {
        if (!CMS.app.userInfo.canManageSites()) {
            return;
        }
        if (!window.localStorage) {
            return;
        }
        if (CMSSERVER && CMSSERVER.data && CMSSERVER.data.urls && CMSSERVER.data.urls.linkResolver) {
            return;
        }
        var showCount = parseInt(window.localStorage.getItem('CMSgettingStartedPopover'), 10) || 0;
        if (showCount === 0) {
            window.localStorage.setItem('CMSgettingStartedPopover', 1);
            this.openGettingStartedPopover();
        }
    },

    /**
     * Click handler for the "getting Started" button
     * Shows or hide the getting started popover
     * @private
     */
    openGettingStartedPopoverHandler: function() {
        if (this.isGettingStartedPopoverVisible()) {
            this.closeGettingStartedPopover();
        } else {
            this.openGettingStartedPopover();
        }
    },

    /**
     * Creates the getting started popover if not exists
     * @private
     */
    createGettingStartedPopover: function() {
        if (!this.gettingStartedPopover || this.gettingStartedPopover.isDestroyed) {
            this.gettingStartedPopover = new CMS.GettingStartedPopover({
                target: this.openGettingStartedWindowButton.getEl()
            });
        }
    },

    /**
     * Return true, if popover is visible
     * @private
     */
    isGettingStartedPopoverVisible: function() {
        return (this.gettingStartedPopover &&
            !this.gettingStartedPopover.isDestroyed && this.gettingStartedPopover.isVisible());
    },

    /**
     * Open the getting started popover
     * @private
     */
    openGettingStartedPopover: function() {
        if (!(CMSSERVER && CMSSERVER.data && CMSSERVER.data.urls && CMSSERVER.data.urls.linkResolver)) {
            return;
        }
        this.createGettingStartedPopover();
        if (this.gettingStartedPopover) {
            this.gettingStartedPopover.show();
        }
    },

    /**
     * Close the getting started popover
     * @private
     */
    closeGettingStartedPopover: function() {
        if (this.gettingStartedPopover) {
            this.gettingStartedPopover.hide();
        }
    },

    /**
     * Reposition the getting started popover
     * @private
     */
    repositionGettingStartedPopover: function() {
        var self = this;
        window.setTimeout(function () {
            if (self.gettingStartedPopover) {
                self.gettingStartedPopover.reposition();
            }
        }, 100);
    },

    /**
     * Open upgrade popover after some minutes delay
     * @private
     */
    openUpgradePopoverDelayed: function() {
        if (!this.openUpgradeUrlButton.isVisible()) {
            return;
        }

        // delay opening for 15 minutes (first session) or 2 minutes (other sessions)
        var showCount = parseInt(window.localStorage.getItem('CMSpopoverUpgrade'), 10) || 0;
        var delayInMinutes = showCount > 0 ? 2 : 15;
        window.localStorage.setItem('CMSpopoverUpgrade', showCount + 1);

        this.openUpgradePopover.defer(delayInMinutes * 60 * 1000, this);
    },

    /**
     * Open the upgrade popover
     * @private
     */
    openUpgradePopover: function() {
        var width = 300;
        this.upgradePopover = new CMS.Popover({
            target: this.openUpgradeUrlButton.getEl(),
            title: CMS.i18n(null, 'ApplicationViewport.upgradePopover.title').replace('{name}', CMS.app.userInfo.get('firstname')),
            html: CMS.i18n(null, 'ApplicationViewport.upgradePopover.text'),
            anchorOffset: width - 40,
            width: width,
            anchor: 'right',
            offsets: {
                top: [-130, 0]
            },
            destroyOnDocMouseDown: false
        });

        this.mon(this.openUpgradeUrlButton, 'click', function () {
            if (this.upgradePopover) {
                this.upgradePopover.destroy();
            }
        }, this);
    },

    /**
     * Opens a Template
     * @param record - template record
     * @param {Function} [cb] - callback
     * @param {Object} [scope] - callback
     */
    openTemplateHandler: function (record, cb, scope) {
        var pageAndTemplatePanel = this.viewport.get('pagesAndTemplatesPanel');
        if (pageAndTemplatePanel) {
            pageAndTemplatePanel.openTemplate(record, cb, scope);
        }
    },

    /**
     * Closes the given pageId and opens the template record with a single call.
     * closeworkbench, opentemplate/openworkbench calls would lock each other
     * @param pageId - page id
     * @param templateRecord - template record
     */
    closePageAndOpenTemplate: function (pageId, templateRecord) {
        // release lock and open template after it is released
        CMS.app.lockManager.releaseLockImmediately({
            websiteId: this.website.id,
            type: 'page',
            id: pageId
        }, function () {
            var mask = Ext.getBody().mask();
            mask.addClass('CMSinvisiblemask');
            var pageAndTemplatePanel = this.viewport.get('pagesAndTemplatesPanel');
            pageAndTemplatePanel.preventPreviewLoadingOnAppear = true;
            this.fireEvent('CMScloseworkbench', pageId, true);
            this.openTemplateHandler(templateRecord, function () {
                Ext.getBody().unmask();
                pageAndTemplatePanel.preventPreviewLoadingOnAppear = false;
            }, this);
        }, this);
    },

    /**
     * Overrides superclass to
     * - unregister fullscreen toggle handler
     * - clean properties
     */
    destroy: function () {
        if (this.fullscreenToggleHandler) {
            Ext.lib.Event.removeListener(window, 'resize', this.fullscreenToggleHandler);
            this.fullscreenToggleHandler = null;
        }

        CMS.layout.ApplicationViewport.superclass.destroy.apply(this, arguments);

        this.activePanel = null;
        this.activeItem = null;
    }
});
