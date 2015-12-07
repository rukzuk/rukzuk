/*global mockResponses*/
/**
 * This script provides an hook for the casperjs tests to inject mocks;
 * Do not include in production version
 */
(function () {
    var params = Ext.urlDecode(location.href.replace(/.*\?/, '').replace(/\/$/, ''));
    if (params.testmode === '1') {
        var log = Ext.emptyFn;
        var requestId = 0;
        if (params.verbose === '1') {
            log = function (msg) {
                console.log('MOCK - ' + msg);
            };
        }

        log('init mock');

        // initialize CMSSERVER object
        window.CMSSERVER = JSON.parse(mockResponses.urls['index/info']);

        // override the request method of Ext.Ajax so ever request will return
        // the mock responses
        Ext.data.Connection.prototype.request = function (o) {
            var me = this;
            if (me.fireEvent('beforerequest', me, o)) {
                var tId = requestId++;
                var urlRe = new RegExp('.*' + CMS.config.urlPrefix);
                var url = (o.url || me.url || '').replace(urlRe, '');
                var response = {
                    responseText: mockResponses.urls[url] || mockResponses.defaultResponse,
                    status: 200,
                    statusText: 'OK',
                    tId: tId
                };
                var success = JSON.parse(response.responseText).success;

                log('request: ' + url);
                setTimeout(function () {
                    if (success) {
                        if (o.success) {
                            log('success: ' +  response.responseText);
                            o.success.call(o.scope, response, o);
                        }
                    } else {
                        if (o.failure) {
                            log('failure');
                            o.failure.call(o.scope, response, o);
                        }
                    }
                    if (o.callback) {
                        o.callback.call(o.scope, o, success, response);
                    }
                }, 10);

                me.transId = requestId++;
                return me.transId;
            } else {
                return o.callback ? o.callback.apply(o.scope, [o, undefined, undefined]) : null;
            }
        };
    }
}());

