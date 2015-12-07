DynCSS.defineModule('rz_style_border_radius', function (api, v) {
    var result = {};

    if (v.cssEnableBorderRadius) {
        if (v.cssTypeBorderRadius == 'all') {
            result.borderRadius = v.cssBorderAllRadius;
        } else {
            result.borderRadius = v.cssBorderTopLeftRadius + ' ' + v.cssBorderTopRightRadius + ' ' + v.cssBorderBottomRightRadius + ' ' + v.cssBorderBottomLeftRadius;
        }
    }

    return result;
});
