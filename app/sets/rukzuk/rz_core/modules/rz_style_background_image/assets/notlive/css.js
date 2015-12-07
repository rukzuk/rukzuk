DynCSS.defineModule('rz_style_background_image', function (api, v, ctx) {

    if (v.cssEnableBackgroundImage) {

        // url
        var imageSize = v.cssBackgroundSize;
        var imageQuality = v.cssEnableImageQuality ? v.cssImageQuality : undefined;

        var imgUrl = api.getImageUrl(v.cssBackgroundImage, imageSize, imageQuality);
        var cssUrl = imgUrl ? 'url("' +  imgUrl + '")' : 'none';

        // pos
        var pos;
        if (v.cssEnableBackgroundPosition == 'dynamic') {
            pos = [v.cssBackgroundPositionX, v.cssBackgroundPositionY].join(' ');
        } else if (v.cssEnableBackgroundPosition == 'numeric') {
            pos = [v.cssBackgroundPositionPixelX, v.cssBackgroundPositionPixelY].join(' ');
        }

        // attachment
        var attachment = v.cssBackgroundAttachment ? 'fixed' : 'scroll';

        // repeat
        var repeat = v.cssBackgroundRepeat;

        // size
        var size = 'auto auto';
        if (v.cssBackgroundSizeCover) {
            size = 'cover';
        } else {
            if (api.unitLess(v.cssBackgroundSize) > 0) {
                size = v.cssBackgroundSize + ' auto';
            }
        }

        // use single methods, with the + make sure there is ALWAYS a property,
        // otherwise your properties get mixed up
        return {
            // use hat version of background image, otherwise the property
            // merging fails (as there are several background rules for prefixes ans fallback)
            hatBackgroundImage: '+' + cssUrl,
            backgroundPosition: '+' + pos,
            backgroundAttachment: '+' + attachment,
            backgroundRepeat: '+' + repeat,
            backgroundSize: '+' + size
        };
    }
});