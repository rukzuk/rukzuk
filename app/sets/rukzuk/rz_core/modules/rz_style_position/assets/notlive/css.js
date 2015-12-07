DynCSS.defineModule('rz_style_position', function (api, v) {
    if (!v.cssEnablePosition) {
        return;
    }

    var css = {};

    if (v.cssPositionType) {
        css.position = v.cssPositionType;

        if (v.cssPositionType === 'relative') {
            css.top = v.cssShiftY;
            css.left = v.cssShiftX;
            css.bottom = 'auto';
            css.right = 'auto';

        } else {
            var origin = v.cssPositionType === 'fixed' ? v.cssFixedOrigin : v.cssAbsoluteOrigin;

            if (origin.match(/Top/)) {
                css.top = v.cssShiftY;
                css.bottom = 'auto';
            } else {
                css.bottom = v.cssShiftY;
                css.top = 'auto';
            }
            if (origin.match(/Left/)) {
                css.left = v.cssShiftX;
                css.right = 'auto';
            } else {
                css.right = v.cssShiftX;
                css.left = 'auto';
            }
        }
    }

    if (v.cssZindex > 0) {
        css.zIndex = v.cssZindex;
    }

    return css;
});
