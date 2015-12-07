define(['jquery'], function ($) {

    var fracsCallback = function (fracs) {
        if (fracs) {
            // set classes with part of the visibility of the element
            var visiblePartOfElement = Math.round(fracs.visible * 100);

            var $element = $(this);

            if (visiblePartOfElement > 10) {
                $element.addClass('visiblePart10');
            }
            if (visiblePartOfElement > 25) {
                $element.addClass('visiblePart25');
            }
            if (visiblePartOfElement > 50) {
                $element.addClass('visiblePart50');
            }
            if (visiblePartOfElement > 75) {
                $element.addClass('visiblePart75');
            }
            if (visiblePartOfElement > 99) {
                $element.addClass('visiblePart100');
            }
            if (visiblePartOfElement < 1) {
                $element.removeClass('visiblePart10 visiblePart25 visiblePart50 visiblePart75 visiblePart100 top50Screen bottom50Screen');
            }

            // set classes regarding the position of the element on the screen
            if (fracs.rects) {
                var elementTop = fracs.rects.viewport.top;
                var elementHeight = fracs.rects.element.height;
                var viewPortHeight = $(window).height();

                if ((elementTop + elementHeight / 2) < viewPortHeight / 2) {
                    $element.addClass('top50Screen');
                }
                if ((elementTop + elementHeight / 2) > viewPortHeight / 2) {
                    $element.addClass('bottom50Screen');
                }
            }
        }
    };

    var initAnimation = function ($element) {
        // set class when animation is running
        $element.on('webkitAnimationStart animationstart', function () {
            $element.addClass('animationRunning');
        });
        $element.on('webkitAnimationEnd animationend', function () {
            $element.removeClass('animationRunning');
            $element.addClass('animationRunOnce');
        });

        $element.fracs(fracsCallback);

        // needed for Chrome to animate already visible elements on page load
        $element.fracs('check');
    };

    var initAllAnimations = function () {
        $(window.rz_style_animation_scroll).each(function (index, selector) {
            initAnimation($(selector));
        });
    };

    return {
        initAllAnimations: initAllAnimations
    };
});