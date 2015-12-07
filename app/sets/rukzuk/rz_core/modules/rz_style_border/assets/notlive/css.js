DynCSS.defineModule('rz_style_border', function (api, v) {
    var result = {};

    // iterate over general settings and all 4 directions
    ['', 'Top', 'Right', 'Bottom', 'Left'].forEach(function (dir) {
        if (v['cssEnableBorder' + dir]) {
            var color = api.getColorById(v['cssBorder' + dir + 'Color']);
            if (color) {
                result['border' + dir + 'Color'] = color;
            }

            result['border' + dir + 'Width'] = v['cssBorder' + dir + 'Width'];
            result['border' + dir + 'Style'] = v['cssBorder' + dir + 'Style'];
        }
    });

    return result;
});
