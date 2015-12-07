// Utility functions by Seitenbau

/**
* @class SB.color
* Just a namespace containing color related functions
* @singleton
*/
Ext.ns('SB.color');

(function () {

    var colorNames;

    // http://www.w3.org/TR/css3-color/#svg-color
    // The conversion grey -> gray is done in parseColorName
    var webColors = {
        'aliceblue': '#f0f8ff',
        'antiquewhite': '#faebd7',
        'aqua': '#00ffff',
        'aquamarine': '#7fffd4',
        'azure': '#f0ffff',
        'beige': '#f5f5dc',
        'bisque': '#ffe4c4',
        'black': '#000000',
        'blanchedalmond': '#ffebcd',
        'blue': '#0000ff',
        'blueviolet': '#8a2be2',
        'brown': '#a52a2a',
        'burlywood': '#deb887',
        'cadetblue': '#5f9ea0',
        'chartreuse': '#7fff00',
        'chocolate': '#d2691e',
        'coral': '#ff7f50',
        'cornflowerblue': '#6495ed',
        'cornsilk': '#fff8dc',
        'crimson': '#dc143c',
        'cyan': '#00ffff',
        'darkblue': '#00008b',
        'darkcyan': '#008b8b',
        'darkgoldenrod': '#b8860b',
        'darkgray': '#a9a9a9', // = darkgrey
        'darkgreen': '#006400',
        'darkkhaki': '#bdb76b',
        'darkmagenta': '#8b008b',
        'darkolivegreen': '#556b2f',
        'darkorange': '#ff8c00',
        'darkorchid': '#9932cc',
        'darkred': '#8b0000',
        'darksalmon': '#e9967a',
        'darkseagreen': '#8fbc8f',
        'darkslateblue': '#483d8b',
        'darkslategray': '#2f4f4f', // = darkslategrey
        'darkturquoise': '#00ced1',
        'darkviolet': '#9400d3',
        'deeppink': '#ff1493',
        'deepskyblue': '#00bfff',
        'dimgray': '#696969', // = dimgrey
        'dodgerblue': '#1e90ff',
        'firebrick': '#b22222',
        'floralwhite': '#fffaf0',
        'forestgreen': '#228b22',
        'fuchsia': '#ff00ff',
        'gainsboro': '#dcdcdc',
        'ghostwhite': '#f8f8ff',
        'gold': '#ffd700',
        'goldenrod': '#daa520',
        'gray': '#808080', // = grey
        'green': '#008000',
        'greenyellow': '#adff2f',
        'honeydew': '#f0fff0',
        'hotpink': '#ff69b4',
        'indianred': '#cd5c5c',
        'indigo': '#4b0082',
        'ivory': '#fffff0',
        'khaki': '#f0e68c',
        'lavender': '#e6e6fa',
        'lavenderblush': '#fff0f5',
        'lawngreen': '#7cfc00',
        'lemonchiffon': '#fffacd',
        'lightblue': '#add8e6',
        'lightcoral': '#f08080',
        'lightcyan': '#e0ffff',
        'lightgoldenrodyellow': '#fafad2',
        'lightgray': '#d3d3d3', // = lightgrey
        'lightgreen': '#90ee90',
        'lightpink': '#ffb6c1',
        'lightsalmon': '#ffa07a',
        'lightseagreen': '#20b2aa',
        'lightskyblue': '#87cefa',
        'lightslategray': '#778899', // = lightslategrey
        'lightsteelblue': '#b0c4de',
        'lightyellow': '#ffffe0',
        'lime': '#00ff00',
        'limegreen': '#32cd32',
        'linen': '#faf0e6',
        'magenta': '#ff00ff',
        'maroon': '#800000',
        'mediumaquamarine': '#66cdaa',
        'mediumblue': '#0000cd',
        'mediumorchid': '#ba55d3',
        'mediumpurple': '#9370db',
        'mediumseagreen': '#3cb371',
        'mediumslateblue': '#7b68ee',
        'mediumspringgreen': '#00fa9a',
        'mediumturquoise': '#48d1cc',
        'mediumvioletred': '#c71585',
        'midnightblue': '#191970',
        'mintcream': '#f5fffa',
        'mistyrose': '#ffe4e1',
        'moccasin': '#ffe4b5',
        'navajowhite': '#ffdead',
        'navy': '#000080',
        'oldlace': '#fdf5e6',
        'olive': '#808000',
        'olivedrab': '#6b8e23',
        'orange': '#ffa500',
        'orangered': '#ff4500',
        'orchid': '#da70d6',
        'palegoldenrod': '#eee8aa',
        'palegreen': '#98fb98',
        'paleturquoise': '#afeeee',
        'palevioletred': '#db7093',
        'papayawhip': '#ffefd5',
        'peachpuff': '#ffdab9',
        'peru': '#cd853f',
        'pink': '#ffc0cb',
        'plum': '#dda0dd',
        'powderblue': '#b0e0e6',
        'purple': '#800080',
        'red': '#ff0000',
        'rosybrown': '#bc8f8f',
        'royalblue': '#4169e1',
        'saddlebrown': '#8b4513',
        'salmon': '#fa8072',
        'sandybrown': '#f4a460',
        'seagreen': '#2e8b57',
        'seashell': '#fff5ee',
        'sienna': '#a0522d',
        'silver': '#c0c0c0',
        'skyblue': '#87ceeb',
        'slateblue': '#6a5acd',
        'slategray': '#708090', // = slategrey
        'snow': '#fffafa',
        'springgreen': '#00ff7f',
        'steelblue': '#4682b4',
        'tan': '#d2b48c',
        'teal': '#008080',
        'thistle': '#d8bfd8',
        'tomato': '#ff6347',
        'turquoise': '#40e0d0',
        'violet': '#ee82ee',
        'wheat': '#f5deb3',
        'white': '#ffffff',
        'whitesmoke': '#f5f5f5',
        'yellow': '#ffff00',
        'yellowgreen': '#9acd32'
    };

    // http://de.wikipedia.org/wiki/HSV-Farbraum#Umrechnung_RGB_in_HSV
    /**
    * Convert RGB to HSV values
    * @method rgbToHsv
    * @param {Object} input An object with attributes r/g/b as numbers 0..255
    * @return Object Object with attributes h/s/v as numbers 0..360/0..1/0..1
    */
    SB.color.rgbToHsv = function (input) {
        var r,
            g,
            b;
        r = input.r / 255;
        g = input.g / 255;
        b = input.b / 255;
        var v = Math.max(r, g, b);
        var min = Math.min(r, g, b);
        var h = 0;
        switch (v) {
        case min:
            break;
        case r:
            h = 60 * (g - b) / (v - min);
            break;
        case g:
            h = 60 * (2 + (b - r) / (v - min));
            break;
        case b:
            h = 60 * (4 + (r - g) / (v - min));
            break;
        default:
            break;
        }
        if (h < 0) {
            h += 360;
        }
        var s = v ? (1 - min / v) : 0;
        return {
            h: h,
            s: s,
            v: v
        };
    };

    /**
    * Convert RGBa to HSVa values
    * @method rgbToHsva
    * @param {Object} input An object with attributes r/g/b/a as numbers 0..255/0..255/0..255/0..1
    * @return Object Object with attributes h/s/v/a as numbers 0..360/0..1/0..1/0..1
    */
    SB.color.rgbaToHsva = function (input) {
        var result = this.rgbToHsv(input);
        result.a = input.a;
        return result;
    };

    // http://de.wikipedia.org/wiki/HSV-Farbraum#Umrechnung_HSV_in_RGB
    /**
    * Convert HSV to RGB values
    * @method hsvToRgb
    * @param {Object} input An object with attributes h/s/v as numbers 0..255/0..1/0..1
    * @return Object Object with attributes r/g/b as integers 0..255
    */
    SB.color.hsvToRgb = function (input) {
        var h = input.h;
        var s = input.s;
        var v = 255 * input.v;
        var hh = Math.floor(h / 60);
        var f = h / 60 - hh;
        var p = parseInt(v * (1 - s) + 0.5, 10);
        var q = parseInt(v * (1 - s * f) + 0.5, 10);
        var t = parseInt(v * (1 - s * (1 - f)) + 0.5, 10);
        v = parseInt(v + 0.5, 10);
        switch (hh) {
        case 1:
            return {
                r: q,
                g: v,
                b: p
            };

        case 2:
            return {
                r: p,
                g: v,
                b: t
            };

        case 3:
            return {
                r: p,
                g: q,
                b: v
            };

        case 4:
            return {
                r: t,
                g: p,
                b: v
            };

        case 5:
            return {
                r: v,
                g: p,
                b: q
            };

        default:
            return {
                r: v,
                g: t,
                b: p
            };
        }
    };

    /**
    * Convert HSVa to RGBa values
    * @method hsvToRgb
    * @param {Object} input An object with attributes h/s/v/a as numbers 0..255/0..1/0..1/0..1
    * @return Object Object with attributes r/g/b/a as numbers 0..255/0..255/0..255/0..1 where r/g/b are integers
    */
    SB.color.hsvaToRgba = function (input) {
        var result = this.hsvToRgb(input);
        result.a = input.a;
        return result;
    };

    // http://ariya.blogspot.com/2008/07/converting-between-hsl-and-hsv.html
    /**
    * Convert HSV to HSL values
    * @method hsvToHsl
    * @param {Object} input An object with attributes h/s/v as numbers 0..255/0..1/0..1
    * @return Object Object with attributes h/s/l as numbers 0..255/0..1/0..1
    */
    SB.color.hsvToHsl = function (input) {
        var s = parseFloat(input.s);
        var v = parseFloat(input.v);
        var l = (2 - s) * v;
        return {
            h: input.h,
            s: s * v / ((l < 1) ? l : (2 - l)),
            l: l / 2
        };
    };

    // http://ariya.blogspot.com/2008/07/converting-between-hsl-and-hsv.html
    /**
    * Convert HSL to HSV values
    * @method hssToHsv
    * @param {Object} input An object with attributes h/s/l as numbers 0..255/0..1/0..1
    * @return Object Object with attributes h/s/v as numbers 0..255/0..1/0..1
    */
    SB.color.hslToHsv = function (input) {
        var s = parseFloat(input.s);
        var l = parseFloat(input.l);
        l *= 2;
        if (l < 1) {
            s *= l;
        } else {
            s *= 2 - l;
        }
        return {
            h: input.h,
            s: 2 * s / (l + s),
            v: (l + s) / 2
        };
    };

    /**
    * Convert rgba string as in CSS values to an HSVa object
    * @method parseRgba
    * @param {String} input A color definition of the form <tt>"rgba(123, 123, 123, 0.12)"</tt>
    * @return Object See {@link #rgbToHsva}
    */
    SB.color.parseRgba = function (input) {
        input = input.replace(/ +/g, '').replace(/^rgba\(/, '').replace(/\)$/, '').split(',');
        if (input.length != 4 || !input[0].length || !input[1].length || !input[2].length || !input[3].length) {
            return null;
        }
        for (var i = 0; i < input.length; i++) {
            if (/%/.test(input[i])) {
                input[i] = parseInt(input[i], 10) * 2.55;
            }
            input[i] = +input[i];
        }
        return this.rgbaToHsva({
            r: parseInt(input[0] + 0.5, 10),
            g: parseInt(input[1] + 0.5, 10),
            b: parseInt(input[2] + 0.5, 10),
            a: parseFloat(input[3])
        });
    };

    /**
    * Convert rgb string as in CSS values to an HSVa object
    * @method parseRgb
    * @param {String} input A color definition of the form <tt>"rgb(123, 123, 123)"</tt>
    * @return Object See {@link #rgbToHsva}. The returned alpha value will equal 1.
    */
    SB.color.parseRgb = function (input) {
        return this.parseRgba(input.replace('rgb(', 'rgba(').replace(')', ',1)'));
    };

    /**
    * Convert hsla string as in CSS values to an HSVa object
    * @method parseHsla
    * @param {String} input A color definition of the form <tt>"hsla(123, 12%, 12%, 0.12)"</tt>
    * @return Object See {@link hslToHsv}
    */
    SB.color.parseHsla = function (input) {
        input = input.replace(/ +/g, '').replace(/^hsla\(/, '').replace(/\)$/, '').split(',');
        if (input.length != 4) {
            return null;
        }
        input[0] = parseInt(input[0], 10) % 360;
        if (input[0] < 0) {
            input[0] += 360;
        }
        var result = this.hslToHsv({
            h: input[0],
            s: parseInt(input[1], 10) / 100,
            l: parseInt(input[2], 10) / 100
        });
        result.a = parseFloat(input[3]);
        return result;
    };

    /**
    * Convert hsl string as in CSS values to an HSVa object
    * @method parseHsl
    * @param {String} input A color definition of the form <tt>"hsl(123, 12%, 12%)"</tt>
    * @return Object See {@link hslToHsv}. The returned alpha value will equal 1.
    */
    SB.color.parseHsl = function (input) {
        return this.parseHsla(input.replace('hsl(', 'hsla(').replace(')', ',1)'));
    };

    /**
    * Convert HEX color string as in CSS values to an HSVa object
    * @method parseHex
    * @param {String} input A color definition of the form <tt>"#abc"</tt> or <tt>"#1a2b3c"</tt>
    * @return Object See {@link rgbaToHsva}. The returned alpha value will equal 1.
    */
    SB.color.parseHex = function (input) {
        var r,
            g,
            b;
        input = input.replace('#', '');
        if (input.length == 3) {
            r = parseInt('' + input[0] + input[0], 16);
            g = parseInt('' + input[1] + input[1], 16);
            b = parseInt('' + input[2] + input[2], 16);
        } else {
            r = parseInt('' + input[0] + input[1], 16);
            g = parseInt('' + input[2] + input[3], 16);
            b = parseInt('' + input[4] + input[5], 16);
        }
        return this.rgbaToHsva({
            r: r,
            g: g,
            b: b,
            a: 1
        });
    };

    /**
    * Convert color keyword as in CSS values to an HSVa object
    * @method parseColorName
    * @param {String} input A color definition as in <a href="http://www.w3.org/TR/css3-color/#colorunits">www.w3.org/TR/css3-color</a>
    * @return Object See {@link rgbaToHsva}. The returned alpha value will equal 0 if input equals <tt>"transparent"</tt>, 1 otherwise.
    */
    SB.color.parseColorName = function (input) {
        input = input.toLowerCase().replace('grey', 'gray');
        if (input == 'transparent') {
            return {
                h: 0,
                s: 0,
                v: 0,
                a: 0
            };
        }
        if (webColors[input]) {
            return this.parseHex(webColors[input]);
        }
        return null;
    };

    /**
    * Convert any CSS color value to an HSVa object
    * @param {String} input A CSS color value as in <a href="http://www.w3.org/TR/css3-color/#colorunits">www.w3.org/TR/css3-color</a>.
    * Please note that the deprecated <a href="http://www.w3.org/TR/css3-color/#css-system">CSS2 system colors</a> are not supported.
    */
    SB.color.parseColor = function (input) {
        if (!input) {
            return null;
        }
        if (/^rgba\(/.test(input)) {
            return this.parseRgba(input);
        } else if (/^rgb\(/.test(input)) {
            return this.parseRgb(input);
        } else if (/^hsla\(/.test(input)) {
            return this.parseHsla(input);
        } else if (/^hsl\(/.test(input)) {
            return this.parseHsl(input);
        } else if (/^#?([a-fA-F0-9]{3}){1,2}$/.test(input)) {
            return this.parseHex(input);
        } else if (/^[a-zA-Z]+$/.test(input)) {
            return this.parseColorName(input);
        }
        return null;
    };

    /**
    * Convert a HSV color value to a CSS String
    * @method hsvToCssString
    * @param {Object} input An object with attributes h/s/v as numbers 0..255/0..1/0..1 and optionally an "a" attribute as number 0..1
    * @param {Boolean} (optional) returnColorNames If <tt>true</tt> and the input is a named color, the colors name as in
    * <a href="http://www.w3.org/TR/css3-color/#colorunits">www.w3.org/TR/css3-color</a> is returned
    * @param {Boolean} forceRgba <tt>true</tt> to return rgba values for fully opaque colors. If <tt>false</tt> or omitted, a hex representation is returned
    * @return {String} If the input color isn't fully opaque, a string of the form <tt>"rgba(123, 123, 123, 0.12)", otherwise
    * a string of the form <tt>"#a1b2c3"</tt>, or (if <tt>returnColorNames</tt> is <tt>true</tt>) a color name if applicable
    */
    /**
    * Alias for {@link hsvToCssString}
    * @method hsvaToCssString
    */
    SB.color.hsvToCssString = SB.color.hsvaToCssString = function (input, returnColorNames, forceRgba) {
        var rgb = this.hsvToRgb(input);
        if (input.a === 0) {
            return returnColorNames ? 'transparent' : 'rgba(0, 0, 0, 0)';
        }
        if (input.hasOwnProperty('a') && input.a != 1 && !Ext.isIE7 && !Ext.isIE8) {
            return 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + input.a + ')';
        }
        var result = '#';
        if (rgb.r < 16) {
            result += '0';
        }
        result += rgb.r.toString(16);
        if (rgb.g < 16) {
            result += '0';
        }
        result += rgb.g.toString(16);
        if (rgb.b < 16) {
            result += '0';
        }
        result += rgb.b.toString(16);
        var colorHasName = false;
        if (returnColorNames) {
            if (!colorNames) {
                colorNames = {};
                for (var name in webColors) {
                    if (webColors.hasOwnProperty(name)) {
                        colorNames[webColors[name]] = name;
                    }
                }
            }
            if (colorNames[result]) {
                result = colorNames[result];
                colorHasName = true;
            }
        }
        if (!colorHasName && forceRgba) {
            result = 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + (input.a || '1') + ')';
        }
        return result;
    };

    /**
    * Converts a color in any given format to an HSVa object
    * @param {Object|String} input An RGB/RGBa/HSV/HSVa/HSL/HSLa object or a string as in {@link #parseColor}
    * @return Object See {@link rgbaToHsva}.
    */
    SB.color.normalizeColor = function (input) {
        var result;
        if (typeof input == 'object') {
            if (input.hasOwnProperty('v')) { // hsv
                result = input;
            } else if (input.hasOwnProperty('l')) { // hsl
                result = this.hslToHsv(input);
            } else { // rgb
                result = this.rgbToHsv(input);
            }
            result.a = input.hasOwnProperty('a') ? input.a : 1;
        } else {
            result = this.parseColor(input);
        }
        return result;
    };

    /**
    * Calculates a color's brightness according to http://www.poynton.com/notes/colour_and_gamma/ColorFAQ.html#RTFToC18
    * @param {String|Object} color Input color in any format (see {@link #normalizeColor})
    * @param {String|Object} background (optional) If color has an alpha channel, it is blended into <tt>background</tt>.
    * Note that background must be fully opaque;
    */
    SB.color.calculateBrightness = function (color, background) {
        color = this.hsvaToRgba(this.normalizeColor(color));
        var brightness = (0.213 * color.r + 0.715 * color.g + 0.072 * color.b) / 255;
        if (background && color.hasOwnProperty('a') && color.a != 1) {
            background = this.hsvaToRgba(this.normalizeColor(background));
            brightness = color.a * brightness + (1 - color.a) * this.calculateBrightness(background);
        }
        return brightness;
    };
})();
