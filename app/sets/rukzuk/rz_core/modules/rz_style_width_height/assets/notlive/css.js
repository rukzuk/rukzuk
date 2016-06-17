DynCSS.defineModule('rz_style_width_height', function (api, v, res) {
    var result = {};
    if (v.cssEnableWidth) {
        if (v.cssWidthType == 'fix') {
            result.width =  v.cssWidth;
        } else if (v.cssWidthType == 'auto') {
            result.width = 'auto';
        }
        if (v.cssEnableMinWidth) {
            result.minWidth = v.cssMinWidth;
        }
        if (v.cssEnableMaxWidth) {
            result.maxWidth = v.cssMaxWidth;
        }
    }
    if (v.cssEnableHeight) {
        var height;
        if (v.cssHeightType == 'min') {
            result.minHeight = v.cssMinHeight;
            result.height = 'auto';
            result.overflowY = 'visible';
            height = v.cssMinHeight;
        } else if (v.cssHeightType == 'auto') {
            result.minHeight = 0;
            result.height = 'auto';
            result.overflowY = 'visible';
            height = 'auto';
        } else {
            result.minHeight = 0;
            result.height = v.cssHeight;
            result.overflowY = v.cssOverflowY;
            height = v.cssHeight;
        }
        if ((v.cssOverflowY == 'auto') || (v.cssOverflowY == 'scroll')) {
            result['-webkit-overflow-scrolling'] = 'touch';
        }
        // Hack for rz_box
        var boxTableFixes;
        if (height.match(/px/)) {
            boxTableFixes = {
                minHeight: height,
                height: height
            };
        } else {
            // reset to default
            boxTableFixes = {
                minHeight: 0,
                height: '100%'
            };
        }
        result['> .isColumnBoxTable'] = boxTableFixes;

        // Hack for rz_grid & rz_container, overwrite padding trick
        result['&.rz_grid > .fillHeight, &.rz_container > .fillHeight'] = {
            'padding-bottom': 0
        };

        // Hack for all resizeHandlers (rz_grid, rz_container, rz_image)
        // TODO only needed in edit mode
        result['> .resizeHeight'] = {
            display: 'none !important'
        };
    }

    return result;
});
