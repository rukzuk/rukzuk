DynCSS.defineModule('rz_style_font', function (api, v, context) {
    var result = {};

    if (v.cssEnableFontFamily) {

        var fonts = [];

        if (v.cssWebFontId) {
            //fix for IE9: length of a name of a font-family must not exceed 31 chars! (see: rz_style_webfont/notlive/css.js)
            var webfontFamily = v.cssWebFontId.replace(/'|"/g, '').substr(0, 31);
            fonts.push('\'' + webfontFamily + '\'');
        }

        if (v.cssFontFamilyGoogle && v.cssFontFamilyGoogle != 'null') {
            fonts.push('\'' + v.cssFontFamilyGoogle + '\'');
        }

        if (v.cssFontFamily.length) {
            fonts.push(v.cssFontFamily);
        }

        result.fontFamily = fonts.join(', ');
    }

    if (v.cssEnableColor) {
        var color = api.getColorById(v.cssColor);

        result['&'] = {
            color: color
        };
    }

    if (v.cssEnableFontSize) {
        result.fontSize = v.cssFontSize;

        if (v.cssEnableFontSizeVw) {
            result.fontSize = v.cssFontSizeVw;
        }
    }

    if (v.cssEnableFontStyle) {
        result.fontWeight = v.cssFontWeight;
        result.fontStyle = v.cssEnableItalic ? 'italic' : 'normal';
        result.fontVariant = v.cssEnableCaps ? 'small-caps' : 'normal';
    }

    if (v.cssEnableTextDecoration) {
        var textDecorations = [];
        if (v.cssEnableUnderline) {
            textDecorations.push('underline');
        }
        if (v.cssEnableOverline) {
            textDecorations.push('overline');
        }
        if (v.cssEnableLineThrough) {
            textDecorations.push('line-through');
        }

        if (textDecorations.length) {
            result.textDecoration = textDecorations.join(' ');
        } else {
            result.textDecoration = 'none';
        }
    }

    if (v.cssEnableTextTransform) {
        result.textTransform = v.cssTextTransform;
    }

    if (v.cssEnableTextAlign) {
        result.textAlign = v.cssTextAlign;
    }

    if (v.cssEnableLineHeight) {
        result.lineHeight = v.cssLineHeight;
    }

    if (v.cssEnableLetterSpacing) {
        result.letterSpacing = v.cssLetterSpacing;
    }

    return result;
});
