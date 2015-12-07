define(['rz_root/notlive/js/baseJsModule', 'rz_slider_images/sliderHelper'], function (JsModule, sliderHelper) {

    return JsModule.extend({

        initUnit: function (sliderUnitId) {
            // init slider
            sliderHelper.initSlider(sliderUnitId);
        }
    });
});
