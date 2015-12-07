DynCSS.defineModule('rz_navigation', function (api, v) {
    var numberOfTotalLevels = 5;
    var result = {};

    for (var i = 1; i <= numberOfTotalLevels; i++) {
        if (!v['enableLevel' + i]) {
            continue;
        }

        var levelDistribution = v['cssLevel' + i + 'Distribution'];
        var levelSpace = v['cssLevel' + i + 'Space'] || 0;
        var levelAlign = v['cssLevel' + i + 'Align'];
        var listSelector = '.navLevel' + i + ' > li';

        switch (levelDistribution) {
        case 'horizontal':
            result[listSelector] = {
                display: 'inline-block',
                paddingLeft: levelSpace,
                paddingTop: 0
            };
            break;

        case 'horizontal_full':
            result[listSelector] = {
                display: 'table-cell',
                paddingLeft: levelSpace,
                paddingTop: 0
            };

            result['> ul'] = {
                display: 'table',
                width: '100%'
            };
            break;

        default:
            result[listSelector] = {
                display: 'block',
                paddingLeft: 0,
                paddingTop: levelSpace
            };
            break;
        }

        if (levelAlign) {
            result['.navLevel' + i] = {
                textAlign: levelAlign
            };
        }
    }

    return result;
});
