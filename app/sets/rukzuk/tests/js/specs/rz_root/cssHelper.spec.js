/* global describe, it, expect, beforeEach */
define(['rz_root/notlive/js/cssHelper', 'CMS', 'DynCSS'], function (cssHelper, CMS, DynCSS) {
    describe('rz_root/notlive/js/cssHelper', function () {
        var units = [{
            id: 'regularUnit',
            moduleId: 'regularModule'
        }, {
            id: 'extensionUnit',
            moduleId: 'extensionModule',
            parentUnit: 'regularUnit'
        }];

        var modules = [{
            id: 'regularModule',
            extensionModule: false
        }, {
            id: 'extensionModule',
            extensionModule: true
        }];

        beforeEach(function () {
            CMS.initMock({
                units: units,
                modules: modules
            });
            DynCSS.initMock();
        });


        describe('refreshCSS', function () {
            it('should not crash when it is not initialized', function () {
                // prepare
                // execute
                cssHelper.refreshCSS('regularUnit');
                // verify
                expect(DynCSS.compile).not.toHaveBeenCalled();
            });
        });
    });
});
