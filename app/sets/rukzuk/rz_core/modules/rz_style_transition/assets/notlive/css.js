DynCSS.defineModule('rz_style_transition', function (api, v) {
    // only generate css if animation is enabled!
    if (!v.cssEnableAnimation) {
        return;
    }

    var easingMapping = {
        'linear': 'linear',
        'ease': 'ease',
        'ease-in': 'ease-in',
        'ease-out': 'ease-out',
        'ease-in-out': 'ease-in-out',
        'easeInExpo': 'cubic-bezier(0.950, 0.050, 0.795, 0.035)',
        'easeInOutBack': 'cubic-bezier(0.680, -0.550, 0.265, 1.550)',
        'easeInBack': 'cubic-bezier(0.600, -0.280, 0.735, 0.045)'
    };

    var easing = easingMapping[v.cssEasing];

    var transition = ['all', v.cssDuration, easing, v.cssDelay].join(' ');

    return {
        '-wm-transition': transition
    };

});
