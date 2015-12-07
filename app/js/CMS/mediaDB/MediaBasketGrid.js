Ext.ns('CMS.mediaDB');

/**
 * @class CMS.userManagement.GroupGrid
 * @extends Ext.grid.GridPanel
 *
 * The grid which is used to add or remove a single user to/from different groups
 *
 */
CMS.mediaDB.MediaBasketGrid = Ext.extend(Ext.grid.GridPanel, {

    cls: 'mediaDBSelectionGrid',

    /**
     * @cfg {CMS.data.UserRecord} user
     * The currently selected user
     */
    user: null,

    initComponent: function () {
        this.viewConfig = this.viewConfig || {};
        this.viewConfig.deferEmptyText = false;
        this.viewConfig.emptyText = CMS.i18n('Bilder hier herein ziehen…');

        this.selectionInfoTxt = CMS.i18n('{num} Datei(en) ausgewählt');

        var config = {
            header: false,
            border: false,
            hideHeaders: true,
            store: new Ext.data.JsonStore({
                fields: CMS.data.mediaFields,
                sortInfo: {
                    field: 'name',
                    direction: 'ASC'
                }
            }),
            columns: [{
                id: 'icon',
                dataIndex: 'icon',
                hideable: false,
                menuDisabled: true,
                resizable: false,
                width: 100,
                renderer: function (value, meta, record) {
                    return '<img class="thumbnail" src="' + value + '" alt="' + (record.get('alt') || '') + '">';
                }
            }, {
                id: 'name',
                dataIndex: 'name',
                hideable: false,
                menuDisabled: true,
                resizable: false
            }, {
                id: 'remove',
                dataIndex: 'name',
                hideable: false,
                menuDisabled: true,
                resizable: false,
                renderer: function (value, meta) {
                    meta.attr = 'ext:qtip="' + CMS.i18n('Aus Auswahl entfernen') + '"';
                    return '<img class="action remove" src="' +  Ext.BLANK_IMAGE_URL + '" width="16">';
                },
                width: 40
            }],
            sm: new Ext.grid.RowSelectionModel({
                singleSelect: true
            }),
            autoExpandColumn: 'name',
            bbar: {
                items: [{
                    xtype: 'box',
                    autoEl: {
                        tag: 'div',
                        html: this.selectionInfoTxt.replace('{num}', 0)
                    },
                    cls: 'selection-info',
                    ref: '../infoTextEl'
                }]
            },
            ddGroup: 'mediaDD',
            enableDrag: true
        };
        Ext.apply(this, config);
        CMS.mediaDB.MediaBasketGrid.superclass.initComponent.apply(this, arguments);

        this.on('cellclick', this.cellclickHandler, this);
        this.on('render', this.initializeDropZone, this);
        this.on('CMSmediareplaced', function () {
            this.getView().refresh();
        }, this);
    },

    /**
     * Initialize the DropZone, so mediaItems can be dropped into albums
     * @private
     */
    initializeDropZone: function (grid) {

        grid.body.addClass('grid-roworderable'); // for CSS

        grid.dropZone = new Ext.dd.DropZone(grid.getEl(), {
            ddGroup: 'mediaDD',

            getTargetFromEvent: function (e) {
                return e.getTarget(grid.getView().rowSelector);
            },

            /**
             * Determine on which row index and position inside the row a mouse event occurs
             * Parameters as passed to {@link Ext.dd.DropZone#onNodeOver}
             * @param {HTMLElement} target
             * @param {Ext.dd.DragSource} dd
             * @param {Event} e
             * @param {Object} data
             * @return Object
             * An object containing the properties <tt>rowIndex</tt> and <tt>delta</tt>, where
             * rowIndex is the index of the row within the grid and
             * delta &lt;/&gt; 0 means the mouse is in the upper/lower half of the row element.
             * @private
             */
            findRowPosition: function (target, dd, e) {
                var view = grid.getView();
                var rindex = view.findRowIndex(target);
                var rowY = Ext.fly(target).getY() - view.scroller.dom.scrollTop;
                var rowHeight = target.offsetHeight;

                return {
                    rowIndex: rindex,
                    delta: e.getPageY() - rowY - (rowHeight / 2)
                };

            },

            onNodeOver: function (target, dd, e, data) {
                var pos = this.findRowPosition(target, dd, e, data);
                var newOver = Ext.get(target);
                if (this.currentOver && (newOver !== this.currentOver)) {
                    this.currentOver.removeClass('grid-row-insert-below');
                    this.currentOver.removeClass('grid-row-insert-above');
                }
                if (pos.delta > 0) {
                    newOver.removeClass('grid-row-insert-above');
                    newOver.addClass('grid-row-insert-below');
                } else {
                    newOver.removeClass('grid-row-insert-below');
                    newOver.addClass('grid-row-insert-above');
                }
                this.currentOver = newOver;
                this.currentPos = pos;
                return Ext.dd.DropZone.prototype.dropAllowed;
            },

            notifyEnter: function (dd, e) {
                var n = this.getTargetFromEvent(e);
                if (n) {
                    return this.dropAllowed;
                }
                var items = grid.store.getRange();
                if (items.length) {
                    this.currentOver = Ext.get(grid.getView().getRow(items.length - 1));
                    this.currentOver.addClass('grid-row-insert-below');
                }
                return this.dropAllowed;
            },

            notifyOut: function () {
                if (this.currentOver) {
                    this.currentOver.removeClass('grid-row-insert-below');
                    this.currentOver.removeClass('grid-row-insert-above');
                    this.currentOver = null;
                }
            },

            onNodeDrop: function (target, dd, e, data) {
                if (this.currentOver) {
                    this.currentOver.removeClass('grid-row-insert-below');
                    this.currentOver.removeClass('grid-row-insert-above');
                    this.currentOver = null;
                }
                var insertionIndex = this.currentPos.rowIndex;
                if (this.currentPos.delta > 0) {
                    insertionIndex++;
                }
                grid.insertInSelection(data.selections, insertionIndex);
                return true;
            },

            onContainerOver: function () {
                return Ext.dd.DropZone.prototype.dropAllowed;
            },

            onContainerDrop: function (source, event, data) {
                if (this.currentOver) {
                    this.currentOver.removeClass('grid-row-insert-below');
                    this.currentOver.removeClass('grid-row-insert-above');
                    this.currentOver = null;
                    this.currentPos = null;
                }
                var store = grid.getStore();
                store.suspendEvents();
                Ext.each(data.selections, function (record) {
                    grid.addToSelection(record);
                });
                store.resumeEvents();
                grid.getView().refresh();
                return true;
            }

        });
    },


    /**
     * Checks if the cell which has been clicked by the user was
     * the 'remove user' one. If this is the case, it fires the
     * 'removeuser' event passing the row record object along
     * @private
     */
    cellclickHandler: function (grid, rowIndex, colIndex) {
        var removeIndex = this.getColumnModel().getIndexById('remove'),
            record = this.store.getAt(rowIndex);
        if (colIndex === removeIndex) {
            this.removeFromSelection(record);
        }
    },

    /**
     * add an record to the grid's store; the record will be cloned to avoid
     * side effects
     *
     * @param {CMS.data.MediaRecord} record
     *      the record to be added
     */
    addToSelection: function (record) {
        var store = this.getStore();
        var present = store.getById(record.id);
        if (present) {
            // move to bottom
            store.remove(present);
            store.add(present);
        } else {
            var clone = record.copy();
            store.add(clone);
            /**
             * @event addfile
             * Fires if a new file has been added to the selection basket (grid)
             *
             * @param record
             *      The added record
             *
             * @param this
             *      This component
             */
            this.fireEvent('addfile', clone, this);
        }

        this.updateSelectionInfoText();
    },

    /**
     * Insert a record or multiple records in the grid's store at the given index.
     * For inserting a single record at the bottom, use {@link #addToSelection} for performance reasons.
     * @param {Array|CMS.data.MediaRecord} records
     * @param {Integer} index The insertion position
     */
    insertInSelection: function (records, index) {
        var store = this.store;
        var ids = Ext.pluck(store.data, 'id');
        var i, l;

        if (!Ext.isArray(records)) {
            records = [records];
        }
        store.suspendEvents();
        for (i = 0, l = records.length; i < l; i++) {
            var record = records[i];
            var pos = ids.indexOf(record.id);
            if (pos === -1) {
                records[i] = record.copy();
            } else {
                if (pos < index) {
                    index--;
                }
                store.remove(store.getById(record.id));
            }
        }
        for (i = 0, l = records.length; i < l; i++) {
            store.data.insert(index, records[i]);
            records[i].join(store);
            index++;
        }
        store.resumeEvents();
        this.getView().refresh();
    },

    /**
     * removed an record from the grid's store
     *
     * @param {Object} record
     *      the record to be removed
     */
    removeFromSelection: function (record) {
        var store = this.getStore();
        store.remove(record);

        this.updateSelectionInfoText();

        /**
         * @event removefile
         * Fires if a new file has been removed from the selection basket (grid)
         *
         * @param record
         *      The removed record
         *
         * @param this
         *      This component
         */
        this.fireEvent('removefile', record, this);
    },

    /**
     * @private
     * updates the info text about how many files are actually selected after
     * adding/removing an entry
     */
    updateSelectionInfoText: function () {
        var num = this.getStore().getRange().length;
        var newInfo = this.selectionInfoTxt.replace('{num}', num);
        this.infoTextEl.getEl().update(newInfo);
    },

    destroy: function () {
        if (this.store) {
            this.store.destroy();
        }
        if (this.dropZone) {
            this.dropZone.destroy();
        }
        CMS.mediaDB.MediaBasketGrid.superclass.destroy.apply(this, arguments);
    }

});

Ext.reg('CMSmediabasketgrid', CMS.mediaDB.MediaBasketGrid);
