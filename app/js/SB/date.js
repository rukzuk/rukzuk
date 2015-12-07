// Utility functions by Seitenbau

/**
* @class SB.date
* Just a namespace containing date related functions
* @singleton
*/
Ext.ns('SB.date');

/**
* Convert Date to JS timestamp
* @method jsTimeStampFromDate
* @param {Date|String} date (optional) A date object or string. If ommitted, creates a new Date object.
* @return Number
*/
SB.date.jsTimeStampFromDate = function (date) {
    if (!date) {
        date = new Date();
    }
    if (typeof date == 'string') {
        return Date.parse(date);
    }
    return +date;
};

/**
* Convert Date to UNIX timestamp
* @method unixTimeStampFromDate
* @param {Date|String} input A date object or string. If ommitted, creates a new Date object.
* @return Number
*/
SB.date.unixTimeStampFromDate = function (date) {
    var jsTimeStamp = SB.date.jsTimeStampFromDate(date);
    return parseInt(jsTimeStamp / 1000, 10);
};

/**
* Convert UNIX timestamp to Date object
* @method dateFromUnixTimeStamp
* @param {Number|String} A unix timestamp
* @return Date
*/
SB.date.dateFromUnixTimeStamp = function (unix) {
    return new Date(unix * 1000);
};


/**
* Add extensions to Date.prototype
*/
SB.date.addDateExtensions = function () {
    if (Date.prototype.getMonthLength) {
        return;
    }

    /**
    * @member Date
    * Get the date's month length
    * @return Integer
    */
    Date.prototype.getMonthLength = function () {
        return 42 - (new Date(this.getFullYear(), this.getMonth(), 42)).getDate();
    };

    /**
    * @member Date
    * Get the number of days that have passed since a given date.
    * May be called with one or three arguments. If called with one argument, this is passed to the Date constructor.
    * @param {Integer} year
    * @param {Integer} month
    * @param {Integer} day
    * @return Integer (may be negative)
    */
    Date.prototype.getDaysSince = function (year, month, day) {
        if (arguments.length == 1) {
            var reference = new Date(year);
            year = reference.getFullYear();
            month = reference.getMonth();
            day = reference.getDate();
        }
        return (new Date(this.getFullYear(), this.getMonth(), this.getDate()) - new Date(+year, +month || 0, +day || 1)) / (1000 * 60 * 60 * 24);
    };

    /**
    * @member Date
    * Get the number of days that will pass till a given date is reached.
    * May be called with one or three arguments. If called with one argument, this is passed to the Date constructor.
    * @param {Integer} year
    * @param {Integer} month
    * @param {Integer} day
    * @return Integer (may be negative)
    */
    Date.prototype.getDaysTo = function () {
        return -this.getDaysSince.apply(this, arguments);
    };
};
