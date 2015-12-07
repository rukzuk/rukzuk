/* global jsPlumb */
define([
    'jquery',
    'CMS',
    'rz_root/notlive/js/baseJsModule',
    'rz_root/notlive/js/cssHelper',
    'rz_root/notlive/js/breakpointHelper',
    'rz_root/notlive/js/visualHelper'
], function ($, CMS, JsModule, cssHelper, bpHelper, visualHelper) {

    function VisualDragPosition(unitId) {
        var unitData = CMS.get(unitId); // inital data of the unit
        var parentIsExtension = CMS.getModule(CMS.get(unitData.parentUnitId, false).moduleId).extensionModule;
        var parentIsRoot = !CMS.get(unitData.parentUnitId, false).parentUnitId;
        var activeResolutionId = CMS.getCurrentResolution();

        // class members
        this.$parentUnit = $('#' + unitData.parentUnitId);
        this.unitId = unitId;
        this.shiftGridX = null;
        this.shiftGridY = null;
        this.positionType = null;
        this.origin = null;
        this.currentShiftY = null;
        this.currentShiftYType = null;
        this.currentShiftX = null;
        this.currentShiftXType = null;
        this.unitData = null;
        this.isCreated = false;

        if (parentIsExtension || parentIsRoot) {
            // do not show any visual tools if the parent is not a default module
            return;
        }

        if (!unitData.formValues.cssShiftX.editable) {
            // dont't display visual tools if the user has no right to change the settings
            return;
        }

        if (!bpHelper.getFormValue(unitData, 'cssEnablePosition', activeResolutionId)) {
            // exit if position module is not enabled
            return;
        }

        // do an inital update to fetch all data (see vars above)
        this.updateData(unitData);

        if (this.positionType !== 'fixed' && this.positionType !== 'absolute') {
            // only show drag handle if position type is "fixed" or "absolute";
            // position "relative" is way too tricky!
            return;
        }

        // inject style position wrapper
        this.$unit = $('<div id="pos' + this.unitId + '" class="stylePositionWrapper"></div>').appendTo(this.$parentUnit);

        // create drag
        this.dragCreate();

        // show a connector
        try {
            this.connectorCreate();
        } catch (e) {
            console.log('error in connectorCreate', e);
        }

        this.isCreated = true;
    }

    VisualDragPosition.prototype.dragCreate = function () {
        var self = this;
        this.$dragHandle = $('<div class="dragHandle"></div>').appendTo(this.$unit);

        // draggable-global states
        var delayedSetPosition = null;

        this.$parentUnit.drag('init', function () {
            // update data
            self.updateData();

            // HACK/TODO prevent transitions from rz_style_transition during drag&drop
            $('body').addClass('preventTransitions');
        }, {
            handle: '.stylePositionWrapper > .dragHandle',
            relative: true
        });

        this.$parentUnit.drag(function (ev, dd) {
            // calculate and set the position directly on the elements style
            var cssPos = self.calculateCssPosition(dd.offsetX, dd.offsetY);
            $(this).css(cssPos);

            // repaint connector
            self.connectorRedraw();

            // delay the setPosition (saves a lot of cpu!)
            clearTimeout(delayedSetPosition);
            delayedSetPosition = setTimeout(function () {
                self.setPosition(cssPos);
            }, 100);

        });

        this.$parentUnit.drag('end', function (ev, dd) {
            // stop delayed position update (we do it now anyways)
            clearTimeout(delayedSetPosition);

            // repaint jsPlumb connector on drag
            self.connectorRedraw();

            // set position and update the css (to the values set via CMS.set)
            self.setPosition(self.calculateCssPosition(dd.offsetX, dd.offsetY));

            // render css code with updated form values
            cssHelper.refreshCSS(self.unitData);

            // reset element stlyes so element can be effected by new css
            self.$parentUnit.css({ 'left': '', 'right': '', 'top': '', 'bottom': ''});

            // HACK/TODO prevent transitions from rz_style_transition during drag&drop
            $('body').removeClass('preventTransitions');
        });

        // prevents default actions on click
        this.preventClick = function (e) {
            e.preventDefault();
            e.stopPropagation();
        };
        this.$dragHandle.on('click', this.preventClick);

    };

    /**
     * @param {Number} [offsetX] new position of the element (x component)
     * @param {Number} [offsetY] new position of the element (y component)
     */
    VisualDragPosition.prototype.calculateCssPosition = function (offsetX, offsetY) {

        // init cssPos
        var cssPos = {top: 'auto', left: 'auto', right: 'auto', bottom: 'auto'};
        var height, width, parentWidth, parentHeight, top, left, right, bottom;
        var newTop, newLeft, newRight, newBottom;

        // calculations
        if (this.positionType == 'absolute') { // position: absolute
            parentWidth = this.$parentUnit.offsetParent().outerWidth();
            parentHeight = this.$parentUnit.offsetParent().outerHeight();

        } else { // position: fixed
            parentWidth = $('body').outerWidth();
            parentHeight = $('body').outerHeight();
        }

        height = this.$parentUnit.outerHeight();
        width = this.$parentUnit.outerWidth();
        top = offsetY;
        left = offsetX;
        bottom = parentHeight - top - height;
        right = parentWidth - left - width;

        // shiftY % or px
        if (this.currentShiftYType === '%') {
            newTop = Math.round(100 * top / parentHeight) + '%';
            newBottom = Math.round(100 * bottom / parentHeight) + '%';
        } else {
            newTop = top + 'px';
            newBottom = bottom + 'px';
        }

        // shiftX % or px
        if (this.currentShiftXType === '%') {
            newLeft = Math.round(left / parentWidth * 100) + '%';
            newRight = Math.round(right / parentWidth * 100) + '%';
        } else {
            newLeft = left + 'px';
            newRight = right + 'px';
        }

        // set only the values required in the current origin mode
        if (this.origin.match(/Top/)) {
            cssPos.top = newTop;
        } else {
            cssPos.bottom = newBottom;
        }
        if (this.origin.match(/Left/)) {
            cssPos.left = newLeft;
        } else {
            cssPos.right = newRight;
        }

        return cssPos;
    };

    /**
     * Sets the position via CMS.set and optionally regenerates the css
     * @param {Object} cssPositionObj object with top, left, bottom, right properties
     */
    VisualDragPosition.prototype.setPosition = function (cssPositionObj) {
        var cssShiftX = bpHelper.getFormValue(this.unitData, 'cssShiftX');
        var cssShiftY = bpHelper.getFormValue(this.unitData, 'cssShiftY');
        var activeResolutionId = CMS.getCurrentResolution();

        if (cssPositionObj.top != 'auto') {
            cssShiftY[activeResolutionId] = cssPositionObj.top;
        } else {
            cssShiftY[activeResolutionId] = cssPositionObj.bottom;
        }

        if (cssPositionObj.left != 'auto') {
            cssShiftX[activeResolutionId] = cssPositionObj.left;
        } else {
            cssShiftX[activeResolutionId] = cssPositionObj.right;
        }

        CMS.set(this.unitId, 'cssShiftX', cssShiftX);
        CMS.set(this.unitId, 'cssShiftY', cssShiftY);

        this.updateData();
    };

    /**
     * Create a visual line between the movable object and the 'offsetParent' of the object
     */
    VisualDragPosition.prototype.connectorCreate = function () {
        var jsPlumbSource;
        var floatMarkerParent;
        var floatMarkerClass = '';

        if (this.positionType == 'absolute') {
            floatMarkerParent = this.$parentUnit.offsetParent();
        } else if (this.positionType == 'fixed') {
            floatMarkerParent = $('body');
            floatMarkerClass = 'floatMarkerFixed';
            // HACK: use jsPlumb with fixed position
            $('#jsPlumb_fixedpos').remove();
            $('body').append('<style id="jsPlumb_fixedpos">._jsPlumb_endpoint, ._jsPlumb_connector { position: fixed !important; }</style>');
        }

        // append folatMarker (source for jsPlumb)
        jsPlumbSource = $('<div class="floatMarker ' + this.origin + ' ' + floatMarkerClass + '"></div>').appendTo(floatMarkerParent);

        // make connector
        jsPlumb.Defaults.Container = $('body');
        jsPlumb.connect({
            source: jsPlumbSource,
            target: this.$parentUnit,
            paintStyle: {
                lineWidth: 2,
                strokeStyle: 'rgb(155, 13, 84)'
            },
            anchor: this.origin,
            connector: 'Straight',
            ConnectorZIndex: 10100,
            drawEndpoints: false,
            overlays: [["PlainArrow", {
                location: 1,
                width: 10,
                length: 10
            }]]
        });
    };

    /**
     * Redraw the connector
     */
    VisualDragPosition.prototype.connectorRedraw = function () {
        try {
            jsPlumb.repaint(this.$parentUnit);
        } catch (e) {
        }
    };

    /**
     * Updates all dynamic data variables
     */
    VisualDragPosition.prototype.updateData = function (updateUnitObject) {
        var activeResolutionId = CMS.getCurrentResolution();
        this.unitData = updateUnitObject || CMS.get(this.unitId);

        // positionType and origin
        this.positionType = bpHelper.getFormValue(this.unitData, 'cssPositionType', activeResolutionId);
        if (this.positionType == 'absolute') {
            this.origin = bpHelper.getFormValue(this.unitData, 'cssAbsoluteOrigin', activeResolutionId);
        } else if (this.positionType == 'fixed') {
            this.origin = bpHelper.getFormValue(this.unitData, 'cssFixedOrigin', activeResolutionId);
        } else {
            // default origin for position: relative
            this.origin = 'TopLeft';
        }

        // set type of shift x,y
        this.currentShiftX = String(bpHelper.getFormValue(this.unitData, 'cssShiftX', activeResolutionId));
        if (this.currentShiftX.match(/%/)) {
            this.currentShiftXType = '%';
        } else {
            this.currentShiftXType = 'px';
        }

        this.currentShiftY = String(bpHelper.getFormValue(this.unitData, 'cssShiftY', activeResolutionId));
        if (this.currentShiftY.match(/%/)) {
            this.currentShiftYType = '%';
        } else {
            this.currentShiftYType = 'px';
        }
    };

    VisualDragPosition.prototype.destroyConnector = function () {
        jsPlumb.reset();
        $('.floatMarker').remove();
        $('#jsPlumb_fixedpos').remove();
    };

    VisualDragPosition.prototype.destroyDrag = function () {
        // reset element stlyes
        this.$parentUnit.css({ 'left': '', 'right': '', 'top': '', 'bottom': ''});
        // remove drag events
        this.$parentUnit.off('draginit dragstart drag dragend');
        //this.$parentUnit.off('click', this.preventClick);
        // remove drag handle and other injected stuff!
        this.$unit.remove();
    };

    VisualDragPosition.prototype.destroy = function () {
        if (this.isCreated) {
            this.destroyConnector();
            this.destroyDrag();
            this.isCreated = false;
        }
    };

    // ---------------------------------------------------------------------------------------------

    var visualDrag;

    return JsModule.extend({
        // initialize afterRenderPage if $unitis selected
        initUnit: function (unitId) {
            var cfg = CMS.getSelected();
            if (cfg.id === unitId) {
                if (visualDrag) {
                    visualDrag.destroy();
                }
                visualDrag = new VisualDragPosition(unitId);
            }
        },

        // initialize on unitselect
        onUnitSelect: function (config) {
            if (visualDrag) {
                visualDrag.destroy();
            }
            visualDrag = new VisualDragPosition(config.unitId);
        },

        // remove draggable on deselect
        onUnitDeselect: function (config) {
            if (visualDrag) {
                visualDrag.destroy();
            }
        },

        // repaint connector when resolution changes
        onResolutionChange: function () {
            var cfg = CMS.getSelected();
            if (cfg.moduleId === this.moduleId) {
                if (visualDrag) {
                    visualDrag.destroy();
                }
                visualDrag = new VisualDragPosition(cfg.id);
            }

        },

        // actions on formValueChange
        onFormValueChange: function (config) {
            var unitId = config.unitId;
            var key = config.key;

            // set resizable/draggable when settings change
            if (key.match(/cssEnablePosition/) || key.match(/Origin/) || key.match(/cssPositionType/)) {
                if (visualDrag) {
                    visualDrag.destroy();
                }
                visualDrag = new VisualDragPosition(unitId);
            }
            // redraw connector
            if (visualDrag) {
                visualDrag.connectorRedraw();
            }
        },
    });


    // // regenerate style if moved to root module
    // // TODO: fix this, see SBCMS-1415
    // CMS.on('beforeMoveUnit' /*, {moduleId: '<?php echo $this->getModuleId(); ?>'}*/, function (cfg) {
    //     if (CMS.get(cfg.unitId, false).moduleId == '<?php echo $this->getModuleId(); ?>') {
    //         // disable position if the new parent is the root module
    //         if (!CMS.get(cfg.parentUnitId, false).parentUnitId) {
    //             CMS.set(cfg.unitId, 'cssEnablePosition0', false);
    //             CMS.set(cfg.unitId, 'cssEnablePosition1', false);
    //             CMS.set(cfg.unitId, 'cssEnablePosition2', false);
    //             CMS.set(cfg.unitId, 'cssEnablePosition3', false);
    //             var unitData = CMS.get(cfg.unitId);
    //             RUKZUK.css.generateAndInsertCss(unitData, 0);
    //             RUKZUK.css.generateAndInsertCss(unitData, 1);
    //             RUKZUK.css.generateAndInsertCss(unitData, 2);
    //             RUKZUK.css.generateAndInsertCss(unitData, 3);
    //         }
    //     }
    // });

});
