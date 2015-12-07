/**
* @class CMS.data.UnitRecord
* @extends Ext.data.Record
*/
CMS.data.UnitRecord = CMS.data.Record.create(CMS.data.unitFields);

Ext.apply(CMS.data.UnitRecord.prototype, {
    /** @lends CMS.data.UnitRecord.prototype */

    /**
     * Stores which unit attribute have been changed
     * @property modifiedUnitAttributes
     */
    modifiedUnitAttributes: null,

    /**
     * Find the corresponding module in ModuleStore
     * @return {CMS.data.ModuleRecord} The module this unit was constructed from;
     *      <code>null</code> if the module cannot be determined (this is possible
     *      if the unit was delete and record has been removed from its store)
     */
    getModule: function () {
        var websiteId = this.websiteId || (this.store && this.store.websiteId);
        var moduleStore = websiteId && CMS.data.StoreManager.get('module', websiteId);
        var module = moduleStore && moduleStore.getById(this.get('moduleId'));
        return module;
    },

    /**
     * Returns the unit name as it should be displayed in the user interface.
     * I.e. the translated unit name or the translated module name if the unit
     * has no own name
     * @return {String} The UI-ready unit name
     */
    getUIName: function () {
        var uiName = this.get('name');
        var module = this.getModule();
        if (!uiName) {
            module = this.getModule();
            uiName = module && module.get('name');
        }
        return CMS.translateInput(uiName) || '';
    },

    /**
     * Returns the parent unit of the current instance which is in the same store
     * @return {Object} The parent unit instance if there is one or <code>undefined</code>
     *      if there is none or the current instance is not in a store
     */
    getParentUnit: function () {
        return this.store ? this.store.getParentUnit(this.id) : undefined;
    },

    /**
     * Checks if the current unit instance is an extension unit
     * @return {Boolean} <code>true</code> if and only if the unit record represents an extension unit
     */
    isExtensionUnit: function () {
        return CMS.data.isExtensionModuleRecord(this.getModule());
    },

    /**
     * Checks if the unit has changes which should affect the ui
     * @return {Boolean} <code>true</code> if unit contains UI affecting changes
     */
    hasUiAffectingChanges: function () {
        var updateUI = false;
        Ext.iterate(this.modifiedUnitAttributes, function (c) {
            if (CMS.config.nonUiAffectingUnitFields.indexOf(c) == -1) {
                updateUI = true;
                return false;
            }
        });
        return updateUI;
    },

    /**
    * Determine whether this unit is editable in the given mode
    * @param {String} mode The template or page mode
    * @return {Boolean} Whether the unit has is editable
    */
    isEditableInMode: function (mode) {
        if (mode == 'template') {
            return true;
        }
        return !!(this.data.visibleFormGroups && this.data.visibleFormGroups.length);
    },

    /**
    * Determine whether the given form value of this unit
    * is editable
    * @param {String} name The name of the form value
    * @return {Boolean} Whether it is editable or not
    */
    isFormValueEditable: function (name, mode) {
        if (mode === 'template') {
            // The template designer can edit anything
            return true;
        }
        // A form property of a unit is editable if the template designer
        // has marked its form group as visible for editors. Confusing, right?
        // Let's say we have a unit which represents a photo. And this unit
        // has two form group: 'photo settings' and 'photo source'. The 'imagesrc'
        // form property would be grouped under 'photo source' which incidentally
        // is not marked visible. Therefore 'imagesrc' is not editable.
        // In contrast 'imagewidth' could be grouped under 'photo settings' which
        // happens to be visible to editors. 'imagewidth' therefore is editable.
        // It's that easy!
        var formGroup = this.getModule().getFormGroupOfField(name);
        return !!(this.data.visibleFormGroups && this.data.visibleFormGroups.indexOf(formGroup) !== -1);
    },

    /**
    * Checks if the unit has insertable child units by checking if each childModule still exists
    * in the module store
    * @param {String} mode The template or page mode
    * @param {Boolean} excludeExtensionModules
    * @return {Boolean} Whether the unit has insertable
    */
    hasInsertableChildrenInMode: function (mode, excludeExtensionModules) {
        if (mode == 'page') {
            return !!this.get('ghostContainer') && ((this.get('ghostChildren') || []).length > 0);
        } else {
            return (this.getInsertableUnitsInMode('template', excludeExtensionModules).length > 0);
        }
    },

    /**
    * Checks whether the unit is deletable in the current
    * context (page or template mode)
    * @param {String} mode The template or page mode
    * @return {Boolean} Whether the unit is deletable or not
    */
    isDeletableInMode: function (mode) {
        var isDeletable;
        if (mode === 'page') {
            // The unit is not deletable if its an extension module
            if (this.getModule().get('moduleType') === CMS.config.moduleTypes.extension) {
                isDeletable = false;
            } else {
                // The unit is deletable if it has been inserted and is not part of another unit.
                var parent = this.store.getParentUnit(this);
                isDeletable = (parent && parent.get('ghostContainer') && this.get('inserted'));
            }
        } else {
            isDeletable = true;
        }
        return isDeletable;
    },

    /**
     * Checks whether the unit can be cloned in the current
     * context (page or template mode)
     *
     * @param {String} mode The current mode ("page" or "template")
     * @return {Boolean}
     */
    isClonableInMode: function (mode) {
        var isRootModule = CMS.data.isRootModuleRecord(this.getModule());
        if (isRootModule) {
            // you cannot have two root modules
            return false;
        } else {
            if (mode === 'page') {
                return !!(this.get('inserted') && this.isMovable(mode));
            } else {
                return true;
            }
        }
    },

    /**
    * Checks whether the unit is movable in the current
    * context (page or template mode)
    * @param {String} mode The template or page mode
    * @param {String} direction (optional) One of 'up'/'down' to determine a specific direction.
    * @return {Boolean} Whether the unit is movable or not
    */
    isMovable: function (mode, direction) {
        if (CMS.data.isRootModuleRecord(this.getModule())) {
            return false;
        }

        var isMovable;
        if (mode === 'page') {
            // The unit is not movable if its an extension module
            if (this.getModule().get('moduleType') === CMS.config.moduleTypes.extension) {
                isMovable = false;
            } else {
                var parent = this.store.getParentUnit(this);
                isMovable = parent && parent.get('ghostContainer');
            }
        } else {
            isMovable = true;
        }
        if (!isMovable) {
            return false;
        }
        if (!this.store) {
            return false;
        }

        if (direction == 'up') {
            var prevSibling = this.store.getPreviousSiblingUnit(this);

            if (!prevSibling || this.isExtensionUnit() != prevSibling.isExtensionUnit()) {
                return false;
            }
            return true;
        } else if (direction == 'down') {
            var nextSibling = this.store.getNextSiblingUnit(this);

            if (!nextSibling || this.isExtensionUnit() != nextSibling.isExtensionUnit()) {
                return false;
            }
            return true;
        }

        return true;
    },

    /**
    * Returns all insertable and existing child units.
    * @param {String} mode
    * @param {Boolean} excludeExtensionModules
    */
    getInsertableUnitsInMode: function (mode, excludeExtensionModules) {
        excludeExtensionModules = excludeExtensionModules || false;

        var moduleStore = CMS.data.StoreManager.get('module', this.store);
        var result = [];

        if (mode == 'page') {
            if (!this.hasInsertableChildrenInMode('page')) {
                return [];
            }
            Ext.each(this.get('ghostChildren'), function (child) {
                result.push(new CMS.data.UnitRecord(child, child.id));
            });
        } else {
            var allowedModuleType = this.getModule().get('allowedChildModuleType');

            if (allowedModuleType === '') {
                return [];
            }

            moduleStore.each(function (record) {
                if (CMS.data.isRootModuleRecord(record)) {
                    return;
                }

                if (excludeExtensionModules && CMS.data.isExtensionModuleRecord(record)) {
                    return;
                }

                if (allowedModuleType === CMS.config.moduleTypes.extension && !CMS.data.isExtensionModuleRecord(record)) {
                    return;
                }

                result.push(record.createUnit(record.id));
            });
        }
        return result;
    },

    /**
     * Checks if a unit of the given module id can be inserted as a child unit
     *
     * @param {String} moduleId The module id of the unit to be inserted
     * @param {String} mode The edit mode ('page' or 'template')
     * @return {Boolean} <code>true</code> if and only if the new is allowed to
     *      be inserted
     */
    canInsertAsChildInMode: function (moduleId, mode) {
        var insertableChildren = this.getInsertableUnitsInMode(mode);
        var ids = Ext.pluck(insertableChildren, 'id');
        return ids.indexOf(moduleId) >= 0;
    },

    /**
     * Checks if a unit of the given module id can be inserted as a sibling
     * of the current unit
     *
     * @param {String} moduleId The module id of the unit to be inserted
     * @param {String} mode The edit mode ('page' or 'template')
     * @return {Boolean} <code>true</code> if and only if the new is allowed to
     *      be inserted
     */
    canInsertAsSiblingInMode: function (moduleId, mode) {
        var parent = this.getParentUnit();
        return parent && parent.canInsertAsChildInMode(moduleId, mode);
    }
});

