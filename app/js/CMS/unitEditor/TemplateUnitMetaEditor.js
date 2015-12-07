Ext.ns('CMS.unitEditor');

/**
 * Editor for a template unit's meta data
 * @class CMS.unitEditor.TemplateUnitMetaEditor
 * @extends Ext.form.FormPanel
 */
CMS.unitEditor.TemplateUnitMetaEditor = Ext.extend(Ext.form.FormPanel, {
    /** @lends CMS.unitEditor.TemplateUnitMetaEditor.prototype */

    bubbleEvents: ['CMSrefreshpage'],

    labelAlign: 'left',
    autoScroll: true,
    border: false,

    /**
     * <tt>true</tt> to hide the "category and behaviour" fieldset, for use in
     * Unit Editing. Defaults to <tt>false</tt>.
     * @property hideBehaviourSettings
     * @type Boolean
     */
    hideBehaviourSettings: false,

    /** @protected */
    initComponent: function () {
        this.defaults = {
            anchor: '-' + Ext.getPreciseScrollBarWidth()
        };

        this.items = [];
        this.items.push({
            /**
             * The fieldset containing the settings if the unit is a ghost container
             * @property
             * @name behaviourFieldset
             * @type Ext.form.Fieldset
             * @memberOf CMS.unitEditor.TemplateUnitMetaEditor
             * @private
             */
            xtype: 'fieldset',
            title: CMS.i18n('Flex-Container', 'templateunitmetaeditor.flexContainer'),
            ref: 'behaviourFieldset',
            defaults: {
                anchor: '100%',
                hideLabel: true,
                checked: false,
                listeners: {
                    scope: this,
                    check: this.metaChangeHandler
                }
            },
            items: [{
                xtype: 'checkbox',
                boxLabel: CMS.i18n('Dieses Modul ist ein Flex-Container. Enthaltene Module müssen in der Seitenbearbeitung eingefügt, sortiert und gelöscht werden.', 'templateunitmetaeditor.ghostContainer'),
                name: 'ghostContainer'
            }]
        });

        this.items.push({
            xtype: 'fieldset',
            title: CMS.i18n('Name und Beschreibung'),
            defaults: {
                anchor: '100%'
            },
            items: [{
                xtype: 'textfield',
                allowBlank: true,
                name: 'name',
                fieldLabel: CMS.i18n('Name der Unit'),
                listeners: {
                    scope: this,
                    change: this.nameChangeHandler
                }
            }, {
                xtype: 'textarea',
                name: 'description',
                fieldLabel: CMS.i18n('Beschreibung'),
                listeners: {
                    scope: this,
                    change: this.metaChangeHandler
                }
            }, {
                /**
                 * The field displaying the module version
                 * @property
                 * @name versionField
                 * @type Ext.form.DisplayField
                 * @memberOf CMS.unitEditor.TemplateUnitMetaEditor
                 * @private
                 */
                xtype: 'displayfield',
                ref: '../versionField',
                fieldLabel: CMS.i18n('Modulversion')
            }]
        });

        this.items.push({
            xtype: 'fieldset',
            title: CMS.i18n(null, 'templateUnitMetaEditor.unitHtmlClass'),
            hidden: true,
            ref: 'htmlSettingsFieldset',
            defaults: {
                anchor: '100%'
            },
            items: [Ext.apply({
                xtype: 'textfield',
                allowBlank: true,
                name: 'htmlClass',
                hideLabel: true,
                listeners: {
                    scope: this,
                    change: this.unitHtmlClassChangeHandler
                }
            }, CMS.config.validation.unitHtmlClass)]
        });


        CMS.unitEditor.TemplateUnitMetaEditor.superclass.initComponent.apply(this, arguments);
    },

    //HACK to disable icon in header
    setIconClass: Ext.emptyFn,

    unitHtmlClassChangeHandler: function (field) {
        this.metaChangeHandler(field);
        this.fireEvent('CMSrefreshpage');
    },

    nameChangeHandler: function (field) {
        this.fireEvent('namechanged', field.getValue());
        this.metaChangeHandler(field);
    },

    metaChangeHandler: function (field) {
        if (!this.isLoading) {
            this.fireEvent('configchanged', field);
        }
    },

    /**
     * Load the specified unit
     * @param {CMS.data.UnitRecord} record
     */
    loadRecord: function (record) {
        this.loadData(record.data);
    },

    /**
     * Load a unit's data
     * @param {Object} data
     */
    loadData: function (data) {
        console.log('[TemplateMetaEditor] loadData', data);

        // workaround for SBCMS-1517, if the property doesn't exists the checkbox field won't get a reset
        Ext.applyIf(data, {
            ghostContainer: false,
            description: '',
            htmlClass: ''
        });

        // prevent events while loading a record
        this.isLoading = true;
        this.getForm().setValues(data);
        this.isLoading = false;

        this.visibleFormGroups = data.visibleFormGroups || [];

        var module = CMS.data.StoreManager.get('module', this.websiteId).getById(data.moduleId);
        if (module) {
            var version = module.get('version');
            this.versionField.setValue(CMS.translateInput(module.get('name')) + (version ? ' (' + version + ')' : ''));

            if (module.canBeGhostContainer()) {
                this.behaviourFieldset.show();
            } else {
                this.behaviourFieldset.hide();
            }

            if (CMS.data.isDefaultModuleRecord(module)) {
                this.htmlSettingsFieldset.show();
            } else {
                this.htmlSettingsFieldset.hide();
            }
        }
    },

    /**
     * Persist the currently entered form values in the given record.
     * @param {CMS.data.UnitRecord} record
     */
    updateRecord: function (record) {
        var originalGhostContainerState = record.get('ghostContainer');
        // update record via form
        this.getForm().updateRecord(record);

        // restore original ghostContainer state
        // should fix some bugs with crazy form settings
        if (!record.getModule().canBeGhostContainer()) {
            record.set('ghostContainer', originalGhostContainerState);
        }

        record.commit();
    }

});

Ext.reg('CMStemplateunitmetaeditor', CMS.unitEditor.TemplateUnitMetaEditor);
