/* global jasmine */
(function (global) {

    var mockstar = {
        define: function (stubs, proto) {
            stubs = stubs || [];
            proto = proto || {};

            var mockInstance;
            var Mock = function (cfg) {
                cfg = cfg || {};
                for (var key in cfg) {
                    if (cfg.hasOwnProperty(key)) {
                        this[key] = cfg[key];
                    }
                }
            };
            Mock.prototype = proto;


            var mockWrap = {
                initMock: function (cfg) {
                    mockInstance = new Mock(cfg);
                    stubs.forEach(function (fnName) {
                        mockWrap[fnName].reset();
                    });
                }
            };

            stubs.forEach(function (fnName) {
                mockWrap[fnName] = jasmine.createSpy(fnName).andCallFake(function () {
                    if (!mockInstance) {
                        throw 'Mock is not initialized!';
                    }

                    if (typeof mockInstance[fnName] === 'function') {
                        return mockInstance[fnName].apply(mockInstance, arguments);
                    }
                });
            });

            return mockWrap;
        }
    };

    if (typeof define === 'function' && define.amd) {
        define('mockstar', [], function () {
            return mockstar;
        });
    }
    global.mockstar = mockstar;

}(window));
