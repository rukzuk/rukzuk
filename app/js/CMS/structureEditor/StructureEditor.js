Ext.ns('CMS.structureEditor');

/**
 * The basic structure editor component to add/remove/rearrange the
 * units of template or a page
 *
 * @class CMS.structureEditor.StructureEditor
 * @extends Ext.Panel
 */
CMS.structureEditor.StructureEditor = Ext.extend(Ext.Panel, {
    /** @lends CMS.structureEditor.StructureEditor.prototype */

    layout: 'border',

    bubbleEvents: ['CMSrender', 'CMSopeneditor', 'CMSopenextensioninsertdialog', 'CMSselectunit', 'CMSinsertunit', 'CMShoverunit', 'CMSbeforemove', 'CMSbeforeremove', 'CMSafterremove', 'CMSunittreeselect', 'CMStreemouseenter', 'CMStreemouseout', 'CMSopeninsertwindow', 'CMSunitstartdrag', 'CMSunitenddrag', 'CMScurrentpagenamechange'],

    /**
     * The unitstore that holds the current units
     * @property unitStore
     * @type {CMS.data.UnitStore}
     */
    unitStore: null,

    /**
     * The currently opened website's id
     * @property websiteId
     * @type {String}
     */
    websiteId: '',

    /**
     * Holds a reference to the dataTree
     * @property dataTree
     * @type {CMS.structureEditor.UnitTreePanel}
     * @private
     */
    dataTree: null,

    initComponent: function () {

        this.tbar = {
            height: 28,
            items: [
                {
                    text: CMS.i18n('JSON anzeigen'),
                    cls: 'CMSbtnmedium',
                    scope: this,
                    hidden: !CMS.config.debugMode,
                    handler: function (btn) {
                        var tree = btn.findParentByType('panel').dataTree;
                        var win = new CMS.TextWindow({
                            object: tree.createJSON(),
                            modal: true
                        });

                        win.show();
                    }
                }
            ]
        };

        CMS.structureEditor.StructureEditor.superclass.initComponent.call(this);

        this.mon(this.unitStore, 'update', this.storeUpdateHandler, this);
        this.mon(this.dataTree.getSelectionModel(), 'selectionchange', function (selModel, node) {
            this.selectUnit(node);
        }, this);
    },

    /**
    * @private
    * Handler for the startdrag event that is fired by treepanel
    */
    onTreeStartDrag: function (tree, node) {
        console.log('[StructureEditor] startdrag', arguments);
        var record = node.getModule();
        if (!record) {
            throw 'Module for unit ' + node.id + ' not found';
        }
        this.dataTree.markDropTargets(record, node);

        this.fireEvent('CMSunitstartdrag');
    },

    /**
    * @private
    * Handler for the enddrag event that is fired by CMSpageunittreepanel
    */
    onTreeEndDrag: function (tree, node) {
        console.log('[StructureEditor] enddrag', arguments);
        this.dataTree.markDropTargets();

        this.fireEvent('CMSunitenddrag');
    },

    /**
    * @private
    * Handler for the startdrag event that is fired by CMSmodulegrid
    */
    onGridStartDrag: function (grid, records) {
        console.log('[StructureEditor] startdrag', arguments);
        if (records.length != 1) {
            throw 'Can\'t handle more than one dragged node';
        }

        var record = records[0];

        // get ModuleRecord if its a templateSnippet
        if (CMS.data.isTemplateSnippetRecord(record)) {
            var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);
            var moduleId = record.get('content')[0].moduleId;
            record = moduleStore.getById(moduleId);
        }

        this.dataTree.markDropTargets(record);
        this.dataTree.highlightWholeDropTarget(true);

        this.fireEvent('CMSunitstartdrag');
    },

    /**
    * @private
    * Handler for the enddrag event that is fired by CMSmodulegrid
    */
    onGridEndDrag: function (grid) {
        console.log('[StructureEditor] enddrag', arguments);
        this.dataTree.markDropTargets();
        this.dataTree.highlightWholeDropTarget(false);

        this.fireEvent('CMSunitenddrag');
    },


    /**
    * @private
    * Handler for the movenode event that is fired by CMSpageunittreepanel
    */
    onMoveNode: function (tree, node, oldParent, newParent, index) {
        var newPosition = this.computeSiblingIds({
            newParent: newParent,
            index: index
        });
        this.fireEvent('CMSbeforemove', {
            parentUnitId: newParent.id,
            unitId: node.id,
            index: index,
            previousSiblingId: newPosition.previousSiblingId,
            nextSiblingId: newPosition.nextSiblingId
        });
        tree.firingAdditionalEvents = false; // HACK: SBCMS-249
        if (oldParent == newParent) {
            this.onTreeChanged(tree, newParent);
        } else {
            this.onTreeChanged(tree);
        }

        //Reset selectedUnitId to allow re-selection of the moved unit
        this.selectedUnitId = null;
        this.selectUnit(node.id);
    },

    /**
    * @private
    * Computes the new ids of the previous and next siblings
    * and the new index of a node which is about moved.
    * @param {Object} pObject The parameter object holding
    * the knowing conditions
    * @return {Object} An object holding the ids of the
    * previous and next sibling and the new index of the node
    */
    computeSiblingIds: function (pObject) {
        var nextSiblingId;
        var previousSiblingId;
        var index;
        var parent;

        if (pObject.newParent && typeof pObject.index === 'number') {
            // We only know the new parent and
            // the new position in the childNodes
            // array of this parent and have
            // to compute the sibling ids
            index = pObject.index;
            parent = pObject.newParent;
            if (index === 0) {
                previousSiblingId = null;
                if (parent.childNodes.length > 1) {
                    nextSiblingId = parent.childNodes[1].id;
                } else {
                    nextSiblingId = null;
                }
            } else {
                if (index === parent.childNodes.length - 1) {
                    nextSiblingId = null;
                } else {
                    nextSiblingId = parent.childNodes[index + 1].id;
                }
                previousSiblingId = parent.childNodes[index - 1].id;
            }
        } else if (pObject.newParent && pObject.previousSibling) {
            // Here we know the parent, the direction (up) and
            // the old previous sibling and we have to compute
            // the new index and the new previous sibling
            parent = pObject.newParent;
            var previousSibling = pObject.previousSibling;
            index = parent.childNodes.indexOf(previousSibling);
            nextSiblingId = previousSibling.id;
            if (index === 0) {
                previousSiblingId = null;
            } else {
                previousSiblingId = parent.childNodes[index - 1].id;
            }

        } else if (pObject.newParent && pObject.nextSibling) {
            // Here we know the parent, the direction (down) and
            // the old next sibling and we have to compute
            // the new index and the new next sibling
            parent = pObject.newParent;
            var nextSibling = pObject.nextSibling;
            index = parent.childNodes.indexOf(nextSibling);
            previousSiblingId = nextSibling.id;
            if (index === parent.childNodes.length - 1) {
                nextSiblingId = null;
            } else {
                nextSiblingId = parent.childNodes[index + 1].id;
            }
        }
        return {
            nextSiblingId: nextSiblingId,
            previousSiblingId: previousSiblingId,
            index: index
        };
    },

    /**
    * @private
    * Handler for events fired by CMSpageunittreepanel, indicating a change in the tree contents
    */
    onTreeChanged: function (tree, node) {
        if (tree.firingAdditionalEvents) { // HACK: SBCMS-249
            return;
        }
        this.processDataChange(node ? node.id : null, true, true);
        this.fireEvent('treechanged', this);
    },

    /**
    * @private
    * Handler for 'update' event from {@link #unitStore}
    */
    storeUpdateHandler: function (store, record, operation) {
        if (operation == 'commit') {
            console.info('[StructureEditor] storeUpdateHandler operation == commit', record);

            var module = record.getModule();
            this.processDataChange(record.id, false, module && module.get('reRenderRequired'));

        } else if (operation == 'edit' && record.modified && Ext.isDefined(record.modified.name)) {
            var node = this.dataTree.getNodeById(record.id);
            if (node) {
                node.setText(record.getUIName());
            }
        }
    },

    /**
    * @private
    * Handle changes in unitStore and tree
    * @param {String} id (optional) The changed unit's id
    * @param {Boolean} forceRender (optional) <tt>true</tt> to force firing the render event,
    * even if no relevant changes are detected in the unitRecord
    * @param {Boolean} replaceAll (optional) <tt>true</tt> to replace the whole page instead of one unit's contents
    */
    processDataChange: function (id, forceRender, replaceAll) {
        var record;
        if (id) {
            record = this.unitStore.getById(id);
        }
        if (!record && !forceRender) {
            // e.g. record == root
            return;
        }
        this.unitStore.isDirty = true;
        if (forceRender || (record && record.hasUiAffectingChanges())) {
            console.log('[StructureEditor] firing event CMSrender unitId:', replaceAll ? null : id);

            this.fireEvent('CMSrender', {
                record: this.dataTree.generatePreview(),
                unitId: replaceAll ? null : id
            });
        }
        if (record) {
            //Remove modifiedUnitAttributes attributes
            delete record.modifiedUnitAttributes;
        }
    },

    /**
    * @private
    * Handler for node hover inside the tree
    * @param {Ext.tree.TreeNode} node The node
    * which is hovered over
    */
    onNodeOver: function (node) {
        this.fireEvent('CMShoverunit', node.id);
        this.fireEvent('CMStreemouseenter', node.id);
    },

    /**
    * @private
    * Handler for 'un-hovering' of a node
    * @param {Ext.tree.TreeNode} node The node
    * which is 'un-hovered'
    */
    onNodeOut: function (node) {
        this.fireEvent('CMStreemouseout', node.id);
    },

    /**
    * @private
    * Handler for node click inside the tree
    */
    onNodeClicked: function (node, e) {
        var del = false;
        var showExtensions = false;
        var showContextMenu = false;
        if (e && e.target) {
            del = Ext.fly(e.target).hasClass('CMSdelete');
            showExtensions = Ext.fly(e.target).hasClass('CMSshowExtensionUnits');
            showContextMenu = Ext.fly(e.target).hasClass('CMSshowContextMenu');
        }
        e.stopEvent();
        if (del) {
            this.removeUnit(node, true);
        } else if (showExtensions) {
            node.showExtensionUnits();
        } else if (showContextMenu) {
            node.fireEvent('contextMenu', node, e);
        } else {
            this.selectUnit(node.id);
            this.fireEvent('CMSunittreeselect', node.id);
        }
        //Cancel default action
        return false;
    },

    /**
    * Moves the specified unit up or down in tree structure
    * @param {Object} cfg The object which describes the unit which is moved
    * @return {Boolean} Whether the unit could successfully be moved or not
    */
    moveUnit: function (cfg) {
        var node = cfg && this.dataTree.getNodeById(cfg.unitId);
        if (!node) {
            return false;
        }
        this.dataTree.suspendEvents();
        var p = node.parentNode;
        var siblings = this.unitStore.getById(p.id).data.children;
        var index = p.indexOf(node);

        var newPosition;
        if (cfg.direction == 'up') {
            // move up
            if (node.isFirst() || node.previousSibling.isExtensionUnit() != node.isExtensionUnit()) {
                this.dataTree.resumeEvents();
                // node cannot be moved upwards
                return false;
            }

            newPosition = this.computeSiblingIds({
                newParent: p,
                previousSibling: node.previousSibling
            });
            this.fireEvent('CMSbeforemove', {
                parentUnitId: p.id,
                unitId: node.id,
                index: newPosition.index,
                previousSiblingId: newPosition.previousSiblingId,
                nextSiblingId: newPosition.nextSiblingId
            });
            p.insertBefore(node, node.previousSibling);
            // update store
            SB.util.moveArrayItem(siblings, index, -1);
        } else {
            // move down
            if (node.isLast() || node.nextSibling.isExtensionUnit() != node.isExtensionUnit()) {
                this.dataTree.resumeEvents();
                // node cannot be moved downwards
                return false;
            }

            newPosition = this.computeSiblingIds({
                newParent: p,
                nextSibling: node.nextSibling
            });
            this.fireEvent('CMSbeforemove', {
                parentUnitId: p.id,
                unitId: node.id,
                index: newPosition.index,
                previousSiblingId: newPosition.previousSiblingId,
                nextSiblingId: newPosition.nextSiblingId
            });
            p.insertBefore(node, node.nextSibling.nextSibling);
            // update store
            SB.util.moveArrayItem(siblings, index, 1);
        }

        this.dataTree.resumeEvents();
        this.processDataChange(p.id, true, true);
        //Reset selectedUnitId to allow re-selection of the moved unit
        this.selectedUnitId = null;
        this.selectUnit(node.id);
        return true;
    },


    /**
    * Outline a unit
    * @param {String} id The id of the unit to be outlined, or null to remove outlines
    */
    outlineUnit: function (id) {
        var node = this.dataTree.getNodeById(id);
        var evtModel = this.dataTree.eventModel;
        // hack to simulate a mouse move
        evtModel.onNodeOver(null, node);
    },


    /**
     * Select a unit, and fire the CMSselectunit event. If the unit is already selected, nothing happens
     * @param {CMS.data.UnitRecord|String} unit The unit to be selected
     */
    selectUnit: function (unit) {
        var id = (!unit || typeof unit == 'string') ? unit : unit.id;
        if (id == this.selectedUnitId) {
            return;
        }
        this.selectedUnitId = id;
        var node = this.dataTree.getNodeById(id);
        if (node) {
            this.dataTree.getSelectionModel().select(node);
            if (node.getOwnerTree().rendered) {
                node.ensureVisible();

                // if an extension unit was selected, show all extension units of the targetNode
                if (node.isExtensionUnit()) {
                    node.parentNode.showExtensionUnits(true);
                }
            }
            this.fireEvent('CMSselectunit', { unit: id });
        } else {
            this.dataTree.getSelectionModel().clearSelections();
            this.fireEvent('CMSselectunit', { unit: null });
        }
    },

    /**
     * Returns the current selected node
     */
    getSelectedNode: function () {
        return this.dataTree.getSelectionModel().getSelectedNode();
    },

    /**
    * Remove a unit
    * @param {Ext.tree.TreeNode|String} node The tree node to be removed, or its id
    * @param {Boolean} showConfirmation Whether the CMS should
    * ask the user for confirmation before removing the unit
    * @param {Function} callback (Optional) The function
    * which will be executed when the unit is actually deleted
    * or not
    * @param {Boolean} scope (Optional) The object in whose
    * scope the callback function should be executed.
    */
    removeUnit: function (node, showConfirmation, callback, scope) {
        var callbackFunc;
        var showConfrm;
        var callbackScope;
        if (Ext.isFunction(showConfirmation)) {
            callbackFunc = showConfirmation;
            showConfrm = false;
            if (Ext.isObject(callback)) {
                callbackScope = callback;
            }
        } else {
            if (Ext.isFunction(callback)) {
                callbackFunc = callback;
            }
            if (Ext.isObject(scope)) {
                callbackScope = scope;
            }
            showConfrm = showConfirmation;
        }
        if (typeof node == 'string') {
            node = this.dataTree.getNodeById(node);
        }
        if (showConfrm) {
            Ext.MessageBox.confirm(CMS.i18n('Löschen?'), CMS.i18n('„{name}“ wirklich löschen?').replace('{name}', node.text || CMS.i18n('unbenannt')), function (btnId) {
                var deleted = false;
                if (btnId == 'yes') {
                    this.removeNode(node, this);
                    deleted =  true;
                }
                this.removeCallback(callbackFunc, deleted, callbackScope);

            }, this);
        } else {
            this.removeNode(node, this);
            this.removeCallback(callbackFunc, true, callbackScope);
        }
    },

    /**
     * Reset unit formValues to module defaults
     * @param {Ext.tree.TreeNode|String} node The tree node to be changed, or its id
     * @param {Boolean} showConfirmation Whether the CMS should
     */
    resetUnit: function (node, showConfirmation) {
        if (typeof node == 'string') {
            node = this.dataTree.getNodeById(node);
        }

        var doReset = function () {
            // reset formValues
            var unit = this.unitStore.getById(node.id);
            unit.set('formValues', SB.util.cloneObject(unit.getModule().get('formValues')));

            // render unit (whole page actually, as we are not
            // sending formValue changes for all formValues (as all of them could have been changed)
            this.fireEvent('CMSrender', {
                record: this.dataTree.generatePreview()
            });

            // update generated form panel
            this.refreshGeneratedFormPanel();
        };

        if (showConfirmation) {
            Ext.MessageBox.confirm(CMS.i18n(null, 'structureEditor.resetTitle'), CMS.i18n(null, 'structureEditor.resetQuestion').replace('{name}', node.text || CMS.i18n('unbenannt')), function (btnId) {
                if (btnId == 'yes') {
                    doReset.call(this);
                }
            }, this);
        } else {
            doReset();
        }
    },

    /**
     * Refresh GeneratedFormPanel. Selects the current unit again.
     * @private
     */
    refreshGeneratedFormPanel: function () {
        var selectedUnitId = this.getSelectedNode().id;
        this.fireEvent('CMSselectunit', { unit: null });
        this.fireEvent('CMSselectunit', { unit: selectedUnitId});
    },

    /**
    * @private
    * Removes the specified node from the data tree
    * @param {Ext.tree.TreeNode} treeNode The node to be
    * removed
    */
    removeNode: function (treeNode) {
        this.fireEvent('CMSbeforeremove', treeNode.id);
        var next = treeNode.previousSibling || treeNode.nextSibling || ((treeNode.parentNode && !treeNode.parentNode.isRoot) ? treeNode.parentNode : null);
        this.dataTree.removeNode(treeNode);
        this.fireEvent('CMSafterremove', treeNode.id);
        this.fireEvent('CMSselectunit', { unit: (next && next.id || null) });
    },

    /**
    * @private
    * Executes the specified callback and passes the result
    * of the remove action to it
    * @param {Function} callback The callback function which is
    * executed
    * @param {Boolean} result The result of the remove action
    * @param {Object} scope (Optional) The object in whose context
    * the callback is executed
    */
    removeCallback: function (callback, result, scope) {
        if (callback) {
            if (scope && Ext.isObject(scope)) {
                callback.call(scope, result);
            } else {
                callback(result);
            }
        }
    },

    /**
    * Copies a unit to the clipboard
    * @param {String} unitId The id of the unit which is has been copied
    */
    copyUnit: function (unitId) {
        var sourceUnit = this.unitStore.getById(unitId);
        CMS.app.clipboard.replace('TreeUnit' + this.websiteId, {
            moduleId: sourceUnit.get('moduleId'),
            data: Ext.encode(sourceUnit.data)
        });
        CMS.Message.toast(CMS.i18n('Hinweis'), CMS.i18n('Unit „{name}“ in Zwischenablage kopiert').replace('{name}', sourceUnit.getUIName()));
    },

    /**
     * Handler for unit pasting from clipboard
     * @param {String} unitId The id of the unit where clipboard content should be pasted to.
     */
    pasteUnit: function (unitId) {
        var targetNode;
        var targetPos;
        var targetUnit = this.unitStore.getById(unitId);
        var unit = CMS.app.clipboard.get('TreeUnit' + this.websiteId);
        var moduleId = unit && unit.moduleId;

        if (!unit) {
            CMS.Message.toast(CMS.i18n('Fehler'), CMS.i18n('Keine Unit in der Zwischenablage'));
            return;
        }

        // determine target node
        if (targetUnit) {
            if (targetUnit.canInsertAsChildInMode(moduleId, this.mode)) {
                // insert clipboard data as child node if possible
                targetNode = this.dataTree.getNodeById(unitId);
                targetPos = null; // insert at the end
            }

            if (!targetNode && targetUnit.canInsertAsSiblingInMode(moduleId, this.mode)) {
                // insert as child is not allowed
                // -> insert as sibling
                var node = this.dataTree.getNodeById(unitId);
                if (node) {
                    targetNode = node.parentNode;
                    targetPos = targetNode.indexOf(node) + 1; // insert next to target sibling
                }
            }
        } else {
            // no target unit
            // -> try to paste as root unit
            var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);
            var module = moduleStore && moduleStore.getById(moduleId);

            if (CMS.data.isRootModuleRecord(module)) {
                targetNode = this.dataTree.getRootNode();
            }
        }

        if (targetNode) {
            this.insertUnitIntoTree(Ext.decode(unit.data), targetNode, targetPos);
        }
    },

    /**
    * Duplicates and selects a unit from a given unitId
    * @param {String} unitId The Id of the unit which has been duplicated
    * @param {String} mode The edit mode (Page/Template)
    * @param {Object} overrides (optional) Data that will be applied to the cloned unit (override cloned values)
    * @return {Boolean} Whether the unit could be duplicated or not
    */
    duplicateUnit: function (unitId, mode, overrides) {
        var sourceUnit = this.unitStore.getById(unitId);
        var sourceNode = this.dataTree.getNodeById(unitId);
        var duplicatable;
        if (mode === 'page') {
            duplicatable = sourceUnit.get('inserted') && sourceUnit.isMovable(mode);
        } else {
            duplicatable = !CMS.data.isRootModuleRecord(sourceUnit.getModule());
        }
        if (!sourceUnit || !sourceNode || !duplicatable) {
            console.warn(CMS.i18n('Die Unit {unitId} ist nicht duplizierbar.').replace('{unitId}', unitId));
            return false;
        }
        var targetNode = sourceNode.parentNode;
        var newData = SB.util.cloneObject(sourceUnit.data);
        if (overrides) {
            Ext.apply(newData, overrides);
        }
        this.insertUnitIntoTree(newData, targetNode, targetNode.indexOf(sourceNode) + 1);
        return true;
    },

    /**
    * @private
    * Convenience method called on duplicate and paste
    * @param {Object} data The data for the newly created record
    * @param {Ext.tree.TreeNode} targetNode The tree node that the new node should be inserted under
    * @param {Integer} position (optional) The insertion position.
    */
    insertUnitIntoTree: function (data, targetNode, position) {
        var record = CMS.data.createUnitRecordWithNewUnitIds(data);
        record.store = this.unitStore; // HACK to link record to store without adding (required by StoreManager)

        var newNode = this.dataTree.insertNodeFromRecord(record, targetNode, position);
        this.selectUnit(newNode);
    }

    /*destroy: function () {
        // we could remove the clipboard entry on destroy, but then it would not be available to other templates
        CMS.app.clipboard.removeKey('TreeUnit' + this.websiteId);
        CMS.structureEditor.StructureEditor.superclass.destroy.apply(this, arguments);
    }*/

});

Ext.reg('CMSstructureeditor', CMS.structureEditor.StructureEditor);
