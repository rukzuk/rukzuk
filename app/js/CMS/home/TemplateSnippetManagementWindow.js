Ext.ns('CMS');

/**
 * @class       CMS.home.TemplateSnippetManagementWindow
 * @extends     CMS.MainWindow
 *
 * TemplateSnippet Management Window
 */
CMS.home.TemplateSnippetManagementWindow = Ext.extend(CMS.MainWindow, {

    cls: 'CMStemplatesnippetwindow',

    /**
     * @cfg {Object} website
     * Website which contains the templateSnippets
     */
    websiteId: undefined,

    initComponent: function () {
        this.title = CMS.i18n('Snippets');
        this.store = CMS.data.StoreManager.get('templateSnippet', this.websiteId);
        this.items = [{
            xtype: 'CMStemplatesnippetgrid',
            viewConfig: {
                emptyText: CMS.i18n('(Keine Snippets vorhanden)')
            },
            enableDragDrop: false,
            groupTextTpl: '{text}',
            border: false,
            store: this.store,
            singleSelect: false,
            ref: 'grid',
            columns: [{
                id: 'icon',
                hidden: true
            }, {
                id: 'name',
                renderer: function (value, meta, record) {
                    var overwriteText = record.get('overwritten') ? '<span class="CMSoverwritemoduletext">' + CMS.i18n(null, 'templatesnippetmgmt.overwrittenIndicatorText') + '</span>' : '';
                    return [
                        '<span class="CMSname">', CMS.translateInput(value), overwriteText, '</span><br>',
                        '<span class="CMSdescription">', CMS.translateInput(record.get('description')), '</span>'
                    ].join('');
                }
            }]
        }];

        this.bbar = [{
            text: CMS.i18n('Löschen'),
            ref: '../deleteButton',
            iconCls: 'delete deletegroup',
            disabled: true,
            handler: this.deleteBtnHandler,
            scope: this
        }, {
            text: CMS.i18n('Importieren'),
            iconCls: 'import importmodule',
            handler: this.importBtnHandler,
            scope: this
        }, {
            text: CMS.i18n('Exportieren'),
            iconCls: 'export exportmodule',
            ref: '../exportButton',
            handler: this.exportBtnHandler,
            disabled: true,
            scope: this
        }, '->', {
            text: CMS.i18n('Bearbeiten'),
            cls: 'primary',
            iconCls: 'properties',
            ref: '../propertiesButton',
            handler: this.propertiesBtnHandler,
            disabled: true,
            scope: this
        }];

        CMS.home.TemplateSnippetManagementWindow.superclass.initComponent.call(this);
        this.filterSnippets('local');
        // listen to grid events
        this.mon(this.grid.getSelectionModel(), 'selectionchange', this.selectionchangeHandler, this);
        this.mon(this.grid, 'rowdblclick', this.dblClickHandler, this);
    },

    /* Grid Events */
    selectionchangeHandler: function (sel) {
        // no selections disable all buttons (but import)
        if (sel.selections.length === 0) {
            this.propertiesButton.disable();
            this.deleteButton.disable();
            this.exportButton.disable();
        } else {
            // multiple selected elements
            this.deleteButton.enable();
            this.exportButton.enable();

            // enable btn only if one element is selected
            if (sel.selections.length === 1) {
                this.propertiesButton.enable();
            } else {
                this.propertiesButton.disable();
            }
        }
    },

    getSelected: function () {
        var sm = this.grid.getSelectionModel();
        return this.store.getById(sm.getSelected().id);
    },

    getSelections: function () {
        var sm = this.grid.getSelectionModel();
        var gridSelections = sm.getSelections();

        var selections = [];
        Ext.each(gridSelections, function (value) {
            selections.push(this.store.getById(value.id));
        });

        return selections;
    },

    dblClickHandler: function () {
        var node = this.getSelected();
        this.showPropertiesDialog(node);
    },

    /* Button-Bar Buttons */
    propertiesBtnHandler: function () {
        var node = this.getSelected();
        this.showPropertiesDialog(node);
    },

    deleteBtnHandler: function () {
        var snippets = this.getSelections();

        // get translated snippet names
        var snippetNames = [];
        Ext.each(Ext.pluck(Ext.pluck(snippets, 'data'), 'name'), function (name) {
            snippetNames.push(CMS.translateInput(name));
        });

        Ext.MessageBox.confirm(
            CMS.i18n('Löschen?'), // title
            CMS.i18n('Folgende Snippets wirklich löschen?: {names}').replace('{names}', snippetNames.join(', ')),
            function (btnId) { // callback
                if (btnId === 'yes') {
                    CMS.app.trafficManager.sendRequest({
                        action: 'deleteTemplateSnippet',
                        data: {
                            websiteId: this.websiteId,
                            ids: Ext.pluck(snippets, 'id')
                        },
                        scope: this,
                        success: function () {
                            // reload store instead of removing the item local (because of global vs local overwrites)
                            this.store.reload();
                        },
                        failure: function () {
                            // reload store (some snippets might be deleted, others not)
                            this.store.reload();
                        },
                        failureTitle: CMS.i18n('Fehler beim Löschen von Snippets')
                    });
                }
            },
        this /*scope*/);
    },

    importBtnHandler: function () {
        var website = Ext.StoreMgr.get('websites').getById(this.websiteId);
        CMS.app.ImportHelper.startImport(website, {
            title: CMS.i18n('Snippets importieren'),
            text: '<p>' + CMS.i18n('Bitte Datei mit Snippets auswählen.') + '</p>',
            allowedType: CMS.config.importTypes.templateSnippet
        });
    },

    /**
     * @private
     * @param sourceType
     */
    filterSnippets: function (sourceType) {
        this.grid.filterBy(function (record) {
            return record.get('sourceType') === sourceType;
        });
    },

    exportBtnHandler: function () {
        var selections = this.getSelections();
        if (!selections.length) {
            return;
        }
        CMS.app.downloadHelper.startDownload({
            action: 'exportTemplateSnippets',
            data: {
                websiteId: this.websiteId,
                ids: Ext.pluck(selections, 'id')
            },
            urlKey: 'data.url',
            failureTitle: CMS.i18n('Fehler beim Exportieren')
        });
    },

    showPropertiesDialog: function (node) {
        var propWin = new CMS.home.TemplateSnippetPropertiesWindow({
            websiteId: this.websiteId
        });
        propWin.setTemplateSnippet(node);
        propWin.show();
    }
});
