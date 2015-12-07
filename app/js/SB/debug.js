// Debugging functions by Seitenbau

/**
* @class SB.debug
* Just a namespace containing debuging functions
* @singleton
*/
Ext.ns('SB.debug');

/*jslint evil:true*/
(function () {

    var orgLogFn = console.log;
    var internalBlacklist = {
        cls: /^(constructor|extend|override|superclass|supr)$/,
        other: /^(create(Callback|Delegate|Interceptor|Sequence)|defer|prototype|SBDebugOriginal)$/
    };

    /**
    * @private
    * Helper for beautiful logging of method calls
    * @param {Object} calledby The method's caller
    * @param {String} description Class name or object description
    * @param {String} name Called method's name
    * @param {Array} args Arguments passed to method
    * @param {Mixed} result The method's return value
    */
    var prettyLogCall = function (calledby, description, name, args, result) {
        var sep = (typeof description == 'string') ? '+ "." +' : ',';
        var debugLine = 'console.debug("[debug]", calledby, "call:", description' + sep + 'name + "(", ';
        for (var i = 0; i < args.length; i++) {
            if (i > 0) {
                debugLine += '",", ';
            }
            debugLine += 'args[' + i + '], ';
        }
        if (typeof result != 'undefined') {
            debugLine += '")", "=", result);';
        } else {
            debugLine += '")");';
        }
        eval(debugLine);
    };
    prettyLogCall.isSBDebug = true;

    /**
    * Log all method calls to a given object/class.
    * This will not log indirect calls, as in event handlers.
    * @param {ExtClass|String|Object} object An Ext class or an instance whose method calls should be logged.
    * Ext classes may be given as strings (xtype or namespace+class)
    * @param {String} description (optional) A description of the object, which will be shown in the log entry
    * @param (Object) options (optional) Additional config options:<ul>
    <li><tt>deep</tt> {Boolean} <tt>true</tt> to log calls of inherited methods. Defaults to <tt>false</tt></li>
    <li><tt>whitelist</tt> {Array} A list of method names to log. If this is defined, all other methods will not be logged.</li>
    <li><tt>blacklist</tt> {Array} A list of method names to ignore</li>
    <li><tt>hook</tt> {Function} A function to call with every method call. It is passed the same arguments as the method.</li>
    <li><tt>result</tt> {Boolean} <tt>false</tt> to hide logging of method's results (return values). Defaults to <tt>true</tt></li>
    <li><tt>scope</tt> {Object} The scope in which <tt>hook</tt> is called</li>
    </ul>
    * @method logCalls
    */
    SB.debug.logCalls = function (object, description, options) {
        var isExtClass = false;
        options = options || description || {};
        if (typeof object == 'string') {
            if (typeof description != 'string') {
                description = object;
            }
            if (Ext.ComponentMgr.isRegistered(object)) {
                object = Ext.ComponentMgr.types[object];
            } else {
                object = Ext.ns(object);
            }
        }
        if (typeof description != 'string') {
            description = object.xtype || object;
        }
        if (typeof description == 'string') {
            description = description.replace(/\.$/, '');
        }
        if (object.prototype && object.hasOwnProperty('superclass')) {
            object = object.prototype;
            isExtClass = true;
        }
        var scope = options.scope || object;
        var hook = options.hook;

        var doOverwrite = function (name) {
            var method = object[name];
            if (method.SBDebugOriginal) {
                method = method.SBDebugOriginal;
            }
            object[name] = function self() {
                var result = method.apply(this, arguments);
                if (self.caller && self.caller != window['eval']  && !self.caller.isSBDebug) {
                    prettyLogCall(self.caller, description, name, arguments, (options.result !== false) ? result : undefined);
                    if (hook) {
                        hook.apply(scope, arguments);
                    }
                }
                return result;
            };
            object[name].SBDebugOriginal = method;
        };
        doOverwrite.isSBDebug = true;

        var keys = [];
        /*jslint forin: true*/
        /*jshint forin: true*/
        for (var name in object) {
            if (!options.deep && !object.hasOwnProperty(name)) {
                continue;
            }
            /*jshint forin: false*/
            if (isExtClass && internalBlacklist.cls.test(name)) {
                continue;
            }
            if (internalBlacklist.other.test(name)) {
                continue;
            }
            if (options.blacklist && options.blacklist.indexOf(name) != -1) {
                continue;
            }
            if (options.whitelist && options.whitelist.indexOf(name) == -1) {
                continue;
            }

            if (typeof object[name] == 'function' && !object[name].test) { // http://stackoverflow.com/questions/4094234/4094246#4094246
                if (object[name].hasOwnProperty('superclass')) {
                    continue;
                }
                doOverwrite(name);
                keys.push(name);
            }
        }

        if (keys.length) {
            console.debug('[debug] Monitoring calls to [' + keys.join(',') + '] in ', description || object);
        } else {
            console.debug('[debug] Nothing to monitor in ', description || object);
        }
    };
    SB.debug.logCalls.isSBDebug = true;

    /**
    * @private
    * Helper for beautiful logging of accessor calls
    * @param {String} type One of 'get'/'set'
    * @param {Object} calledby The accessor's caller
    * @param {String|Object} description Class name or object description
    * @param {String} name The accessed propertie's name
    * @param {Object} object The object on which the accessor was called
    * @param {Mixed} result The accessor's return value
    * @param {Mixed} before (only for setter) The previous value of object[name]
    */
    var prettyLogAccessor = function (type, calledby, description, name, object, result, before) {
        var debugLine = 'console.debug("[debug]", calledby, type + ":"';
        if (description) {
            debugLine += ', description' + ((typeof description == 'string') ? '+ "." +' : ',') + 'name';
        } else {
            debugLine += '"(", object, ")", name';
        }
        debugLine += ', "-' + (type == 'get' ? '>' : '<') + '", result';
        if (type == 'set') {
            debugLine += ', "(before:", before, ")"';
        }
        debugLine += ');';
        eval(debugLine);
    };
    prettyLogAccessor.isSBDebug = true;

    /**
    * Log all property getter/setter calls to a given object
    * @param {ExtClass|String|Object} object An object or Ext class whose accessor calls should be logged.
    * Ext classes may be given as strings
    * @param {String} description (optional) A description of the object, which will be shown in the log entry
    * @param (Object) options (optional) Additional config options:<ul>
    <li><tt>deep</tt> {Boolean} <tt>true</tt> to log calls to inherited properties. Defaults to <tt>false</tt></li>
    <li><tt>noMethods</tt> {Boolean} <tt>true</tt> to prevent logging method accessors. Defaults to <tt>false</tt></li>
    <li><tt>settersOnly</tt> {Boolean} <tt>true</tt> to prevent logging all getters. Defaults to <tt>false</tt></li>
    <li><tt>whitelist</tt> {Array} A list of properties to monitor. If this is defined, all other properties will not be logged.</li>
    <li><tt>blacklist</tt> {Array} A list of properties to ignore</li>
    <li><tt>hook</tt> {Function} A function to call with every accessor call. It is passed the same arguments as the accessor.</li>
    <li><tt>scope</tt> {Object} The scope in which <tt>hook</tt> is called</li>
    <li><tt>setterHook</tt> {Function} A function to call with every setter call. It is passed the same arguments as the setter.</li>
    <li><tt>setterScope</tt> {Object} The scope in which <tt>setterHook</tt> is called</li>
    <li><tt>getterHook</tt> {Function} A function to call with every getter call.</li>
    <li><tt>getterScope</tt> {Object} The scope in which <tt>getterHook</tt> is called</li>
    </ul>
    * @method logAccessors
    */
    SB.debug.logAccessors = function (object, description, options) {
        /*jslint nomen: false*/
        /*jshint nomen: false*/
        if (!object.__lookupGetter__ || !object.__lookupSetter__ || !object.__defineGetter__ || !object.__defineSetter__) {
            throw 'Not supported by this browser';
        }
        options = options || description || {};
        if (typeof object == 'string') {
            if (typeof description != 'string') {
                description = object;
            }
            object = Ext.ns(object);
        }
        if (typeof description != 'string') {
            description = object.xtype || object;
        }
        if (typeof description == 'string') {
            description = description.replace(/\.$/, '');
        }
        if (object.prototype && object.hasOwnProperty('superclass')) {
            object = object.prototype;
        }

        var doOverwrite = function (name) {
            var val = object[name];
            var origGetter = object.__lookupGetter__(name);
            var hook = options.hook;
            var scope = options.scope || object;
            var getterHook = options.getterHook;
            var getterScope = options.getterScope || object;
            if (origGetter && origGetter.SBDebugOriginal) {
                origGetter = origGetter.SBDebugOriginal;
            }
            function newGetter() {
                var result = origGetter ? origGetter.call(this, name) : val;
                if (newGetter.caller && newGetter.caller != window['eval'] && !newGetter.caller.isSBDebug && !options.settersOnly) {
                    prettyLogAccessor('get', newGetter.caller, description, name, object, result);
                    if (hook) {
                        hook.call(scope);
                    }
                    if (getterHook) {
                        getterHook.call(getterScope);
                    }
                }
                return result;
            }
            newGetter.isSBDebug = true;
            newGetter.SBDebugOriginal = origGetter;
            object.__defineGetter__(name, newGetter);

            var origSetter = object.__lookupSetter__(name);
            var setterHook = options.setterHook;
            var setterScope = options.setterScope || object;
            if (origSetter && origSetter.SBDebugOriginal) {
                origSetter = origSetter.SBDebugOriginal;
            }
            function newSetter(input) {
                if (newSetter.caller && newSetter.caller != window['eval']  && !newSetter.caller.isSBDebug) {
                    prettyLogAccessor('set', newSetter.caller, description, name, object, input, val);
                    if (hook) {
                        hook.call(scope, input);
                    }
                    if (setterHook) {
                        setterHook.call(setterScope, input);
                    }
                }
                val = input;
                var result = val;
                if (origSetter) {
                    result = origSetter.call(this, input);
                }
                return result;
            }
            newSetter.isSBDebug = true;
            newSetter.SBDebugOriginal = origSetter;
            object.__defineSetter__(name, newSetter);
        };
        doOverwrite.isSBDebug = true;

        var keys = [];
        /*jslint forin: true*/
        /*jshint forin: true*/
        for (var name in object) {
            if (!options.deep && !object.hasOwnProperty(name)) {
                continue;
            }
            /*jshint forin: false*/
            if (options.blacklist && options.blacklist.indexOf(name) != -1) {
                continue;
            }
            if (options.whitelist && options.whitelist.indexOf(name) == -1) {
                continue;
            }
            if (typeof object[name] == 'function' && options.noMethods) {
                continue;
            }
            doOverwrite(name);
            keys.push(name);
        }

        if (keys.length) {
            console.debug('[debug] Monitoring access to [' + keys.join(',') + '] in ', description || object);
        } else {
            console.debug('[debug] Nothing to monitor in ', description || object);
        }
    };
    SB.debug.logAccessors.isSBDebug = true;
    /*jshint nomen: true*/

    SB.debug.filterLog = function () {
        var filter = [];
        for (var i = 0; i < arguments.length; i++) {
            var arg = arguments[i];
            if (!arg) {
                continue;
            } else if (arg instanceof RegExp) {
                filter.push(arg);
            } else if (typeof arg === 'string') {
                filter.push(new RegExp(arg));
            }
        }

        if (filter.length > 0) {
            console.log = function () {
                var log = false;

                for (var i = 0; !log && i < arguments.length; i++) {
                    for (var j = 0; !log && j < filter.length; j++) {
                        log = filter[j].test(arguments[i]);
                    }
                }

                if (log) {
                    orgLogFn.apply(this, arguments);
                }
            };
        } else {
            console.log = orgLogFn;
        }
    };
})();


