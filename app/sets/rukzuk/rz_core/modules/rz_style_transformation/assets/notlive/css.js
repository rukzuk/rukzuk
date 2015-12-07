DynCSS.defineModule('rz_style_transformation', function (api, v) {
    var propValue = [];

    if (v.cssEnableTransformRotate) {
        propValue.push('rotate(' + v.cssTransformRotate + ')');
    }

    if (v.cssEnableTransformScale) {
        propValue.push('scale(' + (Number(String(v.cssTransformScale).replace('%', '')) / 100) + ')');
    }

    if (v.cssEnable3D) {
        propValue.push('perspective(' + v.cssPerspective + ') translateX(' + v.cssTranslateX + ') translateY(' + v.cssTranslateY + ') translateZ(' + v.cssTranslateZ + ') rotateX(' + v.cssRotateX + ') rotateY(' + v.cssRotateY + ') rotateZ(' + v.cssRotateZ + ')');
    }

    if (propValue.length > 0) {
        var value = propValue.join(' ');
        return {
            transform: value,
            MozTransform: value,
            WebkitTransform: value
        };
    }
});
