Ext.ns('CMS.userManagement');

/**
* @class       CMS.userManagement.AddToGroupPanel
* @extends     Ext.Panel
*
* Provides the ability to add the currently selected/created user to
* one or more user groups of the current web site.
*
*/
CMS.userManagement.AddToGroupPanel = Ext.extend(Ext.Panel, {
    /**
    * @cfg websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @property  selectedGroup
    * @type Ext.data.Record
    * The currently selected user group
    */
    selectedGroup: null,

    initComponent: function () {
        this.cls = (this.cls || '') + ' CMSaddtogrouppanel';
        var config = {
            border: false,
            layout: 'vbox',
            layoutConfig: {
                align: 'stretch'
            },
            items: [{
                layout: 'hbox',
                layoutConfig: {
                    align: 'middle'
                },
                border: false,
                margins: '0 0 10 0',
                items: [{
                    ref: '../combo',
                    xtype: 'CMSaddgroupcombobox',
                    websiteId: this.websiteId,
                    listeners: {
                        recordSelected: this.recordSelectedHandler,
                        noRecordSelected: this.noRecordSelectedHandler,
                        scope: this
                    },
                    flex: 1,
                    margins: '0 10 0 0'
                }, {
                    xtype: 'button',
                    iconCls: 'add joingroup',
                    disabled: true,
                    ref: '../addGroupButton',
                    handler: this.addGroupHandler,
                    scope: this
                }]
            }, {
                flex: 1,
                xtype: 'CMSgroupgrid',
                ref: 'groupGrid',
                websiteId: this.websiteId,
                cls: 'CMSDeleteGrid'
            }]
        };
        Ext.apply(this, config);

        CMS.userManagement.AddToGroupPanel.superclass.initComponent.apply(this, arguments);
    },

    /**
    * Will pass the id of the current website to the underlying components
    * @param {String} id The id of the current website
    */
    setSite: function (id) {
        this.websiteId = id;
        this.groupGrid.setSite(id);
        this.combo.setSite(id);
    },

    /**
    * @private
    * The selectHandler enables the add button if a record has been selected
    * @param {Ext.form.ComboBox} combo The combobox
    * @param {Ext.data.Record} record The currently selected recordcord
    */
    recordSelectedHandler: function (combo, record) {
        this.selectedGroup = record;
        this.addGroupButton.enable();
    },

    /**
    * @private
    * The noRecordSelectedHandler disables the add button if no record has been selected
    * @param {Ext.form.ComboBox} combo The combobox
    */
    noRecordSelectedHandler: function (combo) {
        this.selectedGroup = null;
        this.addGroupButton.disable();
    },

    /**
    * @private
    * The addGroupHandler disables the add button if no record has been selected
    * @param {Ext.form.Button} button The button
    */
    addGroupHandler: function (button) {
        this.groupGrid.addGroup(this.selectedGroup);
    },

    /**
    * Passes the currently selected user record to the Ext.userManagement.GroupGrid component
    * @param {Ext.data.Record} record A user
    */
    setUser: function (record) {
        this.groupGrid.setUser(record);
    }
});

Ext.reg('CMSaddtogrouppanel', CMS.userManagement.AddToGroupPanel);
