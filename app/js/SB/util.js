// Utility functions by Seitenbau

/**
* @class SB.util
* Just a namespace containing utility functions
* @singleton
*/
Ext.ns('SB.util');

/**
* Get the keys of an object. Cross-browser implementation of Object.keys
* @method getKeys
* @param {object} The object to extract the keys from.
* @return {Array} The input object's keys as Strings.
*/
if (typeof Object.keys == 'function') {
    SB.util.getKeys = Object.keys;
} else {
    SB.util.getKeys = function (obj) {
        if (obj === null || typeof obj != 'object') {
            throw 'Requested keys of a value that is not an object.';
        }
        var result = [];
        var hOP = Object.prototype.hasOwnProperty;
        for (var key in obj) {
            if (hOP.call(obj, key)) {
                result.push(key);
            }
        }
        if (Ext.isIE) {
            // borrowed from Ext 4
            var shadowedKeys = ['constructor', 'hasOwnProperty', 'isPrototypeOf', 'propertyIsEnumerable', 'toLocaleString', 'toString', 'valueOf'];
            for (var i = 0, l = shadowedKeys.length; i < l; i++) {
                if (hOP.call(obj, shadowedKeys[i])) {
                    result.push(shadowedKeys[i]);
                }
            }
        }
        return result;
    };
}


/**
* Checks if an object is the empty object {};
* This is just a missing feature in Ext
* @param {Object} obj
*      an object reference
* @return {Boolean}
*      <tt>true</tt> if and only if an object is the empty object {}
*/
SB.util.isEmptyObject = function (obj) {
    return (!!obj && Object.prototype.toString.call(obj) == '[object Object]' && (SB.util.getKeys(obj).length === 0));
};


/**
* Create a deep-copy of an object.
* @param {Object} obj
*      an object reference
* @return {Object} The cloned object
*/
SB.util.cloneObject = function (obj) {
    return JSON.parse(JSON.stringify(obj));
};


/**
* Generates a random-based UUID as described in
* <a href="https://tools.ietf.org/html/rfc4122#section-4.4">RFC4122, section 4.4</a>
* @method UUID
* @return {String} a new UUID
*/
// http://stackoverflow.com/questions/105034/2117523#2117523
/*jslint bitwise: false*/
/*jshint bitwise: false*/
SB.util.UUID = function () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = Math.random() * 0x10 | 0;
        return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
};
/*jshint bitwise: true*/
/*jslint bitwise: true*/


/**
* Returns a string with the first character of str capitalized, if that character is alphabetic.
* @method ucFirst
* @param {String} string
* @return {String} output
*/
SB.util.ucFirst = function (string) {
    if (typeof string != 'string') {
        return string;
    }
    return string[0].toUpperCase() + string.substr(1);
};


/**
* Find the intersection of any number of given lists
* @param {Array} items ...
* @return {Array} The items that appear in each of the input lists
*/
SB.util.setIntersection = function () {
    // arguments processing
    var inputs = Ext.toArray(arguments);
    if (inputs.length === 0) {
        return [];
    }
    Ext.each(inputs, function (input) {
        if (!Ext.isArray(input)) {
            throw 'Arguments must be arrays';
        }
    });
    if (inputs.length == 1) {
        return inputs[0];
    }
    // sort inputs by length, to optimize performance
    inputs.sort(function (a, b) {
        return a.length - b.length;
    });
    var result = [];
    var first = inputs.shift();
    // compare each item in first array with other array items
    Ext.each(first, function (value) {
        var isCommon = true;
        Ext.each(inputs, function (input) {
            if (input.indexOf(value) == -1) {
                isCommon = false;
                return false;
            }
        });
        if (isCommon) {
            result.push(value);
        }
    });
    return result;
};


