DynCSS.defineModule('rz_style_list', function (api, v) {
    var result = {};
    if (v.cssEnableList) {
        result.li = {
            listStyleType: v.cssListStyleType
        };
    }
    return result;
});