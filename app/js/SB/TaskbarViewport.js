Ext.ns('SB');

/**
* @class SB.TaskbarViewport
* @extends Ext.Viewport
* A viewport containing a windows-like taskbar for switching panels
*/
SB.TaskbarViewport = Ext.extend(Ext.Viewport, {

    /**
     * Internal history of focused items.
     *
     * @property activeItemHistory
     * @type Array
     * @private
     */
    activeItemHistory: undefined,

    /**
     * @cfg {String/Number} activeItem
     * The id or index of the active item
     */
    activeItem: undefined,

    /**
    * @cfg {Array|Object} centerComponents A component or a list of components
    * that shall initially be shown in the center region
    * Each component may provide a <tt>conditionalDestroy</tt> method that is called when the user tries to close it.
    * If no such method is provided, the panel is destroyed unconditionally.
    */
    centerComponents: undefined,

    /**
     * @cfg {String} viewportCls
     * A style class wich will be applied to the viewport;
     * (Defaults to '')
     */
    viewportCls: '',

    /**
    * @cfg {Integer} (optional) The position where new buttons shall be inserted.
    * Leave blank to append to the end.
    */
    insertionIndex: false,

    /**
     * @cfg {Array/Object} taskbarItems
     * An inital set of items for the taskbar
     */
    taskbarItems: undefined,

    /**
     * @cfg {String} taskbarRegion
     * The viewport region for the taskbar ('north', 'east', 'south' or 'west')
     * Defaults to 'south'
     */
    taskbarRegion: 'south',

    /**
     * @private
     */
    initComponent: function () {
        this.activeItemHistory = [];
        this.centerComponents = this.centerComponents || [];
        this.taskbarItems = this.taskbarItems || [];


        Ext.apply(this, {
            renderTo: Ext.getBody(),
            layout: 'border',
            items: [{
                xtype: 'container',
                region: 'center',
                layout: 'card',
                ref: 'viewport',
                border: false,
                activeItem: 0,
                deferredRender: true,
                cls: this.viewportCls,
                items: this.centerComponents
            }, {
                region: this.taskbarRegion,
                xtype: 'toolbar',
                ref: 'taskbar',
                cls: 'SBtaskbar',
                height: 43, // 40px button height plus 3px border
                collapsible: false,
                forceLayout: true,
                enableOverflow: false,
                defaults: {
                    cls: 'SBtaskbarbutton SBmaintab',
                    scope: this,
                    enableToggle: true,
                    toggleHandler: this.taskbarHandler,
                    allowDepress: false,
                    toggleGroup: 'toolbar-button',
                    xtype: 'button'
                },
                items: this.taskbarItems
            }]
        });

        SB.TaskbarViewport.superclass.initComponent.call(this);

        if (this.activeItem) {
            this.setActiveItem(this.activeItem);
        }
    },

    /**
     * Creates a new panel from the given panel configuration in the main
     * application viewport if the panel with the given id does not already exsits.
     * Otherwise the existing Panel will be brought to the front
     *
     * This function also ensures, that the taskbar buttons are in the correct state.
     *
     * @param {Object} panelConfig
     * Configuration object for the panel that will be created.
     * @param {Ext.Button} delegateButton
     * (Optional) An existing button that will be connected to the new panel.
     * If not present, a new button is created
     * @param {Object} btnDefaults
     * (optional) Attributes that will be applied to the newly created button if <tt>delegateButton</tt> is not set
     */
    openPanel: function (panelConfig, delegateButton, btnDefaults) {
        var panel;
        if (panelConfig.id) {
            var button = Ext.getCmp('tbb-' + panelConfig.id);
            if (button) { // tab existing
                if (!button.pressed) { // tab not active
                    button.toggle(true);
                }
                this.setActiveItem(button);
                return;
            }
        }

        panel = this.viewport.add(panelConfig);

        if (delegateButton) {
            delegateButton.connectedPanel = panel.getId();
            if (!delegateButton.toggleGroup) {
                delegateButton.toggleGroup = 'toolbar-button';
                Ext.ButtonToggleMgr.register(delegateButton);
            }
            Ext.ComponentMgr.unregister(delegateButton);
            delegateButton.id = 'tbb-' + panelConfig.id;
            Ext.ComponentMgr.register(delegateButton);
        } else {
            var toolbarBtnConfig = {
                xtype: 'splitbutton',
                cls: 'SBtaskbarbutton SBsubtab',
                toggleGroup: 'toolbar-button',
                allowDepress: false,
                enableToggle: true,
                text: panelConfig.title,
                iconCls: panelConfig.iconCls,
                toggleHandler: this.taskbarHandler,
                scope: this,
                connectedPanel: panel.getId(),
                itemId: panelConfig.id || null,
                arrowHandler: function () {
                    this.removePanel(panel);
                }
            };

            if (panelConfig.id) {
                toolbarBtnConfig.id = 'tbb-' + panelConfig.id;
            }

            if (btnDefaults) {
                if (btnDefaults.cls) {
                    toolbarBtnConfig.cls += ' ' + btnDefaults.cls;
                    delete btnDefaults.cls;
                }
                Ext.apply(toolbarBtnConfig, btnDefaults);
            }
            if (this.insertionIndex) {
                this.taskbar.insertButton(this.insertionIndex, toolbarBtnConfig);
            } else {
                this.taskbar.addButton(toolbarBtnConfig);
            }
            this.taskbar.doLayout();
        }

        panel.on('destroy', function (p) {
            this.removePanel(p, true);
        }, this);
        this.setActiveItem(panel);
        return panel;
    },


    /**
     * Sets the active (visible) item in the layout.
     *
     * @param {String/Number} item
     * The string component id or numeric index of the item to activate or the
     * connected taskbar button of the item.
     */
    setActiveItem: function (item) {
        var panel;
        var button;

        item = (typeof item === 'number') ? this.taskbar.items.get(item) : item;
        item = (typeof item === 'string') ? Ext.getCmp(item) : item;

        if (typeof item.connectedPanel !== 'undefined') {
            button = item;
            panel = Ext.getCmp(button.connectedPanel);
        } else {
            panel = item;
            this.taskbar.items.each(function (i) {
                if (i.connectedPanel === panel.id) {
                    button = i;
                    return false;
                }
            });
        }

        this.setActiveItemHistory(panel.getId());

        if (!!this.rendered) {
            this.viewport.getLayout().setActiveItem(panel.getId());
            button.toggle(true, true); // suppress toggleHandler
            button.fireEvent('toggle', button, true); // for ButtonToggleMgr
        } else {
            this.viewport.activeItem = panel.ownerCt.items.indexOf(panel) || 0;
            this.taskbar.items.each(function (item) {
                if (button && typeof item.pressed !== 'undefined') {
                    item.pressed = (button === item);
                }
            });
        }
    },

    /**
     * @private
     * Manages the internal active item history.
     *
     * @param {String} panelId
     * The ID of the panel which was set as active.
     */
    setActiveItemHistory: function (panelId) {
        var totalItems = this.viewport.items.getCount();

        if (this.activeItemHistory[0] !== panelId) {
            this.activeItemHistory.remove(panelId);
            this.activeItemHistory.unshift(panelId);

            if (this.activeItemHistory.length > totalItems) {
                this.activeItemHistory.pop();
            }
        }
    },

    /**
     * Removes an panel from the history of active items and returns the id of the last
     * active item in history
     *
     * @param {Object} panel
     *      The panel to remove
     *
     * @return String
     *      The id of the last active panel
     */
    removeItemFromHistory: function (panel) {
        this.activeItemHistory.remove(panel.id);
        return this.activeItemHistory[0];
    },

    /**
     * @private
     * Sets the panel which is connected to the clicked item as active.
     *
     * @param {Ext.Component} button
     * The button component that was clicked.
     */
    taskbarHandler: function (button, state) {
        if (!!button.connectedPanel && !!state) {
            this.viewport.getLayout().setActiveItem(button.connectedPanel);
        }
    },


    /**
     * Removes a panel and its connected taskbar button from the application
     * viewport.
     *
     * @param {Ext.Panel/String} panel
     * The panel reference or id to remove.
     * @param {Boolean} force <tt>true</tt> to remove the panel without confirmation
     * @return {String} The corresponding panel's title if the panel was successfully closed, <tt>null</tt> if the panel does not exist.
     * <tt>undefined</tt> if the panel is still open and the user is asked for a confirmation
     */
    removePanel: function (panel, force) {
        var lastActiveItem;
        var result;

        if (typeof panel === 'string') {
            panel = Ext.getCmp(panel);
        }

        if (!panel) {
            //No panel found
            return null;
        }

        if (!panel.isDestroyed && typeof panel.conditionalDestroy == 'function' && !force) {
            panel.conditionalDestroy({
                scope: this
            });
        } else {
            lastActiveItem = this.removeItemFromHistory(panel);
            result = null;
            this.viewport.getLayout().setActiveItem(lastActiveItem);

            // remove components
            this.taskbar.items.each(function (item) {
                if (typeof item.connectedPanel !== 'undefined') {
                    if (panel.id === item.connectedPanel) {
                        result = item.text;
                        this.remove(item);
                        this.doLayout();
                    } else if (item.connectedPanel === lastActiveItem) {
                        item.toggle(true, true); // suppress toggleHandler
                        item.fireEvent('toggle', item, true); // for ButtonToggleMgr
                    }
                }
            }, this.taskbar);

            this.viewport.remove(panel);
        }
        return result;
    },

    destroy: function () {
        this.viewport.destroy();
        this.taskbar.destroy();
        SB.TaskbarViewport.superclass.destroy.apply(this, arguments);
        delete this.activeItemHistory;
        delete this.centerComponents;
        delete this.taskbarItems;
    }
});
