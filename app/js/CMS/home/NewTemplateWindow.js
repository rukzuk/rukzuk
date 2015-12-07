Ext.ns('CMS.home');

/**
 * @class CMS.home.NewTemplateWindow
 * @extends CMS.MainWindow
 * Window for template create
 */
CMS.home.NewTemplateWindow = Ext.extend(CMS.MainWindow, {
    cls: 'CMSnewtemplatewindow',
    modal: true,
    border: true,
    resizable: false,
    closable: true,
    maxWidth: 687,
    maxHeight: 350,

    callback: null,

    scope: null,

    websiteId: '',

    initComponent: function () {
        this.title = CMS.i18n(null, 'newTemplateWindow.windowTitle');
        this.store = CMS.data.StoreManager.get('pageType', this.websiteId);
        this.tplStore = CMS.data.StoreManager.get('template', this.websiteId);
        var numTemplates = this.tplStore.getCount();
        var nameValueSuffix = numTemplates > 0 ? ' ' + (numTemplates+1) : '';

        var tpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="pl-wrap pagetype-{[values.id]}" title="{[CMS.translateInput(values.description)]}">',
            '<div class="pl-thumb"><img class="pl-screenshot" src="{[values.previewImageUrl || CMS.config.urls.emptySvgUrl]}" width="100%"></div>',
            '<div class="pl-titlebar"><span>{[CMS.translateInput(values.name)]}</span></div>',
            '</div>',
            '</div>',
            '</tpl>'
        );

        Ext.apply(this, {
            layout: 'vbox',
            autoScroll: true,
            items: [{
                cls: 'nameForm',
                xtype: 'form',
                ref: 'nameForm',
                monitorValid: true,
                labelAlign: 'top',
                layoutConfig: {
                    align: 'stretch',
                    pack: 'start'
                },
                listeners: {
                    clientvalidation: this.updateCreateButtonState,
                    scope: this
                },
                items: [Ext.apply({
                    xtype: 'textfield',
                    fieldLabel: CMS.i18n(null, 'newTemplateWindow.templateName'),
                    name: 'name',
                    value: CMS.i18n(null, 'newTemplateWindow.nameDefaultValue') + nameValueSuffix,
                    ref: '../nameField',
                    listeners: {
                        specialkey: function (field, e) {
                            if (e.getKey() == e.ENTER) {
                                this.createTemplate();
                            }
                        },
                        scope: this
                    }
                }, CMS.config.validation.templateName), {
                    fieldLabel: CMS.i18n(null, 'newTemplateWindow.templatePageType')
                }]
            }, {
                xtype: 'CMSthumbview',
                itemSelector: 'div.pl-wrap',
                overClass: 'hover',
                selectedClass: 'selected',
                singleSelect: true,
                tpl: tpl,
                store: this.store,
                ref: 'pageTypeView',
                containerDeselect: false,
                scrollOffset: 10,
                trackOver: true,
                style: {
                    width: '100%'
                }
            }]
        });

        // buttons
        this.buttons = [{
            text: CMS.i18n(null, 'newTemplateWindow.createButton'),
            iconCls: 'ok',
            cls: 'primary',
            ref: '../createButton',
            disabled: true,
            handler: this.createTemplate,
            scope: this
        }, {
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            handler: this.destroy,
            scope: this
        }];

        CMS.home.NewTemplateWindow.superclass.initComponent.apply(this, arguments);

        // handle changes of selection
        this.mon(this.pageTypeView.dataView, 'selectionchange', this.updateCreateButtonState, this);
        // select first item
        this.mon(this.pageTypeView.dataView, 'afterrender', this.selectFirstItem, this, {single: true});
        this.mon(this.store, 'load', this.selectFirstItem, this, {single: true});

        this.on('afterrender', function () {
            this.nameForm.getForm().items.first().focus(true, 200);
        }, this);
    },

    /**
     * @private
     */
    selectFirstItem: function () {
        if (this.pageTypeView.dataView.getSelectedIndexes().length === 0) {
            this.pageTypeView.dataView.selectRange(0, 0, false);
        }
    },

    /**
     * Toggle disabled state of save button
     * @private
     */
    updateCreateButtonState: function () {
        var selectedRecords = this.pageTypeView.dataView.getSelectedRecords();
        if (selectedRecords.length && this.nameForm.getForm().isValid()) {
            this.createButton.enable();
        } else {
            this.createButton.disable();
        }
    },

    /**
     * Creates a Template
     * @private
     * @param dataView
     */
    createTemplate: function () {

        if (!this.nameForm.getForm().isValid()) {
            return;
        }

        var pageTypeId = this.pageTypeView.dataView.getSelectedRecords()[0].data.id;
        var templateName = this.nameField.getValue();

        var record = new CMS.data.TemplateRecord({
            name: templateName,
            pageType: pageTypeId,
            websiteId: this.websiteId
        });

        var createTemplateMask = Ext.getBody().mask(CMS.i18n(null, 'newTemplateWindow.maskCreateTpl'), 'CMSmaskall');
        var tplStore = this.tplStore;
        var callback = this.callback;
        var cbScope = this.scope;

        CMS.app.trafficManager.sendRequest({
            action: 'createTemplate',
            data: record.data,
            successCondition: 'data.id',
            success: function (resp) {
                var templateId = resp.data.id;
                // reload store to get template record
                tplStore.reload({
                    callback: function () {
                        createTemplateMask.unmask();
                        callback.call(cbScope || window, templateId, this);
                    },
                    scope: this
                });
            },
            failureTitle: CMS.i18n(null, 'newTemplateWindow.failureTitle'),
            scope: this
        });


        this.destroy();
    },

    destroy: function () {
        CMS.home.NewTemplateWindow.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSnewtemplatewindow', CMS.home.NewTemplateWindow);