/**
 * Checks if the given record is an istance of {@link CMS.data.UnitRecord}
 * @param {Object} record The record object to test
 * @return {Boolean} <code>true</code> iff is an intance of CMS.data.UnitRecord
 */
CMS.data.isUnitRecord = function (record) {
    return record && (record.constructor == CMS.data.UnitRecord);
};

/**
 * Creates a new unit record and recursive sets new ids for the unit and all children units.
 * @param {Object} data Unit data object
 * @return {CMS.data.UnitRecord}
 */
CMS.data.createUnitRecordWithNewUnitIds = function (data) {
    var uidManager = CMS.app.UIDManager.getInstance();

    // get a record's descendants as an array
    var getDescendants = function (unit) {
        var result = unit.children || [];
        Ext.each(unit.children, function (curUnit) {
            if (curUnit.children && curUnit.children.length > 0) {
                result = result.concat(getDescendants(curUnit));
            }
        });
        return result;
    };

    var totalDescendants = getDescendants(data);
    var totalDescendantsCount = totalDescendants.length;

    if (totalDescendantsCount) {
        var idSet = uidManager.getIdSet('unit', totalDescendantsCount);
        Ext.each(totalDescendants, function (unit) {
            unit.id = idSet.pop();
        });
    }

    data.id = uidManager.getId('unit');

    return new CMS.data.UnitRecord(data, data.id);
};


/**
 * Fake a unit record by template snippet data
 * @param {CMS.data.TemplateSnippetRecord} templateSnippet
 * @param {String} websiteId
 * @returns {CMS.data.UnitRecord} unit record
 */
CMS.data.createUnitRecordFromTemplateSnippet = function (templateSnippet, websiteId) {
    // currently we only support one root unit
    var rootUnit = templateSnippet.get('content')[0];

    // create a unitRecord
    var unitRecord = CMS.data.createUnitRecordWithNewUnitIds(SB.util.cloneObject(rootUnit));
    // HACK to link record to store without adding (required by StoreManager)
    unitRecord.store = CMS.data.StoreManager.get('unit', websiteId, {
        disableLoad: true
    });

    return unitRecord;
};
