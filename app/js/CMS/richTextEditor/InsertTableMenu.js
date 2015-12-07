Ext.ns('CMS.richTextEditor');

/**
 * @class CMS.richTextEditor.InsertTableMenu
 * @extends Ext.menu.Menu
 */
CMS.richTextEditor.InsertTableMenu = Ext.extend(Ext.menu.Menu, {
    /** @lends CMS.richTextEditor.InsertTableMenu.prototype */

    cls: 'CMSinserttablemenu',
    plain: true,
    showSeparator: false,

    /**
     * The maxium number of columns to choose from (defaults to <code>10</code>)
     * @property columns
     * @type Number
     */
    columns: 10,

    /**
     * The maxium number of rows to choose from (defaults to <code>10</code>)
     * @property rows
     * @type Number
     */
    rows: 10,

    /**
     * The callback method which is executed when selecting a table
     * dimension; the method is called the following arguments:
     * <ul>
     *   <li>the current CMS.richTextEditor.InsertTableMenu instance</li>
     *   <li>the ext event object for the click event</li>
     *   <li>the selected number of columns</li>
     *   <li>the selected number of rows</li>
     * </ul>
     * @property handler
     * @type Function
     */
    handler: undefined,

    /**
     * The excution context for the handler method
     * @property scope
     * @type Object
     */
    scope: undefined,

    /** @protected */
    initComponent: function () {

        // create the data for the grid cells
        var data = [];
        for (var j = 0; j < this.rows; j++) {
            for (var i = 0; i < this.columns; i++) {
                data.push([i + '#' + j, i + 1, j + 1]);
            }
        }

        /**
         * The internal data view instance which contains the cell grid
         * @property dataview
         * @memberOf CMS.richTextEditor.InsertTableMenu
         * @type Ext.DataView
         * @private
         */
        this.dataview = new Ext.DataView({
            cls: 'CMSinserttableview',
            tpl: new Ext.XTemplate(
                '<div class="CMSinserttablegrid">',
                    '<tpl for="."><div class="cell"></div></tpl>',
                '</div>',
                '<div class="CMSinserttableinfo">0 &times; 0</div>'
            ),
            store: new Ext.data.ArrayStore({
                fields: ['id', 'column', 'row'],
                data: data
            }),
            itemSelector: 'div.cell',
            trackOver: true,
            listeners: {
                mouseenter: this.handleCellOver,
                click: this.handleCellClick,
                scope: this
            }
        });

        this.items = [this.dataview];

        CMS.richTextEditor.InsertTableMenu.superclass.initComponent.call(this);

        // clear previous selections
        this.on('show', function () {
            this.dataview.refresh();
        }, this);
    },

    /**
     * The handler method for the "mouseenter" event of the dataview; Updates GUI
     * to visualize the selection in spe
     * @private
     */
    handleCellOver: function (dataview, index) {
        var store = dataview.getStore();
        var cell = store.getAt(index);
        var col = cell.get('column');
        var row = cell.get('row');
        var allCells = store.getRange();

        for (var i = 0; i < allCells.length; i++) {
            var otherCell = allCells[i];
            var cellNode = Ext.fly(dataview.getNode(i));
            if (col >= otherCell.get('column') && row >= otherCell.get('row')) {
                cellNode.addClass('CMSselected');
            } else {
                cellNode.removeClass('CMSselected');
            }
        }

        Ext.fly(Ext.DomQuery.selectNode('div.CMSinserttableinfo', dataview.getEl().dom)).update(col + ' &times; ' + row);
    },

    /**
     * The click handler to select table dimension; calls handler method
     * @private
     */
    handleCellClick: function (dataview, index, node, e) {
        if (Ext.isFunction(this.handler)) {
            var cell = dataview.getStore().getAt(index);
            this.handler.call(this.scope, this, e, cell.get('column'), cell.get('row'));
        }
        this.hide(true);
    }
});

Ext.reg('CMSinserttablemenu', CMS.richTextEditor.InsertTableMenu);
