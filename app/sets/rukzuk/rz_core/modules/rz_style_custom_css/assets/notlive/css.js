DynCSS.defineModule('rz_style_custom_css', function (api, v) {
    if (v.cssEnableCustomCss) {
        return api.rawCSS(v.cssCustomCss);
    }
});
