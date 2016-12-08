Ext.ns('CMS.layout');

/**
 * Creates a container panel with an 26px sidebar on the right
 *
 * @class       CMS.layout.WorkbenchPanel
 * @extends     Ext.Panel
 */
CMS.layout.WorkbenchPanel = Ext.extend(Ext.Panel, {

    header: false,

    /**
    * @property sideBarButtons
    * @type Array
    * Array of Ext.Buttons which will be displayed below the sidebar
    *
    */
    sideBarButtons: [],

    /**
    * @property sideBarButtons
    * @type Array
    * Array of Ext.Components which will be displayed in the sidebar
    *
    */
    sideBarItems: [],

    /**
    * @property panel
    * @type Ext.Panel
    * A Panel which will be displayed beside the sidebar
    */
    panel: null,

    /**
     * @property sideBarOneColumn
     * @type int
     */
    sideBarOneColumn: 340,

    /**
     * @property sideBarTwoColumn
     * @type int
     */
    sideBarTwoColumn: 680,


    /**
     * Sidebar Mode
     * @property sideBarMode
     * @type string
     */
    sideBarMode: 'one',

    initComponent: function () {

        this.initSideBarMode();

        Ext.apply(this, {
            layout: 'border',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            defaults: {
                flex: 1
            },
            items: [Ext.apply(this.panel, {
                region: 'center'
            }), {
                region: 'west',
                ref: 'sidebar',
                width: this.getSideBarWithForCurrentMode(),
                height: '100%',
                layout: 'fit',
                split: true,
                cls: 'CMSsidebar',
                items: [{
                    layout: 'vbox',
                    layoutConfig: {
                        align: 'stretch',
                        pack: 'start'
                    },
                    items: this.sideBarItems,
                    bbar: this.sideBarButtons
                }]
            }]
        });

        CMS.layout.WorkbenchPanel.superclass.initComponent.call(this);
        CMS.app.heartbeat.addItem({
            websiteId: this.websiteId,
            type: this.mode,
            id: this.record.id
        });
    },

    /**
     * Inits the sidebar (size etc.)
     * @protected
     */
    initSideBarMode: function () {
        this.sideBarMode = (window.localStorage && window.localStorage.getItem('CMSsidebarmode')) || this.sideBarMode;
    },

    /**
     * Toggle Sidebar Mode (one / two columns)
     * @protected
     */
    toggleSideBarMode: function () {
        this.sidebar.setWidth(this.getSideBarWithForCurrentMode());
        this.doLayout();
    },

    /**
     * Get the width which the sidebar should have (based on the current mode)
     * @private
     * @returns {Number}
     */
    getSideBarWithForCurrentMode: function () {
        return (this.sideBarMode === 'one' ? this.sideBarOneColumn : this.sideBarTwoColumn);
    },

    destroy: function () {
        var cfg = {
            websiteId: this.websiteId,
            type: this.mode,
            id: this.record.id
        };
        CMS.app.heartbeat.removeItem(cfg);
        CMS.app.lockManager.releaseLock(cfg);
        CMS.layout.WorkbenchPanel.superclass.destroy.apply(this, arguments);
        delete this.sideBarButtons;
        delete this.sideBarItems;
        delete this.panel;
    }
});

Ext.reg('CMSworkbenchpanel', CMS.layout.WorkbenchPanel);
