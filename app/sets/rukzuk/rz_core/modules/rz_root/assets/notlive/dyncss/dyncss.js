/* global Absurd */
(function (global) {

    /**
     * from: https://github.com/efacilitation/diff2
     */
    var Diff = (function() {
        function Diff() {}

        Diff.DIFFERENCE_TYPES = {
            ADDED: 'added',
            DELETED: 'deleted',
            CHANGED: 'changed'
        };

        Diff.prototype.calculateDifferences = function(oldValue, newValue, key, path) {
            var newValueType, oldValueType, pathElement;
            /*jshint eqnull:true */
            if (key == null) {
                key = '';
            }
            /*jshint eqnull:true */
            if (path == null) {
                path = [];
            }
            newValueType = this._getType(newValue);
            oldValueType = this._getType(oldValue);
            if (key !== '') {
                pathElement = {
                    key: key,
                    valueType: newValueType
                };
                path = [].concat(path, [pathElement]);
            }
            if (typeof oldValue === 'function' || typeof newValue === 'function') {
                return [];
            } else if (oldValueType === 'undefined') {
                return [this._createDifference(Diff.DIFFERENCE_TYPES.ADDED, path, newValue)];
            } else if (newValueType === 'undefined') {
                return [this._createDifference(Diff.DIFFERENCE_TYPES.DELETED, path)];
            } else if (oldValueType !== newValueType) {
                return [this._createDifference(Diff.DIFFERENCE_TYPES.CHANGED, path, newValue)];
            } else if (oldValueType === 'object' || oldValueType === 'array') {
                return this._getNestedDifferences(oldValue, newValue, path);
            } else if (newValue !== oldValue) {
                return [this._createDifference(Diff.DIFFERENCE_TYPES.CHANGED, path, newValue)];
            } else {
                return [];
            }
        };

        Diff.prototype._createDifference = function(type, path, value) {
            var difference;
            difference = {
                type: type,
                path: path
            };
            if (this._getType(value !== 'undefined')) {
                difference.value = value;
            }
            return difference;
        };

        Diff.prototype._getNestedDifferences = function(oldObject, newObject, path) {
            var allKeysToCheck, differences;
            /*jshint eqnull:true */
            if (path == null) {
                path = [];
            }
            allKeysToCheck = this._union(Object.keys(oldObject), Object.keys(newObject));
            differences = allKeysToCheck.map((function(_this) {
                return function(key) {
                    return _this.calculateDifferences(oldObject[key], newObject[key], key, path);
                };
            })(this));
            return this._flatten(differences);
        };

        Diff.prototype._union = function(array1, array2) {
            return array1.concat(array2.filter(function(value) {
                return array1.indexOf(value) === -1;
            }));
        };

        Diff.prototype._flatten = function(arrayOfArrays) {
            return arrayOfArrays.reduce((function(prev, current) {
                return prev.concat(current);
            }), []);
        };

        Diff.prototype._getType = function(input) {
            var type;
            type = typeof input;
            if (type === 'object' && this._isArray(input)) {
                return 'array';
            } else {
                return type;
            }
        };

        Diff.prototype._isArray = function(input) {
            return {}.toString.call(input) === "[object Array]";
        };

        Diff.prototype.applyDifferences = function(object, differences) {
            differences.forEach((function(_this) {
                return function(difference) {
                    var lastKey, lastReference, pathCopy;
                    pathCopy = difference.path.slice(0);
                    lastKey = pathCopy.pop().key;
                    lastReference = pathCopy.reduce(function(object, pathElement) {
                        if (!object[pathElement.key]) {
                            _this._createValue(object, pathElement.key, pathElement.valueType);
                        }
                        return object[pathElement.key];
                    }, object);
                    if (difference.type === Diff.DIFFERENCE_TYPES.CHANGED || difference.type === Diff.DIFFERENCE_TYPES.ADDED) {
                        /*jshint -W093 */
                        return lastReference[lastKey] = difference.value;
                    } else {
                        return delete lastReference[lastKey];
                    }
                };
            })(this));
            return object;
        };

        Diff.prototype._createValue = function(object, key, type) {
            /*jshint -W093 */
            return object[key] = this._createFromType(type);
        };

        Diff.prototype._createFromType = function(type) {
            if (type === 'object') {
                return {};
            }
            if (type === 'array') {
                return [];
            }
        };

        return Diff;

    })();

    var diff = new Diff();


    /**
     * Collection of helpers for responsive fields
     * @returns {{valueInheritance: Function, sortResolutionIds: Function, containsResponsiveValue: Function, isResponsiveValue: Function}}
     * @constructor
     */
    var responsiveFieldHelpers = (function () {

        /**
         * Checks weather the provided Object is a responsive (breakpoint) value
         * @protected
         * @param {Object} value
         */
        var isResponsiveValue = function (value) {
            return (value && value.type === 'bp');
        };

        /**
         * Deep copy of an object (using json)
         * @param {Object} obj
         * @returns {Object} copy
         */
        var copyObj = function (obj) {
            return JSON.parse(JSON.stringify(obj));
        };

        /**
         * Check an array of objects if they contain at least one responsive value
         * @protected
         * @param {Array} formValues formValues object (for one unit)
         */
        var containsResponsiveValue = function (formValues) {
            var hasResponsiveValue = false;
            Object.keys(formValues).forEach(function (key) {
                var param = formValues[key];
                if (isResponsiveValue.call(this, param)) {
                    hasResponsiveValue = true;
                    return true;
                }
            }, this);
            return hasResponsiveValue;
        };

        /**
         * Sort the resolutions
         * @param {Object} resolutions
         * @returns {*}
         */
        var sortResolutionIds = function (resolutions) {
            var sortedResolutionIds = copyObj(resolutions.data).sort(function (a, b) {
                return (a.width - b.width);
            }).map(function (item) {
                return item.id;
            });

            return sortedResolutionIds;
        };


        /**
         * Inherit values based on the breakpoint logic (max-width based desktop-first)
         * @protected
         * @param {Object} value - responsive value (type === bp)
         * @param {Array} sortedResolutionIds - ['res3', 'res2', 'res1', 'default'] sorted from small width to big width (ascending)
         *
         * @return {Object} {{value: Object, inheritedFrom: string}}
         *         the value object with inherited values (copy) and if and where it was inherited from
         */
        var valueInheritance = function (value, sortedResolutionIds) {
            // copy value
            var valCopy = copyObj(value);
            var inheritedFrom = {};

            // iterate over all resolutions (excluding default)
            for (var i = 0; i < (sortedResolutionIds.length - 1); i++) {
                var resId = sortedResolutionIds[i];
                // value obj has no value for this resolution, try to find one
                if (valCopy.hasOwnProperty(resId) === false) {
                    for (var j = i + 1; j < sortedResolutionIds.length; j++) {
                        var copyFromRes = sortedResolutionIds[j];
                        if (valCopy.hasOwnProperty(copyFromRes)) {
                            valCopy[resId] = valCopy[copyFromRes];
                            inheritedFrom[resId] = copyFromRes;
                            break;
                        }
                    }
                }
            }

            return {
                value: valCopy,
                inheritedFrom: inheritedFrom
            };
        };

        return {
            valueInheritance: valueInheritance,
            sortResolutionIds: sortResolutionIds,
            containsResponsiveValue: containsResponsiveValue,
            isResponsiveValue: isResponsiveValue,
            copyObj: copyObj,

        };
    })();

    /**
     * Absurdify
     * Convert plain CSS to absurd.js js objects format
     * based on: https://github.com/zachariahtimothy/converter-absurdjs-css/blob/master/app/scripts/main.js
     */
    var absurdify = (function () {
        var parseCSS = function (css) {
            var rules = {};
            css = removeComments(css);
            var blocks = css.split('}');
            blocks.pop();
            var len = blocks.length;
            // no blocks, assume only rules
            if (len === 0) {
                rules['&'] = parseCSSBlock(css);
            } else {
                // parse all blocks
                for (var i = 0; i < len; i++) {
                    var pair = blocks[i].split('{');
                    if (pair.length > 1) {
                        rules[trim(pair[0])] = parseCSSBlock(pair[1]);
                    }
                }
            }

            return rules;
        };

        var parseCSSBlock = function (css) {
            var rule = {};
            var declarations = css.split(';');
            declarations.pop();
            var len = declarations.length;
            for (var i = 0; i < len; i++) {
                var loc = declarations[i].indexOf(':');
                var property = trim(declarations[i].substring(0, loc));
                var value = trim(declarations[i].substring(loc + 1));

                if (property !== "" && value !== "") {
                    rule[property] = value;
                }
            }
            return rule;
        };

        var removeComments = function (css) {
            return css.replace(/\/\*(\r|\n|.)*\*\//g, "");
        };

        var trim = function (str) {
            return (str && typeof str === 'string') ? String.prototype.trim.call(str) : '';
        };

        return {
            parseCSS: parseCSS
        };
    })();


    /**
     * DynCss
     * Runtime for absurd.js based json trees
     * @returns {{defineModule: Function, compile: Function, add: Function, registerGetColorById: Function, registerGetImageUrl: Function, registerGetMediaUrl: Function, setFormValueImpl: Function, setResolutionsImpl: Function, enableDebug: Function, log: Function}}
     * @constructor
     */
    var dynCSS = function () {
        var debug = false;
        var formValuesImpl;
        var resolutionsImpl;
        var _resolutionSortedIds;
        var _resolutionsBigToSmallWithoutDefault;
        var _resolutionsMap;
        var _resolutionsEnabled;

        /* jshint newcap: false */
        var api = Absurd();

        // load absurdhat
        var absurdHat = global.absurdhat;
        absurdHat(api);

        /* jshint newcap: true */
        var helper = responsiveFieldHelpers;
        api.register('rawCSS', function (css) {
            if (typeof css === 'string') {
                return absurdify.parseCSS(css);
            }
            return {};
        });

        var setFormValueImpl = function (fn) {
            if (fn) {
                formValuesImpl = fn;
            }
        };

        var setResolutionsImpl = function (fn) {
            if (fn) {
                resolutionsImpl = fn;
            }
        };

        /**
         * Enable/Disable the Debug Mode
         * Currently:
         * - outputs a custom property which holds the unitId for the generated style
         * - allows the use of the log function
         * @param on
         */
        var enableDebug = function (on) {
            debug = on;
        };

        /**
         * Sorted resolutions ids (res3, res2, res1, ... , default)
         * Order: Small to Big in width
         * with caching
         *
         * @param {Object} [resolutions] - resolutions object
         *
         * @returns {Array} - sorted array of resolutions
         */
        var getResolutionsSortedIds = function (resolutions) {
            if (!_resolutionSortedIds) {
                resolutions = resolutions || getResolutions();
                _resolutionSortedIds = helper.sortResolutionIds(resolutions);

                // default res add if not present
                if (_resolutionSortedIds.indexOf('default') === -1) {
                    // add default
                    _resolutionSortedIds.push('default');
                }
            }
            return _resolutionSortedIds;
        };

        var getResolutionIdsBigToSmallWithoutDefault = function () {
            if (!_resolutionsBigToSmallWithoutDefault) {
                _resolutionsBigToSmallWithoutDefault = helper.copyObj(getResolutionsSortedIds()).reverse();
                // remove default
                var indexOfDefaultRes = _resolutionsBigToSmallWithoutDefault.indexOf('default');
                if (_resolutionsBigToSmallWithoutDefault.indexOf('default') > -1) {
                    _resolutionsBigToSmallWithoutDefault.splice(indexOfDefaultRes, 1);
                }
            }
            return _resolutionsBigToSmallWithoutDefault;
        };

        /**
         * Transform resolutions to resId -> resObj
         * with caching
         * @param {Object} [resolutions] - resolution Object
         * @returns {Object} - key is the resolution id, value is the resolution object
         */
        var getResolutionsMap = function (resolutions) {
            if (!_resolutionsMap) {
                resolutions = resolutions || getResolutions();
                _resolutionsMap = {};
                if (resolutions.data) {
                    resolutions.data.map(function (res) {
                        _resolutionsMap[res.id] = res;
                    });
                }
            }
            return _resolutionsMap;
        };

        /**
         * Tells you if the resolutions are endabled
         * with caching
         * @param [resolutions] - resolutions object
         * @returns {Boolean} weather the resolutions are enabled or not
         */
        var resolutionsAreEnabled = function (resolutions) {
            if (!_resolutionsEnabled) {
                resolutions = resolutions || getResolutions();
                _resolutionsEnabled = resolutions.enabled;
            }
            return _resolutionsEnabled;
        };

        /**
         * Adds a dynamicSelector plugin which supports selectors that can change by formValue
         * Plugin use:
         *   dynamicSelector: {
         *      unitId: 'MUNIT-ID-MUNIT',
         *      formValue: 'additionalSelector',
         *      '&': {
         *           // ... rules ...
         *      }
         *   }
         * @param api
         * @param subtree
         */
        var dynamicSelector = function (api, subtree) {
            var unitId = subtree.unitId;
            var formValue = subtree.formValue;
            var innerSubtree = subtree['&'] || {};
            var selector = getFormValues(unitId)[formValue];
            if (selector) {
                var obj =  {};
                obj[selector] = innerSubtree;
                return obj;
            }
            // no selector found output empty object
            return {};
        };
        api.plugin('dynamicSelector', dynamicSelector);

        /**
         * Removes units (px, em, %, etc) from the given value
         * (in fact removed all non numbers followed by numbers (with point))
         * @param {String|Number} str
         * @returns {Number|Mixed}
         */
        var unitLess = function (str) {
            if (typeof str === 'string') {
                return Number(str.replace(/([0-9\.]+)[^0-9]*/, '$1'));
            }
            return str;
        };
        api.register('unitLess', unitLess);

        /**
         * Define a css module, the plugin name is based on the pluginId
         * @param {String} pluginId - id of the plugin (usually the moduleId)
         * @param {Function} cssCb - callback function to generate css js object
         * @param {Object} [options] - options object (optional)
         *
         * TODO: use Object.seal and/or Object.freeze for params of callback?
         */
        var defineModule = function (pluginId, cssCb, options) {
            options = options || {};
            // register a plugin responsible for the wiring
            api.plugin(pluginId, function (api, unitId) {
                var rules = {};
                var cssResCurrent = null;
                var cssRulesBase;
                // use always the same rawFormValues object as getting the formValues is expensive
                var rawFormValues = getFormValues(unitId);


                // default resolution (desktop - first)
                var formValuesDefaultRes = getFormValuesForResolution(rawFormValues, 'default');
                var cssDefaultRes = cssCb.apply({}, [api, formValuesDefaultRes.values, {unitId: unitId, resolution: {id: 'default'}}]);
                if (cssDefaultRes) {
                    insertInspectorProperty(cssDefaultRes, unitId);
                    rules['&'] = cssDefaultRes;
                }

                // resolutions aka breakpoints (defined by app/user)
                // max width based (override global definitions, aka desktop first)
                // only generate media query if at least one form value is configured to be responsive
                if (resolutionsAreEnabled() && helper.containsResponsiveValue(rawFormValues)) {
                    // resolutions big to low
                    var resIds = getResolutionIdsBigToSmallWithoutDefault();
                    var resMap = getResolutionsMap();

                    for (var i = 0; i < resIds.length; i++) {
                        var resId = resIds[i];
                        var res = resMap[resId];
                        // added empty object for all resolutions to fix media query sorting
                        rules['@media screen and (max-width: ' + res.width + 'px)'] = {};
                        // only generate if not ALL values are inherited!
                        var formValues = getFormValuesForResolution(rawFormValues, resId);
                        // do not generate media query if all values were inherited
                        if (formValues.allInherited) {
                            continue;
                        }
                        // the base: current active css rules
                        cssRulesBase = cssResCurrent || cssDefaultRes;
                        // new css rules (which will override the current active)
                        cssResCurrent = cssCb.apply({}, [api, formValues.values, {unitId: unitId, resolution: res}]);
                        if (cssResCurrent) {
                            var cssOptimized;
                            if (options.keepAllMediaQueryRules) {
                                cssOptimized = cssResCurrent;
                            } else {
                                cssOptimized = optimizeMediaQuery(cssRulesBase, cssResCurrent);
                            }
                            if (_isNotEmpty(cssOptimized)) {
                                insertInspectorProperty(cssOptimized, unitId);
                                rules['@media screen and (max-width: ' + res.width + 'px)'] = cssOptimized;
                            }
                        }
                    }
                }

                return rules;
            });
        };

        /**
         * Only true for non empty arrays or objects
         * @param {Object|Array} input
         * @returns {boolean}
         * @private
         */
        var _isNotEmpty = function (input) {
            if (_isArray(input)) {
                return (input.length > 0);
            }
            if (typeof input === 'object') {
                return Object.keys(input).length > 0;
            }
            return false;
        };

        /**
         * Is input an array?
         * @param input
         * @returns {boolean}
         * @private
         */
        var _isArray = function(input) {
            return {}.toString.call(input) === "[object Array]";
        };


        /**
         * Create object or array based on type string
         * @param {String} type - type string
         * @returns {Object|Array} empty new object or array
         * @private
         */
        var _createFromType = function(type) {
            if (type === 'object') {
                return {};
            }
            if (type === 'array') {
                return [];
            }
        };

        /**
         * Get Type as String (with array support)
         * @param {*} input
         * @returns {string} - the type as string
         * @private
         */
        var _getType = function(input) {
            var type;
            type = typeof input;
            if (type === 'object' && _isArray(input)) {
                return 'array';
            } else {
                return type;
            }
        };

        /**
         * Returns the diff between base and rules
         *
         * @param {Object} cssRulesBase - the rules which are in place (before)
         * @param {Object} cssRules - the new rules (after)
         *
         * @returns {Object} diff of cssRules and cssRulesBase
         */
        var optimizeMediaQuery = function (cssRulesBase, cssRules) {

            // we need a base, create on if falsy
            cssRulesBase = cssRulesBase || _createFromType(_getType(cssRules));

            // do diff
            try {
                var op = diff.calculateDifferences(cssRulesBase, cssRules);
                return diff.applyDifferences(_createFromType(_getType(cssRules)), op);
            } catch(e) {
                console.warn('WARN: optimizeMediaQuery failed', e);
                return cssRules;
            }
        };

        /**
         * Inserts debug info for 'Style Inspector'
         * @param cssJson
         * @param unitId
         */
        var insertInspectorProperty = function (cssJson, unitId) {

            // TODO: enable inspector property if we really do use it
            if (true) {
                return;
            }

            // only in debug mode
            if (!debug) {
                return;
            }
            // do this for ALL sub selectors
            (function thisFn(obj) {
                // add it only to REAL object (not arrays)
                if (isObject(obj)) {
                    obj.DyncssId = unitId;
                }
                // iterate over array or object
                for (var key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        var nextObj = obj[key];
                        // iterate over array or object
                        if (nextObj === Object(nextObj)) {
                            thisFn(nextObj);
                        }
                    }
                }
            })(cssJson);
        };

        var registerGetColorById = function (getColorImpl) {
            api.register('getColorById', getColorImpl);
        };

        var registerGetImageUrl = function (getImageImpl) {
            api.register('getImageUrl', function getColorByIdFixArgs(id, width, quality) {
                // fix different types of inputs
                width = unitLess(width);
                quality = Number(quality);
                return getImageImpl(id, width, quality);
            });
        };

        var registerGetMediaUrl = function (getMediaImpl) {
            api.register('getMediaUrl', getMediaImpl);
        };

        var getResolutions = function () {
            return resolutionsImpl.call(this);
        };

        var getFormValues = function (unitId) {
            var fv = formValuesImpl.call(this, unitId);
            return fv;
        };

        /**
         * Get a single form value (raw value)
         * @param {String|Object} unitId or context object {unitId: String}
         * @param {String} key formValueKey
         * @returns {*}
         */
        var getFormValue = function (unitId, key) {
            // give unitId or context object
            if (unitId && unitId.unitId) {
                unitId = unitId.unitId;
            }
            var fv = getFormValues(unitId);
            return fv ? fv[key] : null;
        };
        api.register('getFormValue', getFormValue);

        /**
         * Checks weather a formValue was inherited in the given resolution
         * @param {Object} context - context object
         * @param {String} context.unitId - unitId
         * @param {Object} context.resolution - resolution object
         * @param {String} context.resolution.id  - resolution id
         * @param {String} formValueKey - formValueKey to check
         * @returns {Boolean}
         */
        var isInherited = function (context, formValueKey) {
            var rawFormValues  = getFormValues(context.unitId);
            var fv = rawFormValues[formValueKey];
            return !fv[context.resolution.id];
        };
        api.register('valueIsInherited', isInherited);


        /**
         * Get object with inherited formValues for a specific resolution
         * @param rawFormValues - unmodified form values object
         * @param {String} [resolutionId='default'] - resolution id
         *
         * @returns {Object} {{values: Object, hasInheritance: Boolean}}
         *          formValues object for one specific resolution (contains no resolution keys)
         */
        var getFormValuesForResolution = function (rawFormValues, resolutionId) {
            resolutionId = resolutionId || 'default';
            var inheritanceMap = {};

            // inherit all values
            var formValuesInherited = {};
            Object.keys(rawFormValues).forEach(function (key) {
                if (helper.isResponsiveValue(rawFormValues[key])) {
                    // TODO: this could be optimized if we don't build all inheritance - only the one we need!
                    // TODO: move this to X-doc-API because the inheritance is defined by the cms
                    // TODO: (would also require an abstraction of @media definitions)
                    var inheritance = helper.valueInheritance(rawFormValues[key], getResolutionsSortedIds());
                    formValuesInherited[key] = inheritance.value[resolutionId];

                    // remember inheritance status
                    inheritanceMap[key] = inheritance.inheritedFrom.hasOwnProperty(resolutionId);
                } else {
                    formValuesInherited[key] = rawFormValues[key];
                }
            });

            // check if only trues are in the map values
            var allInherited = Object.keys(inheritanceMap).map(function (key) { return inheritanceMap[key]; }).every(function (val) { return val; });

            return {
                values: formValuesInherited,
                inheritanceMap: inheritanceMap,
                allInherited: allInherited
            };
        };

        var add = function (json) {
            api.add(json, {combineSelectors: true});
        };

        // simple Log impl
        var logData = [];
        var log = function () {
            var args = Array.prototype.slice.call(arguments, 0);
            logData = logData.concat(args);
        };

        var addLogs = function () {
            if (logData && logData.length > 0) {
                // print log data in a shadow dom element
                var logElement = {
                    'line-height': 1.3,
                    'padding': '10px',
                    'border-radius': '5px',
                    'background': 'rgba(220, 220, 220, .6)',
                    'font-family': 'monospace',
                    'position': 'absolute',
                    'bottom': '10px',
                    'right': '10px',
                    'white-space': 'pre-wrap',
                    'overflow': 'auto',
                    'max-height': '400px',
                    'content': '"<<< CSS DEBUG LOG\\A\\A' + logData.join('\\A') + '\\A\\A>>>"'
                };

                api.add({
                    'body:after': logElement
                });
            }
        };

        var removeEmptyStylesheets = function() {
            var rules = api.getRules();
            for(var stylesheet in rules) {
                var deleteStylesheet = true;
                for(var r in rules[stylesheet]) {
                    if (_isNotEmpty(rules[stylesheet][r])) {
                        deleteStylesheet = false;
                        break;
                    }
                }
                if (deleteStylesheet) {
                    delete rules[stylesheet];
                }
            }
        };

        /**
         * Compile
         * @param cb
         */
        var compile = function (cb) {
            if (debug) {
                addLogs();
            }
            removeEmptyStylesheets();
            api.compile(cb, {combineSelectors: false}); /* minify: !debug */
        };

        var isObject = function (obj) {
            return obj === Object(obj) && Object.prototype.toString.call(obj) !== '[object Array]';
        };

        // var isArray = function (obj) {
        //     return Object.prototype.toString.call(obj) === '[object Array]';
        // };


        return {
            defineModule: defineModule,
            compile: compile,
            add: add,
            registerGetColorById: registerGetColorById,
            registerGetImageUrl: registerGetImageUrl,
            registerGetMediaUrl: registerGetMediaUrl,
            setFormValueImpl: setFormValueImpl,
            setResolutionsImpl: setResolutionsImpl,
            enableDebug: enableDebug,
            log: log
        };
    };

    global.DynCSS = dynCSS();

})(window);