/**
* Manually fire an unload event. This is useful for testing listeners without actually closing the page.
* @param {DOMWindow} win (optional) A window object different from the current one. Use this to work with iframes.
* @param {Boolean} suppressBeforeUnload (optional) <tt>true</tt> to suppress firing of beforeunload event. Defaults to <tt>false</tt>
*/
SB.debug.fireUnload = function (win, suppressBeforeUnload) {
    win = win || window;
    if (!suppressBeforeUnload) {
        this.fireBeforeUnload(win);
    }
    var event = win.document.createEvent('Event');
    event.initEvent('unload', false, true);
    win.dispatchEvent(event);
};

/**
* Manually fire a beforeunload event. This is useful for testing listeners without actually closing the page.
* @param {DOMWindow} win (optional) A window object different from the current one. Use this to work with iframes.
*/
SB.debug.fireBeforeUnload = function (win) {
    win = win || window;
    var event = win.document.createEvent('Event');
    event.initEvent('beforeunload', false, true);
    win.dispatchEvent(event);
};

// This is a known bug in IE. Show alert to make user aware
// http://support.microsoft.com/kb/262161/en-us
if ((document.styleSheets.length == 31) && (document.getElementsByTagName('link').length > 31)) {
    alert('[SB.debug] IE cannot load more than 31 stylesheets');
}
