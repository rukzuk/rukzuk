Ext.ns('CMS.unitEditor');

/**
 * @class CMS.unitEditor.TemplateUnitEditor
 * @extends CMS.unitEditor.UnitEditor
 */
CMS.unitEditor.TemplateUnitEditor = Ext.extend(CMS.unitEditor.UnitEditor, {
    /** @lends CMS.unitEditor.TemplateUnitEditor.prototype */

    bubbleEvents: ['CMSduplicateunit', 'CMScopyunit', 'CMSpasteunit', 'CMSremoveunit', 'CMSmoveunit', 'CMSrefreshpage', 'CMSformvaluechange', 'CMSinsertunit'],

    /** @protected */
    initComponent: function () {
        this.mode = 'template';

        this.items = [{
            /**
             * Reference to the accordion panel containing the unit forms
             * @property
             * @name accordionPanel
             * @type Ext.Panel
             * @memberOf CMS.unitEditor.TemplateUnitEditor.prototype
             * @protected
             */
            xtype: 'panel',
            layout: 'accordion',
            disabled: true,
            border: false,
            flex: 1,
            ref: 'accordionPanel',
            autoDestroy: true,
            items: [{
                /**
                 * Reference to panel to edit the unit's meta data
                 * @property
                 * @name metaEditor
                 * @type CMS.unitEditor.TemplateUnitMetaEditor
                 * @memberOf CMS.unitEditor.TemplateUnitEditor.prototype
                 * @protected
                 */
                xtype: 'CMStemplateunitmetaeditor',
                hideBehaviourSettings: true,
                cls: 'CMSgeneratedformpanel CMSmetaeditor',
                ref: '../metaEditor',
                title: CMS.i18n('Metadaten'),
                locked: true,
                collapsed: true,
                websiteId: this.websiteId,
                listeners: {
                    configchanged: this.metaDataChangeHandler,
                    scope: this
                }
            }],
            remove: function (cmp) {
                if (cmp.locked) {
                    return false;
                }
                Ext.Panel.prototype.remove.apply(this, arguments);
            }
        }];

        CMS.unitEditor.TemplateUnitEditor.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Enable the editor and load the specified content editor (TODO activate item according to selected section)
     * @param {Ext.data.Record} unit (optional) The unit to be loaded
     * @param {String} section (optional) The name of the selected section
     * @param {Object} formGroupDataOverrides (optional)
     */
    enable: function (unit, section, formGroupDataOverrides) {
        if (unit) {
            this.accordionPanel.setDisabled(false);

            if (unit != this.currentUnit) {
                this.currentUnit = unit;
                this.accordionPanel.removeAll();

                var module = unit.getModule();
                if (module) {
                    this.metaEditor.loadData(SB.util.cloneObject(unit.data));

                    var visibleFormGroupsToolTemplate = new Ext.XTemplate(
                        '<div class="x-tool x-tool-{id} {enabledClass}">&#160;</div>'
                    );

                    var formGroups = module.get('form');
                    Ext.each(formGroups, function (fg, index) {
                        var fgData = fg.formGroupData;

                        if (typeof fgData === 'string') { // -> Can be removed when SBCMS-307 is solved
                            console.warn('[TemplateUnitEditor] Obsolete data format of formGroupData');
                            fgData = fg.formGroupData = Ext.decode(fgData);
                        }

                        if (!fgData.length) {
                            return;
                        }

                        // apply fgData overrides
                        fgData = this.applyFormGroupDataOverrides(fgData, formGroupDataOverrides);

                        var visibleFormGroups = this.currentUnit.get('visibleFormGroups') || [];

                        this.accordionPanel.insert(this.accordionPanel.items.length - 1, {
                            xtype: 'CMSgeneratedformpanel',
                            //border: false,
                            idSuffix: this.idSuffix,
                            websiteId: this.websiteId,
                            cfg: fgData,
                            title: CMS.translateInput(fg.name),
                            toolTemplate: visibleFormGroupsToolTemplate,
                            tools:[{
                                id:'visibleFormGroupsToggle',
                                qtip: {
                                    text: CMS.i18n(null, 'templateUnitEditor.visibleFormGroupsToggle'),
                                    align: 'l-r?'
                                },
                                handler: this.visibleFormGroupToggleHandler,
                                scope: this,
                                enabledClass: visibleFormGroups.indexOf(fg.id) !== -1 ? 'enabled' : '',
                                fgId: fg.id
                            }],
                            cls: 'CMSgeneratedformpanel',
                            iconCls: fg.icon,
                            record: unit,
                            plugins: ['CMSradiofieldsetplugin'],
                            listeners: {
                                scope: this,
                                valuechanged: this.valueChangeHandler
                            }
                        });

                        // We store the index of the form group
                        // in order to be able to open it via
                        // its id.
                        this.formGroupIdMapping[fg.id] = index;
                    }, this);

                    if (this.accordionPanel.items.length > 1) {
                        this.metaEditor.collapse();
                    }

                    if (this.accordionPanel.rendered) {
                        this.accordionPanel.getLayout().setActiveItem(0);
                        this.accordionPanel.doLayout();
                    }
                }

                CMS.unitEditor.TemplateUnitEditor.superclass.enable.apply(this, arguments);
            }
        } else {
            this.accordionPanel.setDisabled(true);
        }
    },

    /**
     * Persist the currently toggled state of the visible formValueGroup.
     * @private
     * @param {Ext.EventObject} event
     * @param {Ext.Element} toolEl
     * @param {Ext.Panel} panel
     * @param {Object} toolCfg
     */
    visibleFormGroupToggleHandler: function (event, toolEl, panel, toolCfg) {
        var visibleFormGroups = this.currentUnit.get('visibleFormGroups') || [];
        if (visibleFormGroups.indexOf(toolCfg.fgId) !== -1) {
            visibleFormGroups.remove(toolCfg.fgId);
        } else {
            visibleFormGroups.push(toolCfg.fgId);
        }
        toolEl.toggleClass('enabled');
        this.currentUnit.set('visibleFormGroups', visibleFormGroups);
        this.currentUnit.commit();
    },

    /**
     * Change of Metadata
     * @param field - the changed ext field
     */
    metaDataChangeHandler: function (field) {
        this.metaEditor.updateRecord(this.currentUnit);

        if (field && field.name === 'name') {
            this.onStructureChange();
        }
    },

    /**
     * Clears the editor. Overwrites the CMS.unitEditor.UnitEditor
     * method of the same name
     */
    clearEditor: function () {
        this.currentUnit = null;
        this.accordionPanel.removeAll();
        this.metaEditor.collapse();
        CMS.unitEditor.TemplateUnitEditor.superclass.clearEditor.call(this);
    }
});

Ext.reg('CMStemplateuniteditor', CMS.unitEditor.TemplateUnitEditor);
