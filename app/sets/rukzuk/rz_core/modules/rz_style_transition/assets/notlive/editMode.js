define(['jquery', 'CMS'], function ($, CMS) {

    var disableTransitions = function () {
        $('body').addClass('preventTransitions');
    };

    var enableTransitions = function () {
        $('body').removeClass('preventTransitions');
    };

    return {
        init: function (data) {
            var timeout;

            // prevent all transitions on units on any formValueChange
            // TODO prevent transitions on elements inside units
            CMS.on('formValueChange', function (cfg) {
                disableTransitions();
                if (timeout) {
                    window.clearTimeout(timeout);
                }
                timeout = window.setTimeout(enableTransitions, 100);
            });
        }
    };
});
