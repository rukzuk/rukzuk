(function () {
    if (window.CMS) {
        // already initialized
        return;
    }

    var listeners = {};
    var methodsSuspended = false;
    var eventsSuspended = false;
    var currentApiInstance = parent.CmsApi;
    var pageRendered = false;

    if (!currentApiInstance) {
        console.warn('Could not initialize API. Aborting!');
        return;
    }

    /**
     * Emulate DOMContentLoaded for WebKit/Blink Browsers as they don't support DOMFrameContentLoaded
     */
    (function () {
        // send emulated DOMFrameContentLoaded
        if (window.addEventListener && window.postMessage) {
            window.addEventListener('DOMContentLoaded', function () {
                if (parent && window.frameElement) {
                    parent.postMessage({id: window.frameElement.id, action: 'emulatedDOMFrameContentLoaded'}, '*');
                }
            }, false);
        }
    })();


    /**
     * Overwrite console's methods. We need to be able to do this dynamically,
     * since Firebug overwrites window's console object at runtime.
     * @param {Object} console The console object
     * @param {Boolean} clean True to reset the methods to null for garbage collection
     */
    function messWithConsole(console, undo) {
        var consoleMethods = 'log,warn,info,debug'.split(',');
        var topConsole = undo ? null : currentApiInstance.console;
        var addConsoleMethod = function (method) {
            console[method] = undo ? null : function () {
                var args = [].slice.call(arguments);
                args.unshift('[impl ' + method + ']');
                topConsole.debug.apply(topConsole, args);
            };
        };
        for (var i = 0; i < consoleMethods.length; i++) {
            addConsoleMethod(consoleMethods[i]);
        }
    }

    var privateConsole = window.console || {};
    messWithConsole(privateConsole);
    try {
        Object.defineProperty(window, 'console', {
            get: function () {
                return privateConsole;
            },
            // This is where we fool Firebug
            set: function (x) {
                messWithConsole(privateConsole, true);
                messWithConsole(x);
                privateConsole = x;
            }
        });
    } catch (e) {
        // this fails in Safari 5.0/Mac, but should not be a problem, since there is no Firebug there
        // console.warn('Error trying to sandbox console', e);
    }



    /**
     * Determine whether a unit matches a filter object
     * @private
     * @param {Object} data The unit's data
     * @param {Object} filter The filter provided in {@link #on}
     */
    var matchesFilter = function (data, filter) {
        if (!filter || !data) {
            return true;
        }
        for (var key in filter) {
            if (filter.hasOwnProperty(key)) {
                if (filter[key] != data[key]) {
                    return false;
                }
            }
        }
        return true;
    };

    window.CMS = {
        /**
         * Appends an event handler
         * @param {String} event The name of the event
         * @param {Mixed} [filter] Optional. The id of a unit or a filter object
         * @param {function} handler The callback function
         * @param {Object} [scope] Optional. The execution context for the event handler
         */
        on: function (event, filter, handler, scope) {
            event = event.toLowerCase();
            if (typeof filter === 'function') {
                scope = handler;
                handler = filter;
                filter = null;
            } else if (typeof filter === 'string') {
                filter = {
                    id: filter
                };
            }
            listeners[event] = listeners[event] || [];
            listeners[event].push({
                handler: handler,
                scope: scope,
                filter: filter
            });
        },

        /**
         * Removes an event handler
         * @param {String} event The name of the event
         * @param {function} handler The callback function
         */
        un: function (event, handler, legacy) {
            event = event.toLowerCase();
            if (typeof legacy == 'function') { // old API interface
                handler = legacy;
            }
            var handlers = listeners[event];
            if (!handlers) {
                return;
            }
            for (var i = 0; i < handlers.length; i++) {
                if (handlers[i].handler == handler) {
                    handlers.splice(i--, 1);
                }
            }
            if (!handlers.length) {
                delete listeners[event];
            }
        },

        /**
         * Fires the specified event.
         * @private
         * @param {String} event The name of the event
         * @param {String} unitId The id of the unit that fired the event
         * @param {Object} eventArgsJSON The event arguments as JSON object
         * @param {Object} unitJSON The unit's data as JSON object
         */
        fireEvent: function (event, unitId, eventArgsJSON, unitJSON) {
            if (eventsSuspended) {
                return;
            }
            event = event.toLowerCase();
            var eventArgs;
            if (eventArgsJSON) {
                eventArgs = JSON.parse(eventArgsJSON);
            }
            var handlers = listeners[event];
            if (handlers && handlers.length) {
                var unitData = JSON.parse(unitJSON);
                for (var i = 0; i < handlers.length; i++) {
                    var handler = handlers[i].handler;
                    var scope = handlers[i].scope;
                    if (!matchesFilter(unitData, handlers[i].filter)) {
                        continue;
                    }
                    handler.call(scope || window, eventArgs);
                    if (eventsSuspended) { // we need to check this inside the loop, since one of the handlers may have triggered a reload
                        return;
                    }
                }
            }
        },

        /**
         * Cancel all event firing. Should be called when unloading.
         */
        suspendEventFiring: function () {
            eventsSuspended = true;
        },

        /**
         * Resume all event firing. (see {@link #suspendEventFiring})
         */
        resumeEventFiring: function () {
            eventsSuspended = false;
        },

        /**
         * Suspend the delegation of all method calls. (see {@link #resumeDelegation})
         */
        suspendDelegation: function () {
            methodsSuspended = true;
        },

        /**
         * Resume delegating method calls. (see {@link #suspendDelegation})
         */
        resumeDelegation: function () {
            methodsSuspended = false;
        },

        /**
         * This method makes sure that the given callback function is executed after the page
         * has been rendered. The difference to <code>CMS.on('afterRenderPage', ...);</code> is
         * that the callback is guaranteed to be executed even if the "afterRenderPage" event
         * has already been fired.
         * @param {Function} callback The callback function to be executed after rendering
         * @param {Object} scope The execution context for the callback function
         */
        onAfterRenderPage: function (callback, scope) {
            if (pageRendered) {
                callback.call(scope);
            } else {
                this.on('afterRenderPage', callback, scope);
            }
        }
    };

    var delegateMethodCalls = function (methodName) {
        window.CMS[methodName] = function () {
            if (!methodsSuspended) {
                return currentApiInstance[methodName].apply(currentApiInstance, arguments);
            }
        };
    };


    window.CMS.on('afterRenderPage', function () {
        pageRendered = true;
    });

    var methods = currentApiInstance.methods;
    for (var i = 0; i < methods.length; i++) {
        delegateMethodCalls(methods[i]);
    }

    var unloadListener = function () {
        window.CMS.suspendEventFiring();
        window.CMS = null;
        currentApiInstance = null;
        listeners = null;
        if (window.addEventListener) {
            window.removeEventListener('unload', unloadListener, false);
        } else {
            window.detachEvent('onunload', unloadListener);
        }
        messWithConsole(privateConsole, true);
        privateConsole = null;
        delegateMethodCalls = null;
        matchesFilter = null;
        unloadListener = null;
        pageRendered = false;
    };

    if (window.addEventListener) {
        window.addEventListener('unload', unloadListener, false);
    } else {
        window.attachEvent('onunload', unloadListener);
    }
})();
