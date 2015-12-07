define([
    'jquery',
    'CMS',
    'rz_root/notlive/js/baseJsModule',
    'rz_root/notlive/js/cssHelper',
    'rz_box/notlive/columnsHelper',
    'rz_root/notlive/js/breakpointHelper',
], function ($, CMS, JsModule, cssHelper, columnsHelper, bpHelper) {

    /************************** helper methods **********************************/
    var previewCellCode = [
        '<div class="isColumnBoxCell boxPreview">',
        '<div class="RUKZUKemptyBox"></div>',
        '</div>',
        '<div class="boxSpacer boxPreviewSpacer"></div>'
    ].join('');

    /**
     * Create a deep clone of a data object
     */
    var clone = function (source) {
        return JSON.parse(JSON.stringify(source));
    };

    /**
     * Calls the given callback function once for each available breakpoint,
     * 'default' is always called (even if breakpoints are disabled)
     */
    var forEachBreakpoint = bpHelper.forEachBreakpoint;

    /**
     * Returns a form value for a given breakpoint id respecting the
     * inheritance logic of the formValues
     */
    var getFormValue = bpHelper.getFormValue;

    /**
     * show empty cells of a column in previewmode
     */
    function createBoxPreview(unitId) {
        var maxColumns = 0;
        var cfg = CMS.get(unitId);
        var cssChildWidth = cfg.formValues.cssChildWidth.value;
        var unit = $('#' + unitId + ' > .isColumnBoxTable');
        var previewCells = unit.children('.isColumnBoxCell').length;
        var delta;
        var i = 0;

        forEachBreakpoint(function (bp) {
            var bpValue = cssChildWidth[bp.id];
            var numOfColumns = (bpValue && bpValue.trim().split(' ').length) || 0;
            if (numOfColumns > maxColumns) {
                maxColumns = numOfColumns;
            }
        });
        delta = maxColumns - previewCells;

        if (delta > 0) {
            for (i = 0; i < delta; i++) {
                if (unit.children('.boxSpacer').length > 0) {
                    unit.children('.boxSpacer').last().after(previewCellCode);
                } else {
                    unit.prepend(previewCellCode);
                }
            }
        } else if (delta < 0) {
            for (i = 0; i < delta * -1; i++) {
                unit.children('.boxPreview').last().remove();
                unit.children('.boxPreviewSpacer').last().remove();
            }
        }
    }

    // add or delete column
    function modifyColumns(action, unitId) {
        var unitData = CMS.get(unitId);
        var currentBreakpointId = CMS.getCurrentResolution();
        var childWidth = getFormValue(unitData, 'cssChildWidth', currentBreakpointId);
        var hSpace = getFormValue(unitData, 'cssHSpace', currentBreakpointId);
        var colBoxHelper = columnsHelper.create(childWidth, hSpace);
        var newChildWidth = clone(unitData.formValues.cssChildWidth.value);

        switch (action) {
        case 'add':
            colBoxHelper.childWidths.push(clone(colBoxHelper.childWidths[colBoxHelper.childWidths.length - 1]));
            break;

        case 'del':
            if (colBoxHelper.childWidths.length > 1) {
                colBoxHelper.childWidths.pop();
            } else {
                return;
            }
            break;
        }

        colBoxHelper.correctChildWidth();

        newChildWidth[currentBreakpointId] = colBoxHelper.serializeChildWidth();

        // set and update data
        CMS.set(unitId, 'cssChildWidth', newChildWidth);
        cssHelper.refreshCSS(unitData);
    }

    function showAddModuleButton(unitId) {
        var $unit = $('#' + unitId);
        if ($unit.hasClass('showAddModuleButton')) {
            var $emptyBox = $unit.find('> .isColumnBoxTable > .isColumnBoxCell.boxPreview:first');

            if ($emptyBox.length === 0) {
                $unit.find('> .isColumnBoxTable > .isColumnBoxCell:last').after('<div class="boxSpacer"></div><div class="isColumnBoxCell"><div class="RUKZUKemptyBox"></div></div>');
                $emptyBox = $unit.find('> .isColumnBoxTable > .isColumnBoxCell:last');
            }

            $emptyBox.addClass('boxPreviewAddButton');
            $emptyBox.prev('.boxSpacer').addClass('boxSpacerAddButton');

            $('<button class="add"></button>').appendTo($emptyBox.children('.RUKZUKemptyBox')).click(function () {
                CMS.openInsertWindow(unitId, 0);
            }).wrap('<div class="RUKZUKmissingInputHint"><div>');
        }
    }




    /* VisualDragColumnBox */
    var visualDragColumnBox = null;

    // helper to create new visual drag handlers and destroy the current it exists
    function recreateVisualDragColumnBox(unitId) {
        if (visualDragColumnBox) {
            visualDragColumnBox.destroy();
        }
        visualDragColumnBox = new VisualDragColumnBox(unitId);
        visualDragColumnBox.create();
    }

    // helper to destroy the box
    function destroyVisualDragColumnBox() {
        if (visualDragColumnBox) {
            visualDragColumnBox.destroy();
        }
        visualDragColumnBox = null;
    }

    // Class: VisualDragColumnBox
    function VisualDragColumnBox(unitId) {
        this.unitId = unitId;
        this.isCreated = false;
        this.updateSizeOfDragHandleFunctions = [];
        this.columnBoxSettings = columnsHelper.create();
    }

    /**
     * @public
     */
    VisualDragColumnBox.prototype.create = function () {
        //console.log('VisualDragColumnBox create', this.unitId);
        this.unitData = CMS.get(this.unitId);

        // abort if user has no right to edit the box
        if (!this.unitData.formValues.cssChildWidth.editable) {
            return;
        }

        this.isCreated = true;
        // select unit (excluding wrapper)
        this.$unit = $('#' + this.unitId + ' > .isColumnBoxTable');

        // fetch inital data which could be updated later
        this.update('initial');

        // the update may destroy our instance
        if (this.isCreated) {
            this.injectDragHandlers();

        }
    };

    /**
     * @public
     */

    VisualDragColumnBox.prototype.destroy = function () {
        //console.log('VisualDragColumnBox destroy', this.unitId);
        if (this.isCreated) {
            $('.columnBoxDragHandler').remove();
            this.updateSizeOfDragHandleFunctions = [];
            $(window).off('resize', this.updateSizeOfDragHandleDelegate);
            this.isCreated = false;
        }
    };

    /**
     * @public
     * @param {String} [type] - one of 'resize' - if empty full recreate is performed!
     */
    VisualDragColumnBox.prototype.update = function (type) {
        //console.log('VisualDragColumnBox update', this.unitId);
        switch (type) {
        case 'initial':
            // hide all visual handlers if input hints are not enabled
            if (!$('body').hasClass('RUKZUKenableInputHints')) {
                this.destroy();
                return;
            }

            // update the resolution id
            this.activeResolutionId = CMS.getCurrentResolution();
            this.unitData = CMS.get(this.unitId);

            // // do not display anything
            // if ((!this.unitData.formValues['cssChildWidth' +  this.activeResolutionId].editable) ||
            //     ((this.activeResolutionId > 0) && !this.unitData.formValues['cssEnableResolution' +  this.activeResolutionId].value)) {
            //     this.destroy();
            //     return;
            // }

            this.updateConfiguredChildWidth();
            break;

        default:
            this.destroy();
            this.create();
        }
    };

    // private functions
    VisualDragColumnBox.prototype.updateConfiguredChildWidth = function () {
        // get current data
        var columnString = getFormValue(this.unitData, 'cssChildWidth', this.activeResolutionId);
        var hSpace = getFormValue(this.unitData, 'cssHSpace', this.activeResolutionId);

        this.columnBoxSettings.setChildWidth(columnString, hSpace);

        // shortcut for easy access
        this.childWidth = this.columnBoxSettings.childWidths;
    };

    VisualDragColumnBox.prototype.updateSizeOfDragHandles = function () {
        // call all registered functions
        var updateFunctions = this.updateSizeOfDragHandleFunctions;
        for (var i = 0; i < updateFunctions.length; ++i) {
            updateFunctions[i]();
        }
    };


    /**
     * uses the current this.columnBoxSettings to update cssChildWidth0 (or 1/2/3) also regenerates the CSS
     */
    VisualDragColumnBox.prototype.updateColumnValuesAndCSS = function () {
        // set the new string and update CSS
        var newChildWidth = clone(this.unitData.formValues.cssChildWidth.value);
        newChildWidth[this.activeResolutionId] = this.columnBoxSettings.serializeChildWidth();

        CMS.set(this.unitId, 'cssChildWidth', newChildWidth);
        this.unitData = CMS.get(this.unitId);

        cssHelper.refreshCSS(this.unitData);
    };



    VisualDragColumnBox.prototype.injectDragHandlers = function () {
        var self = this;

        // we do not need to inject drag handler stuff if there is only one column
        if (this.childWidth.length <= 1) {
            return; // end here (this is just a really small performance boost)
        }

        // use the box spacers as reference for the drag handlers
        var $dragHandlerTargets = this.$unit.children('.boxSpacer').slice(0, self.childWidth.length - 1);

        // add drag handler and events
        $dragHandlerTargets.each(function (idx, elem) {

            var currentChildWidthLeft = self.childWidth[idx];

            // skip hidden columns
            if (currentChildWidthLeft.hidden) {
                return true;
            }

            // child widths (configured values)
            var nextNotHiddenIdx = idx + 1;
            while (self.childWidth[nextNotHiddenIdx] && self.childWidth[nextNotHiddenIdx].hidden) {
                nextNotHiddenIdx++;
            }
            var currentChildWidthRight = self.childWidth[nextNotHiddenIdx];

            // there is no right column (all hidden?)
            if (!currentChildWidthRight) {
                return;
            }

            // jquery objects
            var $elem = $(elem);

            // inject html
            var $dragHandler = $('<div class="columnBoxDragHandler"></div>').appendTo($elem);

            // helper to update the size of the element
            var updateSizeOfDragHandle = function () {

                // only update the size if the box spacer isn't as big as the whole table
                // (prevents flickering on resize if the resolution is about to change)
                var boxSpacerWidth = $elem.width();
                if (self.$unit.width() != boxSpacerWidth) {
                    $dragHandler.css('width', boxSpacerWidth); // as of jq 1.8 outerWidth(x) would also work!
                }

                // update size and position of drag handler
                $dragHandler.css('left', $elem.offset().left);

            };


            // DRAG event handlers
            $dragHandler.drag('start', function (ev, dd) {
                // remember value at start of drag (delta will be relative to this value!)
                dd.startChildWidthLeft = currentChildWidthLeft.value;
                dd.startChildWidthRight = currentChildWidthRight.value;
                // calculate factors (used to convert to percent values)
                dd.factor = (100 / self.$unit.width());
                dd.leftFactor = ((currentChildWidthLeft.unit == '%') ? dd.factor : 1);
                dd.rightFactor = ((currentChildWidthRight.unit == '%') ? dd.factor : 1);

            }).drag(function (ev, dd) {

                // update value
                currentChildWidthLeft.value = dd.startChildWidthLeft + (dd.deltaX * dd.leftFactor);
                currentChildWidthRight.value = dd.startChildWidthRight - (dd.deltaX * dd.rightFactor);

                // minimum size of column (10px or 2 %)
                var leftMinimumViolated = ((currentChildWidthLeft.unit == '%') ? (currentChildWidthLeft.value) <= 2 : (currentChildWidthLeft.value) <= 10);
                var rightMinimumViolated = ((currentChildWidthRight.unit == '%') ? (currentChildWidthRight.value) <= 2 : (currentChildWidthRight.value) <= 10);
                if (leftMinimumViolated || rightMinimumViolated) {
                    return;
                }

                self.updateColumnValuesAndCSS();
                self.updateSizeOfDragHandles();

            }).drag('end', function (ev, dd) {

                // correct values and update css
                self.columnBoxSettings.correctChildWidth();
                self.updateColumnValuesAndCSS();
                self.updateSizeOfDragHandles();

            });

            // do inital size of drag handle
            updateSizeOfDragHandle();
            // register size update function
            self.updateSizeOfDragHandleFunctions.push(updateSizeOfDragHandle);

        });

        // register on windows resize event
        this.updateSizeOfDragHandleDelegate = function () {
            self.updateSizeOfDragHandles();
        };
        $(window).on('resize', this.updateSizeOfDragHandleDelegate);

    };


    return JsModule.extend({

        /** @protected */
        onFormValueChange: function (config) {
            var unitId = config.unitId;
            var unitData = CMS.get(unitId);

            // trim and replace comma with dot
            if ((config.key === 'cssChildWidth') || (config.key === 'cssHSpace')) {
                var newChildWidth = clone(unitData.formValues.cssChildWidth.value);
                forEachBreakpoint(function (bp) {
                    var childWidth = unitData.formValues.cssChildWidth.value[bp.id];
                    if (childWidth) {
                        var hSpace =  getFormValue(unitData, 'cssHSpace', bp.id);
                        var colBoxHelper = columnsHelper.create(childWidth, hSpace);
                        newChildWidth[bp.id] = colBoxHelper.correctChildWidth().serializeChildWidth();
                    }
                });

                //apply new value to formValues
                CMS.set(unitId, 'cssChildWidth', newChildWidth);
                cssHelper.refreshCSS(unitId);
            }

            // Buttons
            if (config.key === 'addColumn') {
                modifyColumns('add', unitId);
            }
            if (config.key === 'delColumn') {
                modifyColumns('del', unitId);
            }

            // update js generated code
            createBoxPreview(unitId);

            if (visualDragColumnBox) {
                // offset is wrong without timeout
                setTimeout(function () { visualDragColumnBox.update(); }, 0);
            }

            // webkit bugfixes
            webkitFixTableLayout();
        },

        /** @protected */
        onUnitSelect: function (config) {
            var unitId = config.unitId;
            createBoxPreview(unitId);
            recreateVisualDragColumnBox(unitId);
        },

        /** @protected */
        onUnitDeselect: function (config) {
            destroyVisualDragColumnBox();
        },

        /** @protected */
        initUnit: function (unitId) {
            createBoxPreview(unitId);

            var selectedUnit = CMS.getSelected();
            if (selectedUnit && selectedUnit.id === unitId) {
                recreateVisualDragColumnBox(unitId);
            }

            showAddModuleButton(unitId);
        },

        /** @protected */
        onResolutionChange: function () {
            var cfg = CMS.getSelected();
            // repaint on resolution change
            if (cfg && cfg.moduleId === this.moduleId) {
                createBoxPreview(cfg.id);
                // recreateVisualDragColumnBox(cfg.id);
                if (visualDragColumnBox) {
                    visualDragColumnBox.update('resolution');
                }
            }
            webkitFixTableLayout();
        },

        /** @protected */
        onVisualHelpersChange: function (cfg) {
            if (visualDragColumnBox) {
                visualDragColumnBox.update();
            }
        }
    });
});
