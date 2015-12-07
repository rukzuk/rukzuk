Ext.ns('CMS');

/**
* @class CMS.ModuleGrid
* @extends Ext.grid.GridPanel
*/
CMS.ModuleGrid = Ext.extend(Ext.grid.GridPanel, {
    /** @lends CMS.ModuleGrid.prototype */

    bubbleEvents: ['editmodule'],
    autoExpandColumn: 'name',
    enableDragDrop: true,
    ddGroup: CMS.config.ddGroups.modules,
    loadMask: true,
    enableColumnHide: false,
    forceFit: true,
    cls: 'CMSmodulegrid',

    /**
     * @cfg {Boolean} singleSelect
     * <tt>false</tt> to allow mutliple selection (defaults to <tt>true</tt>)
     */
    singleSelect: true,

    /**
     * @cfg {Function} filterFn
     * A function which is used to filter the grid's content
     * It will be passed the following parameters:
     * <ul>
     *   <li>record : Ext.data.Record<br>
     *   The record to test for filtering. Access field values using Ext.data.Record.get</li>
     *   <li>id : Object<br>
     *   The ID of the Record passed.</li>
     * </ul>
     * The source store is not affected
     */
    filterFn: undefined,

    /**
     * cfg {String} groupField
     * The field name to group modules (defaults to "category")
     */
    groupField: 'category',

    /**
     * @cfg {String} groupDir
     * The grouping direction (defaults to "ASC")
     */
    groupDir: 'ASC',

    /**
     * @cfg {String} groupTextTpl
     * Template for the group header text {@Link Ext.grid.GroupingView#groupTextTpl}
     */
    groupTextTpl: undefined,

    /**
     * @cfg {Function} recordType
     * The type of records which are going to be shown in the grid
     */
    recordType: CMS.data.ModuleRecord,

    /**
     * @cfg {Boolean} descriptionToolTip
     * If enabled, show the module description as tooltip when hovering the grid row
     */
    descriptionToolTip: false,

    /**
     * @private
     */
    initComponent: function () {
        var sourceStore = this.store;
        // create an Ext.data.GroupingStore which is required for grouping
        this.store = new Ext.data.GroupingStore({
            reader: new Ext.data.DataReader({}, this.recordType),
            sortInfo: {
                field: 'name',
                direction: 'ASC'
            },
            groupField: this.groupField,
            groupDir: this.groupDir
        });

        // get actual data from given source
        if (sourceStore) {
            this.bindStore(sourceStore);
        }

        this.columns = this.applyColumnCfg([{
            fixed: true,
            id: 'id',
            dataIndex: 'id',
            hidden: true,
            groupable: false,
            sortable: false,
            hidable: false
        }, {
            fixed: true,
            groupable: false,
            sortable: false,
            id: 'icon',
            dataIndex: 'icon',
            header: '&#160;',
            width: 25,
            resizable: false,
            menuDisabled: true,
            renderer: function (raw) {
                var src = raw ? (CMS.config.urls.moduleIconPath + raw) : Ext.BLANK_IMAGE_URL;
                var alt = raw || CMS.i18n('(kein Icon)');
                return '<img width="16" height="16" src="' + src + '" alt="' + alt + '">';
            }
        }, {
            id: 'name',
            groupable: true,
            sortable: true,
            dataIndex: 'name',
            header: CMS.i18n('Bezeichnung'),
            fixed: true,
            renderer: function (val) {
                return CMS.translateInput(val);
            }
        }, {
            // hidden column required for grouping
            id: 'category',
            sortable: true,
            dataIndex: 'category',
            header: CMS.i18n('Kategorie'),
            width: 130,
            renderer: function (val) {
                return CMS.translateInput(val);
            }
        }], this.columns);

        if (!this.sm) {
            this.sm = new Ext.grid.RowSelectionModel({ singleSelect: !!this.singleSelect });
        }

        this.additionalViewConfig.groupTextTpl = this.groupTextTpl || '{text}';
        this.view = new Ext.grid.GroupingView(Ext.apply(this.viewConfig || {}, this.additionalViewConfig));

        CMS.ModuleGrid.superclass.initComponent.apply(this, arguments);

        this.on({
            'cellclick': {
                fn: this.cellclickHandler,
                scope: this
            }
        });

        if (this.descriptionToolTip) {
            this.on('render', this.addDescriptionToolTips, this);
        }
    },

    /**
     * Shows the module description as tooltip when hovering the grid row
     * @private
     */
    addDescriptionToolTips: function () {
        var store = this.getStore();
        var view = this.getView();
        this.descriptionToolTips = new Ext.ToolTip({
            target: view.mainBody,
            delegate: view.rowSelector,
            trackMouse: false,
            anchor: 'left',
            constrainPosition: true,
            renderTo: Ext.getBody(),
            listeners: {
                beforeshow: function (tip) {
                    var rowIndex = view.findRowIndex(tip.triggerElement);
                    var record = store.getAt(rowIndex);
                    var description = CMS.translateInput(record.get('description'));
                    if (description && description.length) {
                        description = Ext.util.Format.htmlEncode(description);
                        tip.body.dom.innerHTML = description;
                    } else {
                        return false;
                    }
                }
            }
        });

        this.on('destroy', function () {
            this.descriptionToolTips.destroy();
        }, this);
    },

    /**
     * Overrides the default column configuration with the given values
     * @private
     */
    applyColumnCfg: function (defaults, columns) {
        if (Ext.isArray(columns)) {
            var ids = Ext.pluck(defaults, 'id');
            Ext.each(columns, function (col) {
                var idx = ids.indexOf(col.id);
                if (idx >= 0) {
                    defaults[idx] = Ext.apply(defaults[idx], col);
                } else {
                    defaults.push(col);
                }
            });
        }
        return defaults;
    },

    /**
     * The configuration of the GroupingView
     *
     * @property additionalViewConfig
     * @type Object
     * @private
     */
    additionalViewConfig: {
        showGroupName: false,
        enableGrouping: true,
        hideGroupedColumn: true,

        // prevent space reservation for scrollbar
        scrollOffset: 0,

        // Overrides Ext.grid.GroupingView.prototype.renderUI to
        //  * hide "Group by" option in column header menu
        //  * repair drag ghost position
        // NOTICE: this has to adapted when updating Ext
        renderUI: function () {
            Ext.grid.GroupingView.superclass.renderUI.call(this);
            this.mainBody.on('mousedown', this.interceptMouse, this);

            if (this.enableGroupingMenu && this.hmenu) {
                if (this.enableNoGroups) {
                    this.hmenu.add({
                        itemId: 'showGroups',
                        text: this.showGroupsText,
                        checked: true,
                        checkHandler: this.onShowGroupsClick,
                        scope: this
                    });
                }
                this.hmenu.on('beforeshow', this.beforeMenuShow, this);
            }

            // re-position grid's drag ghost after invalid drop
            if (this.grid.enableDragDrop || this.grid.enableDrag) {
                this.dragZone.getRepairXY = function (e, data) {
                    var colIndex = this.grid.colModel.findColumnIndex('name');
                    return Ext.fly(this.view.getCell(data.rowIndex, colIndex)).getXY();
                };
            }
        },

        // override to group always by the configured groupField
        onGroupByClick: function () {
            var grid = this.grid;
            this.enableGrouping = true;
            this.hideGroupedColumn = true;
            grid.store.groupBy(grid.groupField);
            grid.fireEvent('groupchange', grid, grid.store.getGroupState());
            this.beforeMenuShow(); // Make sure the checkboxes get properly set when changing groups
            this.refresh();
        }
    },

    getDragDropText: function () {
        var sel = this.getSelectionModel().getSelected();
        return CMS.translateInput(sel.get('name'));
    },

    cellclickHandler: function (grid, rowIndex, colIndex) {
        var editIndex = this.getColumnModel().getIndexById('edit');
        var checkedIndex = this.getColumnModel().getIndexById('checked');
        var record = this.store.getAt(rowIndex);
        var status;

        switch (colIndex) {
        case editIndex:
            this.fireEvent('editmodule', record);
            break;

        case checkedIndex:
            status = !record.get('checkbox');
            record.set('checkbox', status);

            if (status) {
                this.selectedModules.push(record.id);
            } else {
                this.selectedModules.remove(record.id);
            }

            /**
             * Fired when the selection of modules has changed
             * @event
             * @name modulesselectionchange
             *
             * @param {Array} selectedModules
             *      The ids of the selected modules.
             */
            this.fireEvent('modulesselectionchange', this.selectedModules);
            break;

        default:
            break;
        }
    },

    /**
     * Sets the filter function and updates content
     *
     * @param {Function} [filterFn] The function to filter the store content; Leave empty
     *      to clear filter
     */
    filterBy: function (filterFn) {
        this.filterFn = filterFn;
        if (Ext.isFunction(this.filterFn)) {
            this.store.filterBy(this.filterFn);
        } else {
            this.store.clearFilter();
        }

        if (this.store.groupField) {
            // store was grouped before
            // -> we have to restore the grouping after changing the content
            this.store.groupBy(this.groupField, true, this.groupDir);
        }

        var si = this.store.sortInfo;
        if (si) {
            // sometimes the sorting gets lost -> restore it
            this.store.sort(si.field, si.direction);
        }
    },

    /**
     * Synchronizes the content of the interal GroupingStore with the given
     * source store
     * @private
     */
    syncData: function () {
        this.store.removeAll();
        if (this.sourceStore) {
            var records = this.sourceStore.getRange();
            this.store.add(records);
            for (var i = 0, l = records.length; i < l; i++) {
                records[i].store = this.sourceStore;
            }
            this.filterBy(this.filterFn);
        }
    },

    /**
     * Binds a store as data source to the ModuleGrid
     *
     * @param {Ext.data.Store} store
     *      The data source
     */
    bindStore: function (store) {
        if (this.sourceStore) {
            this.sourceStore.un('datachanged', this.syncData, this);
            this.sourceStore.un('update', this.syncData, this);
            this.sourceStore.un('remove', this.syncData, this);
        }
        this.sourceStore = store;
        if (store) {
            store.on('datachanged', this.syncData, this);
            store.on('update', this.syncData, this);
            store.on('remove', this.syncData, this);
        }
        this.syncData();
    },

    /**
     * Reload the bound source store
     *
     * @param {Object} cfg
     *      The loading configuration; See {@link Ext.data.Store#reload} for details
     */
    reload: function (cfg) {
        if (this.sourceStore) {
            this.sourceStore.reload(cfg);
        }
    },

    // overrides superclass to unbind data source store
    destroy: function () {
        this.bindStore(null); // remove reference to source store
        CMS.ModuleGrid.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('CMSmodulegrid', CMS.ModuleGrid);
