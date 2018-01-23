DynCSS.defineModule('rz_style_icon', function (api, v, ctx) {

    if (v.cssEnableIcon) {

        // url
        var imageSize = v.cssIconSize;
        var imageQuality = v.cssEnableImageQuality ? v.cssImageQuality : undefined;

        var imgUrl = api.getImageUrl(v.cssIcon, imageSize, imageQuality);
        var cssUrl = imgUrl ? 'url("' +  imgUrl + '")' : 'none';

        if (imageSize == '0px') {
            imageSize = 'auto';
        }

        var css = {
            content: cssUrl,
            display: 'inline-block',
            width: imageSize
        };

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

        var cssWrapper = {};
        cssWrapper['&:' + v.cssPseudoElement] = css;
        return cssWrapper;
    }
});