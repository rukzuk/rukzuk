DynCSS.defineModule('rz_style_icon_awesome', function (api, v) {

    if (v.cssEnableIcon) {

        // url
        var iconSize = v.cssIconSize;
		var uniCode = '"\\' + v.cssUnicode + '"';
		var color = api.getColorById(v.cssColor);
		var rotate = 'rotate(' + v.cssRotate + ')';
		var textShadow = [
                v.cssTextShadowOffsetX,
                v.cssTextShadowOffsetY,
                v.cssTextShadowBlur,
                api.getColorById(v.cssTextShadowColor)
            ].join(' ');
		
        var css = {
			fontFamily: 'FontAwesome',
			display: 'inline-block',
            content: uniCode,
			fontSize: iconSize,
			color: color,
			transform: rotate
			
        };

		if (v.cssEnableTextShadow) {
			css.textShadow = textShadow;
		}
		
        // pos
        if (v.cssEnablePosition) {
            css.position = v.cssPositionType;

            if (v.cssPositionType === 'relative') {
                css.top = v.cssShiftY;
                css.left = v.cssShiftX;
                css.bottom = 'auto';
                css.right = 'auto';
            } else {
                var origin = v.cssAbsoluteOrigin;

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

            if (v.cssIconZIndex) {
                css.zIndex = '-1';
            }
        }
        return {
            '&:after': css
        };
    }
});