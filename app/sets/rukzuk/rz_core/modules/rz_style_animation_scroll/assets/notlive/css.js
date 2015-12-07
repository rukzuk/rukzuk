DynCSS.defineModule('rz_style_animation_scroll', function (api, v, ctx) {

    var css = {};
    var selector;

    if (v.cssOnlyOnce) {
        selector = '&.' + v.cssTrigger + ':not(.animationRunOnce)' + ', &.previewAnimation';
    } else {
        selector = '&.' + v.cssTrigger + ', &.previewAnimation';
    }

    if (v.cssEnableAnimation == 'disabled') {
        css = {
            'visibility': 'visible'
        };
        css[selector] = {
            '-w-animation-name': 'none'
        };
    } else {
        var visibility = 'visible';
        if (v.cssVisibilityHidden) {
            visibility = 'hidden';
        }

        css = {
            'visibility': visibility
        };

        css[selector] = {
            '-w-animation-name': v.cssAnimationIn,
            '-w-animation-duration': v.cssDuration,
            '-w-animation-fill-mode': 'both',
            '-w-animation-iteration-count': parseInt(v.cssIteration),
            '-w-animation-delay': v.cssDelay,
            '-w-animation-direction': 'normal',
            'visibility': 'visible'
        };

        if (v.cssOnlyOnce) {
            css['&.animationRunOnce'] = {
                'visibility': 'visible'
            };
        }
    }

    return css;

});