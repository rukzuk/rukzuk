Ext.ns('CMS.home');

/**
 * Panel which contains all functionality to publish a website
 *
 * @class       CMS.home.PublishPanel
 * @extends     CMS.home.ManagementPanel
 * @author      Thomas Sojda
 * @copyright   (c) 2011, by Seitenbau GmbH
 */
CMS.home.PublishPanel = Ext.extend(CMS.home.ManagementPanel, {

    initComponent: function () {
        Ext.apply(this, {
            layout: 'fit',
            tbar: [
                {
                    xtype: 'label',
                    cls: 'CMSpublishpanelurlinfo',
                    ref: '../topInfo',
                    flex: 3,
                }, '->',
                {
                    tooltip: CMS.i18n('Live-Server-Konfiguration'),
                    iconCls: 'properties',
                    handler: this.publishConfigHandler,
                    hidden: !CMS.app.userInfo.canEditPublishingConfig(),
                    scope: this,
                    flex: 0
                },
                {
                    xtype: 'button',
                    tooltip: CMS.i18n(null, 'publish.panel.removeLiveHosting'),
                    iconCls: 'delete',
                    ref: '../disablePublishingBtn',
                    hidden: !CMS.app.userInfo.canEditPublishingConfig(),
                    handler: this.disablePublishingHandler,
                    scope: this,
                    flex: 0
                },
            ],
            items: [
                {
                    xtype: 'CMSbuildgrid',
                    ref: 'buildGrid',
                    loadMask: false,
                    listeners: {
                        CMSdownloadbuild: this.downloadBuildHandler,
                        CMSpublishbuild: this.publishBuildHandler,
                        afterrender: function () {
                            this.mon(this.buildGrid.getStore(), 'load', this.syncPublishingState, this);
                            this.syncPublishingState();
                        },
                        scope: this
                    }
                }
            ],
            bbar: [
                {
                    text: CMS.i18n('Version erzeugen'),
                    handler: this.buildWebsiteHandler,
                    scope: this
                },
                {
                    text: CMS.i18n('Logfile betrachten'),
                    ref: '../logButton',
                    handler: this.viewLogHandler,
                    scope: this

                },
                '->',
                {
                    xtype: 'progress',
                    ref: '../publishProgressBar',
                    animate: true,
                    height: 40,
                    width: 450,
                    hidden: true
                },
                {
                    text: CMS.i18n('Version erzeugen und jetzt publizieren'),
                    cls: 'primary',
                    ref: '../buildAndPublishWebsiteButton',
                    handler: this.buildAndPublishWebsiteHandler,
                    scope: this
                }
            ]
        });

        CMS.home.PublishPanel.superclass.initComponent.call(this);
        this.mon(CMS.data.WebsiteStore.getInstance(), 'update', this.updateWebsiteInfoTextListener, this);
        this.on('buildAndPublishWebsite', this.buildAndPublishWebsite, this);
        this.on('showPublishConfig', this.publishConfigHandler, this);
    },

    /**
     * Updates the Text of the Website Info label based on the given object
     * @private
     * @param {object} record website record
     */
    updateWebsiteInfoText: function (record) {
        if (!record.data || !record.data.publishInfo) {
            return;
        }
        var publishInfo = record.data.publishInfo;
        var websiteUrl = publishInfo.url;
        var websiteUrlNoProtocol = websiteUrl.replace(/^http[s]?:\/\//, '');

        var websiteUrlText = CMS.i18n(null, 'publish.panel.publicWebsiteEmptyUrl');
        if (websiteUrlNoProtocol !== '') {
            websiteUrlText = String.format('<a href="{0}" target="_blank">{1}</a>',
                websiteUrl,
                websiteUrlNoProtocol
            );
        }

        this.topInfo.setText(websiteUrlText, false);
    },

    /**
     * Visualizes the current state of publishing:
     * - if there is a build which is being published then show a progress bar with the current
     *   progress and hide the "build and publish" button
     * - if there is no build which is being published then hide the progress bar and show the
     *   "build and publish" button
     * This method is triggered each time the build store is updated
     * @private
     */
    syncPublishingState: function () {
        var buildStore = this.buildGrid.getStore();
        var isPublishing = buildStore.isPublishing();
        // update url info
        this.topInfo.setVisible(buildStore.hasBeenSuccesfulllyPublished());


        if (isPublishing) {
            this.disablePublishingBtn.setDisabled(true);
            var percent = isPublishing.get('percent');
            var remaining = isPublishing.get('remaining');
            var msg = CMS.i18n('Version {num} wird gerade publiziert').replace(/{num}/g, isPublishing.get('version'));
            if (remaining > 0) {
                var remainingTime = [];
                var hours = Math.floor(remaining / 3600);
                if (hours > 0) {
                    remainingTime.push(hours + CMS.i18n('h'));
                    remaining -= 3600 * hours;
                }
                var minutes = Math.floor(remaining / 60);
                if (minutes > 0) {
                    remainingTime.push(minutes + CMS.i18n('m'));
                    remaining -= 60 * minutes;
                }
                var seconds = remaining;
                if (seconds > 0) {
                    remainingTime.push(seconds + CMS.i18n('s'));
                }
                msg += ' ' + CMS.i18n('(ca. {time} verbleibend)').replace(/{time}/g, remainingTime.join(' '));
            } else {
                msg += ' ' + CMS.i18n('(verbleibende Zeit wird berechnet)');
            }

            this.publishProgressBar.show();
            this.publishProgressBar.updateProgress(percent / 100, msg);
            this.buildAndPublishWebsiteButton.hide();
            this.startPollingForBuildUpdates();
        } else {
            this.stopPollingForBuildUpdates();
            this.publishProgressBar.hide();
            this.publishProgressBar.updateProgress();
            this.buildAndPublishWebsiteButton.show();
            this.buildAndPublishWebsiteButton.enable();
            this.disablePublishingBtn.setDisabled(false);
        }
    },

    /**
     * Starts a task (if there is none already) that reloads the build store in regular
     * intervals to check the state of currently published builds
     * @private
     */
    startPollingForBuildUpdates: function () {
        if (!this.buildUpdatesTask) {
            this.buildUpdatesTask = {
                run: function () {
                    this.buildGrid.getStore().reload();
                },
                scope: this,
                interval: CMS.config.ajaxIntervals.publishBuildUpdate
            };
            Ext.TaskMgr.start(this.buildUpdatesTask);
        }
    },

    /**
     * Stops the task that polls the build list
     * @private
     */
    stopPollingForBuildUpdates: function () {
        if (this.buildUpdatesTask) {
            Ext.TaskMgr.stop(this.buildUpdatesTask);
            this.buildUpdatesTask = null;
        }
    },

    /**
     * Creates a ajax request which will build the current selected website
     *
     * @param {Object} options The request parameter and callbacks
     * @param {String} options.comment The build comment
     * @param {Function} [options.success] Optional. The success callback; if ommitted a success message
     *      is displayed as a default action
     * @param {Function} [options.callback] Optional. A callback function which is always called (success
     *      or failure); if ommitted the build store is reloaded as a default action
     * @param {Object} [options.scope] The execution context for the callback methods
     * @private
     */
    buildWebsite: function (options) {
        CMS.app.trafficManager.sendRequest({
            action: 'buildWebsite',
            modal: true,
            data: {
                websiteId: this.websiteId,
                comment: options.comment
            },
            successCondition: 'data.id',
            success: function (response) {
                if (Ext.isFunction(options.success)) {
                    options.success.call(options.scope, response);
                } else {
                    CMS.Message.info(CMS.i18n('Die Version {version} der Website wurde erfolgreich erstellt.').replace('{version}', response.data.version));
                }
            },
            callback: function () {
                if (Ext.isFunction(options.callback)) {
                    options.callback.apply(options.scope, arguments);
                } else {
                    this.buildGrid.store.reload();
                }
            },
            failureTitle: CMS.i18n('Fehler beim Erstellen einer Website-Version'),
            scope: this
        });

    },

    /**
     * Opens a prompt to enter the a version comment and triggers the build request
     * @private
     */
    buildWebsiteHandler: function () {
        CMS.Message.prompt(CMS.i18n('Website-Version erstellen'), CMS.i18n('Kommentar:'), function (btnId, comment) {
            if (btnId == 'ok') {
                this.buildWebsite({
                    comment: comment
                });
            }
        }, this);
    },

    /**
     * Asks for the comment before build and publish is called
     * @private
     */
    buildAndPublishWebsiteHandler: function () {
        CMS.Message.prompt(CMS.i18n('Website-Version erstellen'), CMS.i18n('Kommentar:'), function (btnId, comment, msgbox) {
            if (btnId == 'ok') {
                this.buildAndPublishWebsite(comment);
            }
        }, this);
    },

    /**
     * Creates a ajax request which will build and publish the current selected website
     * @private
     * @param {string} [comment] - optional comment
     */
    buildAndPublishWebsite: function (comment) {
        comment = comment || '';
        this.buildWebsite({
            comment: comment,
            success: function (response) {
                CMS.Message.toast(CMS.i18n(null, 'publish.panel.buildSuccessStartPublishToast.title'),
                    CMS.i18n(null, 'publish.panel.buildSuccessStartPublishToast.msg').replace('{version}', response.data.version));
                this.publishBuild(response.data.id);
            },
            callback: function () {
                this.buildGrid.store.reload();
            },
            scope: this
        });
    },

    /**
     * Creates a ajax request which will publish the current selected build
     * @param {String} buildId The id of the build to be published
     * @private
     */
    publishBuild: function (buildId) {
        this.buildAndPublishWebsiteButton.disable();
        CMS.app.trafficManager.sendRequest({
            action: 'publishBuild',
            data: {
                id: buildId,
                websiteId: this.websiteId
            },
            successCondition: 'data',
            callback: function () {
                this.buildGrid.store.reload();
                this.buildAndPublishWebsiteButton.enable();
            },
            failureTitle: CMS.i18n('Fehler beim Publizieren'),
            scope: this
        });
    },

    /**
     * Handler for the "CMSpublishbuild" event of the CMSbuildgrid
     * @private
     */
    publishBuildHandler: function (record) {
        CMS.Message.info(CMS.i18n('Die Version {version} der Website wird nun publiziert. Dieser Vorgang kann einige Minuten dauern.').replace('{version}', record.get('version')));
        this.publishBuild(record.id);
    },

    /**
     * Opens a new window which points to the download URL of the build file
     * @param {CMS.data.BuildRecord} record The build to be downloaded
     * @private
     */
    downloadBuildHandler: function (record) {
        var websiteName = CMS.data.WebsiteStore.getInstance().getById(this.websiteId).get('name');
        var comment = record.get('comment');
        var suggestedName = websiteName + ' - v' + record.get('version') + (comment ? ' - ' + comment : '');
        CMS.Message.prompt(CMS.i18n('Website-Version herunterladen'), CMS.i18n('Dateiname:'), function (btnId, name, msgbox) {
            if (btnId == 'ok') {
                var params = CMS.app.trafficManager.createPostParams({
                    id: record.id,
                    websiteId: this.websiteId,
                    name: name
                });
                var href = Ext.urlAppend(CMS.config.urls.downloadBuild, Ext.urlEncode(params));

                window.open(href);
            }
        }, this, false, suggestedName);
    },

    /**
     * Opens a window to edit the publish configuration
     * @private
     */
    publishConfigHandler: function () {
        (new CMS.home.PublishConfigWindow({
            websiteRecord: CMS.data.WebsiteStore.getInstance().getById(this.websiteId)
        })).show();
    },

    /**
     * Requests the log file and shows its content in a window
     * @private
     */
    viewLogHandler: function () {
        CMS.app.trafficManager.sendRequest({
            action: 'viewLog',
            modal: true,
            data: {
                websiteId: this.websiteId,
                format: 'txt'
            },
            successCondition: function (response) {
                return Ext.isArray(response.data);
            },
            success: function (response) {
                var text = response.data.join('\n');
                if (!/[^\s]/.test(text)) {
                    text = CMS.i18n('(Logdatei ist leer)');
                }
                (new CMS.TextWindow({
                    title: CMS.i18n('Logdatei'),
                    modal: true,
                    object: text
                })).show();
            },
            failureTitle: CMS.i18n('Fehler beim Abrufen der Logdatei')
        });
    },

    setSite: function (site) {
        this.site = site;
        var needLogBtn = CMS.config.debugMode && CMS.app.userInfo.canViewLogFile(site);
        this.logButton.setVisible(needLogBtn);

        this.updateWebsiteInfoText(site);
        this.buildGrid.setSite(site);

        return CMS.home.PublishPanel.superclass.setSite.apply(this, arguments);
    },

    /**
     * Update the website info text by update event on website store
     * @param store
     * @param site
     */
    updateWebsiteInfoTextListener: function (store, site) {
        if (site == this.site) {
            this.updateWebsiteInfoText(site);
        }
    },

    /**
     * Removes the live site and all config
     * @private
     */
    disablePublishingHandler: function () {
        Ext.MessageBox.show({
            closable: false,
            title: CMS.i18n(null, 'publish.panel.areYouSureTitle'),
            msg: CMS.i18n(null, 'publish.panel.areYouSureMsg'),
            buttons: {
                yes: true,
                no: true
            },
            icon: Ext.MessageBox.WARNING,
            fn: function (btnId) {
                if (btnId === 'yes') {
                    this.disableHosting(function () {
                        this.close();
                    });
                }
            },
            scope: this
        });
    },


    /**
     * Disables the webhosting aka publishing
     */
    disableHosting: function (cb) {

        var websiteStore = CMS.data.WebsiteStore.getInstance();
        var websiteRecord = websiteStore.getById(this.websiteId);

        CMS.app.trafficManager.sendRequest({
            action: 'disablePublishing',
            modal: true,
            data: {
                'id': this.websiteId
            },
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
    },


    destroy: function () {
        CMS.data.WebsiteStore.getInstance().un('update', this.updateWebsiteInfoTextListener, this);
        this.stopPollingForBuildUpdates();
        CMS.home.PublishPanel.superclass.destroy.apply(this, arguments);
    },

    close: function () {
        this.fireEvent('close');
    }
});

Ext.reg('CMSpublishpanel', CMS.home.PublishPanel);
