DynCSS.defineModule('rz_style_padding_margin', function (api, v) {
    var result = {};
    if (v.cssEnablePadding) {
        result.padding = [v.cssPaddingTop, v.cssPaddingRight, v.cssPaddingBottom, v.cssPaddingLeft].join(' ');
    }
    if (v.cssEnableMargin) {
        result.margin = [v.cssMarginTop, v.cssMarginRight, v.cssMarginBottom, v.cssMarginLeft].join(' ');
    }
    return result;
});