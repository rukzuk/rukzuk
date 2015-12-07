Ext.ns('CMS.structureEditor');

/**
* @class CMS.structureEditor.UnitTreePanel
* @extends CMS.TreePanel
* A treepanel as a common basis for other structure editor treepanels
*/
CMS.structureEditor.UnitTreePanel = Ext.extend(CMS.TreePanel, {

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {String} mode (required) One of 'page'/'template'
    */
    mode: '',

    lines: false,

    initComponent: function () {
        CMS.structureEditor.UnitTreePanel.superclass.initComponent.apply(this, arguments);
        this.root.attributes.allowedChildModuleType = '*';
    },

    /**
    * @private
    * Helper method to make sure, extension units are always ordered before other units
    * @param {Ext.tree.TreeNode} node The record to create the new treeNode from
    * @param {Ext.tree.TreeNode} targetNode The node to insert the new node in.
    * @param {Integer} position (optional) The insertion position.
    * @return {Integer} The fixed insertion position.
    */
    fixInsertPosition: function (node, targetNode, position) {
        if (!targetNode.hasChildNodes()) {
            return null;
        }

        // find position of last extension unit
        var positionLastExtension = 0;
        Ext.each(targetNode.childNodes, function (childNode, index) {
            if (childNode.isExtensionUnit()) {
                positionLastExtension = index + 1;
            }
        });

        if (!Ext.isNumber(position)) {
            // no position given, so insert unit after last extension unit/as last child
            if (node.isExtensionUnit()) {
                return positionLastExtension;
            } else {
                return null;
            }
        } else {
            // position given; fix position if it doesn't match our concept:
            //   - extension units should all be on top
            //   - default units will follow
            if (node.isExtensionUnit()) {
                if (position > positionLastExtension) {
                    return positionLastExtension;
                } else {
                    return position;
                }
            } else {
                if (positionLastExtension > 0 && position <= positionLastExtension) {
                    return positionLastExtension;
                } else {
                    return position;
                }
            }
        }
    },


    /**
    * Create a new TreeNode and append it to the specified targetNode in the specified position.
    * This method also updates the associated {@link #unitStore}
    * @param {CMS.data.ModuleRecord|CMS.data.UnitRecord} record The record to create the new treeNode from
    * @param {Ext.tree.TreeNode} targetNode (optional) The node to insert the new node in.
    * Defaults to this tree's root node.
    * @param {Integer} position (optional) The insertion position.
    * @param {Boolean} skipDataAppend
    *       (optional) if set to <code>true</code> the associated store is not updated.
    *       Defaults to <code>false</code>
    * @return {Ext.tree.TreeNode} The inserted node
    */
    insertNodeFromRecord: function (record, targetNode, position, skipDataAppend) {
        // console.log('[TreePanel] insertNodeFromRecord', record, targetNode, position);
        // we need to do two things here: 1. create a node, 2. create a corresponding UnitRecord.
        // this is because Ext.tree.TreePanel does not keep its data in a store (nor in an Ext.data.Tree)
        targetNode = targetNode || this.getRootNode();
        var newNode = new Ext.tree.TreePanel.nodeTypes[this.loader.baseAttrs.nodeType](record);
        if (!CMS.data.isUnitRecord(record)) {
            // source record is a module
            // -> create a new unit record
            record = record.createUnit(newNode.id);
        }
        record.websiteId = this.websiteId;

        if (this.mode === 'page') {
            record.set('inserted', true);
        }

        var parentRecord = this.unitStore.getById(targetNode.id);

        this.unitStore.add(record);

        this.getSelectionModel().clearSelections();

        // make sure, extension units are always ordered before other units
        position = this.fixInsertPosition(newNode, targetNode, position);

        // show translated unit/-module names
        newNode.setText(record.getUIName());

        // first, update store
        if (!skipDataAppend && parentRecord) { // parentRecord may be undefined when inserting a BaseModule
            if (!parentRecord.data.children) {
                parentRecord.data.children = [];
            }
            if (typeof position == 'number') {
                parentRecord.data.children.splice(position, 0, record.data);
            } else {
                parentRecord.data.children.push(record.data);
            }
        }

        // also process all children of the new node
        Ext.each(record.data.children, function (curData) {
            var curRecord = new CMS.data.UnitRecord(Ext.apply(curData, {
                expanded: true
            }), curData.id);
            curRecord.websiteId = this.websiteId;

            if (this.mode === 'page') {
                curRecord.set('inserted', true);
            }

            this.insertNodeFromRecord(curRecord, newNode, null, true);
        }, this);

        var eventArgs = {
            unitId: record.id,
            parentUnitId: targetNode.id,
            name: record.data.name,
            websiteId: record.websiteId,
            moduleId: record.data.moduleId
        };

        this.fireEvent('CMSbeforeinsertunit', eventArgs);

        // now, update tree. This will fire an event and cause rendering.
        // At this time, the updated store is required
        if (typeof position == 'number') {
            targetNode.insertBefore(newNode, targetNode.childNodes[position]);
        } else {
            targetNode.appendChild(newNode);
        }
        return newNode;
    },

    /**
    * Remove a TreeNode and its associated representation in the UnitStore
    * @param {Ext.tree.TreeNode|String} node The node to be removed or its id
    */
    removeNode: function (node) {
        var cn, index, unit, parentUnit,
            unitStore = this.unitStore;

        node = (typeof node == 'string') ? this.getById(node) : node;

        //update parentNodes's record's children array
        parentUnit = unitStore.getById(node.parentNode.id);
        if (parentUnit) {
            cn = parentUnit.get('children');
            index = Ext.each(cn, function (child) {
                if (child.id == node.id) {
                    return false;
                }
            });
            cn.splice(index, 1);
        }

        // remove children from store
        node.cascade(function (child) {
            var childUnit = unitStore.getById(child.id);
            unitStore.remove(childUnit);
        });

        // Remove unit from store
        unit = unitStore.getById(node.id);
        unitStore.remove(unit);


        node.remove(true);
    },

    /**
    * Move an existing treeNode
    * @param {Ext.tree.TreeNode} node The node to be moved
    * @param {Ext.tree.TreeNode} targetNode The new parentNode
    * @param {Integer|Ext.tree.TreeNode} position The position to insert the node, or a TreeNode to insert the new node before
    */
    moveNode: function (node, targetNode, position) {
        if (position && position.parentNode) {
            position = position.parentNode.indexOf(position);
        }

        // make sure, extension units are always ordered before other units
        position = this.fixInsertPosition(node, targetNode, position);

        // first update store
        var oldParentId = node.parentNode.id;
        var oldParentRecord = this.unitStore.getById(oldParentId);
        var oldPosition = node.parentNode.indexOf(node);
        var child = oldParentRecord.get('children').splice(oldPosition, 1)[0];

        var newParentId = targetNode.id;
        var newParentRecord = this.unitStore.getById(newParentId);
        var newPosition = position;
        newParentRecord.data.children = newParentRecord.data.children || [];
        if (typeof position == 'number') {
            if (newParentId === oldParentId && oldPosition < newPosition) {
                newPosition--;
            }
            newParentRecord.data.children.splice(newPosition, 0, child);
        } else {
            newParentRecord.data.children.push(child);
        }

        // now update tree
        this.firingAdditionalEvents = true; // HACK: SBCMS-249
        if (typeof position == 'number') {
            targetNode.insertBefore(node, targetNode.childNodes[position]);
        } else {
            targetNode.appendChild(node);
        }
    },

    /**
    * Converts the tree data to an object that can be JSON-serialized and
    * passed to a CMS.data.Record's <tt>content</tt> field
    * @return {Array}
    */
    createJSON: function () {
        var nodes = [];
        var root = this.getRootNode();

        root.cascade(function (node) {
            if (node == root) {
                return true;
            }
            var unit = this.unitStore.getById(node.id);
            if (unit) {
                unit.set('expanded', !!node.expanded);

                // TODO check where and why these properties are added to unit.data
                // SBCMS-1201 remove properties that shouldn't be there and sent to the server
                delete unit.data.icon;
                delete unit.data.websiteId;
            }
        }, this);

        root.eachChild(function (childNode) {
            nodes.push(this.unitStore.getById(childNode.id).data);
        }, this);

        return nodes;
    },

    /**
    * Mark all possible drop targets for a specific record, or reset the marks
    * @param {CMS.data.ModuleRecord} record (optional) The record that is possible to be
    * dropped. If left out, this method will reset the marks.
    * @param {Ext.data.Node} draggedNode If dragged from within the tree, this parameter
    * specifies the corresponding tree node.
    */
    markDropTargets: function (record, draggedNode) {
        var hiliteParentClass = 'CMSalloweddroptarget';
        var hiliteChildClass = 'CMSalloweddropsibling';
        var disabledClass = 'CMSforbiddendroptarget';

        var root = this.getRootNode();
        root.cascade(function (node) {
            var ui = node.getUI();
            if (!record) { // clear marks
                //SBCMS-983: need to call removeClass multiple times since it seems to be a bug in ExtJS when using array
                ui.removeClass(hiliteParentClass);
                ui.removeClass(hiliteChildClass);
                ui.removeClass(disabledClass);
                return true;
            }

            var dropAllowed;
            if (draggedNode && draggedNode.contains(node) || draggedNode == node) {
                dropAllowed = false;
            } else {
                dropAllowed = this.isValidDropNode(record, node, root, draggedNode ? draggedNode.getUnit() : null);
            }
            if (dropAllowed) {
                ui.addClass(hiliteParentClass);
                node.eachChild(function (sibling) {
                    if (sibling != draggedNode) {
                        sibling.getUI().addClass(hiliteChildClass);
                    }
                });
                node.allowChildren = true;
            } else {
                ui.addClass(disabledClass);
                node.allowChildren = false;
            }
        }, this);
    },

    /**
    * Highlight complete tree as a possible drop target
    * @param {Boolean} enable If the highlight should be enabled or disabled
    */
    highlightWholeDropTarget: function (enable) {
        if (enable) {
            this.addClass('highlightDropTarget');
        } else {
            this.removeClass('highlightDropTarget');
        }
    },

    /**
    * Abstract method.
    * Subclasses should overwrite this to determine whether a module record can be dropped on a certain tree node.
    * @param {Ext.data.Record} record The dragged module record
    * @param {Ext.tree.TreeNode} node The potential drop node
    * @param {Ext.tree.TreeNode} root This tree's root node, passed for convenience
    * @param {CMS.data.UnitRecord} draggedUnit The dragged unit record
    */
    isValidDropNode: function (record, node, root, draggedUnit) {
        return false;
    },

    destroy: function () {
        delete this.tempRecord;
        CMS.structureEditor.UnitTreePanel.superclass.destroy.apply(this, arguments);
    }
});
