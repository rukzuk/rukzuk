define([
    'jquery',
    'CMS',
    'rz_root/notlive/js/baseJsModule',
    'rz_root/notlive/js/cssHelper',
    'rz_root/notlive/js/breakpointHelper'
], function ($, CMS, JsModule, cssHelper, bpHelper) {

    // resize handles
    var $resizeHeight;
    var $uiBlockerResizeHeight;

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
    };

    return JsModule.extend({

        /** @protected */
        initUnit: function (gridUnitId) {

            var selectedUnit = CMS.getSelectedUnitId();
            if (selectedUnit === gridUnitId) {
                enableResizeHeight(gridUnitId);
            }
        },
        onUnitSelect: function (cfg) {
            enableResizeHeight(cfg.unitId);
        },
        onUnitDeselect: function () {
            disableHeightDrag();
        }
    });
});
