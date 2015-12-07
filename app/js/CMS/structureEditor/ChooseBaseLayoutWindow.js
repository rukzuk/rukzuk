Ext.ns('CMS.structureEditor');

/**
 * @class CMS.structureEditor.ChooseBaseLayoutWindow
 * @extends CMS.MainWindow
 * Window for choosing the base layout
 */
CMS.structureEditor.ChooseBaseLayoutWindow = Ext.extend(CMS.MainWindow, {
    cls: 'CMSchoosebaselayoutwindow',
    modal: true,
    border: false,
    resizable: false,
    closable: false,
    maxWidth: 1100,

    callback: null,

    scope: null,

    baseLayouts: null,

    initComponent: function () {
        this.title = CMS.i18n(null, 'templateStructureEditor.chooseBaseLayout.title');
        var store = new Ext.data.JsonStore({
            autoDestroy: true,
            fields: [
                {name: 'id', type: 'string'},
                {name: 'name', type: 'string'},
                {name: 'previewImageUrl', type: 'string'},
                {name: 'type', type: 'string'}
            ]
        });
        store.sort([{
            field: 'type',
            direction: 'DESC'
        }, {
            field: 'id',
            direction: 'ASC'
        }], 'ASC');
        store.loadData(this.baseLayouts);
        var tpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="baselayout-wrap baselayout-type-{[values.type]}">',
            '<div class="baselayout-thumb"><img class="baselayout-screenshot" src="{[values.previewImageUrl || CMS.config.urls.emptySvgUrl]}" title="{name}" width="100%"></div>',
            '<div class="baselayout-titlebar"><span>{[values.name]}</span></div>',
            '</div>',
            '</div>',
            '</tpl>'
        );
        Ext.apply(this, {
            layout: 'fit',
            items: [{
                xtype: 'CMSthumbview',
                itemSelector: 'div.baselayout-wrap',
                overClass: 'hover',
                selectedClass: 'selected',
                singleSelect: true,
                tpl: tpl,
                store: store,

                ref: 'thumbView',
                trackOver: true,
                scrollOffset: 10,
                listeners: {
                    click: this.clickHandler,
                    scope: this
                }
            }]
        });
        CMS.structureEditor.ChooseBaseLayoutWindow.superclass.initComponent.apply(this, arguments);
    },

    clickHandler: function (dataView) {
        var baseLayout = dataView.getSelectedRecords()[0].data;
        this.executeCallback(baseLayout);
        this.destroy();
    },

    /**
     * @private
     *
     * Executes the callback function if set with the given param
     *
     * @param {Object} baseLayout
     */
    executeCallback: function (baseLayout) {
        //execute handler function
        if (this.callback) {
            this.callback.call(this.scope || window, baseLayout, this);
        }
    },

    destroy: function () {
        this.templateSnippetStore = null;

        CMS.structureEditor.ChooseBaseLayoutWindow.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSchoosebaselayoutwindow', CMS.structureEditor.ChooseBaseLayoutWindow);
