/*global zip:true, Blob:true*/
Ext.ns('CMS.app');

/**
* @class CMS.app.TrafficManager
* @extends Ext.util.Observable
* A helper class that handles HTTP requests and errors, using {@link Ext.Ajax} internally.
*/
CMS.app.TrafficManager = Ext.extend(Ext.util.Observable, {

    /**
    *  if set to true, all request parameters will be compressed on the client side to speed up the data transfer
    */
    requestCompression: CMS.config.requestCompression,

    nextRequestId: 1,

    /**
    * Mapping of our requestIds to the transaction "id" as returned by {@link Ext.Ajax.request}.
    * Can be used to cancel an ongoing request using {@link Ext.Ajax.abort}.
    * Note that the original docs are wrong. "transactionId" is not an integer, but an object.
    */
    requestIdMapping: {},

    constructor: function (cfg) {

        Ext.apply(this, cfg);

        Ext.Ajax.timeout = CMS.config.ajaxTimeouts['default'] || Ext.Ajax.timeout;

        Ext.data.DataProxy.on('exception', this.handleProxyException, this);
        Ext.util.Observable.observeClass(Ext.data.Connection);
        Ext.data.Connection.on('requestexception', this.handleConnectionException, this);

        // disable compression if browser doesn't support it
        var blobSupported = false;
        try {
            // needed for Safari 5.1
            blobSupported = (new Blob([])) instanceof window.Blob;
        } catch (e) {}
        if (!(blobSupported && window.Worker && window.DataView)) {
            console.info('[TrafficManager] browser doesn\'t support request compression');
            this.requestCompression = false;
        }

        this.handledErrors = {};
        CMS.app.TrafficManager.superclass.constructor.call(this);
    },

    /**
    * send a request to one of backend's JSON services
    * @param {Object} options
    * It can have the following properties:
    * <ul><li><strong>action</strong>: (String) One of the possible actions defined in {@link CMS.config.urls}</li>
    * <li><strong>modal</strong>: (Boolean) <tt>true</tt> to mask the UI during load. Defaults to <tt>false</tt></li>
    * <li><strong>maskMsg</strong>: (String) The message for the progress mask. Defaults to <tt>Bitte warten...</tt></li>
    * <li><strong>data</strong>: (Object) The actual payload to be sent via the request</li>
    * <li><strong>failureTitle</strong>: (String) The title of the failure message shown if the request fails</li>
    * <li><strong>failure</strong>: (Function) A callback executed if the request fails. (If failureTitle is set, an error message is shown additionally). This function is called with the following arguments:
        <ul>
          <li><strong>response</strong>: (Object) The JSON response</li>
          <li><strong>error</strong>: (Object) An object containing information about the error that occured:<pre>
              text: The error text as sent by the backend
              formatted: text in HTML encoded form
              tid: Transaction id of the failed request (may be undefined if the request was not sent at all)
              verbose: Error text containing information about request, response, expected data; can be passed to ErrorManager for logging
              </pre>
          </li>
        </ul>
      </li>
    * <li><strong>logFailure</strong>: (Boolean) <tt>false</tt> to prevent automated error logging. Defaults to <tt>true</tt></li>
    * <li><strong>success</strong>: (Function) A callback executed if the request succeeds</li>
    * <li><strong>callback</strong>: (Function) A callback executed after success and failure</li>
    * <li><strong>callbackFirst</strong>: (Boolean) <tt>true</tt> to execute the callback before success/failure. Defaults to <tt>false</tt>
    * <li><strong>successCondition</strong>: (String/Function/Array) If passed as a string, the JSON response is considered a failure if it does not contain this property. /
            If passed as a function, the function will be called with the JSON response as an argument. The request is considered a failure if the function returns false. /
            If passed as an array, each of the contained conditions is checked. The request is considered a failure if on of the conditions is violated.</li>
    * <li><strong>scope</strong>: (Object) The scope of the callback, success and failure callbacks</li>
    * <li><strong>rawText</strong>: (Boolean) <tt>true</tt> to return the raw response text instead of trying a JSON decode. Defaults to <tt>false</tt>
    * <li><strong>fireAndForget</strong>: (Boolean) If <tt>true</tt>, errors will be ignored. Also the <strong>success</strong> and <strong>failure</strong> functions will not be called</li>
    * </ul>
    * @return {Integer} The requestId, can be used to cancel an ongoing request using {@link abortRequest}.
    */
    sendRequest: function (config) {
        var url = CMS.config.urls[config.action];
        var defaultData = SB.util.cloneObject(CMS.config.params[config.action]);
        var self = this;
        if (!url) { // DEBUG
            CMS.Message.error(CMS.i18n('Konfigurationsfehler'), CMS.i18n('Service nicht konfiguriert'));
            CMS.app.ErrorManager.push(CMS.i18n('Request „{action}“ kann nicht gesendet werden: Fehlende URL in Config.').replace('{action}', config.action));
            return;
        }
        if (config.modal) {
            var mask = Ext.getBody().mask(config.maskMsg || CMS.i18n('Bitte warten…'));
            mask.addClass('CMSwait'); // add class to distinguish it from an ordinary mask
            mask.setStyle('z-index', 20000);
        }

        // construct failure handler for Ajax call
        var showMsg = !!config.failureTitle || !config.failure;
        var failureHandler = function (json, error) { // leaks?
            var tId = (typeof error.tId != 'undefined') ? error.tId : (typeof json.tId != 'undefined') ? json.tId : null;
            if (tId !== null) {
                if (self.handledErrors[tId]) { // make sure we don't handle the same error twice
                    return false;
                }
                self.handledErrors[tId] = true;
            }
            if (showMsg) {
                CMS.Message.error(config.failureTitle || CMS.i18n('Fehler'), error.formatted || CMS.i18nTranslateMacroString(CMS.config.errorTexts.generic));
            }
        };
        if (config.fireAndForget) {
            config.failure = Ext.emptyFn;
        } else if (config.failure) {
            config.failure = config.failure.createInterceptor(failureHandler);
        } else {
            config.failure = failureHandler;
        }
        failureHandler = null;


        var requestId = this.nextRequestId++;
        this.requestIdMapping[requestId] = null;

        var postData = CMS.app.trafficManager.createPostParams(Ext.apply(defaultData, config.data));

        var requestOptions = {
            url: url,
            form: config.form,
            params: postData,
            success: function (response, options) {
                if (!window.CMS || CMS.app.willunload) {
                    return;
                }
                if (config.modal) {
                    Ext.getBody().unmask();
                }
                if (config.callback && config.callbackFirst) {
                    config.callback.call(config.scope || window, response);
                }
                this.handleRequestSuccess(response, config, options);
                if (config.callback && !config.callbackFirst) {
                    config.callback.call(config.scope || window, response);
                }
                self = null;
            },
            failure: function (response, error) {
                if (!window.CMS || CMS.app.willunload) {
                    return;
                }
                if (config.modal) {
                    Ext.getBody().unmask();
                }
                if (config.callback && config.callbackFirst) {
                    config.callback.call(config.scope || window, response, error);
                }
                config.failure.call(config.scope || window, response, error);
                if (config.callback && !config.callbackFirst) {
                    config.callback.call(config.scope || window, response, error);
                }
            },
            callback: function () {
                // clean up request mapping
                delete this.requestIdMapping[requestId];
            },
            timeout: CMS.config.ajaxTimeouts[config.action],
            scope: this
        };


        // Compress request-params if compression enabled
        // and browser supports WebWorkers and TypedArrays
        if (this.requestCompression) {
            var paramsAsString = Ext.encode(requestOptions.params);
            if (paramsAsString.length >= CMS.config.requestCompressionMinSize) {
                this.compressData(paramsAsString, function (paramsCompressed, headers) {
                    // compression was successful, send compressed request
                    if (Ext.isDefined(CMS.app.trafficManager.requestIdMapping[requestId])) {
                        requestOptions.headers = headers;
                        requestOptions.params = paramsCompressed;

                        CMS.app.trafficManager.requestIdMapping[requestId] = Ext.Ajax.request(requestOptions);
                    }
                }, function () {
                    // compression failed, send uncompressed request
                    if (Ext.isDefined(CMS.app.trafficManager.requestIdMapping[requestId])) {
                        CMS.app.trafficManager.requestIdMapping[requestId] = Ext.Ajax.request(requestOptions);
                    }
                });
            } else {
                // compression not necessary, send uncompressed request
                if (Ext.isDefined(CMS.app.trafficManager.requestIdMapping[requestId])) {
                    CMS.app.trafficManager.requestIdMapping[requestId] = Ext.Ajax.request(requestOptions);
                }
            }
        } else {
            // compression disabled, send uncompressed request
            if (Ext.isDefined(CMS.app.trafficManager.requestIdMapping[requestId])) {
                CMS.app.trafficManager.requestIdMapping[requestId] = Ext.Ajax.request(requestOptions);
            }
        }

        return requestId;
    },

    /**
    * Abort request with the given requestId; wrapper for Ext.Ajax.abort()
    * @param {Integer} requestId id of the request returned by {@link sendRequest}
    */
    abortRequest: function (requestId) {
        var extRequestId = this.requestIdMapping[requestId];
        if (extRequestId) {
            Ext.Ajax.abort(extRequestId);
        }
        delete this.requestIdMapping[requestId];
    },

    /**
    * @private
    * Handle a request that has been successfully answered.
    * We now need to scan the response's payload for error messages
    * prior to actually calling the sender components' success or failure functions
    * @param {Object} response The response as received by Ext.Ajax.request
    * @param {Object} config The config parameter as passed with the 'sendrequest' event
    * @param {Object} options The processed options as passed to the internatl Ext.Ajax call
    */
    handleRequestSuccess: function (response, config, options) {
        if (config.fireAndForget && !CMS.config.debugMode) {
            return;
        }
        var text = response.responseText;

        // ugly hack to get real response text, because Ext.data.Connection.doFormUpload() is too stupid,
        // and the method itself is so large that I don't want to override it
        // http://www.sencha.com/forum/showthread.php?17248
        if (/HTMLDocument/.test(Object.prototype.toString.call(response.responseXML))) {
            text = response.responseText = response.responseXML.body.textContent || response.responseXML.body.innerText;
        }

        // another hack for http://www.sencha.com/forum/showthread.php?17248
        // just to make sure...
        if (/^<pre[^>]*>[\s\S]*<\/pre>$/.test(text)) { // [\s\S] matches . and \n
            text = response.responseText = text.replace(/^<pre[^>]*>/, '').replace(/<\/pre>$/, '');
        }

        var errorText;
        var json;
        if (config.rawText) {
            if (config.success) {
                config.success.call(config.scope || window, text, config);
            }
            return;
        }
        if (SB.json.isSafe(text)) {
            json = Ext.decode(text);
        } else {
            // invalid JSON response
            json = {
                success: false,
                error: CMS.config.genericErrors.invalidJSON
            };
            errorText = text;
            // handle PHP's HTML-JSON-mix shit
            if (/\}\n*&lt;|&gt;\n*\{/.test(errorText)) {
                errorText = Ext.util.Format.htmlDecode(errorText).replace(/<br ?\/?>/g, '\n');
            }
            if (/<.*>\n*\{[\s\S]*\}[\s\n]*$/.test(errorText)) {
                errorText = errorText.replace(/>\n*\{[\s\S]*/, '>\n[+JSON]');
            } else if (/^[\s\n]*\{[\s\S]*\}\n*<.*>/.test(errorText)) {
                errorText = errorText.replace(/[\s\S]*\}\n*</, '[JSON+]\n<');
            }
            errorText = CMS.i18n('Response (ungültiges JSON):\n') + errorText;
            console.warn(Ext.util.Format.stripTags(errorText));
        }
        if (json.success && (config.successCondition || config.successConditions)) {
            this.processSuccessCondition(response, config.successCondition || config.successConditions, json);
        }
        this.processNotifications(response, config, json);
        if (json.success === false) {
            this.handleError(response, config, json, errorText, options);
        } else {
            if (config.success) {
                config.success.call(config.scope || window, json, config);
            }
        }
    },

    /**
    * @private
    * Generate notification messages if present
    * @param {Object} response The response as received by Ext.Ajax.request
    * @param {Object} config The config parameter as passed with the 'sendrequest' event
    * @param {Object} json The decoded json payload of the response
    */
    processNotifications: function (response, config, json) {
        var msgTextFragments = [];
        if (json.message && json.message.length) {
            Ext.each(json.message, function (msg) {
                var msgText = msg.text;
                if (!msgText) {
                    return;
                }
                msgText = new Ext.Template(msgText);
                msgTextFragments.push(msgText.apply(msg.param));
                if (msgTextFragments.length >= CMS.config.maxMessageItems) {
                    msgTextFragments.push('…');
                    return false;
                }
            });
            if (msgTextFragments.length >= CMS.config.maxMessageItems) {
                return false;
            }
            CMS.Message.toast(CMS.i18n('Hinweis'), Ext.util.Format.htmlEncode(msgTextFragments.join('\n\n')));
        }
    },

    /**
    * @private
    * Check for correct response format
    * @param {Object} response The response as received by Ext.Ajax.request
    * @param {Mixed} successCondition The successCondition config parameter as passed with the 'sendrequest' event
    * @param {Object} json The decoded json payload of the response (Will be modified in-place)
    * @return Boolean <tt>true</tt> if all successConditions are fulfilled, <tt>false</tt> if one is violated
    */
    processSuccessCondition: function (response, successCondition, json) {
        var hasWrongFormat = false;
        if (typeof successCondition == 'string') {
            if (successCondition.indexOf('.') == -1) {
                hasWrongFormat = !json.hasOwnProperty(successCondition);
            } else {
                var sub = json;
                var props = successCondition.split('.');
                var prop;
                do {
                    prop = props.shift();
                    if (!Ext.isObject(sub) || !sub.hasOwnProperty(prop)) {
                        hasWrongFormat = true;
                        prop = sub = props = null;
                        break;
                    }
                    sub = sub[prop];
                } while (props.length);
            }
        } else if (typeof successCondition == 'function') {
            hasWrongFormat = !successCondition(json);
        } else if (Ext.isArray(successCondition)) {
            Ext.each(successCondition, function (cond) {
                return this.processSuccessCondition(response, cond, json);
            }, this);
        }
        if (hasWrongFormat) {
            console.warn(typeof successCondition == 'string' ? 'Missing property:' : 'successCondition violated', successCondition);
            Ext.apply(json, {
                success: false,
                error: CMS.config.genericErrors.unexpectedResponse
            });
        }
        return !hasWrongFormat;
    },

    /**
    * @private
    * Handle a successfully answered request that contains an error code, or a generic error.
    * @param {Object} response The response as received by Ext.Ajax.request
    * @param {Object} config The config parameter as passed with the 'sendrequest' event
    * @param {Object} json The decoded json payload of the response
    * @param {String} customErrorText (optional) An errorText for logging purposes
    * @param {Object} options The options as passed to Ext.Ajax.request
    */
    handleError: function (response, config, json, customErrorText, options) {
        var errors = json.error || CMS.config.genericErrors.unknown;
        var errortextFragments = [];
        var loggedIn = true;
        if (config.fireAndForget) {
            return;
        }
        Ext.each(errors, function (err) {
            if (err.code == CMS.config.specialErrorCodes.noSession && config.action != 'login') {
                loggedIn = false;
                return false;
            }
            var errtext = err.text || CMS.i18nTranslateMacroString(CMS.config.errorTexts[err.code]) || CMS.i18nTranslateMacroString(CMS.config.errorTexts.generic);
            errtext = new Ext.Template(errtext);
            errortextFragments.push(errtext.apply(err.param));
        });
        if (!loggedIn) {
            CMS.app.loginHelper.startLogin(CMS.app.userInfo.get('email'), options);
            return;
        }
        errortextFragments = Ext.unique(errortextFragments);
        if (errortextFragments.length > 1) {
            errortextFragments[0] = '- ' + errortextFragments[0];
        }
        var errortext = errortextFragments.join('\n\n- ');
        var formatted = Ext.util.Format.htmlEncode(errortext).replace(/\n/g, '<br>');
        var error = {
            text: errortext,
            formatted: formatted,
            tId: response && response.tId,
            verbose: ''
        };
        var logText = customErrorText;
        if (!logText) {
            logText = CMS.i18n('Response:') + '\n';
            if (response && response.responseText) {
                logText += this.removePasswords(response.responseText.substring(0, 100000));
            } else {
                logText += CMS.i18n('(keine)\n\n----\n\nStatus:\n{status}').replace('{status}', response && response.status);
            }
            logText += CMS.i18n('\n\n----\n\nFehler:\n{description}\n\n----\n\n').replace('{description}', errortext);
            if (config.successCondition) {
                logText += CMS.i18n('Erwartete Daten:') + '\n' + (config.successCondition.join ? ('\u2022 ' + config.successCondition.join('\n\u2022 ')) : config.successCondition) + '\n\n----\n\n';
            } else if (config.reader && config.reader.meta && config.reader.meta.root) {
                logText += CMS.i18n('Erwartete Daten:') + '\n' + config.reader.meta.root + '\n\n----\n\n';
            }
        }
        var dataText = this.removePasswords(Ext.encode(config.data || config.params));
        // get request url back from JSON reader. This is so hacky because ExtJS suckx.
        var action = config.action || (config.reader && config.reader.meta && config.reader.meta.url) || CMS.i18n('(unbekannt)');
        error.verbose = CMS.i18n('Request:\n{action}\n\n----\n\nData:\n{data}\n\n----\n\n{description}').replace('{action}', action).replace('{data}', dataText).replace('{description}', logText);
        if (config.logFailure !== false) {
            CMS.app.ErrorManager.push(error.verbose);
        }
        // execute callback
        if (config.failure) {
            config.failure.call(config.scope || window, json, error);
        } else {
            CMS.Message.error(CMS.i18n('Fehler:'), error.formatted);
        }
    },

    /**
    * @private
    * Remove passwords from JSON string for logging purposes
    * @param {String} string The string to clean up
    * @return String
    */
    removePasswords: function (string) {
        if (!string) {
            return string;
        }
        return string.replace(/("password" *(, *"value")?: *)"(\\.|[^\"])*"/g, '$1"(removed by logger)"');
    },

    /**
    * @private
    * Handle the exception event fired by data proxies
    */
    handleProxyException: function (proxy, type, action, options, response, mix) {
        options.action = proxy.api.read.url;
        // really ugly. See {@link Ext.data.DataProxy#exception} for details
        if (type == 'response') {
            // response is the raw browser request
            this.handleConnectionException(null, response, options, mix);
        } else {
            // response is an Ext.data.Response object
            var responseJson;
            try {
                responseJson = Ext.decode(response.raw.responseText);
            } catch (e) {
                responseJson = {};
            }
            this.handleError(response.raw, options, responseJson, null, options);
        }
    },

    /**
    * @private
    * Handle the exception event fired by data connections
    */
    handleConnectionException: function (connection, response, options, mix) {
        if (!window.CMS || CMS.app.willunload) {
            return;
        }
        var jsonDummy = {};
        options.action = options.url;
        if (response.isTimeout) {
            jsonDummy = {
                error: CMS.config.genericErrors.timeout
            };
        } else if (response.statusText == 'communication failure') {
            jsonDummy = {
                error: CMS.config.genericErrors.connectionBroken
            };
        } else if (response.status && response.status != -1 && response.status != 200) {
            jsonDummy = {
                error: CMS.config.httpErrors[response.status] || [{
                    text: CMS.i18n('HTTP-Fehler „{code} {statustext}“'),
                    param: {
                        code: response.status,
                        statustext: response.statusText
                    }
                }]
            };
        } else if (mix) {
            if (mix == 'ROOT_NOT_FOUND') {
                // dirty little hack to handle error when root was not found while loading store. This is needed because ExtJS suckx.
                jsonDummy = {
                    error: CMS.config.genericErrors.unexpectedResponse
                };
            } else {
                console.warn('[TrafficManager] Internal Ajax error:', mix);
                mix.cmstitle = CMS.i18n('Interner Ajax-Fehler:');
                CMS.app.ErrorManager.push(mix);
            }
        }
        this.handleError(response, options, jsonDummy);
    },

    /**
     * Hack for SBCMS-68: Backend services require all data to be encapsuled
     * in one single parameter.
     *
     * @param {Object} input
     *       The actual data that should be sent
     *
     * @return Object
     *       The wrapped data object, that can be understood by backend
     */
    createPostParams: function (input) {
        var result = {};
        result[CMS.config.postKeyName] = Ext.encode(Ext.apply({
            runId: CMS.app.runId
        }, input));
        return result;
    },

    /**
     * Decodes a given url and extractes the parameter;
     * Hack for SBCMS-68: Backend services require all data to be encapsuled
     * in one single parameter.
     *
     * @param {String} url
     *       The raw url string
     *
     * @return Object
     *       The parameter data
     */
    extractPostParams: function (url) {
        var params = decodeURIComponent(url).replace(/.*\{/, '{');
        return Ext.decode(params);
    },

    purgeListeners: function () {
        Ext.data.DataProxy.un('exception', this.handleProxyException, this);
        Ext.data.Connection.un('requestexception', this.handleConnectionException, this);
        return CMS.app.TrafficManager.superclass.purgeListeners.apply(this, arguments);
    },

    /**
    * Compress given data string via deflate algorithm
    * @param {String} dataAsString The string to compress
    * @param {Function} onEnd Callback when data was successfully compressed
    * @param {Function} onError will get called when an error occurred
    * @param {Boolean} htmlSafe Whether the returning binary data should only contain safe characters for inserting in HTML forms
    */
    compressData: function (dataAsString, onEnd, onError, htmlSafe) {
        zip.workerScriptsPath = CMS.config.urls.zipjsWorkerScriptsPath;

        // use a BlobWriter to store the compressed data into a Blob object
        zip.createWriter(new zip.BlobWriter(), function (writer) {
            // we added two own methods to zip.js since we don't need the ZIP file container: addSB(), closeSB()
            // use a TextReader to read the String to add
            writer.addSB(new zip.TextReader(dataAsString), function () {
                // onsuccess callback
                writer.closeSB(function (blob) {
                    var reader = new window.FileReader();
                    reader.onload = function (event) {
                        var data = event.target.result;

                        var method = 'gz';

                        if (htmlSafe) {
                            var safeData = CMS.app.trafficManager.replaceUnsafeCharacters(data);
                            data = safeData.data;
                            method += '-' + safeData.charMarkerCode;
                        }

                        // call callback with the compressed data and additional HTTP headers,
                        // so the server knows how to decompress the data
                        onEnd(data, {
                            'X-cms-encoding': method
                        });
                    };
                    reader.readAsBinaryString(blob);
                });
            });
        }, function (error) {
            // onerror callback
            console.warn('[TrafficManager] compression failed', error);

            if (onError) {
                onError(error);
            }
        });
    },

    /**
    * @private
    * Replace unsafe characters so data can be put into HTML form elements
    * @param {String} data compressed binary data
    * @return Object object with the safe data and the charMarkerCode used
    */
    replaceUnsafeCharacters: function (data) {
        var charMarkerCode = 1; // < 128
        var i;

        // generate char table only once and cache it for next requests
        if (!this.requestCompressionHtmlSafeCharTable) {
            this.requestCompressionHtmlSafeCharTable = [];

            var charsToReplace = [0, 10, 13, 26, 38, 60, 62, 160, charMarkerCode];

            for (i = 0; i <= 255; i++) {
                this.requestCompressionHtmlSafeCharTable[i] = String.fromCharCode(i);
            }
            for (i = 0; i < charsToReplace.length; i++) {
                this.requestCompressionHtmlSafeCharTable[charsToReplace[i]] =
                    String.fromCharCode(charMarkerCode) +
                    String.fromCharCode((charMarkerCode + charsToReplace[i]));
            }
        }

        var buffer = [];
        for (i = 0; i < data.length; i++) {
            /*jslint bitwise: false*/
            /*jshint bitwise: false*/
            var charKey = data.charCodeAt(i) & 0xff;
            /*jshint bitwise: true*/
            /*jslint bitwise: true*/
            buffer[i] = this.requestCompressionHtmlSafeCharTable[charKey];
        }

        return {
            data: buffer.join(''),
            charMarkerCode: charMarkerCode
        };
    }
});
