DynCSS.defineModule('rz_style_background_gradient', function (api, v) {
    // enabled?
    if (!v.cssEnableBackgroundGradient) {
        return;
    }

    // config
    var rotationValue = 0;
    var direction = 'top';
    var useRotationValue = false;
    var isLinear = true;
    var radialShape = 'ellipse';

    var colorsStopValues = [];
    var colorsStopPosition = [];

    // the new official w3c syntax (supported by IE10 and FF > 16)
    var newSyntaxDirectionMapping = {left: 'to right', top: 'to bottom'};

    var w3cSyntax = [];


    // read config-form values
    if (v.cssGradientType == 'linear') {

        // rotation presetes
        if (v.cssLinearType == 'preset') {
            if (v.cssBackgroundGradientDirection == 'top' || v.cssBackgroundGradientDirection == 'left') {
                direction = v.cssBackgroundGradientDirection;

            } else if (v.cssBackgroundGradientDirection == 'diagonal_tl') {
                useRotationValue = true;
                rotationValue = -45;

            } else if (v.cssBackgroundGradientDirection == 'diagonal_bl') {
                useRotationValue = true;
                rotationValue = 45;
            }

            // manual rotation
        } else if (v.cssLinearType == 'custom') {
            useRotationValue = true;
            // remove unit - we need to do some caluclations later (other units are not supported atm)
            rotationValue = Number(String(v.cssRotationValue).replace('deg', ''));
        }

    } else if (v.cssGradientType == 'radial') {
        isLinear = false;
        radialShape = v.cssRadialType;
    }

    if (v.cssColorStops) {
        if (v.cssCustomColor1On && v.cssCustomColor1Color) {
            colorsStopValues.push(api.getColorById(v.cssCustomColor1Color));
            colorsStopPosition.push(v.cssCustomColor1Pos.replace('%', ''));
        }

        if (v.cssCustomColor2On && v.cssCustomColor2Color) {
            colorsStopValues.push(api.getColorById(v.cssCustomColor2Color));
            colorsStopPosition.push(v.cssCustomColor2Pos.replace('%', ''));
        }

        if (v.cssCustomColor3On && v.cssCustomColor3Color) {
            colorsStopValues.push(api.getColorById(v.cssCustomColor3Color));
            colorsStopPosition.push(v.cssCustomColor3Pos.replace('%', ''));
        }

        if (v.cssCustomColor4On && v.cssCustomColor4Color) {
            colorsStopValues.push(api.getColorById(v.cssCustomColor4Color));
            colorsStopPosition.push(v.cssCustomColor4Pos.replace('%', ''));
        }
    }

    if (v.cssBackgroundGradientStartColor && v.cssBackgroundGradientEndColor) {
        v.cssBackgroundGradientStartColor = api.getColorById(v.cssBackgroundGradientStartColor);
        v.cssBackgroundGradientEndColor = api.getColorById(v.cssBackgroundGradientEndColor);

        if (isLinear) {
            // linear gradient
            if (!useRotationValue) {
                w3cSyntax.push('linear-gradient(' + newSyntaxDirectionMapping[direction]);
            } else {
                w3cSyntax.push('linear-gradient(' + rotationValue + 'deg');
            }

            // start color
            w3cSyntax.push(v.cssBackgroundGradientStartColor + ' 0% ');

            // stop colors
            for (var k = 0; k < colorsStopValues.length; ++k) {
                var colorL = colorsStopValues[k];
                var posL = colorsStopPosition[k];

                var w3c_stop_colorL = colorL + ' ' + posL + '% ';

                w3cSyntax.push(w3c_stop_colorL);
            }

            // end color
            w3cSyntax.push(v.cssBackgroundGradientEndColor + ' 100%)');

        } else {
            //radial
            w3cSyntax.push('radial-gradient(' + radialShape + ' at center');


            // start color
            w3cSyntax.push(v.cssBackgroundGradientStartColor + ' 0%');

            // stop colors
            for (var i = 0; i < colorsStopValues.length; ++i) {
                var colorR = colorsStopValues[i];
                var posR = colorsStopPosition[i];

                var w3c_stop_colorR = colorR + ' ' + posR + '% ';

                w3cSyntax.push(w3c_stop_colorR);
            }

            // end color
            w3cSyntax.push(v.cssBackgroundGradientEndColor + ' 100%)');
        }
    }

    var wc3SyntaxString = '+' + w3cSyntax.join(', ');

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
    var size = v.cssBackgroundSizeWidth + ' ' + v.cssBackgroundSizeHeight;

    return {
        // use absurdHat to generate prefixed variants
        //hatBackgroundImage: wc3SyntaxString,
        hatBackgroundImage: wc3SyntaxString,
        // write all properties written in rz_background_image because of
        // the multiple backgrounds (otherwise the order gets mixed up!)
        backgroundSize: '+' + size,
        backgroundPosition: '+' + pos,
        backgroundAttachment: '+' + attachment,
        backgroundRepeat: '+' + repeat
    };
});
