Ext.ns('CMS.home');

/**
 * TreePanel for displaying a site's navigation
 *
 * @class CMS.home.PageTreePanel
 * @extends CMS.TreePanel
 * @requires CMS.home.PageTreeLoader
 * @requires CMS.home.PageTreeDragZone
 * @requires CMS.home.PageTreeDropZone
 */
CMS.home.PageTreePanel = Ext.extend(CMS.TreePanel, {
    /** @lends CMS.home.PageTreePanel */

    border: true,
    disabled: false,
    bubbleEvents: ['CMSopenpage', 'CMSopenworkbench', 'CMScloseworkbench'],
    cls: 'CMSpagetreepanel',

    /**
     * The id of the current website
     *
     * @property websiteId
     * @type String
     * @readonly
     */
    websiteId: undefined,

    /**
     * The Ext record representing the current website
     *
     * @property websiteRecord
     * @type Ext.data.Record
     * @readonly
     */
    websiteRecord: undefined,

    /** @private */
    initComponent: function () {
        this.bbar = this.createBBItems();
        this.loader = new CMS.home.PageTreeLoader({
            websiteId: this.websiteId
        });

        this.dragConfig = {
            Class: CMS.home.PageTreeDragZone
        };

        this.dropConfig = {
            Class: CMS.home.PageTreeDropZone
        };
        this.plugins = (this.plugins || []).concat(this.createContextMenu());

        CMS.home.PageTreePanel.superclass.initComponent.apply(this, arguments);

        this.root.renderChildren = function (children) {
            this.ui.ctNode.innerHTML = this.childNodes.length ? '' : [
                '<span class="x-treepanel-empty">',
                (this.getOwnerTree().emptyText || ''),
                '</span>'
            ].join('');
            return Ext.tree.TreeNode.prototype.renderChildren.apply(this, arguments);
        };

        this.mon(this.getSelectionModel(), 'selectionchange', this.onSelectionChange, this);

        // add listener to update toolbar buttons if template store is changed
        var templateStore = CMS.data.StoreManager.get('template', this.websiteId, {disableLoad: true});
        this.mon(templateStore, 'load', this.onTemplateStoreChanged, this);
        this.mon(templateStore, 'add', this.onTemplateStoreChanged, this);
        this.mon(templateStore, 'remove', this.onTemplateStoreChanged, this);

        // reload page tree on template rename
        this.on('CMStemplaterenamed', function () {
            this.reload(false, false, null, this, true);
        }, this);

        // collapse page tree after rendering for better overview
        this.on('afterrender', function () {
            this.collapseAll();
        });

        // prevent collapsing
        this.eventModel.onNodeDblClick = function (e, node) {
            if (node.ui.fireEvent('beforedblclick', node, e) !== false) {
                node.ui.fireEvent('dblclick', node, e);
            }
        };

        this.on('click', this.clickHandler, this);
        this.on('dblclick', this.dblClickHandler, this);
        this.on('treeupdated', this.onTreeupdated, this);

        this.websiteRecord = CMS.data.WebsiteStore.getInstance().getById(this.websiteId);
        this.buildPageTree();

        // refresh button state
        this.syncButtonState();

        // update tree nodes on change of navigation object inside of website
        this.mon(CMS.data.WebsiteStore.getInstance(), 'update', function (store, record) {
            if (record.id === this.websiteId && record.isModified('navigation')) {
                if (this.loader) {
                    this.loader.replaceChildNodes(this.getRootNode(), record.data.navigation || []);
                }
            }
        }, this);

    },

    /**
     * Creates the item configurations for the panel's button bar
     * @private
     */
    createBBItems: function () {
        return [{
            tooltip: {
                text: CMS.i18n('Neue Page erstellen'),
                align: 't-b?'
            },
            iconCls: 'add addpage',
            ref: '../newButton',
            disabled: true,
            scope: this,
            handler: this.onNewBtnClick
        }, {
            tooltip: {
                text: CMS.i18n('Kopie der Page erzeugen'),
                align: 't-b?'
            },
            iconCls: 'clone clonepage',
            ref: '../cloneButton',
            handler: this.onCloneBtnClick,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n('Page löschen'),
                align: 't-b?'
            },
            iconCls: 'delete deletepage',
            ref: '../deleteButton',
            handler: this.onDeleteBtnClick,
            disabled: true,
            scope: this
        }, {
            tooltip: {
                text: CMS.i18n('Eigenschaften der Seite anzeigen und bearbeiten'),
                align: 't-b?'
            },
            iconCls: 'properties',
            ref: '../propertiesButton',
            handler: this.onPropertiesBtnClick,
            disabled: true,
            scope: this
        }, '->', {
            tooltip: {
                text: CMS.i18n(null, 'pageTreePanel.editPage'),
                align: 't-b?'
            },
            cls: 'primary',
            iconCls: 'edit editpage',
            ref: '../editButton',
            disabled: true,
            handler: this.handleEditBtn,
            scope: this
        }];
    },

    /**
     * Creates the context menu plugin for the tree panel
     * @private
     */
    createContextMenu: function () {
        return new CMS.TreeContextMenu({
            items: [{
                iconCls: 'edit',
                text: CMS.i18n('Bearbeiten'),
                condition: this.checkCanEditPage,
                handler: this.handleEditBtn,
                scope: this
            }, {
                iconCls: 'add',
                text: CMS.i18n('Neue Seite erstellen'),
                condition: this.checkCanAddPage,
                handler: this.onNewBtnClick,
                scope: this
            }, {
                iconCls: 'add addsubpage',
                text: CMS.i18n(null, 'pageTreePanel.newButtonAsChild'),
                condition: this.checkCanAddChildPage,
                handler: this.onNewBtnClickAsChild,
                scope: this
            }, {
                iconCls: 'clone',
                text: CMS.i18n('Duplizieren'),
                condition: this.checkCanClonePage,
                handler: this.onCloneBtnClick,
                scope: this
            }, {
                iconCls: 'settings',
                text: CMS.i18n('Eigenschaften'),
                condition: this.checkCanChangePageSettings,
                handler: this.onPropertiesBtnClick,
                scope: this
            }, {
                iconCls: 'delete',
                text: CMS.i18n('Löschen'),
                condition: this.checkCanDeletePage,
                handler: this.onDeleteBtnClick,
                scope: this
            }]
        });
    },

    /**
     * Select the topmost page in the tree
     */
    selectFirstPage: function () {
        if (!this.websiteId) {
            return;
        }
        this.getSelectionModel().clearSelections();
        console.log('[PageTree] selecting first node');
        this.getSelectionModel().selectNext(this.getRootNode());
    },

    /**
     * Re-select the currently selected page.
     * This is useful for manually firing the selectionchange event.
     * If no node is currently selected, the first node will be selected.
     */
    reselectPage: function () {
        if (!this.websiteId) {
            return;
        }
        var sm = this.getSelectionModel();
        var selectedNode = sm.getSelectedNode();
        if (selectedNode) {
            sm.clearSelections();
            sm.select(selectedNode);
        } else {
            this.selectFirstPage();
        }
    },

    /**
     * (Re-)Creates the page tree nodes from the current website record
     * @private
     */
    buildPageTree: function (record, forceReload, idToSelect) {
        // fix reloads where the loader is not there (yet?)
        if (!this.loader.replaceChildNodes) {
            return;
        }
        this.suspendEvents();
        this.loader.replaceChildNodes(this.getRootNode(), this.websiteRecord.get('navigation') || [], this.websiteId);
        this.getRootNode().attributes.allowChildren = CMS.app.userInfo.canCreateRootPages(record);
        this.resumeEvents();
    },


    /**
     * Reload the currently opened site' navigation
     *
     * @param {Boolean} showNotification <tt>true</tt> to show a confirmation
     *      notification. Defaults to <tt>false</tt>.
     * @param {Boolean} fireSelectEvent <tt>true</tt> to make the selectionmodel
     *      fire the selectionchange event after restoring the selected node.
     *      Defaults to <tt>false</tt>
     * @param {Function} [callback] (optional) A function to be executed after
     *      reloading. Will be called with the response as argument.
     * @param {Object} [scope] - callbacks scope
     * @param {Boolean} [preventSelectionAfterReload] - do not re-select the current element after reload, defaults to false
     */
    reload: function (showNotification, fireSelectEvent, callback, scope, preventSelectionAfterReload) {
        var markedNode = this.getSelectionModel().getSelectedNode();
        if (!!markedNode) {
            markedNode = markedNode.id;
        }
        CMS.app.trafficManager.sendRequest({
            action: 'getWebsite',
            data: {
                id: this.websiteId
            },
            successCondition: 'data',
            success: function (response) {
                this.loader.replaceChildNodes(this.getRootNode(), response.data.navigation || []);
                if (!preventSelectionAfterReload) {
                    markedNode = this.getNodeById(markedNode);
                    if (!!markedNode) {
                        if (fireSelectEvent) {
                            this.lastPageId = null;
                        }
                        this.getSelectionModel().select(markedNode);
                    } else {
                        this.selectFirstPage();
                    }
                }
                if (callback) {
                    callback.call(scope || this, response);
                }
                if (showNotification) {
                    CMS.Message.toast(CMS.i18n('Website erfolgreich neu geladen'));
                }
            },
            scope: this,
            failureTitle: CMS.i18n('Fehler beim Laden der Website')
        });
    },

    /**
     * Notifies anyone who cares that the tree has been updated and a refresh
     * is required (e.g. after creating, deleting or moving a page node)
     * @protected
     */
    broadcastTreeUpdated: function (response, selectedNodeId) {
        if (response && response.data) {
            this.fireEvent('treeupdated', response, selectedNodeId);
        } else {
            this.reload(false, false, function (response) {
                this.fireEvent('treeupdated', response, selectedNodeId);
            }, this);
        }
    },

    /**
     * Refreshes the tree and restores the selection after changing the
     * tree structure
     * @private
     */
    onTreeupdated: function (response, selectedNodeId) {
        var selectedNode;

        this.websiteRecord.set('navigation', response.data.navigation);

        if (!selectedNodeId) {
            selectedNode = this.getSelectionModel().getSelectedNode();
            // get the id of the tree node because the tree is about to be rebuild
            selectedNodeId = selectedNode && selectedNode.id;
        }

        this.buildPageTree();

        // restore selection
        if (selectedNodeId) {
            selectedNode = this.getNodeById(selectedNodeId);
        }
        if (selectedNode) {
            selectedNode.select();
        } else {
            this.selectFirstPage();
        }
    },

    /** @protected */
    destroy: function () {
        this.clearSingleClickTimeOut();

        CMS.home.PageTreePanel.superclass.destroy.apply(this, arguments);

        this.websiteRecord = null;
    },

    //
    //
    // Check conditions for context menu items
    //
    //

    /**
     * Checks if the user has sufficient rights to edit a page
     * @private
     */
    checkCanEditPage: function (page) {
        if (Ext.isString(page)) {
            page = this.getNodeById(page);
        }
        return Ext.isObject(page) && CMS.app.userInfo.canEditPage(page);
    },

    /**
     * Checks if the user has sufficient rights to create new pages (on the same level)
     * @private
     */
    checkCanAddPage: function (page) {
        if (Ext.isString(page)) {
            page = this.getNodeById(page);
        }
        if (!Ext.isObject(page) || !Ext.isObject(page.parentNode) || page.parentNode.id === 'root') {
            return CMS.app.userInfo.canCreateRootPages(this.websiteRecord);
        } else {
            return CMS.app.userInfo.canCreateChildPages(page.parentNode);
        }
    },

    /**
     * Checks if the user has sufficient rights to create new sub pages
     * @private
     */
    checkCanAddChildPage: function (page) {
        if (Ext.isString(page)) {
            page = this.getNodeById(page);
        }
        if (page) {
            return CMS.app.userInfo.canCreateChildPages(page);
        }
        return false;
    },

    /**
     * Checks if the user has sufficient rights to clone a page
     * @private
     */
    checkCanClonePage: function (page) {
        if (Ext.isString(page)) {
            page = this.getNodeById(page);
        }
        if (Ext.isObject(page)) {
            if (page.parentNode.id === 'root') {
                return CMS.app.userInfo.canCreateRootPages(this.websiteRecord);
            } else {
                return CMS.app.userInfo.canCreateChildPages(page.parentNode);
            }
        }
        return false;
    },

    /**
     * Checks if the user has sufficient rights to edit the page properties
     * @private
     */
    checkCanChangePageSettings: function (page) {
        if (Ext.isString(page)) {
            page = this.getNodeById(page);
        }
        return Ext.isObject(page) && CMS.app.userInfo.canEditPage(page);
    },

    /**
     * Checks if the user has sufficient rights to delete a page
     * @private
     */
    checkCanDeletePage: function (page) {
        if (Ext.isString(page)) {
            page = this.getNodeById(page);
        }
        return Ext.isObject(page) && CMS.app.userInfo.canDeletePage(page);
    },

    //
    //
    // button handler
    //
    //

    /**
     * Handler for propertyform's edit button
     * @private
     */
    handleEditBtn: function () {
        var node = this.getSelectionModel().getSelectedNode();
        if (node) {
            this.editPage(node);
        }
    },

    /**
     * Handle delete button click
     * @private
     */
    onDeleteBtnClick: function () {
        var node = this.getSelectionModel().getSelectedNode();
        var nodeName = node.text || CMS.i18n('unbenannt');
        var title = CMS.i18n('Löschen?');
        var msg = CMS.i18n('Page „{name}“ und alle darunterliegenden Pages wirklich löschen?').replace('{name}', nodeName);
        var cb = function (btnId) {
            if (btnId === 'yes') {
                CMS.app.trafficManager.sendRequest({
                    action: 'deletePage',
                    data: {
                        websiteId: this.websiteId,
                        id: node.id
                    },
                    scope: this,
                    successCondition: 'data.navigation',
                    success: function (response) {
                        this.fireEvent('CMScloseworkbench', node.id, true);
                        this.broadcastTreeUpdated(response);
                    },
                    failureTitle: CMS.i18n('Fehler beim Löschen der Page')
                }, this);
            }
        };
        var scope = this;

        Ext.MessageBox.confirm(title, msg, cb, scope);
    },

    /**
     * Handle clone button click
     * @private
     */
    onCloneBtnClick: function () {
        var node = this.getSelectionModel().getSelectedNode();

        CMS.Message.prompt(
            CMS.i18n('Bezeichnung eingeben'), // title
            CMS.i18n('Bezeichnung der neuen Page'), // message
            function (btnId, title) { // callback
                if (btnId === 'ok') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'clonePage',
                        data: {
                            id: node.id,
                            websiteId: this.websiteId,
                            name: title
                        },
                        scope: this,
                        successCondition: 'data.id',
                        success: function (response) {
                            this.broadcastTreeUpdated(response, response.data.id);
                        },
                        failureTitle: CMS.i18n('Fehler beim Duplizieren der Seite')
                    }, this);
                }
            },
            this, // scope
            false, // multiline
            CMS.i18n('{name} – Kopie').replace('{name}', node.text) // suggested value
        );
    },

    /**
     * Handler for the "Eigenschaften" button; Opens a {@link CMS.home.EditPagePropertiesWindow}
     * @private
     */
    onPropertiesBtnClick: function () {
        var node = this.getSelectionModel().getSelectedNode();
        if (node) {
            var win = new CMS.home.EditPagePropertiesWindow({
                websiteId: this.websiteId,
                pageId: node.id,
                readonly: !CMS.app.userInfo.canEditPage(node),
                listeners: {
                    metadataupdated: this.onMetaDataUpdated,
                    cancel: this.closePageProperties,
                    scope: this
                }
            });
            win.show();
            this.closePageProperties();
            this.pagePropertyWin = win;
        }
    },

    /**
     * Handler function for clicking the "new" button
     * @private
     */
    onNewBtnClick: function () {
        this.createPage('below');
    },

    /**
     * Handler for new button as child
     * @private
     */
    onNewBtnClickAsChild: function () {
        this.createPage('child');
    },

    /**
     *
     * @param {string} position - 'below' | 'child'
     */
    createPage: function (position) {
        var node = this.getSelectionModel().getSelectedNode();
        var newPageWindow = new CMS.home.CreatePageWindow({
            websiteId: this.websiteId,
            position: position,
            node: node,
            listeners: {
                scope: this,
                pagecreated: function (response) {
                    this.broadcastTreeUpdated(response, response.data.id);
                }
            }
        });

        newPageWindow.show();
    },

    //
    //
    // other helper
    //
    //

    /**
     * Make sure a double click is not interpreted as two single clicks
     * @private
     */
    clickHandler: function (node) {
        this.clearSingleClickTimeOut();
        this.singleClickTimeout = (function () {
            this.fireEvent('singleclick', node);
        }).defer(200, this);
    },

    /** @private */
    dblClickHandler: function (node) {
        this.clearSingleClickTimeOut();

        if (CMS.app.userInfo.canEditPage(node)) {
            this.editPage.apply(this, arguments);
        } else {
            CMS.Message.info(CMS.i18n('Fehlende Berechtigung'), CMS.i18n('Unzureichende Berechtigungen um diese Page zu bearbeiten.'));
        }
    },

    /** @private */
    clearSingleClickTimeOut: function () {
        if (this.singleClickTimeout) {
            window.clearTimeout(this.singleClickTimeout);
            this.singleClickTimeout = null;
        }
    },

    /**
     * @private
     */
    editPage: function (node) {
        if (Ext.isString(node)) {
            node = this.getNodeById(node);
        }
        if (!Ext.isObject(node)) {
            return;
        }

        var options = {
            action: 'getPage',
            data: {
                id: node.id,
                websiteId: this.websiteId
            },
            success: function (response) {
                var record = new CMS.data.PageRecord(response.data, response.data.id);
                var title = CMS.i18n('Page „{pagename}“ bearbeiten').replace('{pagename}', node.attributes.name);
                var panelConfig = {
                    xtype: 'CMSpageworkbenchpanel',
                    cls: 'CMSpageeditorpanel',
                    plugins: ['CmsApi'],
                    border: false,
                    title: title,
                    websiteId: this.websiteId,
                    requiredStores: ['module'],
                    record: record,
                    templateId: record.get('templateId')
                };

                this.fireEvent('CMSopenworkbench', panelConfig);
            },
            scope: this,
            failureTitle: CMS.i18n('Fehler beim Laden der Page')
        };

        CMS.app.lockManager.requestLock({
            id: node.id,
            websiteId: this.websiteId,
            type: 'page',
            success: function () {
                CMS.app.trafficManager.sendRequest(options);
            }
        });
    },

    /**
     * Set button state according to the currently selected treenode
     * @private
     *
     * @param {Ext.tree.TreeNode} node The currently selected node
     */
    syncButtonState: function (node) {
        this.newButton.setDisabled(true);
        CMS.data.StoreManager.get('template', this.websiteId, {
            callback: function (store, records) {
                var templatesAvailable = records.length !== 0;

                // update empty text and refresh tree
                if (templatesAvailable) {
                    this.emptyText = CMS.i18n(null, 'pageTreePanel.emptyText');
                } else {
                    this.emptyText = '';
                }
                if (this.rendered) {
                    this.root.renderChildren();
                }

                this.newButton.setDisabled(!this.checkCanAddPage(node) || !templatesAvailable);

            },
            scope: this
        });

        this.editButton.setDisabled(!this.checkCanEditPage(node));
        this.cloneButton.setDisabled(!this.checkCanClonePage(node));
        this.deleteButton.setDisabled(!this.checkCanDeletePage(node));
        this.propertiesButton.setDisabled(!this.checkCanChangePageSettings(node));
    },

    /**
     * Event handler for changing the content of the template store
     * Triggers update of toolbar buttons (creating new pages is not possible if there
     * are no templates)
     * @private
     */
    onTemplateStoreChanged: function () {
        this.syncButtonState(this.getSelectionModel().getSelectedNode());
    },

    /**
     * Event handler for the "selectionchange" event of the tree's selection model
     * Triggers update of toolbar buttons
     * @private
     */
    onSelectionChange: function (selmodel, node) {
        this.syncButtonState(node);

        if (!node || !node.id || node.id == this.lastPageId) {
            this.lastPageId = null;
        } else {
            this.lastPageId = node.id;
        }
        node.ensureVisible();
    },

    /**
     * Sets the new name to the record and updates the treenode ui
     * @private
     */
    onMetaDataUpdated: function (pageData) {
        //Update the treenode text
        this.broadcastTreeUpdated(null, pageData.id);
        this.closePageProperties();
    },

    /**
     * Closes any exiting page property windows
     * @private
     */
    closePageProperties: function () {
        if (this.pagePropertyWin) {
            this.pagePropertyWin.destroy();
            this.pagePropertyWin = null;
        }
    }
});

Ext.reg('CMSpagetreepanel', CMS.home.PageTreePanel);
