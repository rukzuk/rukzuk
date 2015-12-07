define([
    'jquery',
    'CMS',
    'rz_root/notlive/js/baseJsModule',
    'rz_root/notlive/js/cssHelper',
    'rz_root/notlive/js/breakpointHelper',
    'rz_grid/notlive/gridHelper',
], function ($, CMS, JsModule, cssHelper, bpHelper, gridHelper) {

    /**
     * Returns a form value for a given breakpoint id respecting the
     * inheritance logic of the formValues
     */
    var getFormValue = bpHelper.getFormValue;

    /**
     * get definition of the grid for the current resolution
     */
    var getGridDefinitionForCurrentResolution = function (unitData) {
        var currentBreakpointId = CMS.getCurrentResolution();
        return String(getFormValue(unitData, 'cssGridDefinition', currentBreakpointId));
    };

    /**
     * set definition of the grid for the current resolution
     */
    var setGridDefinitionForCurrentResolution = function (unitData, newGridDefinition) {
        var currentBreakpointId = CMS.getCurrentResolution();
        var gridDefinition = unitData.formValues.cssGridDefinition.value;
        gridDefinition[currentBreakpointId] = newGridDefinition;

        CMS.set(unitData.id, 'cssGridDefinition', gridDefinition);
    };

    /**
     * enable visual interface; only if user has editing rights
     */
    var enableVisualInterface = function (unitId, selectedUnitId) {
        if (CMS.get(unitId).formValues.gridSize.editable) {
            enableMarginMarker(unitId);
            enableDrag(unitId, selectedUnitId);
        }
    };

    /**
     * disable visual interface
     */
    var disableVisualInterface = function (unitId) {
        disableDrag();
        disableHeightDrag();
        disableMarginMarker(unitId);
        disableGridRaster(unitId);
    };

    // resize handles
    var $resizeMarginLeft;
    var $resizeMarginRight;
    var $resizeWidth;
    var $resizeHeight;
    var $uiBlocker;
    var $uiBlockerResizeHeight;

    /**
     * enable visual margin markers
     */
    var enableMarginMarker = function (unitId) {
        disableMarginMarker(unitId);

        // inject margin marker
        $('#' + unitId + ' > .gridElements > div').append('<span class="marker markerLeft"></span><span class="marker markerCenter"></span><span class="marker markerRight"></span>');
    };

    /**
     * disable visual margin markers
     */
    var disableMarginMarker = function (unitId) {
        // remove margin marker
        $('#' + unitId + ' > .gridElements > div > .marker').remove();
    };

    /**
     * enable visual grid raster
     */
    var enableGridRaster = function (unitId) {
        disableGridRaster(unitId);

        var formValues = CMS.get(unitId).formValues;
        if (!formValues.gridSize.editable) {
            return;
        }

        var gridRasterHtml = '<span class="gridRaster">';
        for (var i = 0; i < formValues.gridSize.value; i++) {
            gridRasterHtml += '<div></div>';
        }
        gridRasterHtml += '</span>';

        // inject grid raster
        $('#' + unitId).append(gridRasterHtml);
    };

    /**
     * disable visual grid raster
     */
    var disableGridRaster = function (unitId) {
        // remove grid raster
        $('#' + unitId + ' > .gridRaster').remove();
    };

    /**
     * enable visual resize
     */
    var enableDrag = function (unitId, selectedUnitId) {
        disableDrag();

        var unitData;

        var gridDefinitionHelper;
        var triggerWidth;
        var windowWidth;
        var activeColumnIndex;

        $resizeMarginLeft = $('<span class="resizeMarginLeft"></span>');
        $resizeMarginRight = $('<span class="resizeMarginRight"></span>');
        $resizeWidth = $('<span class="resizeWidth"></span>');
        $uiBlocker = $('<div class="uiBlocker"></div>');

        var startDrag = function () {
            unitData = CMS.get(unitId);
            var gridDefinition = getGridDefinitionForCurrentResolution(unitData);

            var gridSize = unitData.formValues.gridSize.value;
            triggerWidth = $('#' + unitId).width() / gridSize;
            windowWidth = $(window).width();

            gridDefinitionHelper = gridHelper.create(gridDefinition, gridSize);

            var $activeColumn = $(this).parent().parent();
            activeColumnIndex = $('#' + unitId + ' > .gridElements > div').index($activeColumn);

            $('body').append($uiBlocker);
        };

        var doDrag = function (what, deltaX) {
            var delta = Math.round(deltaX / triggerWidth);
            var dirty;

            switch (what) {
            case 'columnSize':
                dirty = gridDefinitionHelper.dragColumnSize(activeColumnIndex, delta);
                break;
            case 'marginLeft':
                dirty = gridDefinitionHelper.dragColumnMarginLeft(activeColumnIndex, delta);
                break;
            case 'marginRight':
                dirty = gridDefinitionHelper.dragColumnMarginRight(activeColumnIndex, delta);
                break;
            }

            if (dirty) {
                setGridDefinitionForCurrentResolution(unitData, gridDefinitionHelper.serializeGridDefinition());
                cssHelper.refreshCSS(unitId);
            }
        };

        var endDrag = function () {
            gridDefinitionHelper = null;
            $uiBlocker.detach();
        };

        // drag for resizing column width
        $resizeWidth
            .drag('start', startDrag)
            .drag(function (ev, dd) {
                // increase deltaX when mouse is leaving the screen to allow grid elements to flow into next row
                var deltaX = dd.deltaX;
                if (dd.offsetX > windowWidth) {
                    deltaX += triggerWidth;
                }

                doDrag('columnSize', deltaX);
            })
            .drag('end', endDrag);

        // drag for resizing offset left
        $resizeMarginLeft
            .drag('start', startDrag)
            .drag(function (ev, dd) {
                doDrag('marginLeft', dd.deltaX);
            })
            .drag('end', endDrag);

        // drag for resizing offset right
        $resizeMarginRight
            .drag('start', startDrag)
            .drag(function (ev, dd) {
                doDrag('marginRight', dd.deltaX);
            })
            .drag('end', endDrag);

        // inject resize handles
        var $selectedUnitId = $('#' + selectedUnitId);
        $selectedUnitId.find('~ .markerRight').append($resizeWidth);
        $selectedUnitId.find('~ .markerCenter').append($resizeMarginLeft).append($resizeMarginRight);
    };

    /**
     * enable visual height resize of whole grid
     */
    var enableResizeHeight = function (unitId) {
        disableHeightDrag();

        var triggerHeight;
        var minHeight;

        $resizeHeight = $('<span class="resizeHeight"></span>');
        $uiBlockerResizeHeight = $('<div class="uiBlocker uiBlockerResizeHeight"></div>');

        var startDrag = function () {
            var $unit = $('#' + unitId);
            var unitHeight = $unit.height();

            var unitWidth = $unit.width();
            triggerHeight = unitWidth / 100;
            minHeight = Math.round(unitHeight / unitWidth * 100);


            $('body').append($uiBlockerResizeHeight);
        };

        var doDrag = function (deltaY, dd) {
            var delta = Math.round(deltaY / triggerHeight);
            CMS.set(unitId, 'cssMinHeight', (minHeight + delta) + '%');
            cssHelper.refreshCSS(unitId);
        };

        var endDrag = function () {
            $uiBlockerResizeHeight.detach();
        };

        // drag for resizing height
        $resizeHeight
            .drag('start', startDrag)
            .drag(function (ev, dd) {
                doDrag(dd.deltaY, dd);
            })
            .drag('end', endDrag);

        // inject resize handles
        $('#' + unitId).append($resizeHeight);
    };

    /**
     * remove elements of height drag
     */
    var disableHeightDrag = function () {
        if ($resizeHeight) {
            $resizeHeight.remove();
        }
        if ($uiBlockerResizeHeight) {
            $uiBlockerResizeHeight.remove();
        }
    };

    /**
     * remove resize handles and listeners
     */
    var disableDrag = function () {
        if ($resizeMarginLeft) {
            $resizeMarginLeft.remove();
        }
        if ($resizeMarginRight) {
            $resizeMarginRight.remove();
        }
        if ($resizeWidth) {
            $resizeWidth.remove();
        }
        if ($uiBlocker) {
            $uiBlocker.remove();
        }
    };

    /**
     * Sync grid definition with child units for all resolutions
     * Corrects the grid settings (column config) for changes of units (move, delete, add)
     * Units will keep their settings if moved, added or removed
     *
     * @param {string} unitId - id of the grid
     * @param {array} [childUnits] - list of unitIds of direct children (non recursive)
     */
    var syncColumnsWithUnits = function (unitId, childUnits) {
        var unitData = CMS.get(unitId);

        if (!childUnits) {
            childUnits = filterForRegularUnits(unitData.children);
        }

        var childUnitsInDom = [];
        $('#' + unitId + ' > div > div > .isModule').each(function () {
            childUnitsInDom.push(this.id);
        });

        var gridSize = unitData.formValues.gridSize.value;
        var gridDefinitions = unitData.formValues.cssGridDefinition.value;

        // for all resolutions
        for (var resId in gridDefinitions) {
            if (resId == 'type') {
                continue;
            }
            var gridDefinition = gridDefinitions[resId].replace(/\n/g, ' ');
            var gridDefinitionArray = gridDefinition.split(' ');

            var columnTable = {};
            /*jshint -W083 */
            childUnitsInDom.forEach(function (childId, index) {
                columnTable[childId] = gridDefinitionArray[index];
            });

            var newGridDefinition = '';
            var lastChildWidth = gridSize;
            for (var i = 0; i < childUnits.length; i++) {
                var childId = childUnits[i];

                if (columnTable[childId]) {
                    newGridDefinition = newGridDefinition + columnTable[childId] + ' ';
                    lastChildWidth = columnTable[childId];
                } else {
                    newGridDefinition = newGridDefinition + lastChildWidth + ' ';
                }
            }

            var gridDefinitionHelper = gridHelper.create(newGridDefinition, gridSize);
            gridDefinitions[resId] = gridDefinitionHelper.serializeGridDefinition();
        }

        CMS.set(unitId, 'cssGridDefinition', gridDefinitions);
    };

    var isResolutionResetChange = function (key, config) {
        return (config.key == key && !config.newValue[CMS.getCurrentResolution()]);
    };

    /**
     * Fixes the gridDefinition if number of columns in gridDefinition is not equal to the number of child units.
     * @param {string} unitId - id of the grid
     */
    var fixGridDefinitionLength = function (unitId) {
        var unitData = CMS.get(unitId);
        var childUnits = filterForRegularUnits(unitData.children);

        var gridSize = unitData.formValues.gridSize.value;
        var gridDefinition = getGridDefinitionForCurrentResolution(unitData).replace(/\n/g, ' ');
        var gridDefinitionArray = gridDefinition.split(' ');

        if (childUnits.length != gridDefinitionArray.length) {
            // rebuild gridDefinition
            var newGridDefinition = '';
            var j = 0;

            for (var i = 1; i <= childUnits.length; i++) {
                newGridDefinition = newGridDefinition + gridDefinitionArray[j] + ' ';
                j++;
                if (j == gridDefinitionArray.length) {
                    j = 0;
                }
            }

            var gridDefinitionHelper = gridHelper.create(newGridDefinition, gridSize);
            setGridDefinitionForCurrentResolution(unitData, gridDefinitionHelper.serializeGridDefinition());
        }
    };

    /**
     * Non recursive filter for regular (i. e. non-extension) units
     * @param {array} unitIds - array of unit ids (string)
     * @returns {array}  - array of unitIs of units which are not based on an extension module (i.e. non-extension units)
     */
    var filterForRegularUnits = function (unitIds) {
        unitIds = unitIds || [];
        return unitIds.filter(function (unitId) { return !CMS.getModule(CMS.get(unitId).moduleId).extensionModule; });
    };

    return JsModule.extend({

        /** @protected */
        onFormValueChange: function (eventData) {
            // always validate grid definition & add/remove column buttons
            if (['cssGridDefinition', 'gridSize'].indexOf(eventData.key) != -1) {
                var unitId = eventData.unitId;

                if (!isResolutionResetChange('cssGridDefinition', eventData)) {
                    var unitData = CMS.get(unitId);
                    var gridSize = unitData.formValues.gridSize.value;

                    var gridDefinition = getGridDefinitionForCurrentResolution(unitData);

                    var gridDefinitionHelper = gridHelper.create(gridDefinition, gridSize);
                    setGridDefinitionForCurrentResolution(unitData, gridDefinitionHelper.serializeGridDefinition());
                }

                syncColumnsWithUnits(unitId);
                cssHelper.refreshCSS(unitId);
                enableDrag(unitId);

                if (eventData.key == 'gridSize') {
                    CMS.preventRendering(unitId);
                    enableGridRaster(unitId);
                }
            }
        },

        /** @protected */
        onResolutionChange: function () {
            var selectedUnit = CMS.getSelected(false);
            if (selectedUnit.moduleId == 'rz_grid') {
                enableDrag(selectedUnit.id);
            }
        },

        /** @protected */
        onUnitSelect: function (config) {
            enableGridRaster(config.unitId);
            enableMarginMarker(config.unitId);
            enableResizeHeight(config.unitId);
        },

        /** @protected */
        onUnitDeselect: function (config) {
            disableVisualInterface(config.unitId);
        },

        /** @protected */
        initUnit: function (gridUnitId) {
            var selectedUnit = CMS.getSelected(false);

            if (selectedUnit && selectedUnit.parentUnitId == gridUnitId) {
                enableVisualInterface(gridUnitId, selectedUnit.id);      
                enableGridRaster(gridUnitId);
                fixGridDefinitionLength(gridUnitId);
            }

            if (selectedUnit && selectedUnit.id === gridUnitId) {
                enableGridRaster(gridUnitId);
                enableMarginMarker(gridUnitId);
                enableResizeHeight(gridUnitId);
                fixGridDefinitionLength(gridUnitId);
            }

            // disable grid raster & visual interface if selected unit is a child of the grid
            CMS.on('unitDeselect', function (config) {
                var unitData = CMS.get(config.unitId, false);
                if (unitData && unitData.parentUnitId == gridUnitId) {
                    disableVisualInterface(gridUnitId, config.unitId);
                }
            });

            // enable grid raster & visual interface if selected unit is a child of the grid
            CMS.on('unitSelect', function (config) {
                var unitData = CMS.get(config.unitId, false);
                if (unitData && unitData.parentUnitId == gridUnitId) {
                    enableVisualInterface(gridUnitId, config.unitId);
                    fixGridDefinitionLength(gridUnitId);
                    if (unitData.moduleId != 'rz_grid') {
                        enableGridRaster(gridUnitId);
                    }
                }
            });

            // add listeners for children unit syncing
            CMS.on('beforeMoveUnit', function (eventData) {
                if (eventData.parentUnitId == gridUnitId) {
                    syncColumnsWithUnits(gridUnitId);
                }
            });

            CMS.on('beforeRemoveUnit', function (removeUnitId) {
                var removeUnitData = CMS.get(removeUnitId, false);
                if (removeUnitData && removeUnitData.parentUnitId == gridUnitId) {
                    var parentUnitData = CMS.get(removeUnitData.parentUnitId, false);
                    var childUnits = parentUnitData.children;
                    childUnits.splice(childUnits.indexOf(removeUnitId), 1);
                    syncColumnsWithUnits(gridUnitId, filterForRegularUnits(childUnits));
                }
            });

            CMS.on('beforeInsertUnit', function (eventData) {
                if (eventData.parentUnitId == gridUnitId) {
                    syncColumnsWithUnits(gridUnitId);
                }
            });
        }
    });
});
