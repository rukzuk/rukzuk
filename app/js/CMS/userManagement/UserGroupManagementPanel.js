Ext.ns('CMS.userManagement');

/**
* @class CMS.userManagement.UserGroupManagementPanel
* @extends CMS.home.ManagementPanel
* Panel for managing user groups.
* Like GroupedTabPanel, this panel consists of a "ribbonBar" containing tab groups,
* and a content area with a card layout.
*/
CMS.userManagement.UserGroupManagementPanel = Ext.extend(CMS.home.ManagementPanel, {
    layout: 'fit',
    cls: 'CMSUserGroupManagementPanel',
    buttonAlign: 'left',

    initComponent: function () {
        this.dummyConfig = {
            border: false,
            ref: 'dummy',
            html: CMS.i18n('Keine Gruppen vorhanden. Bitte zuerst eine Gruppe erstellen.'),
            style: 'padding: 20px 50px;'
        };
        this.items = {
            border: false,
            layout: 'hbox',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            items: [{
                border: false,
                cls: 'CMSgrouptablist',
                ref: '../ribbonBar',
                width: 200
            }, {
                border: false,
                ref: '../mainPanel',
                cls: 'CMSgrouptab',
                layout: 'card',
                deferredRender: false,
                flex: 1,
                activeItem: 0,
                items: [this.dummyConfig]
            }]
        };

        this.bbar = [{
            text: CMS.i18n('Neu'),
            iconCls: 'add addgroup',
            handler: this.newButtonHandler,
            scope: this
        }, {
            text: CMS.i18n('Umbenennen'),
            ref: '../renameButton',
            iconCls: 'rename renamegroup',
            disabled: true,
            handler: this.renameButtonHandler,
            scope: this
        }, {
            text: CMS.i18n('Duplizieren'),
            ref: '../cloneButton',
            iconCls: 'clone clonegroup',
            disabled: true,
            handler: this.cloneButtonHandler,
            scope: this
        }, {
            text: CMS.i18n('Löschen'),
            ref: '../deleteButton',
            iconCls: 'delete deletegroup',
            disabled: true,
            handler: this.deleteButtonHandler,
            scope: this
        }];

        CMS.userManagement.UserGroupManagementPanel.superclass.initComponent.apply(this, arguments);

        // fetch store
        this.store = CMS.data.StoreManager.get('group', this.websiteId, {disableLoad: true});

        // add load mask and reload
        this.on('afterrender', function () {
            this.loadMask = new Ext.LoadMask(this.getEl(), {msg: CMS.i18n('Bitte warten…'), store: this.store});
            this.store.reload({callback: this.storeLoadHandler, scope: this});
        }, this);

        // on destroy, also destroy loadMask
        this.on('destroy', function () {
            this.loadMask.destroy();
        }, this);

    },

    /**
    * @private
    * Handler for the attached userGroupStore's load event
    */
    storeLoadHandler: function () {
        this.ribbonBar.removeAll(true);
        this.mainPanel.add(this.dummyConfig);
        this.mainPanel.getLayout().setActiveItem(0);

        var firstRecord = this.store.getAt(0);
        var activeItem = this.activeGroupId || (firstRecord ? firstRecord.id : null);
        this.store.each(function (record) {
            var activate = record.id == activeItem;
            this.addGroup(record, activate);
        }, this);
    },

    /**
    * Add a group to the ribbon bar
    * @cfg {CMS.data.UserGroupRecord} record The userGroup that is being displayed
    * @cfg {Boolean} activate (optional) <tt>true</tt> to immediately activate the newly added group
    */
    addGroup: function (record, activate) {
        var self = this,
            title = record.get('name'),
            id = record.id;

        var buttonDefaults = {
            enableToggle: true,
            allowDepress: false,
            toggleGroup: 'GMPgroup',
            toggleHandler: this.btnClickHandler,
            scope: this
        };

        var newPanel = new Ext.Panel({
            groupId: id || Ext.id(),
            collapsible: true,
            animCollapse: true,
            title: title,
            defaultType: 'button',
            layout: 'vbox',
            height: 71,
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            listeners: {
                beforedestroy: function (panel) {
                    if (panel.attachedPanel) {
                        panel.attachedPanel.destroy();
                    }
                    panel.items.each(function (btn) {
                        if (btn.attachedPanel) {
                            btn.attachedPanel.destroy();
                        }
                    });
                    self = null;
                    return true;
                },
                beforecollapse: self.headerClickHandler,
                beforeexpand: self.headerClickHandler,
                scope: self
            },
            onRender: function () {
                Ext.Panel.prototype.onRender.apply(this, arguments);
                this.mon(this.header, 'click', self.headerClickHandler.createDelegate(self, [this]));
            },
            items: [Ext.applyIf({
                text: CMS.i18n('Pages'),
                panelCfg: {
                    border: false,
                    layout: 'fit',
                    items: [{
                        cls: 'CMSpageprivilegestab',
                        websiteId: this.websiteId,
                        record: record,
                        xtype: 'CMSpageprivilegestreepanel',
                        ref: 'privilegesPanel'
                    }]
                }
            }, buttonDefaults), Ext.applyIf({
                text: CMS.i18n('Weitere'),
                panelCfg: {
                    border: false,
                    layout: 'fit',
                    items: [{
                        cls: 'CMSotherprivilegestab',
                        xtype: 'CMSotherprivilegespanel',
                        websiteId: this.websiteId,
                        record: record
                    }]
                }
            }, buttonDefaults)],
            panelCfg: {
                border: false,
                layout: 'fit',
                items: [{
                    xtype: 'CMSadduserpanel',
                    websiteId: this.websiteId,
                    groupId: id,
                    record: record,
                    listeners: {
                        'CMSusergroupchanged': this.changeHandler,
                        scope: this
                    }
                }]
            }
        });

        this.ribbonBar.add(newPanel);
        // change panel titles after renaming
        newPanel.mon(this.store, 'update', function (store, record, operation) {
            if (newPanel.groupId == record.id) {
                var name = record.get('name');
                newPanel.setTitle(name);
                newPanel.items.each(function (btn) {
                    if (btn.attachedPanel && btn.attachedPanel.privilegesPanel) {
                        btn.attachedPanel.privilegesPanel.setTitle(name);
                    } else if (btn.panelCfg) {
                        btn.panelCfg.items[0].title = name;
                    }
                });
            }
        });

        // activate newly created panel
        var actPanel;
        if (this.mainPanel.dummy) {
            Ext.destroy(this.mainPanel.dummy);
            delete this.mainPanel.dummy;
            actPanel = this.ribbonBar.get(0);
        }
        if (activate) {
            actPanel = this.ribbonBar.items.last();
        }
        if (actPanel) {
            if (actPanel.el) {
                this.headerClickHandler(actPanel);
            } else {
                actPanel.on('afterrender', function () {
                    this.headerClickHandler(actPanel);
                }, this, { single: true });
            }
        }
        // enable/disable buttons
        this.cloneButton.enable();
        this.renameButton.enable();
        this.deleteButton.enable();

        if (this.isVisible()) {
            this.ribbonBar.doLayout();
        }
    },

    /**
    * Get a group by its id
    * @param {String|Ext.Panel} id The group's id or the group itself as an Ext.Panel
    */
    getGroup: function (id) {
        var group = null;
        if (typeof id == 'string') {
            this.ribbonBar.items.each(function (panel) {
                if (panel.groupId == id) {
                    group = panel;
                    return false;
                }
            });
            return group;
        }
        return id;
    },

    /**
    * Remove a group from the ribbon bar
    * @param {String|Ext.Panel} The group's id or the group itself as an Ext.Panel
    */
    removeGroup: function (group) {
        group = this.getGroup(group);
        if (!group) {
            return;
        }
        this.store.remove(this.store.getById(group.groupId));
        group.destroy();
        if (group == this.activeGroup) {
            var firstGroup = this.ribbonBar.get(0);
            if (firstGroup) {
                this.headerClickHandler(firstGroup);
            } else {
                this.activeGroupId = null;
                this.cloneButton.disable();
                this.renameButton.disable();
                this.deleteButton.disable();
                this.mainPanel.add(this.dummyConfig);
                this.mainPanel.getLayout().setActiveItem(0);
            }
        }
    },

    /**
    * @private
    * Handler for click of a button within a ribbonBar group
    */
    btnClickHandler: function (button, evt) {
        if (this.pressedButton) {
            if (button == this.pressedButton) {
                return;
            }
        }
        var panel = button.findParentByType('panel');
        this.activeGroupId = panel.groupId;
        this.setActiveCss(panel);
        this.setActiveCard(button);
        this.pressedButton = button;
    },

    /**
    * @private
    * Handler for click of a ribbonBar group's header
    */
    headerClickHandler: function (panel) {
        this.activeGroupId = panel.groupId;
        this.setActiveCss(panel);
        this.setActiveCard(panel);
        this.pressedHeader = panel.header;
        this.pressedHeader.addClass('selected');
    },

    /**
    * @private
    * Add/remove CSS to ribbonBar's groups
    * @param {Ext.Panel} panel The group to become active
    */
    setActiveCss: function (panel) {
        panel.ownerCt.items.each(function (cmp) {
            if (cmp == panel) {
                cmp.el.addClass('active');
            } else if (cmp.el) {
                cmp.el.removeClass('active');
            }
        });
        this.activeGroup = panel;
    },

    /**
    * @private
    * @param {Ext.Button|Ext.Panel} sender The button or group connected to the panel to be set active
    */
    setActiveCard: function (sender) {
        var first = false;

        if (this.pressedButton) {
            this.pressedButton.toggle(false, true);
        }
        this.pressedButton = null;
        if (this.pressedHeader) {
            this.pressedHeader.removeClass('selected');
        }
        this.pressedHeader = null;
        if (!sender.attachedPanel) {
            sender.attachedPanel = new Ext.Panel(sender.panelCfg);
            first = true;
        }
        var item = sender.attachedPanel;
        this.mainPanel.add(item);
        this.mainPanel.getLayout().setActiveItem(item);
    },

    show: function () { // HACK for layout issue
        CMS.userManagement.UserGroupManagementPanel.superclass.show.apply(this, arguments);
        this.ribbonBar.doLayout();
    },

    /**
    * @private
    * Handler for click on 'new' button
    */
    newButtonHandler: function () {
        var self = this;
        CMS.Message.prompt(CMS.i18n('Neue Gruppe'), CMS.i18n('Bezeichnung der neuen Gruppe:'), function (btnId, name, box) {
            if (btnId == 'ok') {
                var data = {
                    name: name,
                    websiteId: self.websiteId,
                    rights: CMS.config.defaultGroupRights,
                    users: []
                };
                CMS.app.trafficManager.sendRequest({
                    action: 'createGroup',
                    modal: true,
                    data: data,
                    success: function (response) {
                        var id = data.id = response.data.id;
                        var newGroup = new CMS.data.UserGroupRecord(data, id);
                        this.store.add(newGroup);
                        this.addGroup(newGroup, id, true);
                    },
                    callback: function () {
                        self = null;
                    },
                    scope: self,
                    successCondition: 'data.id',
                    failureTitle: CMS.i18n('Fehler beim Erstellen der Gruppe')
                });
            }
        }, this, false, CMS.i18n('Neue Gruppe'), CMS.config.validation.userGroupName);
    },

    /**
    * @private
    * Handler for click on 'rename' button
    */
    renameButtonHandler: function () {
        var self = this;
        var id = this.activeGroupId;
        var record = this.store.getById(id);
        CMS.Message.prompt(CMS.i18n('Gruppe umbenennen'), CMS.i18n('Neue Bezeichnung der Gruppe:'), function (btnId, name, box) {
            if (btnId == 'ok') {
                var data = {
                    name: name,
                    websiteId: self.websiteId,
                    id: id
                };
                CMS.app.trafficManager.sendRequest({
                    action: 'editGroup',
                    modal: true,
                    data: data,
                    success: function (response) {
                        record.set('name', name);
                    },
                    callback: function () {
                        self = null;
                    },
                    scope: self,
                    failureTitle: CMS.i18n('Fehler beim Umbenennen der Gruppe')
                });
            }
        }, this, false, record.get('name'), CMS.config.validation.userGroupName);
    },

    /**
    * @private
    * Handler for click on 'duplicate' button
    */
    cloneButtonHandler: function () {
        var self = this;
        var oldId = this.activeGroupId;
        CMS.Message.prompt(CMS.i18n('Neue Gruppe'), CMS.i18n('Bezeichnung der neuen Gruppe:'), function (btnId, name, box) {
            if (btnId == 'ok') {
                CMS.app.trafficManager.sendRequest({
                    action: 'cloneGroup',
                    modal: true,
                    data: {
                        id: oldId,
                        websiteId: this.websiteId,
                        name: name
                    },
                    success: function (response) {
                        var newId = response.data.id;
                        this.mon(this.store, 'load', function () {
                            var record = this.store.getById(newId);
                            this.addGroup(record, newId, true);
                        }, this, {single: true});
                        this.store.reload();
                    },
                    callback: function () {
                        self = null;
                    },
                    scope: self,
                    successCondition: 'data.id',
                    failureTitle: CMS.i18n('Fehler beim Duplizieren der Gruppe')
                });
            }
        }, this, false, CMS.i18n('Neue Gruppe'), CMS.config.validation.userGroupName);
    },

    /**
    * @private
    * Handler for click on 'delete' button
    */
    deleteButtonHandler: function () {
        var self = this;
        var id = this.activeGroupId;
        var group = this.store.getById(id);
        Ext.MessageBox.confirm(CMS.i18n('Gruppe löschen?'), CMS.i18n('Gruppe „{name}“ wirklich löschen?').replace('{name}', group.get('name')), function (btnId) {
            if (btnId == 'yes') {
                CMS.app.trafficManager.sendRequest({
                    action: 'deleteGroup',
                    data: {
                        websiteId: self.websiteId,
                        id: id
                    },
                    success: function () {
                        CMS.Message.toast(CMS.i18n('Gruppe wurde gelöscht.'));
                        this.removeGroup(id);
                    },
                    callback: function () {
                        self = null;
                    },
                    scope: self,
                    failureTitle: CMS.i18n('Fehler beim Löschen der Gruppe')
                });
            }
        });
    },

    /**
    * @private
    * Reloads user and group store since both are invalid after a user/group relation changed
    */
    changeHandler: function () {
        CMS.data.StoreManager.get('filteredusers', this.websiteId).reload();
        CMS.data.StoreManager.get('group', this.websiteId).reload();
        CMS.data.StoreManager.get('user', null).reload();
    },

    // Overwritten superclass method: Refreshes all rendered cards, if applicable.
    onAppear: function () {
        this.mainPanel.items.each(function (wrapper) {
            var item = wrapper.items && wrapper.items.get(0);
            if (item && Ext.isFunction(item.refresh)) {
                item.refresh();
            }
        });
    },

    // Overwritten superclass method: Existance of a single dirty card determines dirty state of whole tab
    isDirty: function () {
        var result = false;
        this.mainPanel.items.each(function (wrapper) {
            var item = wrapper.items && wrapper.items.get(0);
            if (item && Ext.isFunction(item.isDirty)) {
                result = result || item.isDirty();
            }
            return !result;
        });
        return result;
    }
});

Ext.reg('CMSgroupmanagementpanel', CMS.userManagement.UserGroupManagementPanel);
