DynCSS.defineModule('rz_thumbnail_gallery', function (api, v, context) {

    var result = {};
    var numberOfCols = v.cssColumnCount;
    var hSpace = parseFloat(v.cssHSpace);
    var hSpaceMeasure;
    var i;
    var numberOfImages = v.galleryImageIds.length;

    if (v.cssHSpace.match(/px/)) {
        hSpaceMeasure = 'px';
    } else {
        hSpaceMeasure = '%';
    }

    if (v.cssType == 'standard') {
        result['& > ul > li'] = {
            flexGrow: '0 !important',
            flexBasis: 'calc(' + (100/numberOfCols) +'% - ' + (hSpace * ((numberOfCols-1)/numberOfCols) + hSpaceMeasure) + ')',
            marginBottom: 0
        };

        for (i=0; i < numberOfImages; i++) {

            if (i % numberOfCols == 0) {
                result['& > ul > div:nth-of-type(' + i + ')'] = {
                    flexBasis: '100%',
                    paddingTop: v.cssVSpace
                };
            } else {
                result['& > ul > div:nth-of-type(' + i + ')'] = {
                    flexBasis: v.cssHSpace,
                    paddingTop: 0,
                    display: 'block',
                    flexGrow: 0
                };
            }

        }

        result['& > ul > div:last-of-type'] = {
            flexGrow: '0'
        };


    } else if (v.cssType == 'columns') {
        if (v.cssHSpace.match(/px/)) {
            result['& > ul > li'] = {
                marginBottom: v.cssVSpace,
                marginRight: 0
            };
            result['& > ul'] = {
                display: 'block',
                columnCount: numberOfCols,
                columnGap: v.cssHSpace,
                marginRight: 0
            };
        } else {
            result['& > ul > li'] = {
                marginRight: v.cssHSpace,
                marginBottom: v.cssVSpace
            };
            result['& > ul'] = {
                display: 'block',
                columnCount: numberOfCols,
                columnGap: 0,
                marginRight: '-' + (hSpace/numberOfCols) + '%'

            };
        }

        result['& > ul > div'] = {
            display: 'none'
        };

    } else {

        for (i=0; i < numberOfImages; i++) {

            if (i % numberOfCols == 0) {
                result['& > ul > div:nth-of-type(' + i + ')'] = {
                    flexBasis: '100%',
                    paddingTop: v.cssVSpace
                };
            } else {
                result['& > ul > div:nth-of-type(' + i + ')'] = {
                    flexBasis: v.cssHSpace,
                    paddingTop: 0,
                    display: 'block'
                };
            }

        }

        result['& > ul > li'] = {
            marginBottom: 0,
            marginRight: 0
        };

        if (numberOfImages % numberOfCols == 1) {
            result['& > ul > div:last-of-type'] = {
                flexGrow: (numberOfCols-1)
            };
        } else {
            result['& > ul > div:last-of-type'] = {
                flexGrow: 0
            };
        }

        if (numberOfCols == 1) {
            result['& > ul > li'] = {
                flexGrow: '1 !important'
            };
        }
    }

    return result;
});
