/* global DynCSS */
DynCSS.defineModule('rz_flexbox', function (api, v, res) {
    'use strict';

    var result = {};
	result['& > div'] = {};

    result['& > div']['-w-flex-wrap'] = 'wrap';
	result['& > div']['-w-justify-content'] = v.cssJustifyContentH;
	result['& > div']['-w-align-items'] = v.cssAlignItemsH;
	result['& > div']['-w-align-content'] = v.cssAlignItemsH;

	var columns = v.cssChildWidth.trim().replace(/\s+/g, ' ').split(' ');
	var numberOfColumns = columns.length;
    var order;

	for (var i = 0; i < numberOfColumns; i++) {

        if (v.cssReverse) {
            order = numberOfColumns - i;
        } else {
            order = i;
        }

		result['& > div > .isModule:nth-child(' + (i+1) + 'n)'] = {
			'width': columns[i],
			'content': '"' + columns[i] + '"',
            '-w-order': order
		};	
	
	}
	
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


    return result;
}, { keepAllMediaQueryRules: true });
