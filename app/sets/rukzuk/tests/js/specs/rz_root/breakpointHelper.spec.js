/* global describe, it, expect, jasmine, beforeEach */
define(['rz_root/notlive/js/breakpointHelper', 'CMS'], function (bpHelper, CMS) {
    describe('rz_root/notlive/js/breakpointHelper', function () {

        var bpData = [{
            width: 768,
            name: 'Tablet',
            id: 'res1'
        }, {
            width: 480,
            name: 'Smartphone quer',
            id: 'res2'
        }, {
            width: 320,
            name: 'Smartphone hoch',
            id: 'res3'
        }];

        var enabledRes = {
            enabled: true,
            data: bpData
        };

        var disabledRes = {
            enabled: false,
            data: bpData
        };

        var formValue = {
            'type': 'bp',
            'default': 'foo',
            'res1': 'bar',
            'res3': 'baz'
        };

        beforeEach(function () {
            CMS.initMock({
                getResolutions: function () {
                    return enabledRes;
                }
            });
        });

        describe('forEachBreakpoint', function () {
            it('should call the given callback method once for each configured breakpoint and the default one', function () {
                // prepare
                var cb = jasmine.createSpy('callback');
                // execute
                bpHelper.forEachBreakpoint(cb);
                // verify
                expect(cb.callCount).toBe(4);
                expect(cb.calls[0].args[0]).toEqual({id: 'default'});
                for (var i = 0; i < enabledRes.data.length; i++) {
                    expect(cb.calls[i + 1].args[0]).toBe(enabledRes.data[i]);
                }
            });

            it('should exit if the callback method returns "false"', function () {
                // prepare
                var cb = jasmine.createSpy('callback').andCallFake(function (bp) {
                    return bp.id !== 'res1';
                });
                // execute
                bpHelper.forEachBreakpoint(cb);
                // verify
                expect(cb.callCount).toBe(2);
                expect(cb.calls[0].args[0].id).toBe('default');
                expect(cb.calls[1].args[0].id).toBe('res1');
            });

            it('should call the callback with the default if breakpoints are disabled', function () {
                // prepare
                CMS.initMock({
                    getResolutions: function () {
                        return disabledRes;
                    }
                });
                var cb = jasmine.createSpy('callback');
                // execute
                bpHelper.forEachBreakpoint(cb);
                // verify
                expect(cb.callCount).toBe(1);
                expect(cb).toHaveBeenCalledWith({id: 'default'});
            });
        });

        describe('forEachBreakpointValue', function () {
            it('should call the given callback with the value for each BP', function () {
                // prepare
                var cb = jasmine.createSpy('callback');

                // execute
                bpHelper.forEachBreakpointValue(formValue, cb);
                // verify
                expect(cb.callCount).toBe(3);
                expect(cb.calls[0].args[1]).toEqual({id: 'default'});
                expect(cb.calls[0].args[0]).toBe('foo');
                expect(cb.calls[1].args[1]).toBe(bpData[0]);
                expect(cb.calls[1].args[0]).toBe('bar');
                expect(cb.calls[2].args[1]).toBe(bpData[2]);
                expect(cb.calls[2].args[0]).toBe('baz');
            });

            it('should call the given callback only once for non-responsive form values', function () {
                // prepare
                var cb = jasmine.createSpy('callback');
                var formValue = 'foo';
                // execute
                bpHelper.forEachBreakpointValue(formValue, cb);
                // verify
                expect(cb.callCount).toBe(1);
                expect(cb).toHaveBeenCalledWith('foo');
            });
        });

        describe('getFormValue', function () {
            it('can retrieve the value for a specific breakpoint id', function () {
                // prepare
                var unit = {
                    formValues: {
                        foo: {
                            value: formValue
                        }
                    }
                };
                // execute/verify
                expect(bpHelper.getFormValue(unit, 'foo', 'default')).toBe('foo');
                expect(bpHelper.getFormValue(unit, 'foo', 'res1')).toBe('bar');
                expect(bpHelper.getFormValue(unit, 'foo', 'res2')).toBe('bar'); // no value for res2 -> get "inherited" value from res1
                expect(bpHelper.getFormValue(unit, 'foo', 'res3')).toBe('baz');
            });

            it('can retrieve the unit if a unit is specified', function () {
                // prepare
                var unit = {
                    id: 'unit-42',
                    formValues: {
                        foo: {
                            value: formValue
                        }
                    }
                };
                CMS.initMock({
                    units: [unit],
                    getResolutions: function () {
                        return enabledRes;
                    }
                });
                // execute/verify
                expect(bpHelper.getFormValue('unit-42', 'foo', 'default')).toBe('foo');
                expect(bpHelper.getFormValue('unit-42', 'foo', 'res1')).toBe('bar');
                expect(bpHelper.getFormValue('unit-42', 'foo', 'res2')).toBe('bar'); // no value for res2 -> get "inherited" value from res1
                expect(bpHelper.getFormValue('unit-42', 'foo', 'res3')).toBe('baz');
            });

            it('returns all the complete form value if breakpoint id was omitted', function () {
                // prepare
                var unit = {
                    formValues: {
                        foo: {
                            value: formValue
                        }
                    }
                };
                // execute/verify
                expect(bpHelper.getFormValue(unit, 'foo')).toBe(formValue);
            });
        });
    });
});
