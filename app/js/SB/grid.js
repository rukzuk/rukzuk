// Grid utility functions by Seitenbau

/**
* @class SB.grid
* @singleton
* Just a namespace containing grid utility functions
*/
Ext.ns('SB.grid');


/**
* @method ImageRenderer
*/
SB.grid.ImageRenderer = function (value, meta, record, rowIndex, colIndex, store) {
    return '<img src="' + value + '" alt="' + (record.get('alt') || '') + '">';
};
