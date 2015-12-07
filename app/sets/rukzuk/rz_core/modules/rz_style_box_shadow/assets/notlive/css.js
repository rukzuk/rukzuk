DynCSS.defineModule('rz_style_box_shadow', function (api, v, context) {
    var result;
    if (v.cssEnableBoxShadow) {
        if (v.cssBoxShadowColor) {
            var color = api.getColorById(v.cssBoxShadowColor);
            var value = [v.cssBoxShadowOffsetX, v.cssBoxShadowOffsetY, v.cssBoxShadowBlur, v.cssBoxShadowSpread, color];

            if (v.cssBoxShadowInset) {
                value.push('inset');
            }

            result = {
                boxShadow: '+' + value.join(' ')
            };
        } else {
            result = {
                boxShadow: 'none'
            };
        }
    }
    return result;

});
