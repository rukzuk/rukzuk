DynCSS.defineModule('rz_container_flow', function (api, v, context) {
    return {
        textAlign: v.cssTextAlign,
        '& > .isModule': {
            display: 'inline-block',
            width: 'auto',
            textAlign: 'left'
        }
    };
});
