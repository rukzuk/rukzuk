Ext.ns('CMS.data');

CMS.data.unitFields = [{
    name: 'id',
    type: 'string',
    allowBlank: false
}, {
    name: 'moduleId',
    type: 'string',
    allowBlank: false
}, {
    name: 'name',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'description',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'htmlClass',
    type: 'string',
    defaultValue: '',
    allowBlank: true
}, {
    name: 'ghostContainer',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'ghostChildren',
    type: 'array',
    defaultValue: []
}, {
    name: 'inserted',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'formValues',
    type: 'json',
    defaultValue: {},
    allowBlank: true
}, {
    name: 'visibleFormGroups',
    type: 'array',
    defaultValue: []
}, {
    name: 'expanded',
    type: 'boolean',
    defaultValue: false
}, {
    name: 'templateUnitId',
    type: 'string',
    defaultValue: ''
}, {
    name: 'children',
    type: 'array',
    defaultValue: []
}];

/**
* @class CMS.data.UnitStore
* Store for units
* This is a true singleton. Use {@link #getInstance} to access its instance.
*/
CMS.data.UnitStore = Ext.extend(Ext.data.Store, {
    idProperty: 'id',
    recordType: CMS.data.UnitRecord,

    /**
    * @property isDirty
    * @type Boolean
    * <tt>true</tt> if the store holds unsaved changes. This needs to be set manually.
    */
    isDirty: false,

    /**
    * Fills the store with all units contained in the specified page.
    * @param {CMS.data.PageRecord} record The page to be loaded
    */
    loadPage: function (record) {
        this.removeAll();

        var self = this;
        var extractRecords = function (obj) {
            if (Ext.isArray(obj.children)) {
                Ext.each(obj.children, extractRecords);
            }
            self.add(new CMS.data.UnitRecord(obj, obj.id));
        };

        var json = record.get('content');
        Ext.each(json, extractRecords);
        self = null;
    },
    /**
    * Fills the store with all units contained in the specified template.
    * @param {CMS.data.TemplateRecord} record The template to be loaded
    */
    loadTemplate: function (record) {
        // currently, a template has the same structure as a page.
        return this.loadPage(record);
    },

    /**
     * finds the parent for a given unit
     *
     * @param {String/Object} id
     *      the {@link CMS.data.UnitRecord} instance or its id
     *
     * @return {@link CMS.data.UnitRecord}
     *      the parent unit
     */
    getParentUnit: function (id) {
        if (typeof id == 'object') {
            id = id.id;
        }
        var result = null;
        this.each(function (record) {
            var cn = record.get('children') || [];
            for (var i = 0; i < cn.length; i++) {
                if (cn[i].id == id) {
                    result = record;
                    return false;
                }
            }
        }, this);
        return result;
    },

    /**
     * Walks up the unit tree and finds the next parent non-extension module
     *
     * @param {String/Object} id the {@link CMS.data.UnitRecord} instance or its id
     *
     * @return {@link CMS.data.UnitRecord} the parent unit which is not an extension module
     */
    getNextParentNonExtensionUnit: function (id) {
        var parent;

        if (typeof id == 'object') {
            id = id.id;
        }

        var parentId = id;
        do {
            parent = this.getParentUnit(parentId);
            parentId = parent.id;
        } while (parent && parent.isExtensionUnit());

        return parent;
    },

    /**
    * Determine wether a unit has any siblings following it
    * @param {String|CMS.data.UnitRecord} A record or its id
    * @return Boolean
    */
    isLastChild: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.getById(unit);
        }
        var parent = this.getParentUnit(unit);
        if (!parent) {
            return false;
        }
        var siblings = parent.get('children');
        if (!siblings || !siblings.length) {
            return false;
        }
        return siblings[siblings.length - 1].id == unit.id;
    },

    /**
    * Determine wether a unit has any siblings preceding it
    * @param {String|CMS.data.UnitRecord} A record or its id
    * @return Boolean
    */
    isFirstChild: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.getById(unit);
        }
        var parent = this.getParentUnit(unit);
        if (!parent) {
            return false;
        }
        var siblings = parent.get('children');
        if (!siblings || !siblings.length) {
            return false;
        }
        // siblings are not neccessarily records, so check id
        return siblings[0].id == unit.id;
    },

    /**
    * Find the next sibling unit of the current unit
    * @param {String|CMS.data.UnitRecord} A record or its id
    * @return The next sibling unit
    */
    getNextSiblingUnit: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.getById(unit);
        }
        if (this.isLastChild(unit)) {
            return null;
        }

        var parentUnit = this.getParentUnit(unit);
        if (!parentUnit) {
            return null;
        }
        var siblings = parentUnit.get('children');
        if (!siblings || !siblings.length) {
            return null;
        }

        for (var i = 0; i < siblings.length; i++) {
            if (siblings[i].id == unit.id) {
                return this.getById(siblings[i + 1].id);
            }
        }

        return null;
    },

    /**
    * Find the previous sibling unit of the current unit
    * @param {String|CMS.data.UnitRecord} A record or its id
    * @return The previous sibling unit
    */
    getPreviousSiblingUnit: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.getById(unit);
        }
        if (this.isFirstChild(unit)) {
            return null;
        }

        var parentUnit = this.getParentUnit(unit);
        if (!parentUnit) {
            return null;
        }
        var siblings = parentUnit.get('children');
        if (!siblings || !siblings.length) {
            return null;
        }

        for (var i = 0; i < siblings.length; i++) {
            if (siblings[i].id == unit.id) {
                return this.getById(siblings[i - 1].id);
            }
        }

        return null;
    }
});
