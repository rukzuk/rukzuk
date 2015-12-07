define(['rz_root/notlive/js/baseJsModule', 'CMS', 'jquery', 'rz_slider/sliderHelper', 'rz_root/notlive/js/visualHelper'], function (JsModule, CMS, $, sliderHelper, visualHelper) {

    var allChildren = {};

    /**
     * Slide to unit
     * @param sliderUnitId
     * @param slideToUnitId
     */
    var slideToUnit = function (sliderUnitId, slideToUnitId) {
        if (!sliderUnitId || !slideToUnitId) {
            return;
        }
        var $unit = $(document.getElementById(slideToUnitId));
        var $sliderUnit = $(document.getElementById(sliderUnitId));
        // was the search successful
        if ($sliderUnit.length) {
            var knownSliders = sliderHelper.getSliders();
            // calculate slide index
            var $slider = $unit.parents('.slides');
            var thisSlide = $unit.parents('.slide').first();
            var thisIndex = $slider.children('.slide').index(thisSlide);

            // slide to the slide containing the unit if there is a slider object
            if (sliderUnitId && knownSliders[sliderUnitId]) {
                // check if we need to slide
                if (knownSliders[sliderUnitId].getCurrentSlide && knownSliders[sliderUnitId].getCurrentSlide() != thisIndex) {
                    // select the slider itself to prevent some handles drawn by the really selected unit (which is a child of this slider)
                    visualHelper.suspendVisualHelper();
                    // goToSlide
                    knownSliders[sliderUnitId].goToSlide(thisIndex, undefined, true, function ($elem, oldIndex, newIndex) {
                        visualHelper.resumeVisualHelper();
                    });
                }
            }
        }
    };

    /**
     * Finds the next parent which is not an extension module,
     * @param unit - unit object, can be obtained by CMS.get(unitId) [no formValues are required, so use CMS.get(unitId, false)]
     * @returns unit object if next notExtension unit
     */
    var getNextNonExtensionModule = function thisFn(unit) {
        if (unit && typeof unit === 'string') {
            unit = CMS.get(unit, false);
        }
        // this module is not an extension module, just return the unit
        if (!CMS.getModule(unit.moduleId).extensionModule) {
            return unit;
        } else {
            // recursive call with parent unit
            return thisFn(CMS.get(unit.parentUnitId, false));
        }
    };

    /**
     * Collects all children of a Slider
     * @param sliderUnitId
     * @param unitId
     */
    var collectAllChildren = function collectAllChildrenClosure(sliderUnitId, unitId) {
        var unit = CMS.get(unitId);
        var children = unit && unit.children;
        if (children) {
            children.forEach(function (cuid) {
                if (!allChildren[sliderUnitId]) {
                    allChildren[sliderUnitId] = {};
                }
                var nextNonExtensionUnit = getNextNonExtensionModule(CMS.get(cuid, false)).id;
                if (nextNonExtensionUnit !== sliderUnitId) {
                    allChildren[sliderUnitId][cuid] = nextNonExtensionUnit;
                }
                collectAllChildrenClosure(sliderUnitId, cuid);
            });
        }
    };

    /**
     * Get slideToUnit params for a given unitId and a slider
     * @param unitId
     * @param sliderUnitId
     * @returns {Array}
     */
    var getSlideForUnitInSlider = function (unitId, sliderUnitId) {
        var slideToUnitId = allChildren && allChildren[sliderUnitId] && allChildren[sliderUnitId][unitId];
        if (slideToUnitId) {
            return [sliderUnitId, slideToUnitId];
        }
        return [];
    };

    /**
     * Finds the slideToUnit params for a given unitId (in all sliders)
     * @param unitId
     * @returns {Array} [0: string, 1: string]
     */
    var getSlideUnitIdForAllKnownSliders = function (unitId) {
        var res = [];
        Object.keys(allChildren).forEach(function (sliderUnitId) {
            res = getSlideForUnitInSlider(unitId, sliderUnitId);
            if (res.length > 0) {
                return false;
            }
        });
        return res;
    };

    return JsModule.extend({

        initModule: function () {
            // global unit select event
            CMS.on('unitSelect', function (config) {
                slideToUnit.apply(this, getSlideUnitIdForAllKnownSliders(config.unitId));
            });
        },

        initUnit: function (sliderUnitId) {
            // init slider
            sliderHelper.initSlider(sliderUnitId);

            // remember children for slideToUnit
            collectAllChildren(sliderUnitId, sliderUnitId);

            // slide to unit after reload
            var selectedUnitId = CMS.getSelected().id;
            slideToUnit.apply(this, getSlideForUnitInSlider(selectedUnitId, sliderUnitId));
        }
    });
});
