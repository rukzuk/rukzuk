Ext.ns('CMS.layout');

/**
 * SideBarPanel which contains all page edit components and functionality
 *
 * @class       CMS.layout.PageWorkbenchPanel
 * @extends     CMS.layout.IframeWorkbenchPanel
 * @author      Thomas Sojda
 * @copyright   (c) 2011, by Seitenbau GmbH
 */
CMS.layout.PageWorkbenchPanel = Ext.extend(CMS.layout.IframeWorkbenchPanel, {
    /** @lends CMS.layout.PageWorkbenchPanel.prototype */

    bubbleEvents: ['CMSclosepageandopentemplate', 'CMScloseworkbench'],

    /** @private */
    initComponent: function () {
        this.mode = 'page';
        this.editorXType = 'CMSpageuniteditor';
        CMS.layout.PageWorkbenchPanel.superclass.initComponent.call(this);
    },

    /**
     * Override superclass to open unit editor on start for the initally selected node
     * @private
     */
    afterRender: function () {
        CMS.layout.PageWorkbenchPanel.superclass.afterRender.apply(this, arguments);

        var selectedNode = this.structureEditor.getSelectedNode();
        if (selectedNode) {
            this.openFormPanel();
        }
    },

    /**
     * Generates the PageEditor window config
     * @private
     */
    buildStructureEditor: function () {
        return {
            xtype: 'CMSpagestructureeditor',
            unitStore: this.unitStore,
            websiteId: this.websiteId,
            title: CMS.i18n('Seiten-Struktur'),
            noRightsMessage: this.createsNoRightsInfoMessage()
        };
    },

    /**
     * Overrides superclass to apply "no edit rights" message
     * @return {Object} Ext.Component config object
     */
    buildUnitEditor: function () {
        var unitEditorCfg = CMS.layout.PageWorkbenchPanel.superclass.buildUnitEditor.call(this);
        unitEditorCfg.noRightsMessage = this.createsNoRightsInfoMessage();
        return unitEditorCfg;
    },

    /**
     * Reverts all unsaved changes
     * @private
     */
    restore: function () {
        CMS.layout.PageWorkbenchPanel.superclass.restore.call(this, 'getPage');
    },


    /**
     * Save the currently opened page
     * Called when the user clicks the save button
     * (Overrides CMS.layout.IframeWorkbenchPanel#save and maps the intial parameter)
     *
     * @param {Function} [callback] Optional. A callback function which will be executed after successful saving
     * @param {Boolean} [silent] Optional. If set to <code>true</code> no success message will appear and there
     *      will be no masking (defaults to <code>false</code>)
     */
    save: function (callback, silent) {
        var record = this.structureEditor.dataTree.createPageRecord();
        var params = Ext.apply(record.data, {websiteId: this.websiteId});
        var action = 'editPage';

        CMS.layout.PageWorkbenchPanel.superclass.save.call(this, record, params, action, callback, !!silent);
    },

    /**
     * Overwrites the handleInsert method of the CMS.layout.IframeWorkbenchPanel class to
     * prevent displaying the 'Insert unit' window in a situation where nothing can
     * be inserted.
     * @private
     */
    handleInsert: function () {
        if (!this.selectedUnit) {
            return;
        }
        var parent = this.selectedUnit.store.getParentUnit(this.selectedUnit);
        if (this.selectedUnit.hasInsertableChildrenInMode('page') || (parent && parent.hasInsertableChildrenInMode('page'))) {
            CMS.layout.PageWorkbenchPanel.superclass.handleInsert.apply(this, arguments);
        }
    },

    /**
     * Creates a info message to inform the user that he has not sufficient rights to edit the selected unit
     * @return {Object} ExtJS item object
     */
    createsNoRightsInfoMessage: function () {
        var msg = '<p>' + CMS.i18n('', 'PageWorkbenchPanel.noRightsInfo.editor') + '</p>';
        var websiteStore = CMS.data.WebsiteStore.getInstance();
        var websiteRecord = websiteStore.getById(this.websiteId);

        var templateStore = CMS.data.StoreManager.get('template', this.websiteId);
        var templateRecord = templateStore.getById(this.templateId);
        var templateName = templateRecord.get('name');
        var canEditTemplates = CMS.app.userInfo.canEditTemplates(websiteRecord);
        if (canEditTemplates) {
            msg += '<p>' + CMS.i18n('', 'PageWorkbenchPanel.noRightsInfo.designer') + '</p>';
        }

        var templateNameEllipsis = templateName.length > 16 ? templateName.substring(0, 16) + '…' :  templateName;

        return {
            items: [{
                xtype: 'box',
                autoEl: {
                    tag: 'div',
                    html: msg
                }
            }, {
                xtype: 'button',
                tooltip: templateName,
                text: String.format(CMS.i18n('', 'PageWorkbenchPanel.noRightsInfo.goToTemplateBtn'), templateNameEllipsis),
                handler: this.goToTemplate,
                scope: this,
                hidden: !canEditTemplates
            }]
        };
    },

    /**
     * Open corresponding Template
     */
    goToTemplate: function () {

        var closePageAndOpenTemplate = function () {
            var templateStore = CMS.data.StoreManager.get('template', this.websiteId);
            var templateRecord = templateStore.getById(this.templateId);
            this.fireEvent('CMSclosepageandopentemplate', this.record.id, templateRecord);
        };

        if (this.isDirty()) {
            Ext.MessageBox.show({
                closable: false,
                title: CMS.i18n('Änderungen speichern?'),
                msg: CMS.i18n('Ungespeicherte Änderungen in {type} „{name}“ speichern?').replace('{name}', this.record.get('name')).replace('{type}', ''),
                buttons: {
                    yes: true,
                    no: true,
                    cancel: true
                },
                icon: Ext.MessageBox.WARNING,
                fn: function (btnId) {
                    if (btnId === 'yes') {
                        this.save(closePageAndOpenTemplate.createDelegate(this));
                    } else if (btnId === 'no') {
                        closePageAndOpenTemplate.call(this);
                    }
                },
                scope: this
            });
        } else {
            closePageAndOpenTemplate.call(this);
        }

    },
});

Ext.reg('CMSpageworkbenchpanel', CMS.layout.PageWorkbenchPanel);
