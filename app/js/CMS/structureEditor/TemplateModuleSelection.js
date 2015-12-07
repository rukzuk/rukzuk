Ext.ns('CMS.structureEditor');

/**
 * @class CMS.structureEditor.TemplateModuleSelection
 * @extends Ext.TabPanel
 *
 * A panel to insert modules and/or module set into a template via drag&drop
 */
CMS.structureEditor.TemplateModuleSelection = Ext.extend(Ext.Panel, {

    /**
     * @cfg {String} websiteId
     * Id of the current selected website
     */
    websiteId: undefined,

    /**
     * @cfg {String} mode, either 'modules', 'extensions', 'snippets'
     * @string
     */
    mode: '',

    header: false,

    initComponent: function () {
        var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);

        this.mode = this.mode || 'modules';

        this.layout = 'fit';
        this.layoutConfig ={
            align: 'stretch'
        };

        var moduleItems = [{
            xtype: 'CMSmodulegrid',
            title: CMS.i18n('Standardmodule'),
            header: false,
            hideHeaders: true,

            startGrouped: true,

            layout: 'fit',
            layoutConfig: {
                align: 'stretch'
            },
            viewConfig: {
                emptyText: CMS.i18n(null, 'templateModuleSelection.modulesEmptyText'),
                deferEmptyText: false
            },
            store: moduleStore,
            filterFn: function (record) {
                return (record.data.moduleType !== CMS.config.moduleTypes.extension) &&
                    (record.data.moduleType !== CMS.config.moduleTypes.root);
            },
            descriptionToolTip: true,
            bubbleEvents: ['startdrag', 'enddrag', 'CMSinsertbyclick'],
            cellclickHandler: function (grid, rowIndex) {
                var unit = this.store.getAt(rowIndex);
                this.fireEvent('CMSinsertbyclick', {unit: unit});
            }
        }];

        var extensionItems = [{
            xtype: 'CMSmodulegrid',
            title: CMS.i18n('Erweiterungen'),
            layout: 'fit',
            header: false,
            hideHeaders: true,
            viewConfig: {
                emptyText: CMS.i18n(null, 'templateModuleSelection.extensionModuleEmptyText'),
                deferEmptyText: false
            },
            store: moduleStore,
            filterFn: function (record) {
                return record.data.moduleType === CMS.config.moduleTypes.extension;
            },
            descriptionToolTip: true,
            bubbleEvents: ['startdrag', 'enddrag', 'CMSinsertbyclick'],
            cellclickHandler: function (grid, rowIndex) {
                var unit = this.store.getAt(rowIndex);
                this.fireEvent('CMSinsertbyclick', {unit: unit});
            }
        }];

        var snippetItems = [{
            xtype: 'CMStemplatesnippetgrid',
            title: CMS.i18n('Snippets'),
            layout: 'fit',
            header: false,
            hideHeaders: true,
            viewConfig: {
                emptyText: CMS.i18n(null, 'templateModuleSelection.snippetsEmptyText'),
                deferEmptyText: false
            },
            store: CMS.data.StoreManager.get('templateSnippet', this.websiteId),
            filterFn: function (record) {
                return record.data.baseLayout === false;
            },
            websiteId: this.websiteId,
            descriptionToolTip: true,
            columns: [{
                id: 'icon',
                width: 20
            }],
            bubbleEvents: ['startdrag', 'enddrag', 'CMSinsertbyclick'],
            cellclickHandler: function (grid, rowIndex) {
                var tplsnippet = this.store.getAt(rowIndex);
                this.fireEvent('CMSinsertbyclick', {tplsnippet: tplsnippet});
            }
        }];

        switch(this.mode) {
            case 'modules':
                this.items = moduleItems;
                break;
            case 'extensions':
                this.items = extensionItems;
                break;
            case 'snippets':
                this.items = snippetItems;
                break;
        }

        // add class
        this.cls = (this.cls || '').split(/\s+/).concat('CMStemplatemoduleselection').concat('CMStemplatemoduleselection-' + this.mode).join(' ');

        CMS.structureEditor.TemplateModuleSelection.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('CMStemplatemoduleselection', CMS.structureEditor.TemplateModuleSelection);



