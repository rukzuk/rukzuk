DynCSS.defineModule('rz_style_text_shadow', function (api, v) {
    if (v.cssEnableTextShadow) {
        return {
            'text-shadow': '+' + [
                v.cssTextShadowOffsetX,
                v.cssTextShadowOffsetY,
                v.cssTextShadowBlur,
                api.getColorById(v.cssTextShadowColor)
            ].join(' ')
        };
    }
});
