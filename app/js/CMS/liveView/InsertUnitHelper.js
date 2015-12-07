Ext.ns('CMS.liveView');

/**
* @class CMS.liveView.InsertUnitHelper
* Helper which computes which units can be inserted in the context of
* a given unit
* This is a true singleton. Use {@link #getInstance} to access its instance.
*/
CMS.liveView.InsertUnitHelper = (function () {

    var instance;

    var Class = Ext.extend(Ext.util.Observable, {

        /**
        * @property possiblePositions
        * @type Object
        * The possible insertion positions
        */
        possiblePositions: {
            above: 'above',
            inside: 'inside',
            below: 'below'
        },

        /**
        * Returns the siblings of the given unit which are insertable
         *
        * @param {CMS.data.UnitRecord} unit The unit in whose
        * context the siblings should be inserted
        * @param {String} mode The mode in we are operating
        * (Page/Template)
        * @param {Boolean} excludeExtensionModules
        * @return {Ext.util.MixedCollection} The insertable siblings
        */
        getInsertableSiblings: function (unit, mode, excludeExtensionModules) {
            if (unit && mode) {
                var siblingParent = unit.store.getParentUnit(unit);
                if (siblingParent) {
                    return siblingParent.getInsertableUnitsInMode(mode, excludeExtensionModules);
                }
            }
            return [];
        },

        /**
        * Returns children of the given unit which are insert-able
         *
        * @param {CMS.data.UnitRecord} unit The unit in whose
        * context the children should be inserted
        * @param {String} mode The mode in we are operating
        * (Page/Template)
        * @param {Boolean} excludeExtensionModules
        * @return {Ext.util.MixedCollection} The insertable children
        */
        getInsertableChildren: function (unit, mode, excludeExtensionModules) {
            if (unit && mode) {
                return unit.getInsertableUnitsInMode(mode, excludeExtensionModules);
            }
            return [];
        },


        /**
         * Determine best-guess position for given unit and owner unit
         *
         * @param {CMS.data.UnitRecord} unit - which will be inserted
         * @param {CMS.data.UnitRecord} ownerUnit - parent/target unit
         * @returns {String} 'inside' or 'below' if insert-able, null otherwise
         */
        getAutoPositionForTemplate: function (unit, ownerUnit) {
            var insertAbleChildren = this.getInsertableChildren(ownerUnit, 'template', false);
            var insertAbleChildrenIds = insertAbleChildren.map(function (d) { return d.id; });
            if (insertAbleChildrenIds.indexOf(unit.data.moduleId) > -1) {
                return 'inside';
            } else {
                var insertAbleSiblings = this.getInsertableSiblings(ownerUnit, 'template', false);
                var insertAbleSiblingsIds = insertAbleSiblings.map(function (d) { return d.id; });
                if (insertAbleSiblingsIds.indexOf(unit.data.moduleId) > -1) {
                    return 'below';
                }
            }
            return null;
        },

        /**
        * Returns the object which can be inserted as a unit
        * @param {CMS.data.UnitRecord} unit The unit which should be inserted
        * @param {String} position The new position relative to the owner unit
        * (above, below, inside)
        * @param {CMS.data.UnitRecord} ownerUnit The unit in whose context
        * the new unit should be inserted
        */
        getInsertObject: function (unit, position, ownerUnit) {
            var cfg = null;
            if (unit && ownerUnit) {
                if (this.possiblePositions[position]) {
                    var parentUnit;
                    var siblingIndex;
                    if (position === this.possiblePositions.inside) {
                        parentUnit = ownerUnit;
                    } else {
                        parentUnit = ownerUnit.store.getParentUnit(ownerUnit);
                        Ext.each(parentUnit.get('children'), function (child, index) {
                            if (child.id == ownerUnit.id) {
                                siblingIndex = index;
                            }
                        }, this);
                    }
                    cfg = {
                        templateUnit: unit,
                        parentUnit: parentUnit,
                        websiteId: CMS.data.StoreManager.getWebsiteId(ownerUnit),
                        name: unit.data.name
                    };
                    if (position === this.possiblePositions.above) {
                        cfg.position = siblingIndex;
                    } else if (position === this.possiblePositions.below) {
                        if (!ownerUnit.store.isLastChild(ownerUnit)) {
                            cfg.position = siblingIndex + 1;
                        } // per default, new unit is appended
                    }

                } else {
                    console.warn('Invalid insertion point "' + position + '"');
                }
            }
            return cfg;
        }
    });

    return {
        /**
         * (Class method)
         */
        getInstance: function () {
            if (!instance) {
                instance = new Class();
            }
            return instance;
        }
    };
})();
