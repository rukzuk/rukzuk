DynCSS.defineModule('rz_grid', function (api, v, context) {
    var result = {};

    var hSpace = parseFloat(v.cssHSpace);
    var hSpaceMeasure;
    if (v.cssHSpace.match(/px/)) {
        hSpaceMeasure = 'px';
    } else {
        hSpaceMeasure = '%';
    }

    var gridSize = parseInt(v.gridSize);
    var gridDefinition = String(v.cssGridDefinition); // make sure it's a string for further operations

    // calculate width of one column
    var oneColWidth;
    if (hSpaceMeasure == 'px') {
        oneColWidth = (100 / gridSize);
    } else {
        if ((hSpace * gridSize) > 100) {
            hSpace = 100 / gridSize;
        }

        oneColWidth = (100 / gridSize) - (hSpace * (gridSize - 1) / gridSize);
    }

    var gridRows = gridDefinition.split("\n");
    var numberOfRows = gridRows.length;
    var colCounter = 0;
    var totalColumns = gridDefinition.replace(/\n/g, ' ').split(' ').length;

    // build css for width of the columns
    for (var i = 0; i < numberOfRows; i++) {
        var gridColumns = gridRows[i].split(' ');
        var numberOfColumns = gridColumns.length;
        var paddingLeft = 0;
        var paddingRight = 0;
        var cssProperties;

        var marginLeftPercent;
        var marginRightPercent;
        var colWidth;

        var currentPos = 0;

        for (var j = 0; j < numberOfColumns; j++) {
            var colSize = gridColumns[j];

            var selector = ' > div:nth-of-type(' + totalColumns + 'n+' + (colCounter + 1) + ')';

            var marginLeft = 0;
            var marginRight = 0;

            // check for offset
            if (colSize.match(/([0-9]+)-([0-9]+)-([0-9]+)/)) {
                marginLeft = parseInt(RegExp.$1);
                colSize = parseInt(RegExp.$2);
                marginRight = parseInt(RegExp.$3);

                currentPos += marginLeft;
            }

            colSize = parseInt(colSize);

            var widthMarkerRight;

            // calculate width of column
            if (hSpaceMeasure == 'px') {
                colWidth = colSize * oneColWidth;
                marginLeftPercent = marginLeft * oneColWidth;
                marginRightPercent = marginRight * oneColWidth;

                paddingLeft = currentPos * (hSpace / gridSize);
                paddingRight = (gridSize - (currentPos + colSize)) * (hSpace / gridSize);

                cssProperties = {
                    width: colWidth + '%',
                    paddingLeft: paddingLeft + 'px',
                    paddingRight: paddingRight + 'px',
                    marginLeft: marginLeftPercent + '%',
                    marginRight: marginRightPercent + '%'
                };


                // TODO edit mode
                /* set dimension for column markers */

                // center marker
                result['& > .gridElements ' + selector + ' > span.markerCenter'] = {
                    width: 'calc(100% - ' + (paddingLeft + paddingRight) + 'px)'
                };

                // left marker
                var marginLeftPaddingLeft = (currentPos - marginLeft) * (hSpace / gridSize);
                var widthMarkerCenter = marginLeftPercent * (100 / colWidth);
                var leftMarkerCenter = 'calc(-' + widthMarkerCenter + '% + ' + marginLeftPaddingLeft + 'px)';
                result['& > .gridElements ' + selector + ' > span.markerLeft'] = {
                    width: 'calc(' + widthMarkerCenter + '% + ' + (paddingLeft - marginLeftPaddingLeft) + 'px)',
                    left: leftMarkerCenter
                };

                // right marker
                var marginRightPaddingRight = paddingRight - (gridSize - (currentPos + colSize + marginRight)) * (hSpace / gridSize);
                widthMarkerRight = marginRightPercent * (100 / colWidth);
                var leftMarkerRight = 'calc(100% - ' + paddingRight + 'px)';
                result['& > .gridElements ' + selector + ' > span.markerRight'] = {
                    width: 'calc(' + widthMarkerRight + '% + ' + marginRightPaddingRight + 'px)',
                    left: leftMarkerRight
                };
                // TODO end edit mode

                currentPos += colSize + parseInt(marginRight);

            } else {
                colWidth = (colSize * oneColWidth + (colSize - 1) * hSpace);

                marginLeftPercent = (marginLeft * oneColWidth + (marginLeft - 1) * hSpace) + hSpace;
                marginRightPercent = (marginRight * oneColWidth + (marginRight - 1) * hSpace) + hSpace;

                // TODO edit mode
                /* set dimension for column markers */

                // center marker
                result['& > .gridElements ' + selector + ' > span.markerCenter'] = {
                    width: '100%'
                };

                // left marker
                var widthMarkerLeft = marginLeftPercent * (100 / colWidth);
                result['& > .gridElements ' + selector + ' > span.markerLeft'] = {
                    width: widthMarkerLeft + '%',
                    left: '-' + widthMarkerLeft + '%'
                };

                // right marker
                widthMarkerRight = marginRightPercent * (100 / colWidth);
                result['& > .gridElements ' + selector + ' > span.markerRight'] = {
                    width: widthMarkerRight + '%',
                    left: '100%'
                };
                // TODO end edit mode

                if (j > 0) {
                    marginLeftPercent += hSpace;
                }

                cssProperties = {
                    width: colWidth + '%',
                    padding: 0,
                    marginLeft: marginLeftPercent + '%',
                    marginRight: marginRightPercent + '%'
                };
            }

            // set width of column
            result['& > .gridElements ' + selector] = cssProperties;

			// set margin-top for vertical space
            // first row has no margin-top
            if (i < 1) {
                result['& > .gridElements > div:nth-of-type(' + (colCounter + 1) + ')'] = { marginTop: 0 };
            } else {
                result['& > .gridElements > div:nth-of-type(' + (colCounter + 1) + ')'] = { marginTop: v.cssVSpace };
            }

            colCounter++;
        }
    }

    // vertical alignment
    result['& > div, & > .gridElements > div'] = {
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

    // min height (using padding trick)
    result['& > .fillHeight'] = {
        'padding-bottom': v.cssMinHeight
    };


    // grid raster
    var cssGridRaster = {};
    if (hSpaceMeasure == 'px') {
        var delta = hSpace - (1 / gridSize) * hSpace;
        var patternColWidth = oneColWidth + '% - ' + delta + 'px';
        cssGridRaster.width = 'calc(' + patternColWidth + ')';
    } else {
        cssGridRaster.width = oneColWidth + '%';
    }

    cssGridRaster.marginLeft = v.cssHSpace;

    result['& > .gridRaster > div'] = cssGridRaster;

    // hide all unused raster columns
    result['& > .gridRaster > div:nth-child(1n+' + (gridSize + 1) + ')'] = {
        display: 'none'
    };
    // TODO end edit mode

    return result;
});