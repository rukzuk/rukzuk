DynCSS.defineModule('rz_anchor', function (api, v, context) {
    return {
        '> .anchor': {
            textAlign: v.cssVisualHelperValign
        }
    };
});
