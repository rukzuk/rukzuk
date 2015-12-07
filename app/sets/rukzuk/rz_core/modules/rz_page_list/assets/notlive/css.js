DynCSS.defineModule('rz_page_list', function (api, v, context) {
    var hSpace = parseFloat(v.cssHSpace);
    var hSpaceMeasure;
    if (v.cssHSpace.match(/px/)) {
        hSpaceMeasure = 'px';
    } else {
        hSpaceMeasure = '%';
    }

    var numberOfCols = v.cssColumnCount;

    // calculate width of one column
    var oneColWidth;
    if (hSpaceMeasure == 'px') {
        oneColWidth = (100 / numberOfCols);
    } else {
        if ((hSpace * numberOfCols) > 100) {
            hSpace = 100 / numberOfCols;
        }

        oneColWidth = (100 / numberOfCols) - (hSpace * (numberOfCols - 1) / numberOfCols);
    }

    var result = {};

    // width for all columns
    result['& > ul > li'] = {
        width: oneColWidth + '%'
    };

    var cssProperties;
    var paddingLeft = 0;
    var paddingRight = 0;
    var marginLeftPercent = 0;

    // spacing for each column
    for (var i = 0; i < numberOfCols; i++) {
        if (hSpaceMeasure == 'px') {
            paddingLeft = i * (hSpace / numberOfCols);
            paddingRight = (numberOfCols - (i + 1)) * (hSpace / numberOfCols);

            cssProperties = {
                paddingLeft: paddingLeft + 'px',
                paddingRight: paddingRight + 'px',
                marginLeft: 0
            };
        } else {
            if (i > 0) {
                marginLeftPercent = hSpace;
            }

            cssProperties = {
                padding: 0,
                marginLeft: marginLeftPercent + '%'
            };
        }

        // set margin-top for vertical space
        cssProperties.marginTop = v.cssVSpace;

        var selector = '& > ul > li:nth-of-type(' + numberOfCols + 'n+' + (i + 1) + ')';
        result[selector] = cssProperties;
    }

    // remove margin-top for first row
    result['& > ul > li:nth-of-type(-n+' + numberOfCols + ')'] = {
        marginTop: 0
    };

    return result;
});
