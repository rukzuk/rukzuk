define(['rz_root/notlive/js/baseJsModule', 'CMS', 'jquery'], function (JsModule, CMS, $) {

    /**
     * Non recursive filter for regular (i. e. non-extension) units
     * @param {array} unitIds - array of unit ids (string)
     * @returns {array}  - array of unitIs of units which are not based on an extension module (i.e. non-extension units)
     */
    var filterForRegularUnits = function (unitIds) {
        unitIds = unitIds || [];
        return unitIds.filter(function (unitId) { return !CMS.getModule(CMS.get(unitId, false).moduleId).extensionModule; });
    };

    /**
     * Sync tab titles with child units on changes of units (move, delete, add)
     * Units will keep their tab titles if moved, added or removed
     *
     * @param {string} unitId - id of the grid
     * @param {array} [childUnits] - list of unitIds of direct children (non recursive)
     */
    var syncTabTitlesWithUnits = function (unitId, childUnits) {
        var unitData = CMS.get(unitId);

        if (!childUnits) {
            childUnits = filterForRegularUnits(unitData.children);
        }

        var childUnitsInDom = [];
        $('#' + unitId + ' > .tabsWrapper > section > div > .isModule').each(function () {
            childUnitsInDom.push(this.id);
        });

        var tabTitles = unitData.formValues.tabTitles.value || '';
        var tabTitlesArray = tabTitles.split('\n');

        var columnTable = {};
        /*jshint -W083 */
        childUnitsInDom.forEach(function (childId, index) {
            columnTable[childId] = tabTitlesArray[index];
        });

        var newTabTitles = [];
        for (var i = 0; i < childUnits.length; i++) {
            var childId = childUnits[i];

            if (columnTable[childId]) {
                newTabTitles.push(columnTable[childId]);
            } else {
                if (tabTitlesArray[i]) {
                    newTabTitles.push(tabTitlesArray[i]);
                } else {
                    newTabTitles.push('Tab ' + (i + 1));
                }
            }
        }

        CMS.set(unitId, 'tabTitles', newTabTitles.join('\n'));
    };

    return JsModule.extend({

        initModule: function () {
        },

        initUnit: function (tabsUnitId) {

            // add listeners for children unit syncing
            CMS.on('beforeMoveUnit', function (eventData) {
                if (eventData.parentUnitId == tabsUnitId) {
                    syncTabTitlesWithUnits(tabsUnitId);
                }
            });

            CMS.on('beforeRemoveUnit', function (removeUnitId) {
                var removeUnitData = CMS.get(removeUnitId, false);
                if (removeUnitData && removeUnitData.parentUnitId == tabsUnitId) {
                    var parentUnitData = CMS.get(removeUnitData.parentUnitId, false);
                    var childUnits = parentUnitData.children;
                    childUnits.splice(childUnits.indexOf(removeUnitId), 1);
                    syncTabTitlesWithUnits(tabsUnitId, filterForRegularUnits(childUnits));
                }
            });

            CMS.on('beforeInsertUnit', function (eventData) {
                if (eventData.parentUnitId == tabsUnitId) {
                    syncTabTitlesWithUnits(tabsUnitId);
                }
            });
        }
    });
});
