Ext.ns('CMS.mediaDB');

/**
* @class CMS.mediaDB.MediaDBWindow
* @extends CMS.MainWindow
* This is an n-gleton. Use {@link #getInstance} to access its per-website instance.
* @requires CMS.mediaDB.MediaPropertyWindow
*/
CMS.mediaDB.MediaDBWindow = (function () {

    var instances = {};
    var SELECTION_GRID_WIDTH = 300;
    var hasUnloadListener = false;

    var Class = Ext.extend(CMS.MainWindow, {
        width: 1000,
        height: 800,
        cls: 'CMSmediadbwindow',
        constrainHeader: true,
        modal: true,
        closeAction: 'hide',
        layout: 'hbox',
        layoutConfig: {
            align: 'stretch',
            pack: 'start'
        },
        minButtonWidth: 0,
        mediaType: null,

        multiSelect: false,

        /**
         * tells the class if MediaDB has changed or not
         * @property
         * @private
         */
        mediaChanged: false,

        initComponent: function () {
            this.title = this.title || CMS.i18n('Mediendatenbank');
            this.items = [{
                /**
                 * Reference to the wrapped panel containing the images of the
                 * selected album
                 *
                 * @property panel
                 * @type CMS.mediaDB.MediaDBPanel
                 */
                ref: 'panel',
                xtype: 'CMSmediadbpanel',
                websiteId: this.websiteId,
                flex: 1,
                listeners: (function (self) {
                    var listeners = {
                        'CMSmediareplaced': self.mediaReplacedHandler,
                        scope: self
                    };

                    if (!self.multiSelect) {
                        listeners.mark = self.markUnmarkHandler;
                        listeners.unmark = self.markUnmarkHandler;
                    }
                    return listeners;
                })(this)

            }];

            this.height = CMS.app.viewport.getHeight() * 0.8;

            if (this.multiSelect) {
                this.width += SELECTION_GRID_WIDTH;
                this.items.push({
                    /**
                     * Reference to the grid containing the selected items (only
                     * available in multi selection mode)
                     *
                     * @property selCartGrid
                     * @type CMS.mediaDB.MediaBasketGrid
                     * @private
                     */
                    ref: 'selCartGrid',
                    xtype: 'CMSmediabasketgrid',
                    width: SELECTION_GRID_WIDTH,
                    listeners: {
                        'addfile': this.markUnmarkHandler,
                        'removefile': this.markUnmarkHandler,
                        scope: this
                    }
                });

                // flash MediaBasketGrid when user drags for the first time
                var self = this;
                this.on('afterrender', function () {
                    var zone = self.panel.mediaSelector.getView().dragZone;
                    zone.onInitDrag = (function (init) {
                        return function () {
                            self.selCartGrid.getEl().addClass('highlight');
                            setTimeout(function () {
                                self.selCartGrid.getEl().removeClass('highlight');
                            }, 400);
                            var result = init.apply(this, arguments);
                            zone.onInitDrag = init;
                            return result;
                        };
                    })(zone.onInitDrag);
                });
                this.on('destroy', function () {
                    self = null;
                });
            }

            this.buttons = [{
                text: CMS.i18n('Übernehmen'),
                iconCls: 'ok',
                ref: '../okButton',
                handler: this.okButtonHandler,
                scope: this,
                disabled: true,
                hidden: true
            }];

            CMS.MainWindow.prototype.initComponent.apply(this, arguments);

            this.on('afterrender', function () {
                if (this.mediaType) {
                    this.filterMediaType(this.mediaType);
                }
            }, this);


            // window closed (see closeAction)
            this.on('hide', function () {
                // tell others that the media has changed
                if (this.mediaChanged) {
                    this.panel.mediaSelector.store.fireMediachanged();
                    this.mediaChanged = false;
                }
            }, this);
        },

        /**
         * Handler for the CMSmediareplaced Event
         * @private
         */
        mediaReplacedHandler: function () {
            this.mediaChanged = true;
            // update selection basket (grid), only an object if multiselection is on
            if (this.selCartGrid) {
                this.selCartGrid.fireEvent('CMSmediareplaced');
            }
        },

        /**
        * @private
        * Handler for click on okButton
        */
        okButtonHandler: function () {
            /**
             * @event select
             * Fired when the user selects a file from the list and confirms the selection
             */
            this.fireEvent('select', this.getSelectedFiles());
            this.hide();
        },

        /**
         * returns the selected files
         *
         * @return Array
         *      an array of records representing the selected files
         */
        getSelectedFiles: function () {
            var files;
            if (this.multiSelect) {
                files = this.selCartGrid.getStore().getRange();
            } else {
                files = this.panel.mediaSelector.getSelectionModel().getSelections();
            }
            return files;
        },

        /**
         * initializes the selected files (only for multi selection mode)
         *
         * @param {Array} files
         *      an array of mediaDB file ids
         */
        setSelectedFiles: function (files) {
            if (!this.multiSelect) {
                return;
            }

            if (this.requestId) {
                CMS.app.trafficManager.abortRequest(this.requestId);
            }

            if (files && files.length) {
                this.requestId = CMS.app.trafficManager.sendRequest({
                    action: 'getMultipleMedia',
                    data: {
                        websiteId: this.websiteId,
                        ids: files
                    },
                    successCondition: CMS.config.roots.getMultipleMedia,
                    scope: this,
                    success: function (resp) {
                        // clear existing selection
                        this.selCartGrid.getStore().removeAll();

                        // initialize selected items according to the given ids
                        var media = SB.util.getObjectByIndexPath(resp, CMS.config.roots.getMultipleMedia);
                        Ext.each(files, function (id) {
                            this.selCartGrid.addToSelection(new CMS.data.MediaRecord(media[id], id));
                        }, this);
                    },
                    failure: function () {
                        // clear existing selection
                        this.selCartGrid.getStore().removeAll();
                    },
                    callback: function () {
                        this.requestId = null;
                        this.doLayout();
                    }
                });
            } else {
                this.requestId = null;
                this.selCartGrid.getStore().removeAll();
                this.doLayout();

            }
        },

        /**
        * @private
        * Handler for panel's mark event
        */
        markUnmarkHandler: function (record, grid, sm) {
            var files = this.getSelectedFiles();
            var disable = this.multiSelect ? files.length < 1 : files.length !== 1;
            this.okButton.setDisabled(disable);
        },

        /**
        * Proxy method, passed to the enclosed panel
        */
        filterMediaType: function (type) {
            if (this.panel) {
                this.panel.filterMediaType.apply(this.panel, arguments);
            } else {
                this.mediaType = type;
            }

            if (type) {
                if (this.panel.possibleTypes[type]) {
                    this.setTitle(CMS.i18n('%type% auswählen').replace('%type%', this.panel.possibleTypes[type][0]));
                } else {
                    console.warn('Invalid media type', type);
                    return;
                }
            } else {
                this.setTitle(this.panel.possibleTypes[''][0]);
            }
        },

        /**
        * sets if the window is used as an media selector.
        * @param {Boolean} isSelector <tt>true</tt> to show the ok button, <tt>false</tt> to hide
        */
        setIsSelector: function (isSelector) {
            if (isSelector) {
                this.okButton.show();
                if (!this.panel.hasListener('rowdblclick')) {
                    this.panel.on('rowdblclick', this.rowdblclickHandler, this);
                }
            } else {
                this.okButton.hide();
                this.panel.un('rowdblclick', this.rowdblclickHandler, this);
            }
        },

        /**
         * event handler for the 'rowdblclick' event of the MediaDBPanel
         * @private
         */
        rowdblclickHandler: function (grid, row, event) {
            if (this.multiSelect) {
                var files = this.panel.mediaSelector.getSelectionModel().getSelections();
                this.selCartGrid.addToSelection(files[0]);
            } else {
                this.okButtonHandler();
            }
        }
    });

    return {
        /**
        * (Class method)
        * @param {String} siteId
        * The currently opened website's id
        * @param {Boolean} multiSelect
        * <tt>true</tt> to allow selection of multiple files.
        * Defaults to <tt>false</tt>
        */
        getInstance: function (siteId, multiSelect) {
            var instanceId = siteId + (multiSelect ? 'true' : 'false');
            if (!instances[instanceId]) {
                instances[instanceId] = new Class({
                    websiteId: siteId,
                    multiSelect: multiSelect
                });
            }
            if (!hasUnloadListener) {
                Ext.lib.Event.addListener(window, 'unload', function self() {
                    Ext.iterate(instances, function (id, instance) {
                        instance.destroy();
                    });
                    instances = null;
                    Ext.lib.Event.removeListener(window, 'unload', self);
                });
            }
            return instances[instanceId];
        }
    };
})();
