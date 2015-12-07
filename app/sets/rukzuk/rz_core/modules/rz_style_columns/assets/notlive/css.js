DynCSS.defineModule('rz_style_columns', function (api, v) {
    var result = {};

    if (v.cssEnableColumns) {

        result.MozColumns = v.cssColumnCount;
        result.WebkitColumns = v.cssColumnCount;
        result.columns = v.cssColumnCount;

        result.MozColumnGap = v.cssColumnGap;
        result.WebkitColumnGap = v.cssColumnGap;
        result.columnGap = v.cssColumnGap;

        if (v.cssEnableColumnRule) {

            var color = api.getColorById(v.cssColumnRuleColor);

            result.WebkitColumnRule = v.cssColumnRuleWidth + ' ' + v.cssColumnRuleStyle + ' ' + color;
            result.MozColumnRule = v.cssColumnRuleWidth + ' ' + v.cssColumnRuleStyle + ' ' + color;
            result.columnRule = v.cssColumnRuleWidth + ' ' + v.cssColumnRuleStyle + ' ' + color;
        }
    }
    return result;
});