/**
* Find the union of any number of given lists
* @param {Array} items ...
* @return {Array} The items that appear in any of the input lists (duplicates removed)
*/
SB.util.setUnion = function () {
    var inputs = Ext.toArray(arguments);
    var result = [];
    Ext.each(inputs, function (input) {
        if (!Ext.isArray(input)) {
            throw 'Arguments must be arrays';
        }
        result = result.concat(input);
    });
    return Ext.unique(result);
};


/**
* Find the difference of two sets
* @param {Array} set1 The set to subtract elements from
* @param {Array} set2 The elements to substract
* @return {Array} The items that appear in set1, but not in set2 (duplicates removed)
*/
SB.util.setDifference = function (set1, set2) {
    var result = [];
    Ext.each(set1, function (el) {
        if (set2.indexOf(el) == -1 && result.indexOf(el) == -1) {
            result.push(el);
        }
    });
    return Ext.unique(result);
};


/**
* Move an item inside an array
* @param {Array} array The array to modify. Note that this array will be edited in-place.
* @param {Integer} indexBefore The index of the item to move
* @param {Integer} offset The offset of the movement
*/
SB.util.moveArrayItem = function (array, indexBefore, offset) {
    if (!offset || indexBefore + offset >= array.length || indexBefore + offset < 0) {
        return array;
    }
    array.splice(indexBefore + offset, 0, array.splice(indexBefore, 1)[0]);
    return array;
};


/**
* Remove all attributes of an object, in order to free the values for garbage collection
* @param {Object} object The object to be cleaned
* @param {String|Array} blacklist (optional) Attributes that should be kept
*/
SB.util.cleanupObject = function (object, blacklist) {
    var keysToDelete = this.getKeys(object);
    if (typeof blacklist == 'string') {
        keysToDelete.remove(blacklist);
    } else if (Ext.isArray(blacklist)) {
        keysToDelete = this.setDifference(keysToDelete, blacklist);
    }
    for (var i = 0; i < keysToDelete.length; i++) {
        delete object[keysToDelete[i]];
    }
    return object;
};

/**
* Interate over the given object and return the value of the key given as a string index path (e.g. 'foo.bar.foo').
* @param {Object} object
* @param {String} path Index path as a string
* @return {Object} The value of the key
*/
SB.util.getObjectByIndexPath = function (object, path) {
    path = path.split('.');
    while (path.length && object) {
        object = object[path.shift()];
    }
    return object;
};

/**
* Convert a relative URL to an absolute one
* @param {String} relativeUrl The relative URL
* @return {String} The absolute URL
*/
SB.util.toAbsoluteUrl = (function () {
    var a;
    return function (relativeUrl) {
        a = a || document.createElement('a');
        a.href = relativeUrl;
        return a.href;
    };
})();

/**
* Checks if a given number is an integer
* @param {Number} n The number to check
* @param {Boolean} allowCoercion <tt>true</tt> to allow non-numerical inputs, e.g. <tt>"1 Eur"</tt>. Defaults to <tt>false</tt>.
*/
SB.util.isInteger = function (n, allowCoercion) {
    if (allowCoercion) {
        n = parseFloat(n);
    }
    return (n === parseInt(n, 10) && !isNaN(n));
};

/**
* Compare properties of two objects
* @param {Object} object1
* @param {Object} object2
* @param {Array|String} keys The properties which to compare. May be an array of strings, or a comma separated list
* @return {Boolean} <tt>true</tt> if the properties are identical, <tt>false</tt> otherwise
*/
SB.util.compareObjectProperties = function (object1, object2, keys) {
    if (typeof keys == 'string') {
        keys = keys.split(/ *, */);
    }
    for (var i = 0; i < keys.length; i++) {
        if (object1[keys[i]] !== object2[keys[i]]) {
            return false;
        }
    }
    return true;
};

