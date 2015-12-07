/* global mockstar */
(function (global) {
    var stubs = [
        'defineModule',
        'compile',
        'add',
        'registerApi',
        'setFormValueImpl',
        'setResolutionsImpl',
        'enableDebug',
        'log',
    ];

    var DynCSS = mockstar.define(stubs);


    define('DynCSS', [], function () {
        return DynCSS;
    });
    global.DynCSS = DynCSS;

}(window));
