DynCSS.defineModule('rz_container', function (api, v, context) {
    var result = {};


    // vertical space
    if (v.cssVSpace) {
        result['& > .cntElements > div'] = {marginTop: v.cssVSpace};
        result['& > .cntElements > div:first-of-type'] = { marginTop: 0 };
    }


    // vertical alignment
    result['& > div, & > .cntElements > div'] = {
        verticalAlign: v.cssVerticalAlign
    };

    // horizontal align of this module
    if (v.cssEnableHorizontalAlign) {
        switch (v.cssHorizontalAlign) {
        case 'center':
            result.marginLeft = 'auto !important';
            result.marginRight = 'auto !important';
            break;
        case 'right':
            result.marginLeft = 'auto !important';
            result.marginRight = '0 !important';
            break;
        default:
            result.marginLeft = '0 !important';
            result.marginRight = 'auto !important';
            break;
        }
    }

    // min height
    result['& > .fillHeight'] = {
        'padding-bottom': v.cssMinHeight
    };

    return result;
});