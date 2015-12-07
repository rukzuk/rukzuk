DynCSS.defineModule('rz_style_background_color', function (api, v, context) {
    var css;

    if (v.cssEnableBackgroundColor) {
        if (v.cssBackgroundColor) {
            var color = api.getColorById(v.cssBackgroundColor);
            css = {
                backgroundColor: color
            };
        } else {
            css = {
                backgroundColor: 'transparent'
            };
        }
    }
    return css;
});
