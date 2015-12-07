Ext.ns('CMS.unitEditor');

/**
 * @class CMS.unitEditor.PageUnitEditor
 * @extends CMS.unitEditor.UnitEditor
 */
CMS.unitEditor.PageUnitEditor = Ext.extend(CMS.unitEditor.UnitEditor, {
    /** @lends CMS.unitEditor.PageUnitEditor.prototype */

    bubbleEvents: [
        'CMSremoveunit',
        'CMSduplicateunit',
        'CMSmoveunit', 'CMSrefreshpage',
        'CMSformvaluechange',
        'CMShoverunit',
        'CMSinsertunit'
    ],

    /**
     * A info message to inform the user that he has not sufficient rights to edit the selected unit
     * @property noRightsMessage
     * @type Object
     */
    noRightsMessage: null,

    /** @protected */
    initComponent: function () {
        this.mode = 'page';
        this.items = [{
            xtype: 'panel',
            layout: 'accordion',
            layoutConfig: {},
            border: false,
            flex: 1,
            ref: 'accordionPanel',
            autoDestroy: true,
            remove: function (cmp) {
                if (cmp.locked) {
                    return false;
                }
                Ext.Panel.prototype.remove.apply(this, arguments);
            }
        }];

        CMS.unitEditor.PageUnitEditor.superclass.initComponent.apply(this, arguments);
    },

    /**
     * Enable the editor and load the specified content editor (TODO activate item according to selected section)
     * @param {Ext.data.Record} unit (optional) The unit to be loaded
     * @param {String} section (optional) The name of the selected section
     * @param {Object} formGroupDataOverrides (optional)
     */
    enable: function (unit, section, formGroupDataOverrides) {
        if (unit && unit != this.currentUnit) {
            this.currentUnit = unit;
            this.accordionPanel.removeAll();
            var isEditable = unit.isEditableInMode('page');
            var hasInsertableChildren = unit.hasInsertableChildrenInMode('page');

            if (!isEditable) {
                if (hasInsertableChildren) {
                    this.accordionPanel.add({
                        cls: 'CMSnorightsinfo',
                        items: [
                            {
                                xtype: 'box',
                                autoEl: {
                                    tag: 'div',
                                    html: CMS.i18n('', 'pageUnitEditor.noRightsInfo.ghostContainer')
                                }
                            }
                        ]
                    });
                } else {
                    this.accordionPanel.add({
                        cls: 'CMSnorightsinfo',
                        items: [this.noRightsMessage]
                    });
                }
            } else {
                var visibleFormGroups = unit.data.visibleFormGroups || [];
                var module = unit.getModule();
                if (module) {
                    var formGroups = module.get('form');
                    Ext.each(formGroups, function (fg, index) {
                        if (visibleFormGroups.indexOf(fg.id) == -1) {
                            return;
                        }
                        var fgData = fg.formGroupData;
                        if (!fgData.length) {
                            return;
                        }

                        // apply fgData overrides
                        fgData = this.applyFormGroupDataOverrides(fgData, formGroupDataOverrides);


                        this.accordionPanel.insert(this.accordionPanel.items.length, {
                            xtype: 'CMSgeneratedformpanel',
                            border: false,
                            idSuffix: this.idSuffix,
                            websiteId: this.websiteId,
                            cfg: fgData,
                            title: CMS.translateInput(fg.name),
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
                }
            }

            this.accordionPanel.doLayout();

            CMS.unitEditor.PageUnitEditor.superclass.enable.apply(this, arguments);
        }
    },

    /**
     * Clears the editor. Overwrites the CMS.unitEditor.UnitEditor
     * method of the same name
     */
    clearEditor: function () {
        this.currentUnit = null;
        this.accordionPanel.removeAll();
        CMS.unitEditor.PageUnitEditor.superclass.clearEditor.call(this);
    }
});

Ext.reg('CMSpageuniteditor', CMS.unitEditor.PageUnitEditor);
