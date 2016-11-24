define(['jquery'], function ($) {
    // the slider handles
    var slider = {};

    /**
     * retrieve current slider configuration, add start function
     * @param $unit - jquery object of the slider element
     * @returns {Object} - config object
     */
    var getSliderConfig = function ($unit) {
        var sliderConfig = $unit.data('sliderconfig');
        return sliderConfig;
    };

    /**
     * initialize a slider
     * @param unitId
     */
    var initSlider = function (unitId) {
        var $unit = $('#' + unitId);
        var sliderConfig = getSliderConfig($unit);
        sliderConfig.onSlideAfter = function ($slideElement, oldIndex, newIndex) {
            $slideElement.siblings('.slide').removeClass('slideActive');
            $slideElement.addClass('slideActive');
        };

        var $slider = $unit.children('.slides').first();

        // clean up old sliders for this unitId
        if (slider[unitId] && slider[unitId].destroySlider) {
            slider[unitId].destroySlider();
        }

        slider[unitId] = $slider.bxSlider(sliderConfig);
    };

    var initAllSlidersInDom = function () {
        $(window).on('load', function () {
            $('.rz_slider_images').each(function () {
                initSlider($(this).attr('id'));
            });
        });
    };

    return {
        initSlider: initSlider,
        initAllSlidersInDom: initAllSlidersInDom,
        getSliders: function () {
            return slider;
        }
    };
});

