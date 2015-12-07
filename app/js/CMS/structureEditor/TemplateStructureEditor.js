Ext.ns('CMS.structureEditor');

/**
 * @class CMS.structureEditor.TemplateStructureEditor
 * @extends CMS.structureEditor.StructureEditor
 */
CMS.structureEditor.TemplateStructureEditor = Ext.extend(CMS.structureEditor.StructureEditor, {
    /** @lends CMS.structureEditor.TemplateStructureEditor */

    initComponent: function () {

        this.items = [{
            /**
             * The tree panel that shows the template structure
             * @name dataTree
             * @type CMS.structureEditor.TemplateUnitTreePanel
             * @memberOf CMS.structureEditor.TemplateStructureEditor
             * @property
             */
            xtype: 'CMStemplateunittreepanel',
            plugins: [new CMS.TreeContextMenu({
                items: [{
                    iconCls: 'rename',
                    text: CMS.i18n('Umbenennen'),
                    handler: function (item, e, nodeId) {
                        this.dataTree.triggerRename(nodeId);
                    },
                    scope: this
                }, {
                    iconCls: 'copy',
                    text: CMS.i18n('Kopieren'),
                    event: 'CMScopyunit',
                    condition: {
                        fn: this.checkCanCopy,
                        scope: this
                    }
                }, {
                    iconCls: 'paste',
                    text: CMS.i18n('Einfügen', 'templatestructureeditor.contextMenuPaste'),
                    event: 'CMSpasteunit',
                    condition: {
                        fn: this.checkCanPaste,
                        scope: this
                    }
                }, {
                    iconCls: 'clone',
                    text: CMS.i18n('Duplizieren'),
                    event: 'CMSduplicateunit',
                    condition: {
                        fn: this.checkCanDuplicate,
                        scope: this
                    }
                }, {
                    iconCls: 'createTemplateSnippet',
                    text: CMS.i18n('Snippet erstellen'),
                    event: 'CMScreatetemplatesnippet',
                    condition: {
                        fn: this.checkCanCreateTemplateSnippet,
                        scope: this
                    }
                }, {
                    iconCls: 'reset',
                    text: CMS.i18n(null, 'templateStructureEditor.reset'),
                    event: 'CMSresetunit',
                    condition: {
                        fn: this.checkCanReset,
                        scope: this
                    }
                }, {
                    iconCls: 'delete',
                    text: CMS.i18n('Löschen'),
                    event: 'CMSremoveunit',
                    condition: {
                        fn: this.checkCanDelete,
                        scope: this
                    }
                }]
            })],
            ref: 'dataTree',
            region: 'center',
            unitStore: this.unitStore,
            websiteId: this.websiteId,
            dropConfig: {
                websiteId: this.websiteId,
                ddGroup: CMS.config.ddGroups.modules,
                Class: CMS.structureEditor.TemplateUnitTreeDropZone
            },
            listeners: {
                startdrag: this.onTreeStartDrag,
                movenode: this.onMoveNode,
                append: this.onTreeChanged,
                remove: this.onTreeChanged,
                insert: this.onTreeChanged,
                replace: this.onTreeChanged,
                update: this.onTreeChanged,
                enddrag: this.onTreeEndDrag,
                beforeclick: this.onNodeClicked,
                nodeout: this.onNodeOut,
                afterdrop: function (node) {
                    // SBCMS-34 make sure new node is selected
                    this.fireEvent('CMSselectunit', { unit: node.id });
                },
                nodeover: this.onNodeOver,
                scope: this
            }
        }];

        this.templateTitle = null;
        CMS.structureEditor.TemplateStructureEditor.superclass.initComponent.apply(this, arguments);

        // calculate height every time the menu opens
        var setModuleSelectionHeight = function (menu) {
            menu.get(0).setHeight(window.innerHeight - 200);
        };

        this.topToolbar.add('->');
        this.addModulesAndSnippetsButtons = this.topToolbar.add([{
            xtype: 'button',
            iconCls: 'add',
            cls: 'CMSbtnmedium',
            text: CMS.i18n(null, 'templateStructureEditor.addModuleText'),
            scope: this,
            menu: {
                cls: 'CMStemplatemodulemenu',
                listeners: {
                    beforeshow: setModuleSelectionHeight
                },
                items: [{
                    xtype: 'CMStemplatemoduleselection',
                    websiteId: this.websiteId,
                    mode: 'modules',
                    width: 252,
                    listeners: {
                        startdrag: this.onGridStartDrag,
                        enddrag: this.onGridEndDrag,
                        CMSinsertbyclick: this.onInsertByClick,
                        scope: this
                    }
                }
                ]
            }
        }, {
            xtype: 'button',
            iconCls: 'addExtension',
            cls: 'CMSbtnmedium',
            text: CMS.i18n(null, 'templateStructureEditor.addExtensionText'),
            scope: this,
            menu: {
                cls: 'CMStemplatemodulemenu',
                listeners: {
                    beforeshow: setModuleSelectionHeight
                },
                items: [{
                    xtype: 'CMStemplatemoduleselection',
                    websiteId: this.websiteId,
                    mode: 'extensions',
                    width: 220,
                    listeners: {
                        startdrag: this.onGridStartDrag,
                        enddrag: this.onGridEndDrag,
                        CMSinsertbyclick: this.onInsertByClick,
                        scope: this
                    }
                }]
            }
        }, {
            xtype: 'button',
            iconCls: 'createTemplateSnippet',
            cls: 'CMSbtnmedium',
            tooltip: {
                text: CMS.i18n(null, 'templateStructureEditor.addSnippetTooltip'),
                align: 'b-t?'
            },
            scope: this,
            menu: {
                cls: 'CMStemplatemodulemenu',
                listeners: {
                    beforeshow: setModuleSelectionHeight
                },
                items: [{
                    xtype: 'CMStemplatemoduleselection',
                    websiteId: this.websiteId,
                    mode: 'snippets',
                    width: 220,
                    listeners: {
                        startdrag: this.onGridStartDrag,
                        enddrag: this.onGridEndDrag,
                        CMSinsertbyclick: this.onInsertByClick,
                        scope: this
                    }
                }]
            }
        }]);

        this.on('CMSshowPopovers', this.showPopover, this);

        // SBCMS-2149: remove rename editor when structureEditor is hidden; somehow the blur event doesn't work here
        this.on('beforehide', function () {
            this.dataTree.completeRename();
        }, this);
    },

    /**
     * Open Extension Menu
     */
    openExtensionMenu: function () {
        try {
            var addExtensionBtn = this.addModulesAndSnippetsButtons[1];
            addExtensionBtn.el.dom.click();
        } catch (e){}
    },

    /**
     * Shows the popover for add module/style/snippet once (stored in local storage)
     * @private
     */
    showPopover: function () {
        // Show popover only once
        if (window.localStorage) {
            var showCount = parseInt(window.localStorage.getItem('CMSpopoverAddModuleStyleSnippet'), 10) || 0;
            if (showCount === 0) {
                window.localStorage.setItem('CMSpopoverAddModuleStyleSnippet', 1);
                /*jshint nonew: false */
                new CMS.Popover({
                    target: this.topToolbar.getEl(),
                    title: CMS.i18n(null, 'templateStructureEditor.addPopover.title'),
                    html: CMS.i18n(null, 'templateStructureEditor.addPopover.text'),
                    anchorOffset: 10,
                    maxWidth: 420,
                    offsets: {
                        right: [0, 0],
                        left: [7, -8]
                    }
                });
            }
        }
    },

    /**
     * @overrides
     */
    onGridStartDrag: function (e) {
        this._closeModulesAndSnippetsMenus();
        CMS.structureEditor.TemplateStructureEditor.superclass.onGridStartDrag.apply(this, arguments);
    },

    /**
     * Insert a unit or a template snippet by click on the module/extensions/snippet grids inside the menus
     * @param data - event data
     * @private
     */
    onInsertByClick: function (data) {
        // find owner unit
        var ownerUnitId = this.getSelectedNode() ? this.getSelectedNode().id : null;
        var ownerUnit = ownerUnitId ? this.unitStore.getById(ownerUnitId) : this.unitStore.getAt(0);

        var unit;
        // prepare unit or snippet data
        if (data.unit) {
            unit = data.unit;
            // create unit if we got anything else (module record)
            if (!CMS.data.isUnitRecord(unit)) {
                // source record is a module -> create a new unit record
                unit = unit.createUnit();
            }
        } else if (data.tplsnippet) {
            unit = CMS.data.createUnitRecordFromTemplateSnippet(data.tplsnippet, this.websiteId);
        }

        // we have a valid unit and ownerUnit -> insert it
        if (unit && ownerUnit) {
            if (!this._insertUnitWithAutoPos(unit, ownerUnit)) {
                // show error if unit insert with auto pos fails
                CMS.Message.toast(
                    CMS.i18n(null, 'templateStructureEditor.insertByClick.failToast.title'),
                    CMS.i18n(null, 'templateStructureEditor.insertByClick.failToast.text')
                );
            }
        }

        this._closeModulesAndSnippetsMenus();
    },

    /**
     * Close all top menus
     * @private
     */
    _closeModulesAndSnippetsMenus: function () {
        // hide all menus
        Ext.each(this.addModulesAndSnippetsButtons, function (btn) {
            if (btn && btn.hideMenu) {
                btn.hideMenu();
            }
        });
    },

    /**
     * Inserts unit which is automatically positioned (inside if possible, below otherwise)
     * Also uses the parent as owner if current module could not be inserted.
     * @param {CMS.data.UnitRecord} unit - needs to be a unit record (not a module record!)
     * @param ownerUnit
     * @returns {Boolean} - weather the insert was successful or not
     * @private
     */
    _insertUnitWithAutoPos: function (unit, ownerUnit) {
        var insertUnitHelper = CMS.liveView.InsertUnitHelper.getInstance();
        var pos = null;
        do {
            pos = insertUnitHelper.getAutoPositionForTemplate(unit, ownerUnit);
        } while (pos === null && (ownerUnit = this.unitStore.getParentUnit(ownerUnit)));

        if (pos !== null) {
            var cfg = insertUnitHelper.getInsertObject(unit, pos, ownerUnit);
            this.insertUnit(cfg);
            return true;
        }
        return false;
    },

    /**
    * Inserts the specified unit into the tree of the template
    * @param {Object} cfg The object which describes the inserted unit.
    */
    insertUnit: function (cfg) {
        if (cfg && cfg.templateUnit) {
            var targetNode = cfg.parentUnit && this.dataTree.getNodeById(cfg.parentUnit.id);
            var id = CMS.app.UIDManager.getInstance().getId('unit');
            // generate a record for the new unit
            var record = new CMS.data.UnitRecord(Ext.applyIf({
                id: id
            }, SB.util.cloneObject(cfg.templateUnit.data)), id);
            record.websiteId = cfg.websiteId;
            record.data.name = cfg.name;

            this.dataTree.insertNodeFromRecord(record, targetNode, cfg.position);
            this.fireEvent('CMSselectunit', { unit: id });
        }
    },

    /**
     * Condition for the "paste" context menu option
     * @private
     */
    checkCanPaste: function (unitId) {
        var clipboardData = CMS.app.clipboard.get('TreeUnit' + this.websiteId);
        var id = clipboardData && clipboardData.moduleId;
        var unit = this.unitStore.getById(unitId);

        if (id && unit) {
            return unit.canInsertAsChildInMode(id, 'template') || unit.canInsertAsSiblingInMode(id, 'template');
        } else {
            return false;
        }
    },

    /**
     * Condition for the clone unit context menu option
     * @private
     */
    checkCanDuplicate: function (unitId) {
        var unit = this.unitStore.getById(unitId);
        var isRootModule = unit && CMS.data.isRootModuleRecord(unit.getModule());
        return unit && !isRootModule;
    },

    /**
     * Condition for the clone unit context menu option
     * @private
     */
    checkCanCopy: function (unitId) {
        var unit = this.unitStore.getById(unitId);
        var isRootModule = unit && CMS.data.isRootModuleRecord(unit.getModule());
        return unit && !isRootModule;
    },

    /**
     * Condition for the clone unit context menu option
     * @private
     */
    checkCanDelete: function (unitId) {
        var unit = this.unitStore.getById(unitId);
        var isRootModule = unit && CMS.data.isRootModuleRecord(unit.getModule());
        return unit && !isRootModule;
    },

    /**
     * Condition for the reset unit context menu option
     * @private
     * @param unitId
     * @returns {boolean}
     */
    checkCanReset: function (unitId) {
        var unit = this.unitStore.getById(unitId);
        return unit && CMS.config.debugMode;
    },

    /**
     * Condition for the create template snippet context menu option
     * @private
     */
    checkCanCreateTemplateSnippet: function (unitId) {
        var unit = this.unitStore.getById(unitId);
        var isRootModule = unit && CMS.data.isRootModuleRecord(unit.getModule());
        return unit && (!isRootModule || CMS.config.debugMode);
    },

    // overrides superclass to destroy resizer
    destroy: function () {
        if (this.resizer) {
            this.resizer.destroy();
            this.resizer = null;
        }

        CMS.structureEditor.TemplateStructureEditor.superclass.destroy.apply(this, arguments);
    }

});

Ext.reg('CMStemplatestructureeditor', CMS.structureEditor.TemplateStructureEditor);
