/**
 * Breakpoint Helper
 */
define(['CMS'], function (CMS) {

    /**
     * Calls the given callback function once for each available breakpoint,
     * 'default' is always called (even if breakpoints are disabled)
     */
    var forEachBreakpoint = function (cb) {
        var bps = CMS.getResolutions();
        var exit = (cb({id: 'default'}) === false);
        if (bps.enabled) {
            for (var i = 0; i < bps.data.length && !exit; i++) {
                exit = cb(bps.data[i]) === false;
            }
        }
    };

    /**
     * Iterates over all Breakpoints (CMS Settings), looks in the formValue if there is something defined
     * @param {Object|Mixed} formValue
     * @param{Function} cb
     */
    var forEachBreakpointValue = function (formValue, cb) {
        if (formValue && formValue.type === 'bp') {
            forEachBreakpoint(function forEachBreakpointCallback(bp) {
                var fv = formValue[bp.id];
                if (fv !== undefined) {
                    return cb(fv, bp);
                }
            });
        } else {
            cb(formValue);
        }
    };

    /**
     * Returns a form value for a given breakpoint id respecting the
     * inheritance logic of the formValues
     */
    var getFormValue = function (unit, key, bpId) {
        var unitData = (typeof unit === 'string') ? CMS.get(unit) : unit;
        var formValue = unitData && unitData.formValues[key];
        var result;

        if (formValue) {
            formValue = formValue.value;
        }
        if (bpId && formValue && typeof formValue === 'object') {
            forEachBreakpoint(function (bp) {
                if (formValue.hasOwnProperty(bp.id)) {
                    result = formValue[bp.id];
                }
                if (bp.id === bpId) {
                    // target breakpoint found -> exit loop
                    return false;
                }
            });
        } else {
            result = formValue;
        }

        return result;
    };

    return {
        forEachBreakpoint: forEachBreakpoint,
        forEachBreakpointValue: forEachBreakpointValue,
        getFormValue: getFormValue
    };
});