/**
* Hide objects from access from other window objects. For example, this may be used to prevent access to
* top.Ext from scipts inside iframes.
* <strong>Warning:</strong> This does not work reliably, since calls from top-level code cannot be detected.
* You may either <strong>allow all</strong> top-level access (from top and other windows), or <strong>disallow all</strong> top-level access.
* Also remember that objects may have indirect references.
* @param {Object} object The object whose properties shall be hidden
* @param {Array|String} properties A comma-separated list or an array of property names
* @param {Boolean} allowTopLevel <tt>true</tt> to allow access from top-level code. Defaults to <tt>false</tt>
*/
SB.util.hideObjectsFromFrames = function (object, properties, allowTopLevel) {
    if (typeof properties == 'string') {
        properties = properties.split(/ *, */);
    }

    if (!allowTopLevel) {
        // feature detection for webkit bug (Safari 5.0) https://bugs.webkit.org/show_bug.cgi?id=45480
        var supportsCaller = true;
        var o = {
            runtest: function f() {
                return this.prop;
            }
        };

        Object.defineProperty(o, 'prop', {
            get: function getter() {
                supportsCaller = !!getter.caller;
                return 'test';
            }
        });

        o.runtest();
        o = null;
        if (!supportsCaller) {
            console.warn('This browser does not properly support getter.caller. Weakening sandbox for ', properties);
            allowTopLevel = true;
        }
    }

    Ext.each(properties, function (property) {
        var orig = object[property];
        if (allowTopLevel) { // checking outside the accessors improves performance
            Object.defineProperty(object, property, {
                get: function g() {
                    if (g.caller && !(g.caller instanceof Function)) {
                        throw 'Security error. Attempt to access ' + property + ' from foreign window';
                    }
                    return orig;
                },
                set: function s(x) {
                    if (s.caller && !(s.caller instanceof Function)) {
                        throw 'Security error. Attempt to overwrite ' + property + ' from foreign window';
                    }
                    orig = x;
                }
            });
        } else {
            Object.defineProperty(object, property, {
                get: function g() {
                    if (!(g.caller instanceof Function)) {
                        throw 'Security error. Attempt to access ' + property + ' from foreign window';
                    }
                    return orig;
                },
                set: function s(x) {
                    if (!(s.caller instanceof Function)) {
                        throw 'Security error. Attempt to overwrite ' + property + ' from foreign window';
                    }
                    orig = x;
                }
            });
        }
    });
};

/**
 * Tries to extract a valid CSS color from a viven id
 * The color may ids contain the actual a color as a fallback
 * (see https://confluence.seitenbau.net/display/CMSENTW/Website#Website-Farbschema-AttributColorscheme-JSON)
 *
 * @param {String} id
 *      The color id
 *
 * @return Strings
 *      The CSS color representation;
 *      undefined if the given id does not contains a color
 */
SB.util.getColorFromColorId = function (id) {
    var result;
    var match = id && id.match(/--(.*?)--COLOR$/);
    var raw = match && match[1];

    if (raw && raw.charAt(0) === '1') {
        var red = parseInt(raw.substr(1, 2), 16);
        var green = parseInt(raw.substr(3, 2), 16);
        var blue = parseInt(raw.substr(5, 2), 16);
        var alpha = parseInt(raw.substr(7, 2), 16) / 100;

        result = 'rgba(' + red + ',' + green + ',' + blue + ',' + alpha + ')';
    }
    if (!result && (!!SB.color.parseColor(id) || !!SB.color.parseColorName(id))) {
        // there is no color within the id but the id is valid color itself
        result = id;
    }

    // no fallback color given, use transparent (this prevents any failures for css or later use)
    if (!result && raw == '000000000000') {
        result = 'transparent';
    }

    return result;
};

/**
 * Adds a GET parameter to the given URL. Supports URLs which already have parameters, hashes etc.
 *
 * @param {String} url
 * @param {String} parameterName
 * @param {String} parameterValue
 *
 * @return {String}
 *      The new URL
 */
SB.util.addParameterToUrl = function (url, parameterName, parameterValue) {

    /* global URI: false */
    var uri = new URI(url);

    // update param
    var searchObj = uri.search(true);
    searchObj[parameterName] = parameterValue;
    uri.search(searchObj);

    return uri.toString();
};
