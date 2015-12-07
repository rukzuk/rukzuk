Ext.ns('CMS.home');

/**
* Abstract class which contains common used functions for the website suptabpanels
*
* @class       CMS.home.ManagementPanel
* @extends     Ext.Panel
* @author      Thomas Sojda
* @copyright   (c) 2011, by Seitenbau GmbH
*/
CMS.home.ManagementPanel = Ext.extend(Ext.Panel, {

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @property abandonChanges
    * @type Boolean
    * @private
    * If true, don't ask to save changes when leaving.
    */
    abandonChanges: false,

    layout: 'fit',

    initComponent: function () {

        CMS.home.ManagementPanel.superclass.initComponent.apply(this, arguments);

        this.on('activate', this.onAppear, this);
        this.on('deactivate', this.onDisappear, this);
    },


    /**
    * Open the specified site
    * @param {CMS.data.WebsiteRecord} record The site to be opened
    */
    setSite: function (record) {
        if (record && record.id) {
            this.enable();
            this.websiteId = record.id;
        } else {
            this.disable();
            this.websiteId = '';
        }
    },

    /**
    * Called when the tab is switched to. Meant to be overwritten by child components.
    */
    onAppear: Ext.emptyFn,

    /**
    * Called when the tab is switched away from. Meant to be overwritten by child components.
    */
    onDisappear: Ext.emptyFn,

    /**
    * Detemines whether the component holds unsaved changes. Meant to be overwritten by child components.
    * @return Boolean <code>true</code> if unsaved changes exist.
    */
    isDirty: Ext.emptyFn,

    /**
    * Called before the tab is switched away from. Checks for unsaved changes and displays a warning
    * message if there are any.
    */
    onBeforeDisappear: function (tabPanel, newTab, currentTab) {
        if (!this.abandonChanges && this.isDirty()) {
            // Defer is necessary because the click on the tab brings the main window to front,
            // hiding the message box behind it.
            Ext.MessageBox.show.defer(10, Ext.MessageBox, [{
                title: CMS.i18n('Warnung'),
                msg: CMS.i18n('Ungespeicherte Änderungen gehen beim Verlassen des Tabs verloren. Fortfahren?'),
                buttons: Ext.Msg.OKCANCEL,
                fn: function (btn) {
                    if (btn == 'ok') {
                        this.abandonChanges = true;
                        tabPanel.activate(newTab);
                    }
                },
                scope: this,
                icon: Ext.MessageBox.WARNING
            }]);
            return false;
        }
        this.abandonChanges = false;
        return true;
    }
});

Ext.reg('CMSmanagementpanel', CMS.home.ManagementPanel);
