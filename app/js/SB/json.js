// JSON utility functions by Seitenbau

/**
* @class SB.json
* Just a namespace containing JSON utility functions
* @singleton
*/
Ext.ns('SB.json');


/**
 * Encodes an Object, Array or other value, sorting Object keys alphabetically for better comparability.
 * Uses parts of Ext's JSON implementation, which in turn is a modification of Douglas Crockford's json.js.
 * @param {Mixed} o The variable to encode
 * @return {String} The JSON string
 */
SB.json.sortedEncode = (function () {
    var m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"': '\\"',
            '\\': '\\\\'
        },
        encodeString = function (s) {
            if (/["\\\x00-\x1f]/.test(s)) {
                return '"' + s.replace(/([\x00-\x1f\\"])/g, function (a, b) {
                    var c = m[b];
                    if (c) {
                        return c;
                    }
                    c = b.charCodeAt();
                    return '\\u00' +
                        Math.floor(c / 16).toString(16) +
                        (c % 16).toString(16);
                }) + '"';
            }
            return '"' + s + '"';
        },
        encodeArray = function (o) {
            var a = ['['], b, i, l = o.length, v;
            for (i = 0; i < l; i += 1) {
                v = o[i];
                switch (typeof v) {
                case 'undefined':
                case 'function':
                case 'unknown':
                    break;

                default:
                    if (b) {
                        a.push(',');
                    }
                    a.push(v === null ? 'null' : Ext.util.JSON.encode(v));
                    b = true;
                }
            }
            a.push(']');
            return a.join('');
        },
        doEncode = function (o) {
            if (!Ext.isDefined(o) || o === null) {
                return 'null';
            } else if (Ext.isArray(o)) {
                return encodeArray(o);
            } else if (Ext.isDate(o)) {
                return Ext.util.JSON.encodeDate(o);
            } else if (Ext.isString(o)) {
                return encodeString(o);
            } else if (typeof o == 'number') {
                //don't use isNumber here, since finite checks happen inside isNumber
                return isFinite(o) ? String(o) : 'null';
            } else if (Ext.isBoolean(o)) {
                return String(o);
            } else {
                var a = ['{'], b, i, v, keys, l;

                // don't encode DOM objects
                if (!o.getElementsByTagName) {
                    // get keys and sort them
                    keys = SB.util.getKeys(o);
                    keys.sort();

                    // parse object properties alphabetically
                    for (i = 0, l = keys.length; i < l; i++) {
                        v = o[keys[i]];
                        switch (typeof v) {
                        case 'undefined':
                        case 'function':
                        case 'unknown':
                            break;
                        default:
                            if (b) {
                                a.push(',');
                            }
                            a.push(doEncode(keys[i]), ':',
                                    v === null ? 'null' : doEncode(v));
                            b = true;
                        }
                    }
                }

                a.push('}');
                return a.join('');
            }
        };

    return function (o) {
        return doEncode(o);
    };

})();

/**
* Check a string for JSON validity. Note that this method does not find all
* syntax errors. It just makes sure the input does not contain code that can
* cause invocation, so it's okay to pass it to <tt>eval</tt>.
* @param {String} input A string to check for JSON validity
* @return {Boolean} <tt>true</tt> if the input is safe for JSON conversion, <tt>false</tt> otherwise
*/
SB.json.isSafe = function (input) {
    if (typeof input != 'string') {
        return false;
    }
    // the following line is taken from http://json.org/json2.js
    var haystick = input.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
                        .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
                        .replace(/(?:^|:|,)(?:\s*\[)+/g, '');
    return (/^[\],:{}\s]*$/.test(haystick) && !/,\s*\}/.test(haystick));
};
