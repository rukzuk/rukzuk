Ext.ns('CMS.layout');

/**
 * WorkbenchPanel that contains an iframe.
 *
 * @class       CMS.layout.IframeWorkbenchPanel
 * @extends     CMS.layout.WorkbenchPanel
 */
CMS.layout.IframeWorkbenchPanel = Ext.extend(CMS.layout.WorkbenchPanel, {
    /** @lends CMS.layout.IframeWorkbenchPanel.prototype */

    /**
     * Record which contains all page/template data
     * @property record
     * @type Ext.data.Record
     */
    record: null,

    /**
     * Holds a reference to the unitStore
     * @property unitStore
     */
    unitStore: null,

    /**
     * Id of the current selected website
     * @property websiteId
     * @type String
     */
    websiteId: '',

    /**
     * e.G. template or page
     * @property mode
     * @type String
     */
    mode: '',

    /**
     * Stores whether the render event should be handled or not.
     * @property ignoreRenderEvent
     * @type Boolean
     */
    ignoreRenderEvent: false,

    /**
     * Holds a reference to the inline editor which will be initialized
     * if editingEnabled is set to true
     * @property inlineEditor
     * @type CMS.richTextEditor.InlineRichTextEditor
     */
    inlineEditor: null,

    bubbleEvents: ['CMSopenworkbench', 'CMScloseworkbench', 'CMScurrentpagenamechange'],

    /**
     * The xtype of the unit editor
     * @property editorXType
     * @type String
     */
    editorXType: null,

    initComponent: function () {
        this.initSideBarMode();
        this.idSuffix = this.record.id;
        var storeOptions = {
            disableLoad: true,
            idSuffix: this.idSuffix
        };
        this.unitStore = CMS.data.StoreManager.get('unit', this.websiteId, storeOptions);
        this.apiRichTextEditorConfigStore = CMS.data.StoreManager.get('RichTextEditorConfig', this.websiteId, storeOptions);

        /**
         * temporary cache for form field config data overrides used when get CMS.updateFormFieldConfig on a non-active unit
         * @type Object
         */
        this.formFieldOverrideCache = {};
        this.previewAreaId = Ext.id();

        Ext.apply(this, {
            panel: {
                xtype: 'CMSpreviewarea',
                flex: 1,
                id: this.previewAreaId,
                mode: this.mode,
                /**
                 * @property previewarea
                 * @type CMS.liveView.PreviewArea
                 */
                ref: 'previewarea',
                unitStore: this.unitStore,
                websiteId: this.websiteId,
                listeners: {
                    CMSview: this.handleView,
                    CMSshowqrcode: this.handleShowQrCode,
                    scope: this
                }
            },
            sideBarItems: this.buildSideBarItems(),
            sideBarButtons: this.buildSideBarButtons()
        });
        CMS.layout.IframeWorkbenchPanel.superclass.initComponent.call(this);

        //init inlineEditor
        this.buildInlineEditor();

        //Set up events
        this.on('CMShoverunit', this.hoverUnitHandler, this);
        this.on('CMSselectunit', this.selectUnitHandler, this);
        this.on('CMSselectsection', this.selectSectionHandler, this);
        this.on('CMSremoveunit', this.removeUnitHandler, this);
        this.on('CMSresetunit', this.resetUnitHandler, this);
        this.on('CMScopyunit', this.copyUnitHandler, this);
        this.on('CMSpasteunit', this.pasteUnitHandler, this);
        this.on('CMSduplicateunit', this.duplicateUnitHandler, this);
        this.on('CMSrender', this.renderHandler, this);
        this.on('CMSrefreshpage', this.refreshHandler, this);
        this.on('CMSinsertunit', this.insertUnitHandler, this);
        this.on('CMSmoveunit', this.moveUnitHandler, this);
        this.on('CMSrenameunit', this.setUnitName, this);
        this.on('CMSopeninsertwindow', this.openInsertWindowHandler, this);
        this.on('CMSresolutionchanged', this.handleResolutionChange);
        this.on('afterlayout', function () {
            this.previewarea.renderDocument({
                record: this.record
            });
        }, this, {
            single: true
        });

        // reload the page/template if e.g. the websites colorscheme or resolutions where changed
        this.mon(CMS.data.WebsiteStore.getInstance(), 'update', function () {
            this.refresh();
        }, this);


        // reload if media db items are replace (mediadb changed)
        var mediaStore = CMS.data.StoreManager.get('media', this.websiteId, { disableLoad: true });
        this.mon(mediaStore, 'CMSmediachanged', function () {
            this.refresh();
        }, this);

        // reload if websitesettings are changed
        var websiteSettingsStore = CMS.data.StoreManager.get('websiteSettings', this.websiteId, { disableLoad: true });
        this.mon(websiteSettingsStore, 'update', function () {
            this.refresh();
        }, this);


        this.mon(this.structureEditor, 'treechanged', function () {
            this.unitEditor.onStructureChange();
        }, this);
    },

    /**
     * Applies the given RichTextEditor config object to the default config
     * set in the module of the unit for a specific section of a unit
     *
     * @param {String} unitId The id of the unit
     * @param {String} section The name of the section
     * @param {Object} config The RichTextEditor config object which will be
     *   merged with the default config
     */
    applyRichTextEditorConfig: function (unitId, section, config) {
        var store = this.apiRichTextEditorConfigStore;
        var id = unitId + '_' + section;
        var record = store.getById(id);

        if (record) {
            record.set('config', config);
        } else {
            record = new CMS.data.RichTextEditorConfigRecord({
                config: config
            }, id);
            store.add(record);
        }

        if (this.inlineEditor) {
            this.inlineEditor.saveAndRemoveEditor();
        }
    },

    /**
     * Select first editable node on start
     * @private
     */
    afterRender: function () {
        CMS.layout.IframeWorkbenchPanel.superclass.afterRender.apply(this, arguments);
        this.structureEditor.dataTree.openRecord(this.record, true);

        //Select 'BaseModule'
        var root = this.structureEditor.dataTree.getRootNode();
        var baseModuleNode = root.childNodes[0];
        var firstEditableNode = root.findChildBy(function (node) {
            var unit = this.unitStore.getById(node.id);
            return (unit && (unit.isEditableInMode(this.mode) || unit.hasInsertableChildrenInMode(this.mode)));
        }, this, true) || baseModuleNode;

        if (firstEditableNode) {
            this.selectUnit({
                unit: firstEditableNode.id
            });
        }
    },

    /**
     * Generates all required sidebarButtons
     * @private
     */
    buildSideBarButtons: function () {
        var result = [{
            xtype: 'button',
            cls: 'CMSbuttontogglenohighlight',
            tooltip: {
                text: CMS.i18n(null, 'iframeWorkbenchPanel.sidebarButtons.toggleMode'),
                align: 't-b?'
            },
            enableToggle: true,
            pressed: this.sideBarMode === 'two',
            iconCls: 'expandSidebar',
            scope: this,
            handler: this.toggleSideBarMode
        }, {
            iconCls: 'refresh',
            tooltip: {
                text: CMS.i18n('Vorschau neu rendern'),
                align: 't-b?'
            },
            handler: this.handleRefresh,
            scope: this
        }, {
            xtype: 'button',
            tooltip: {
                text: CMS.i18n(null, 'iframeWorkbenchPanel.sidebarButtons.resetToLastVersion'),
                align: 't-b?'
            },
            iconCls: 'restore',
            scope: this,
            handler: this.onRestore
        }, '->', {
            xtype: 'button',
            tooltip: {
                text: CMS.i18n('Speichern'),
                align: 't-b?'
            },
            cls: 'primary',
            iconCls: 'save',
            scope: this,
            handler: this.onSave
        }];
        Ext.each(result, function (btn) {
            btn.iconCls = btn.iconCls + ' ' + btn.iconCls + this.mode;
        }, this);
        return result;
    },

    /**
     * Generates all required sidebarItems
     * @protected
     */
    buildSideBarItems: function () {
        var sideBarItems = {
            /**
             * The side bar panel containing the structure tree and the
             * unit editor
             * @name sidebarMainPanel
             * @type Ext.Panel
             * @memberOf CMS.layout.IframeWorkbenchPanel
             * @property
             */
            layout: 'border',
            layoutConfig: {
                align: 'stretch',
                pack: 'start'
            },
            cls: 'CMSworkbenchmainpanel',
            ref: '../../sidebarMainPanel',
            flex: 1,
            border: false,
            items: [Ext.apply(this.buildStructureEditor(), {
                /**
                 * The structure tree panel
                 * @name structureEditor
                 * @type CMS.structureEditor.StructureEditor
                 * @memberOf CMS.layout.IframeWorkbenchPanel
                 * @property
                 */
                cls: 'CMSstructureeditor',
                ref: '../../../structureEditor',
                pageOrTemplateId: this.record.id,
                layout: 'fit',
                minHeight: 150,
                region: 'center',
                header: false,
                border: false,
                listeners: {
                    CMSunitstartdrag: this.unitStartDragHandler,
                    CMSunitenddrag: this.unitEndDragHandler,
                    scope: this
                }
            }), {
                /**
                 * Unit Editor Wrapper for the south region
                 * @name unitEditorSouthWrapper
                 * @type Ext.Panel
                 * @memberOf CMS.layout.IframeWorkbenchPanel
                 * @property
                 */
                ref: '../../../unitEditorSouthWrapper',
                layout: 'fit',
                region: 'south',
                split: true,
                hidden: true,
                height: this.getUnitEditorHeight(),
                listeners: {
                    resize: function (panel, width, height) {
                        if (window.localStorage) {
                            window.localStorage.setItem('CMSuniteditorheight', height);
                        }
                    },
                    scope: this
                },
                items: [
                    /* empty */
                ]
            }, {
                /**
                 * Unit Editor Wrapper for the est region
                 * @name unitEditorEastWrapper
                 * @type Ext.Panel
                 * @memberOf CMS.layout.IframeWorkbenchPanel
                 * @property
                 */
                ref: '../../../unitEditorEastWrapper',
                cls: 'CMSuniteditoreastwrapper',
                layout: 'fit',
                width: 340,
                hidden: true,
                region: 'east',
                items: [
                    /* empty */
                ]
            }]
        };
        this.addUnitEditor(sideBarItems);
        return sideBarItems;
    },

    getUnitEditorHeight: function () {
        return (window.localStorage && parseInt(window.localStorage.getItem('CMSuniteditorheight'), 10))
            || (window.innerHeight - 400);
    },

    /**
     * Build initial configuration for sidebar (unit editor)
     * @param sideBarItems
     * @private
     */
    addUnitEditor: function (sideBarItems) {
        var unitEditor = Ext.apply(this.buildUnitEditor(), {
            /**
             * The side bar panel containing the structure tree and the
             * unit editor
             * @name unitEditor
             * @type CMS.unitEditor.UnitEditor
             * @memberOf CMS.layout.IframeWorkbenchPanel
             * @property
             */
            cls: 'CMSuniteditor',
            ref: '../../../../unitEditor',
            layout: 'fit',
            header: false,
            border: false
        });

        var target = this.sideBarMode === 'one' ? sideBarItems.items[1] : sideBarItems.items[2];
        target.items.push(unitEditor);
        target.hidden = false;
    },


    /**
     * Toggle Sidebar Mode (one / two columns).
     * May only work for rendered elements.
     * @see WorkbenchPanel
     * @protected
     */
    toggleSideBarMode: function () {

        var isOneCol = this.sideBarMode === 'one';

        var wrapperRemove = isOneCol ? this.unitEditorSouthWrapper : this.unitEditorEastWrapper;
        var wrapperAdd = isOneCol ? this.unitEditorEastWrapper : this.unitEditorSouthWrapper;

        var items = wrapperRemove.removeAll(false);
        wrapperRemove.hide();
        wrapperAdd.add(items);
        wrapperAdd.show();


        // update current mode
        this.sideBarMode = isOneCol ? 'two' : 'one';
        if (window.localStorage) {
            window.localStorage.setItem('CMSsidebarmode', this.sideBarMode);
        }
        // update size
        CMS.layout.IframeWorkbenchPanel.superclass.toggleSideBarMode.call(this);

        // do layout
        wrapperRemove.doLayout();
        wrapperAdd.doLayout();
    },

    /**
     * Initializes the inline editor
     * @private
     */
    buildInlineEditor: function () {
        if (!CMS.config.insiteEditingEnabled) {
            return;
        }
        console.log('[IframeWorkbenchPanel] Creating InlineEditor');

        this.inlineEditor = new CMS.richTextEditor.InlineRichTextEditor({
            frame: this.previewarea.iframe,
            toolbarTarget: '#' + this.previewAreaId,
            websiteId: this.websiteId,
            pageOrTemplateId: this.record.id
        });

        this.mon(this.inlineEditor, 'CMSinlinesectionchanged', this.handleInlineSectionChanged, this);
        this.mon(this.inlineEditor, 'CMSinlinesectionblurred', this.handleInlineSectionBlurred, this);
    },

    /**
     * Shows a confirm dialog and reverts all unsaved changes
     * if the user presses ok
     * @private
     */
    onRestore: function () {
        Ext.MessageBox.confirm(CMS.i18n('Änderungen verwerfen?'), CMS.i18n('Änderungen wirklich verwerfen?'), function (btnId) {
            if (btnId == 'yes') {
                this.restore();
            }
        }, this);
    },


    /**
     * Reloads the page structure tree and the preview iframe
     * This method is called when the user clicks on the reload button
     * @private
     */
    restore: function (action) {
        CMS.app.trafficManager.sendRequest({
            action: action,
            data: {
                id: this.record.id,
                websiteId: this.websiteId
            },
            successCondition: 'data',
            success: function (response) {
                //Reset dirty state
                this.unitStore.isDirty = false;

                //save current selection before replacing content
                var prevSelection = this.getCurrentSelection();

                this.record = new CMS.data.PageRecord(response.data, response.data.id);
                this.structureEditor.dataTree.openRecord(this.record, true);
                this.previewarea.renderDocument({
                    record: this.structureEditor.dataTree.generatePreview()
                });

                //Restore previous selection
                this.restoreSelection(prevSelection);
            },
            failureTitle: (this.mode == 'template') ? CMS.i18n('Fehler beim Öffnen des Templates') : CMS.i18n('Fehler beim Öffnen der Page'),
            scope: this
        });
    },


    /**
     * Destroys the created unitstore object
     * @private
     */
    destroy: function () {
        if (this.inlineEditor) {
            this.inlineEditor.destroy();
            this.inlineEditor = null;
        }

        // RZ-1215: reload template store on destroy to get current content,
        //          otherwise the duplicate action will contain the changes
        if (this.mode == 'template') {
            CMS.data.StoreManager.get('template', this.websiteId).reload();
        }

        Ext.destroy(this.unitStore);
        Ext.destroy(this.apiRichTextEditorConfigStore);

        CMS.layout.IframeWorkbenchPanel.superclass.destroy.call(this, arguments);

        this.record = null;
        this.unitStore = null;
        this.apiRichTextEditorConfigStore = null;
        this.formFieldOverrideCache = null;
    },

    /**
     * Called when the user clicks the save button
     * @private
     */
    onSave: function () {
        this.save();
    },

    /**
     * Handler for the CMSselect event from PreviewArea and PageEditor
     * @private
     */
    selectUnitHandler: function (options) {
        this.selectUnit(options);
    },

    /**
     * Selects the specified unit.
     * @param {Object} options The unit id and the section element
     * An object containing the properties <ul>
     <li><tt>unit</tt>: The {@link CMS.data.UnitRecord} to be selected, or its id as a <tt>String</tt></li>
     <li><tt>section</tt>: A <tt>String</tt> The name of the selected section (optional)</li>
     <li><tt>callback</tt>: A <tt>Function</tt> The callback function which is executed after the unit is selected (optional)</li>
     </ul>
     */
    selectUnit: function (options) {
        var unit = options.unit;
        var section = options.section;
        var callback = options.callback;

        if (typeof unit == 'string') {
            unit = this.unitStore.getById(unit);
        }

        if (unit == this.selectedUnit) {
            return;
        }

        if (this.inlineEditor) {
            this.inlineEditor.saveAndRemoveEditor();
        }

        this.selectedUnit = unit;
        this.structureEditor.selectUnit(unit);
        this.previewarea.selectUnit(unit);

        if (unit) {
            // dirty HACK to enforce the blur events of the GeneratedFormPanel before it is distroyed (SBCMS-254)
            var activeElement = document.activeElement;
            if (activeElement) {
                console.log('[IframeWorkbenchPanel] blur activeElement', activeElement);
                activeElement.blur();
            }

            this.unitEditor.enable(unit, section, this.formFieldOverrideCache[unit.id]);
            if (callback) {
                callback();
            }
            this.fireEvent('CMSunitselected', unit);
        } else {
            this.unitEditor.clearEditor();
            if (callback) {
                callback();
            }
        }
        this.selectedSection = null;
    },

    /**
     * Handles the CMScopyunit event and calls the copyUnit method
     * of the structure editor
     * @private
     * @param {String} unitId The id of the unit which has been copied
     */
    copyUnitHandler: function (unitId) {
        this.structureEditor.copyUnit(unitId);
    },

    /**
     * Handles the CMSpasteunit event and calls the pasteUnit method
     * of the structure editor
     * @private
     * @param {String} unitId The id of the unit where clipboard content should be pasted to.
     */
    pasteUnitHandler: function (unitId) {
        this.structureEditor.pasteUnit(unitId);
    },

    /**
     * Handles the CMSremoveunit event
     * @private
     * @param {Ext.tree.TreeNode|String} node The tree node to be removed, or its id
     */
    removeUnitHandler: function (node) {
        this.removeUnit(node, true);
    },

    /**
     * Handles the CMSresetunit event
     * @private
     * @param {Ext.tree.TreeNode|String} node The tree node to be removed, or its id
     */
    resetUnitHandler: function (node) {
        this.resetUnit(node, true);
    },

    /**
     * Handles the CMSduplicateunit event and calls the duplicateUnit
     * method of the structure editor
     * @private
     * @param {String} unitId The id of the unit which has been duplicated
     */
    duplicateUnitHandler: function (unitId) {
        this.duplicateUnit(unitId);
    },


    /**
     * Handles the CMSinsertunit event and calls the insertUnit method
     * of the structure editor
     * @private
     * @param {Object} cfg The object which describes the unit which is inserted
     */
    insertUnitHandler: function (cfg) {
        this.insertUnit(cfg);
    },

    /**
     * Removes the specified unit
     * @param {Ext.tree.TreeNode|String} node The tree node to be removed, or its id
     * @param {Boolean} showConfirmation Whether the CMS should
     * ask the user for confirmation before removing the unit
     * @param {Boolean} callback (Optional) The function
     * which will be executed when the unit is actually deleted
     * or not
     * @param {Boolean} scope (Optional) The object in whose
     * scope the callback function should be executed.
     */
    removeUnit: function (node, showConfirmation, callback, scope) {
        this.structureEditor.removeUnit(node, showConfirmation, callback, scope);
    },

    /**
     * Reset Unit formValues to module defaults
     * @param node
     * @param showConfirmation
     * @param [callback]
     * @param [scope]
     */
    resetUnit: function (node, showConfirmation, callback, scope) {
        this.structureEditor.resetUnit(node, showConfirmation, callback, scope);
    },

    /**
     * Moves the unit up or down inside the tree.
     * @param {Object} cfg The object which describes the unit which is moved
     * @return {Boolean} Whether the unit could successfully be moved or not
     */
    moveUnit: function (cfg) {
        return this.structureEditor.moveUnit(cfg);
    },

    /**
     * Inserts the given unit into the tree and frame
     * @param {Object} cfg The object which describes the unit which is inserted
     */
    insertUnit: function (cfg) {
        this.structureEditor.insertUnit(cfg);
    },

    /**
     * Triggers the duplication of the given unit
     * @param {String} unitId The id of the unit which should be duplicated
     * @param {String} newName (optional) The name of the cloned unit
     */
    duplicateUnit: function (unitId, newName) {
        if (newName) {
            this.structureEditor.duplicateUnit(unitId, this.mode, {
                name: newName
            });
        } else {
            this.structureEditor.duplicateUnit(unitId, this.mode);
        }
    },

    /**
     * Handles the CMSmoveunit event and calls the moveUnit method of
     * the structure editor
     * @param {Object} cfg The object which describes the unit which is moved
     */
    moveUnitHandler: function (cfg) {
        this.moveUnit(cfg);
    },

    /**
     * Handler for the CMSselectSection event from PreviewArea and PageEditor
     * @private
     */
    selectSectionHandler: function (options) {
        var unit = options.unit;
        var section = options.section;

        var run = (section != this.selectedSection);
        if (run) {
            this.selectedSection = section;
            if (this.inlineEditor) {
                this.inlineEditor.saveAndRemoveEditor();
                this.inlineEditor.initEditorForSection(unit, section);
            }
        }
    },

    /**
     * Handles the CMSrender event
     * @private
     * @param {Object} cfg The preview which is to be rendered.
     */
    renderHandler: function (cfg) {
        console.log('[IframeWorkbenchPanel] renderHandler, id: ' + this.getId());

        if (this.ignoreRenderEvent) {
            console.log('[IframeWorkbenchPanel] Not rendering now.');
        } else {

            // TODO: if this method is called multiple times with multiple cfg.unitIds, during CMS.config.renderBuffer, only the last unit will be replaced

            // currently running (delayed) render task is an replaceAll render -> force replaceAll mode for next
            if (this.renderTaskIsReplaceAll) {
                cfg.unitId = null;
            }

            var renderFn = function () {
                if (this.inlineEditor) {
                    this.inlineEditor.saveAndRemoveEditor();
                }

                this.previewarea.renderDocument(cfg);

                // reset the replaceAll flag
                this.renderTaskIsReplaceAll = false;
            };

            // delayed rendering
            this.renderTask = this.renderTask || new Ext.util.DelayedTask(null, this);
            this.renderTask.delay(CMS.config.renderBuffer, renderFn);


            // remember that this was a replaceAll render
            if (!cfg.unitId) {
                this.renderTaskIsReplaceAll = true;
            }
        }
        // reset ignoreRenderEvent
        this.ignoreRenderEvent = false;
    },

    /**
     * Tells the IframeWorkbenchPanel to ignore the next CMSrender event.
     */
    preventRendering: function () {
        console.log('[IframeWorkbenchPanel] Will ignore next renderHandler call');
        this.ignoreRenderEvent = true;
    },

    /**
     * Resets the flag set by {@link #preventRendering}, so the next CMSrender event will be processed as normal.
     */
    resetRendering: function () {
        //console.debug('[IframeWorkbenchPanel] Will handle next renderHandler call.');
        this.ignoreRenderEvent = false;
    },

    /**
     * Handles node hover
     * @private
     */
    hoverUnitHandler: function (id) {
        var run = (id != this.hoveredId);
        this.hoveredId = id;
        if (run) {
            this.structureEditor.outlineUnit(id);
            this.previewarea.outlineUnit(id);
        }
    },

    /**
     * Restores the given unit selection
     * @private
     */
    restoreSelection: function (selection) {
        this.resetSelectedUnit();

        if (selection && selection.id) {
            var selectedNode = this.unitStore.getById(selection.id);
            if (selectedNode) {
                this.selectUnitHandler({
                    unit: selectedNode
                });
                return;
            }

            if (selection.prev) {
                var prevNode = this.unitStore.getById(selection.prev);
                if (prevNode) {
                    this.selectUnitHandler({
                        unit: prevNode
                    });
                    return;
                }
            }

            if (selection.next) {
                var nextNode = this.unitStore.getById(selection.next);
                if (nextNode) {
                    this.selectUnitHandler({
                        unit: nextNode
                    });
                    return;
                }
            }

            if (selection.parent) {
                var parentNode = this.unitStore.getById(selection.parent);
                if (parentNode) {
                    this.selectUnitHandler({
                        unit: parentNode
                    });
                    return;
                }
            }
        }

        //Select 'BaseModule'
        var baseModuleNode = this.structureEditor.dataTree.root.childNodes[0];
        if (baseModuleNode) {
            this.selectUnitHandler({
                unit: baseModuleNode.id
            });
            return;
        }

        //No unit available to select
        this.selectUnitHandler({
            unit: null
        });
        this.unitEditor.hide();
    },


    /**
     * Returns the current Unit selection including child/parent/prev/next unit
     * @private
     */
    getCurrentSelection: function () {
        //Store curr, parent, prev, next
        var selection;

        //Store Node references to restore Selection
        var selectedNode = this.structureEditor.getSelectedNode();

        if (selectedNode) {
            selection = {};

            selection.id = selectedNode.id;

            var nextNode = selectedNode.nextSibling;
            if (nextNode) {
                selection.next = nextNode.id;
            }

            var prevNode = selectedNode.previousSibling;
            if (prevNode) {
                selection.prev = prevNode.id;
            }

            var parentNode = selectedNode.parentNode;
            if (parentNode) {
                selection.parent = parentNode.id;
            }
        }

        return selection;
    },

    /**
     * Resets the stored selectedUnitIds to allow selecting the same unit again
     * @private
     */
    resetSelectedUnit: function () {
        this.selectedUnit = null;
        this.selectedSection = null;
        this.structureEditor.selectedUnitId = null;
        this.previewarea.selectedId = null;
    },

    /**
     * Destroy this panel if no unsaved changes are present
     * In case of unsaved changes, present a confirmation dialog to the user.
     * @param {Object} cfg
     */
    conditionalDestroy: function (cfg) {
        var type = this.mode === 'template' ? CMS.i18n('Template') : CMS.i18n('Page');

        var destroy = (function () {
            this.destroy();
            if (cfg && cfg.success) {
                cfg.success.call(cfg.scope || window);
            }
        }).createDelegate(this);


        if (this.isDirty()) {
            Ext.MessageBox.show({
                closable: false,
                title: CMS.i18n('Änderungen speichern?'),
                msg: CMS.i18n('Ungespeicherte Änderungen in {type} „{name}“ speichern?').replace('{name}', this.record.get('name')).replace('{type}', type),
                buttons: {
                    yes: true,
                    no: true,
                    cancel: true
                },
                icon: Ext.MessageBox.WARNING,
                fn: function (btnId) {
                    if (btnId === 'yes') {
                        this.save(destroy);
                    } else if (btnId === 'no') {
                        destroy();
                    }
                },
                scope: this
            });
        } else {
            destroy();
        }

    },


    /**
     * Called when the panel retrieves the CMSrefresh Event
     * @private
     */
    refreshHandler: function () {
        this.refresh();
    },

    /**
     * Triggers the CMSrender event which will refresh the page
     * or the unit if a unit id has been specified.
     * @param {String} [unitId] The unit
     */
    refresh: function (unitId) {
        var cfg = {
            record: this.structureEditor.dataTree.generatePreview()
        };
        if (unitId) {
            Ext.apply(cfg, {
                unitId: unitId
            });
        }
        this.fireEvent('CMSrender', cfg);
    },

    /**
     * Set a unit's formValue without entailing re-rendering.
     * @param {String|CMS.data.UnitRecord} unit The unit to be changed
     * @param {String} key The name of the form variable to be changed
     * @param {Mixed} value The new value to be stored
     * @return {Boolean} Whether the key value pair could be
     * successfully set.
     */
    silentlySetValue: function (unit, key, value) {
        if (typeof unit == 'string') {
            unit = this.unitStore.getById(unit);
        }

        var formValues = unit.get('formValues');

        // update unit if allowed
        if (key in formValues || key in unit.getModule().get('formValues')) {
            if (unit.isFormValueEditable(key, this.mode)) {
                //HACK SBCMS-731: if unit has no own formValues, the module formValues have to be copied
                if (SB.util.isEmptyObject(formValues)) {
                    Ext.apply(formValues, SB.util.cloneObject(unit.getModule().get('formValues')));
                }
                formValues[key] = value;
            } else {
                console.warn(key, 'is not editable in unit', unit.id);
            }
        } else {
            console.warn('Property', key, 'does not exist in unit ', unit.id);
            return false;
        }
        this.unitStore.isDirty = true;

        // update GUI (if active)
        if (unit == this.unitEditor.currentUnit) {
            this.unitEditor.updateField(key, value);
        }
        return true;
    },

    /**
     * Update a form field config of a unit
     * @param {String|CMS.data.UnitRecord} unit The unit to be changed
     * @param {String} key The name of the form field to be changed
     * @param {Object} config The new value to be stored
     */
    updateFormFieldConfig: function (unit, key, config) {
        if (typeof unit == 'string') {
            unit = this.unitStore.getById(unit);
        }

        // filter config according to white-list
        var filteredConfig = {};
        Ext.iterate(config, function (key, value) {
            if (CMS.config.allowedUnitFormFieldParamsApiOverride.indexOf(key) > -1) {
                // disallow undefined values
                // TODO: check 'type' of config values (needs to be configured)
                if (value !== undefined) {
                    filteredConfig[key] = value;
                } else {
                    console.warn('[IframeWorkbenchPanel] updateFormFieldConfig - param "' + key + '" value is undefined');
                }
            } else {
                console.warn('[IframeWorkbenchPanel] updateFormFieldConfig - param "' + key + '" in config is not allowed to be changed');
            }
        });

        // save changed params for later use (display of unitEditor)
        this.formFieldOverrideCache[unit.id] = this.formFieldOverrideCache[unit.id] || {};
        // merge possibly existing field override cache for this key
        this.formFieldOverrideCache[unit.id][key] = Ext.apply(this.formFieldOverrideCache[unit.id][key] || {}, filteredConfig);

        // update GUI (if active)
        if (unit == this.unitEditor.currentUnit) {
            // GUI is rendered?
            if (this.unitEditor.rendered) {
                console.log('[IframeWorkbenchPanel] updateFormFieldConfig this.unitEditor.rendered = true');
                this.unitEditor.updateFieldConfig(key, filteredConfig);
            } else {
                console.log('[IframeWorkbenchPanel] updateFormFieldConfig this.unitEditor.rendered = false, register on afterrender');
                this.unitEditor.on('afterrender', function () {
                    this.unitEditor.updateFieldConfig(key, filteredConfig);
                }, this, {'single': true});
            }
        }
    },

    /**
     * Updates the specified unit's name
     * @param {String} unitId The id of the unit
     * @param {String} name The new name of the unit
     * @return {Boolean} Whether the name could be successfully updated
     */
    setUnitName: function (unitId, name) {
        var unit = this.unitStore.getById(unitId);
        if (unit && unit.get('name') !== name) {
            unit.set('name', name);
            if (unit == this.unitEditor.currentUnit) {
                this.unitEditor.setUnitName(name);
            }
            this.unitStore.isDirty = true;
        }
    },

    /**
     * Tells the unit editor to open the "Insert unit" window.
     * @param {CMS.data.UnitRecord} unit (Optional) The unit
     * in whose context the insert window should be opened
     * @param {Integer} position (optional) The default position choice in the dialog.
     * Possible values:<ul>
     <li>-1 - above</li>
     <li> 0 - inside</li>
     <li> 1 - below (default)</li>
     </ul>
     */
    openInsertWindow: function (unit, position) {
        this.unitEditor.openInsertWindow(unit, position);
    },

    /**
     * Opens the Insert Extensions (Styles) Menu
     */
    openExtensionMenu: function () {
        if (this.structureEditor.openExtensionMenu) {
            this.structureEditor.openExtensionMenu();
        }
    },

    /**
     * Opens the form group which corresponds to the given
     * form group id. If no argument is passed, it will just bring the form panel to front.
     * @param {String} formGroupId (optional) The UUID of a form group
     */
    openFormPanel: function (formGroupId) {
        /* Do nothing, as the Form Panel is always visible */
        if (Ext.isDefined(formGroupId)) {
            this.unitEditor.openFormPanel(formGroupId);
        }
    },

    /**
     * Brings the structure tree panel to front
     */
    openTreePanel: function () {
        /* Do nothing, as the Tree Panel is always visible */
    },

    /******************
     * Abstract Methods
     ******************/

    /**
     * checks if the current page/template has unsaved changes
     * @protected
     * @return {Boolean}
     */
    isDirty: function () {
        return (this.unitStore && this.unitStore.isDirty) || (this.inlineEditor && this.inlineEditor.editor && this.inlineEditor.editor.isDirty());
    },

    /**
     * Called when the user clicks the save button
     * MUST BE EXTENDED (!) by the subclass (Define params before calling method)
     * @private
     */
    save: function (record, params, action, callback, silent) {
        if (this.inlineEditor) {
            // sometimes there is no blur from the RTE and changes are not written to the JSON (SBCMS-1850)
            this.inlineEditor.saveAndRemoveEditor();
        }

        CMS.app.trafficManager.sendRequest({
            modal: !silent,
            action: action,
            data: params,
            scope: this,
            success: function (data) {
                //Reset dirty state
                this.unitStore.isDirty = false;
                
                if (typeof(callback) === typeof(Function)) {
                    callback.call(this, data);
                }
                if (!silent) {
                    CMS.Message.toast(CMS.i18n('Hinweis'), CMS.i18n('Speichern erfolgreich.'));
                }
            },
            failureTitle: CMS.i18n('Fehler beim Speichern.')
        });
    },

    /**
     * Synchronizes the content value of the RTE with the UnitStore and the field in the UnitEditor.
     * Will get called when the InlineRichTextEditor fires the event CMSinlinesectionchanged.
     * @private
     * @param {CMS.data.UnitRecord} unit
     * @param {String} section
     * @param {String} content
     */
    handleInlineSectionChanged: function (unit, section, content) {
        if (!unit || !section) {
            CMS.console.warn('[IframeWorkbenchPanel] handleInlineSectionChanged: No unit or section was set!');
            return;
        }
        this.silentlySetValue(unit, section, content);
    },

    /**
     * Resets the selected section.
     * Will get called when the InlineRichTextEditor fires the event CMSinlinesectionblurred.
     * @private
     */
    handleInlineSectionBlurred: function () {
        this.selectedSection = null;
    },

    /**
     * Must be overwritten by a method which generates a Template-/PageEditor
     * @return {Object} Ext.Window config object
     */
    buildStructureEditor: function () {
        return {};
    },

    /**
     * Must be overwritten by a method which generates a Template-/PageUnitEditor
     * @return {Object} Ext.Component config object
     */
    buildUnitEditor: function () {
        return {
            xtype: this.editorXType,
            idSuffix: this.idSuffix,
            websiteId: this.websiteId
        };
    },

    /**
     * Handler for the 'refresh' button
     * @private
     */
    handleRefresh: function () {
        this.fireEvent('CMSrefreshpage');
    },

    /**
     * Handler for the "CMSopeninsertwindow" event
     * @param {String|Object} unit The parent unit or its id
     * @private
     */
    openInsertWindowHandler: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.unitStore.getById(unit);
        }
        this.openInsertWindow(unit);
    },

    /**
     * Convenience method to make sure the users saves the page/template before opening a preview
     * @private
     * @param {Function} callback Function which is going to be called after the user has saved all changes
     */
    forceSaveBeforePreview: function (callback) {
        if (this.isDirty()) {
            var type = this.mode === 'page' ? CMS.i18n('Page') : CMS.i18n('Template');
            var msg = CMS.i18n('Alle Änderungen müssen gespeichert werden um eine Vorschau anzeigen zu können.\nUngespeicherte Änderungen in {type} „{name}“ speichern?').replace('{name}', this.record.get('name')).replace('{type}', type);
            Ext.MessageBox.confirm(CMS.i18n('Änderungen speichern?'), msg, function (btnId) {
                    if (btnId === 'yes') {
                        this.save(callback.createDelegate(this), "", function (data) {
                            callback.call(data);
                        });
                    }
                }, this /* <- scope*/
            );
        } else {
            callback.call(this);
        }
    },
    
    /**
     * Handler for the "CMSview" event
     * @private
     */
    handleView: function () {
        var key = 'preview' + SB.util.ucFirst(this.mode) + 'ById';
        var cfg = {}, win, params, url;
        this.forceSaveBeforePreview(function () {
            cfg.websiteId = this.websiteId;
            cfg[this.mode + 'Id'] = this.record.id;
            params = CMS.app.trafficManager.createPostParams(Ext.apply(cfg, CMS.config.params[key]));
            url = Ext.urlAppend(CMS.config.urls[key], Ext.urlEncode(params));
            win = window.open();
            if (!win) {
                CMS.Message.info(CMS.i18n(null, "IframeWorkbenchPanel.preview.popUpBlockerInfo"));
            } else {
                win.location.href = url;
            }
        });
    },

    /**
     * Handler for the "CMSshowqrcode" button
     * @private
     */
    handleShowQrCode: function () {
        var params = {
            websiteId: this.websiteId,
            mode: this.mode,
            recordId: this.record.id
        };

        this.forceSaveBeforePreview(function () {
            (new CMS.QrCodeWindow(params)).show();
        });
    },

    /**
     * Updates the current resolution field which can be read by children via {@link #getCurrentResolution}
     * @param resolutionData
     * @private
     */
    handleResolutionChange: function (resolutionData, allResData) {
        this.currentResolution = resolutionData;
        this.resolutions = allResData;
    },

    /**
     * Get the current resolution
     * @returns {Object} current resolution data
     * @public
     */
    getCurrentResolution: function () {
        return this.currentResolution || CMS.config.theDefaultResolution;
    },

    /**
     * Get all configured resolutions
     * @returns Object[] all resolution data
     * @public
     */
    getAllResolutions: function () {
        return this.resolutions || [CMS.config.theDefaultResolution];
    },

    unitStartDragHandler: function () {
        this.previewarea.getEl().mask().addClass('CMSunitDragMask');
    },

    unitEndDragHandler: function () {
        this.previewarea.getEl().unmask();
    }

});

Ext.reg('CMSiframeworkbenchpanel', CMS.layout.IframeWorkbenchPanel);
