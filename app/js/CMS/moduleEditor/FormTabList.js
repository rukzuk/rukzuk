Ext.ns('CMS.moduleEditor');

/**
 * A list of form groups
 *
 * @class CMS.moduleEditor.FormTabList
 * @extends Ext.grid.GridPanel
 */
CMS.moduleEditor.FormTabList = Ext.extend(Ext.grid.GridPanel, {
    /** @lends CMS.moduleEditor.FormTabList.prototype */

    cls: 'CMSformtablist',

    /**
     * Id of the current selected website
     * @property websiteId
     * @type {String}
     */
    websiteId: '',

    initComponent: function () {
        this.enableHdMenu = false;

        this.columns = [{
            id: 'id',
            dataIndex: 'id',
            resizable: false,
            hidden: true
        }, {
            id: 'name',
            dataIndex: 'name',
            renderer: function (raw) {
                return CMS.translateInput(raw);
            },
            resizable: false,
            header: CMS.i18n('Bezeichnung')
        }];

        this.autoExpandColumn = 'name';

        this.bbar = {
            items: [{
                tooltip: CMS.i18n('Neuer Reiter'),
                iconCls: 'add addformgrp',
                ref: '../addButton',
                handler: this.addHandler,
                scope: this
            }, {
                tooltip: CMS.i18n('Reiter löschen'),
                iconCls: 'delete deleteformgrp',
                ref: '../deleteButton',
                handler: this.deleteHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: CMS.i18n('Umbenennen'),
                iconCls: 'rename renameformgrp',
                ref: '../renameButton',
                handler: this.renameHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: CMS.i18n('Nach oben verschieben'),
                ref: '../upButton',
                iconCls: 'up upformgrp',
                disabled: true,
                handler: this.upHandler,
                scope: this
            }, {
                tooltip: CMS.i18n('Nach unten verschieben'),
                ref: '../downButton',
                iconCls: 'down downformgrp',
                disabled: true,
                handler: this.downHandler,
                scope: this
            }]
        };
        this.sm = new Ext.grid.RowSelectionModel({ singleSelect: true });
        this.store = this.store || new Ext.data.ArrayStore({
            fields: CMS.data.FormGroupRecord
        });
        CMS.moduleEditor.FormTabList.superclass.initComponent.apply(this, arguments);
        this.on('viewready', function () {
            if (this.getView().hasRows()) {
                this.getSelectionModel().selectFirstRow();
            }
        }, this, { single: true });
        this.mon(this.getSelectionModel(), 'selectionchange', this.onSelectionChange, this);
    },

    /**
     * Handle selectionChange
     * @private
     */
    onSelectionChange: function (sm) {
        this.updateButtons();
        if (!this.moving && sm.hasSelection()) {
            /**
            * @event select Fired when an entry is selected
            * @param {CMS.data.FormGroupRecord} record The selected record
            */
            this.fireEvent('select', sm.getSelected());
        }
    },

    /**
     * Enable/disable buttons
     * @private
     */
    updateButtons: function () {
        var sm = this.getSelectionModel();
        if (!sm.hasSelection()) {
            this.deleteButton.setDisabled(true);
            this.renameButton.setDisabled(true);
            this.upButton.setDisabled(true);
            this.downButton.setDisabled(true);
            return;
        }
        this.deleteButton.setDisabled(false);
        this.renameButton.setDisabled(false);
        this.upButton.setDisabled(!sm.hasPrevious());
        this.downButton.setDisabled(!sm.hasNext());
    },

    /**
     * handler for "rename" button
     * @private
     */
    renameHandler: function () {
        var sm = this.getSelectionModel();
        var record = sm.getSelected();
        CMS.Message.prompt(
            CMS.i18n('Neuen Titel eingeben', 'formtablist.rename.title'),
            CMS.i18n('Bezeichnung', 'formtablist.rename.message'),
            function (btnId, title, msgbox) {
                if (btnId == 'ok') {
                    record.set('name', title);
                    record.commit();
                    this.fireEvent('select', sm.getSelected()); // propagate changes
                }
            },
            this,
            false,
            record.get('name')
        );
    },

    /**
     * Handler for "new" button
     * @private
     */
    addHandler: function () {
        CMS.Message.prompt(
            CMS.i18n('Titel eingeben', 'formtablist.add.title'),
            CMS.i18n('Bezeichnung', 'formtablist.add.message'),
            function (btnId, title, msgbox) {
                if (btnId == 'ok') {
                    var newRecord = new CMS.data.FormGroupRecord({
                        id: SB.util.UUID(),
                        name: title,
                        icon: '',
                        formGroupData: []
                    });
                    this.store.add(newRecord);
                    this.getSelectionModel().selectRecords([newRecord]);
                }
            },
            this,
            false,
            CMS.i18n('Neuer Reiter', 'formtablist.add.default')
        );
        this.updateButtons();
    },

    /**
     * Handler for "clone" button menu items
     * @private
     */
    cloneHandler: function (item) {
        var newRecord = new CMS.data.FormGroupRecord({
            id: SB.util.UUID(),
            name: item.text,
            icon: '',
            formGroupData: item.initialConfig.formGroupData
        });
        this.store.add(newRecord);
        this.getSelectionModel().selectRecords([newRecord]);
        this.updateButtons();
    },

    /**
     * Handler for "delete" button
     * @private
     */
    deleteHandler: function () {
        var record = this.getSelectionModel().getSelected();
        Ext.MessageBox.confirm(
            CMS.i18n('Löschen?', 'formtablist.delete.title'),
            CMS.i18n('Reiter "{name}" wirklich löschen?', 'formtablist.delete.message').replace(/{name}/g, CMS.translateInput(record.get('name'))),
            function (btnId) {
                if (btnId == 'yes') {
                    this.store.remove(record, true);
                    if (this.store.getCount()) {
                        this.getSelectionModel().selectFirstRow();
                    } else {
                        /**
                         * Fired when the last list entry is deleted
                         * @event
                         * @name clear
                         * @param {CMS.moduleEditor.FormTabList} cmp This component.
                         */
                        this.fireEvent('clear', this);
                    }
                }
            },
            this
        );
    },

    /**
     * Handler for "up" button
     * @private
     */
    upHandler: function () {
        this.moveSelectedRecord(-1);
    },

    /**
     * Handler for "up" button
     * @private
     */
    downHandler: function () {
        this.moveSelectedRecord(+1);
    },

    /**
     * @private
     */
    moveSelectedRecord: function (offset) {
        this.moving = true;
        var store = this.store;
        var sm = this.getSelectionModel();
        var record = sm.getSelected();
        var index = store.indexOf(record);
        store.removeAt(index);
        store.insert(index + offset, record);
        sm.selectRow(index + offset);
        this.moving = false;
    },

    /**
     * Calls the grid's selectionmodel's selectFirstRow method if the view is available
     */
    selectFirstRow: function () {
        var sm = this.getSelectionModel();
        if (sm.grid) {
            sm.selectFirstRow();
        }
    },

    /**
     * Calls the grid's selectionmodel's clearSelections method if the view is available
     */
    clearSelections: function () {
        var sm = this.getSelectionModel();
        if (sm.grid) {
            sm.clearSelections();
        }
    }
});

Ext.reg('CMSformtablist', CMS.moduleEditor.FormTabList);
