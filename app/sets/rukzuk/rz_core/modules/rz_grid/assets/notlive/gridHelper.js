define([], function () {

    //
    //
    // private helper
    //
    //

    /**
     * Removes all not allowed characters
     * Returns the validated string for a single resolution grid definition
     * @private
     * @returns {String}
     */
    function validateGridDefinitionString(gridDefinition) {
        // only numbers
        gridDefinition = gridDefinition.replace(/[^0-9\n\s\-]/g, "");

        // only one newline at once
        gridDefinition = gridDefinition.replace(/\n{2,}/g, "\n");

        // delete more than 1 whitespace
        gridDefinition = gridDefinition.replace(/ {2,}/g, " ");

        // delete whitespace at the end of a row
        gridDefinition = gridDefinition.replace(/ \n/g, "\n");

        // delete whitespace at the beginning of a row
        gridDefinition = gridDefinition.replace(/\n /g, "\n");

        // delete whitespace and newline at the end
        gridDefinition = gridDefinition.replace(/[ \n]$/g, "");

        // delete whitespace and newline at the beginning
        gridDefinition = gridDefinition.replace(/^[ \n]/g, "");

        gridDefinition = gridDefinition.replace(/\n/g, " ");

        return gridDefinition;
    }

    /**
     * Parses the grid column definition string (e.g. "1-3-1 3 5") and returns
     * an array of column configuration objects
     * @private
     * @param {String} [gridDefinitionString] - represents the grid column configuration as string (e.g. "1-3-1 3 5")
     * @param {String} [gridSize] - e.g. 12 or 24
     * @returns {Array}
     */
    function parseGridDefinitionString(gridDefinitionString, gridSize) {
        var columns = gridDefinitionString.length ? gridDefinitionString.replace(/\n/g, ' ').split(' ') : [];

        var numberOfColumns = columns.length;
        var result = [];

        for (var i = 0; i < numberOfColumns; i++) {
            var column = columns[i];
            var marginLeft = 0;
            var columnSize = 0;
            var marginRight = 0;

            // parse column definition
            if (column.toString().match(/([0-9]+)-([0-9]+)-([0-9]+)/)) {
                marginLeft = parseInt(RegExp.$1);
                columnSize = parseInt(RegExp.$2);
                marginRight = parseInt(RegExp.$3);
            } else {
                columnSize = parseInt(column);
            }

            // correct column width
            if (columnSize < 1) {
                columnSize = 1;
            }
            if (columnSize > gridSize) {
                columnSize = gridSize;
            }

            // gridSize is the maximal total column width
            var totalColumnWidth = marginLeft + columnSize + marginRight;
            if (totalColumnWidth > gridSize) {
                marginLeft = 0;
                columnSize = gridSize;
                marginRight = 0;
            }

            result.push({
                columnSize: columnSize,
                marginLeft: marginLeft,
                marginRight: marginRight
            });
        }

        return result;
    }


    //
    //
    // public interface
    //
    //

    /**
     * @public
     * @param {String} [gridDefinitionString] - represents the grid column configuration as string (e.g. "1-3-1 3 5")
     * @param {String} [gridSize] - e.g. 12 or 24
     * @constructor
     */
    function GridDefinitionHelper(gridDefinitionString, gridSize) {
        this.gridSize = gridSize;

        gridDefinitionString = validateGridDefinitionString(gridDefinitionString);
        this.gridColumns = parseGridDefinitionString(gridDefinitionString, gridSize);
        this.originalGridColumns = JSON.parse(JSON.stringify(this.gridColumns));
    }

    /**
     * @public
     * @returns {Integer}
     */
    GridDefinitionHelper.prototype.getNumberOfColumns = function () {
        return this.gridColumns.length;
    };

    /**
     * @public
     * @param {Integer} columnIndex
     * @param {Integer} delta
     * @returns {Boolean}
     */
    GridDefinitionHelper.prototype.dragColumnSize = function (columnIndex, delta) {
        var column = this.gridColumns[columnIndex];
        var columnStringBeforeChange = JSON.stringify(column);
        var originalColumn = this.originalGridColumns[columnIndex];

        column.columnSize = originalColumn.columnSize + delta;

        // prevent column size smaller then 1
        if (column.columnSize < 1) {
            column.columnSize = 1;
        }

        // prevent column size bigger than grid size
        if (column.columnSize > this.gridSize) {
            column.columnSize = this.gridSize;
        }

        return columnStringBeforeChange !== JSON.stringify(column);
    };

    /**
     * @public
     * @param {Integer} columnIndex
     * @param {Integer} delta
     * @returns {Boolean}
     */
    GridDefinitionHelper.prototype.dragColumnMarginLeft = function (columnIndex, delta) {
        var column = this.gridColumns[columnIndex];
        var columnStringBeforeChange = JSON.stringify(column);
        var originalColumn = this.originalGridColumns[columnIndex];
        var originalColumnTotalSize = originalColumn.columnSize + originalColumn.marginLeft + originalColumn.marginRight;

        column.marginLeft = originalColumn.marginLeft + delta;
        column.columnSize = originalColumn.columnSize - delta;

        // prevent negative margin
        if (column.marginLeft < 0) {
            column.marginLeft = 0;
        }

        // prevent column size smaller then 1
        if (column.columnSize < 1) {
            column.columnSize = 1;
        }

        // make sure, the total column size won't get changed
        var newColumnTotalSize = column.columnSize + column.marginLeft + column.marginRight;
        if (newColumnTotalSize != originalColumnTotalSize) {
            return false;
        }

        return columnStringBeforeChange !== JSON.stringify(column);
    };

    /**
     * @public
     * @param {Integer} columnIndex
     * @param {Integer} delta
     * @returns {Boolean}
     */
    GridDefinitionHelper.prototype.dragColumnMarginRight = function (columnIndex, delta) {
        var column = this.gridColumns[columnIndex];
        var columnStringBeforeChange = JSON.stringify(column);
        var originalColumn = this.originalGridColumns[columnIndex];
        var originalColumnTotalSize = originalColumn.columnSize + originalColumn.marginLeft + originalColumn.marginRight;

        column.marginRight = originalColumn.marginRight - delta;
        column.columnSize = originalColumn.columnSize + delta;

        // prevent negative margin
        if (column.marginRight < 0) {
            column.marginRight = 0;
        }

        // prevent column size smaller then 1
        if (column.columnSize < 1) {
            column.columnSize = 1;
        }

        // make sure, the total column size won't get changed
        var newColumnTotalSize = column.columnSize + column.marginLeft + column.marginRight;
        if (newColumnTotalSize != originalColumnTotalSize) {
            return false;
        }

        return columnStringBeforeChange !== JSON.stringify(column);
    };

    /**
     * @public
     * @returns {String}
     */
    GridDefinitionHelper.prototype.serializeGridDefinition = function () {
        var rowSum = 0;
        var result = '';

        for (var i = 0; i < this.gridColumns.length; i++) {
            var column = this.gridColumns[i];
            var totalColumnSize = column.marginLeft + column.columnSize + column.marginRight;

            var columnString;
            if (column.marginLeft === 0 && column.marginRight === 0) {
                columnString = column.columnSize;
            } else {
                columnString = column.marginLeft + '-' + column.columnSize + '-' + column.marginRight;
            }

            // add new lines if row is filled
            if ((rowSum + totalColumnSize) > this.gridSize) {
                result = result.trim();
                result += "\n" + columnString + ' ';
                rowSum = totalColumnSize;
            } else {
                result += columnString + ' ';
                rowSum += totalColumnSize;
            }
        }

        // delete whitespace and newline at the end
        result = result.replace(/[ \n]$/g, '');

        return result;
    };

    return {
        create: function (gridDefinition, gridSize) {
            return new GridDefinitionHelper(gridDefinition, gridSize);
        }
    };
});
