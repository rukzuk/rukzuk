/* global describe, it, expect */
define(['rz_box/notlive/columnsHelper'], function (columnsHelper) {
    describe('columnsHelper', function () {
        it('it allows creating new helper instances', function () {
            // prepare
            // execute
            var helper1 = columnsHelper.create();
            var helper2 = columnsHelper.create();
            // verify
            expect(helper1).toBeDefined();
            expect(helper2).toBeDefined();
            expect(helper1).not.toBe(helper2);
        });

        it('can parse a "width-string"', function () {
            // prepare
            // execute
            var helper = columnsHelper.create('100px u50%', '0px');
            // verify
            expect(helper.childWidths).toEqual([{
                value: 100,
                unit: 'px',
                valign: '',
                index: 0
            }, {
                value: 50,
                unit: '%',
                valign: 'u',
                index: 1
            }]);
        });

        it('can re-serialze its column widths into a "width-string"', function () {
            // prepare
            var helper = columnsHelper.create();
            // execute
            helper.childWidths = [{
                value: 100,
                unit: 'px',
                valign: ''
            }, {
                value: 50,
                unit: '%',
                valign: 'u'
            }];
            // verify
            expect(helper.serializeChildWidth()).toBe('100px u50%');
        });

        it('can sum the column widths by unit', function () {
            // prepare
            var helper = columnsHelper.create('100px 50% 20px 30px 20%', '0px');
            // execute
            // verify
            expect(helper.widthSumByUnitType('px').sum).toEqual(150);
            expect(helper.widthSumByUnitType('%').sum).toEqual(70);
        });

        it('can correct the column width', function () {
            // prepare
            var helper = columnsHelper.create('100% 100% 100%', '0px');
            // execute
            helper.correctChildWidth();
            // verify
            expect(helper.serializeChildWidth()).toBe('33.3% 33.3% 33.3%');
        });

        it('considers percent values of horizontal space when correcting widths', function () {
            // prepare
            var helper1 = columnsHelper.create('50% 50%', '10%');
            var helper2 = columnsHelper.create('33% 33% 33%', '10%');
            // execute
            helper1.correctChildWidth();
            helper2.correctChildWidth();
            // verify
            expect(helper1.serializeChildWidth()).toBe('45% 45%');
            expect(helper2.serializeChildWidth()).toBe('26.7% 26.7% 26.7%');
        });
    });
});

