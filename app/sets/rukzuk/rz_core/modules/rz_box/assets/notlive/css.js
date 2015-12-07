/* global DynCSS */
DynCSS.defineModule('rz_box', function (api, v, res) {
    'use strict';

    function ColumnBoxSettingsHelper(childWidthString, hSpace) {
        this.childWidths = [];
        this.hSpace = '';
        if (childWidthString && hSpace) {
            this.setChildWidth(childWidthString, hSpace);
        }
    }

    /**
     * @public
     * @param {String} childWidthString
     * @param {String} hSpace
     */
    ColumnBoxSettingsHelper.prototype.setChildWidth = function (childWidthString, hSpace) {
        this.hSpace = hSpace;
        this.childWidths = this.parseChildWidthString(childWidthString);

        return this;
    };

    /**
     * @private
     * @param childWidthString
     * @returns {Array}
     */
    ColumnBoxSettingsHelper.prototype.parseChildWidthString = function (childWidthString) {
        var columns = childWidthString.trim().replace(/\s+/g, ' ').split(' ');
        var numberOfColumns = columns.length;
        var col;
        var childWidth = [];

        for (var i = 0; i < numberOfColumns; i++) {
            col = {};

            var type = columns[i].match(/px|%$/);
            var valign = columns[i].match(/^[mu]/);

            col.unit = type !== null ? type[0] : '';
            col.valign = valign !== null ? valign[0] : '';
            col.value = parseFloat(columns[i].replace(/[mu]|%|px/g, '').replace(',', '.'));
            col.hidden = (['0', '0%', '0px'].indexOf(columns[i]) >= 0);
            col.index = i;

            childWidth.push(col);
        }

        return childWidth;
    };

    var hSpace = v.cssHSpace || 0;
    var vSpace = v.cssVSpace || 0;
    var colBoxHelper = new ColumnBoxSettingsHelper(v.cssChildWidth, hSpace);
    var columns = colBoxHelper.childWidths;
    var numberOfColumns = columns.length;
    var hiddenStyle = {
        '%%display': 'none'
    };

    var result = {};

    // add spacers
    var spacer = []; // array to preserve order in CSS output
    spacer.push({
        '&:nth-child(1n)': {
            display: 'table-cell',
            '%%width': hSpace,
            '%%height': 0
        }
    });
    var spacerRows = {};
    spacerRows['&:nth-child(' + numberOfColumns * 2 + 'n)'] = {
        display: 'table-row',
        '%%width': 0,
        '%%height': vSpace
    };
    spacer.push(spacerRows);
    result['& > .isColumnBoxTable > .boxSpacer'] = spacer;

    // generate CSS for each column (where the children will be placed into)
    for (var i = 0; i < numberOfColumns; i++) {
        var column = columns[i];
        var verticalAlignValue = ((column.valign == 'u') ? 'bottom' : ((column.valign == 'm') ? 'middle' : ''));
        var columnWidth = '' + column.value + column.unit;
        var cellSelector = '& > .isColumnBoxTable > .isColumnBoxCell:nth-child(' + numberOfColumns * 2 + 'n+' + (i * 2 + 1) + ')';

        result[cellSelector] = {
            '%%display': 'table-cell', // %% to preserve order by prevent DynCSS merging
            '%%width': columnWidth
        };

        if (verticalAlignValue) {
            result[cellSelector]['vertical-align'] = verticalAlignValue;
        }

        // logic for hiding cells
        if (column.hidden) {
            var isLastColumn = i == (numberOfColumns - 1);
            var hiddenSpacerSelector = '& > .isColumnBoxTable > .boxSpacer:nth-child(' + numberOfColumns * 2 + 'n+' + (isLastColumn ? i * 2 :  i * 2 + 2) + ')';

            result[cellSelector]['%%display'] = 'none';
            result[hiddenSpacerSelector] = hiddenStyle;
        }
    }

    // box preview for empty cells
    result['& > .isColumnBoxTable > .boxPreview:nth-child(1n+' + (numberOfColumns * 2 + 1) + ')'] = hiddenStyle;
    result['& > .isColumnBoxTable > .boxSpacer.boxPreviewSpacer:nth-child(1n+' + (numberOfColumns * 2) + ')'] = hiddenStyle;

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

    // vertical align of child modules
    result['& > .isColumnBoxTable > .isColumnBoxCell'] = {
        'vertical-align': v.cssVerticalAlign || 'top'
    };

    return result;
}, { keepAllMediaQueryRules: true });
