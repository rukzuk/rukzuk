DynCSS.defineModule('rz_trigger_event', function (api, v) {
    var result = {};
    if (v.eventType == 'click') {
        if (v.cssCursorPointer) {
            result.cursor = 'pointer';
        } else {
            result.cursor = 'inherit';
        }
        
    }
    return result;
});