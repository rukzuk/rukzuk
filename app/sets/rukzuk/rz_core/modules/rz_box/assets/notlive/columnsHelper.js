define([], function () {

    //
    //
    // private helper
    //
    //

    /**
     * Parses the column width descriptor string (e.g. "50px 40% 60%") and returns
     * an array of column configuration objects
     * @private
     */
    function parseChildWidthString(childWidthString) {
        var columns = childWidthString.trim().replace(/\s+/g, ' ').split(' ');
        var numberOfColumns = columns.length;
        var childWidth = [];

        for (var i = 0; i < numberOfColumns; i++) {
            var type = columns[i].match(/px|%$/);
            var valign = columns[i].match(/^[mu]/);

            childWidth.push({
                unit: type !== null ? type[0] : '',
                valign: valign !== null ? valign[0] : '',
                value: parseFloat(columns[i].replace(/[mu]|%|px/g, '').replace(',', '.')),
                index: i
            });
        }

        return childWidth;
    }

    //
    //
    // public interface
    //
    //

    /**
     * @public
     * @param {String} [childWidthString] - represents the column configuration as string (e.g. "u20% 200px 30%")
     * @param {String} [hSpace] - e.g 3% or 20px
     * @constructor
     */
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
        this.childWidths = parseChildWidthString(childWidthString);

        return this;
    };

    /**
     * @public
     * @returns {String}
     */
    ColumnBoxSettingsHelper.prototype.serializeChildWidth = function () {
        var childWidthObj = this.childWidths;
        var result = '';
        for (var i = 0; i < childWidthObj.length; i++) {
            // round with two fractional digits if percent, round to integers otherwise
            var value = (childWidthObj[i].unit == '%') ? Math.round(childWidthObj[i].value * 10) / 10 : Math.round(childWidthObj[i].value);
            // append to result
            result += childWidthObj[i].valign + value + childWidthObj[i].unit + ' ';
        }
        return result.slice(0, -1); // remove last space char
    };

    ColumnBoxSettingsHelper.prototype.widthSumByUnitType = function (unit) {
        var childSum = 0;
        var elements = [];

        // sum up all percent-columns - remember them
        for (var i = 0; i < this.childWidths.length; i++) {
            if (this.childWidths[i].unit == unit) {
                childSum += this.childWidths[i].value;
                elements.push(this.childWidths[i]);
            }
        }

        return {
            sum: childSum,
            elements: elements
        };
    };

    /**
     * @public
     */
    ColumnBoxSettingsHelper.prototype.correctChildWidth = function () {
        var numberOfColumns = this.childWidths.length;
        var hSpaceValue = parseFloat(this.hSpace);
        var hSpaceIsPercent = (this.hSpace.indexOf('%') > -1);
        var widthByType = this.widthSumByUnitType('%');
        var childWidthsPercent = widthByType.elements;
        var numberOfChildWidthsPercent = childWidthsPercent.length;
        var numberOfSpacer = hSpaceIsPercent ? numberOfColumns - 1 : 0;
        var maxPercent = 100 - numberOfSpacer * hSpaceValue;
        var factor = maxPercent / widthByType.sum;

        for (var i = 0; i < numberOfChildWidthsPercent; i++) {
            // correct percent value
            childWidthsPercent[i].value = childWidthsPercent[i].value * factor;

            // round
            childWidthsPercent[i].value = Math.round(childWidthsPercent[i].value * 10) / 10;

            // fix negative values!!
            if (childWidthsPercent[i].value < 0) {
                childWidthsPercent[i].value = 0.1;
            }
        }

        return this;
    };

    return {
        create: function (childWidth, hSpace) {
            return new ColumnBoxSettingsHelper(childWidth, hSpace);
        }
    };
});
