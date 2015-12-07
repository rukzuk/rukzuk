DynCSS.defineModule('rz_style_opacity', function (api, v) {
    var result = {};
    if (v.cssEnableOpacity) {
        result.opacity = api.unitLess(v.cssOpacity) / 100;
    }
    return result;
});
