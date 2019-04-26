Ext.ns('CMS.liveView');

/**
 * Window for inserting a new Unit
 * @class CMS.liveView.InsertUnitWindow
 * @extends Ext.Window
 */
CMS.liveView.InsertUnitWindow = Ext.extend(CMS.MainWindow, {
    /** @lends CMS.liveView.InsertUnitWindow.prototype */

    bubbleEvents: ['CMSinsertunit'],
    layout: 'fit',
    modal: true,
    cls: 'CMSinsertwindow CMSinsertunitwindow',
   	maxWidth: 940,
   	maxHeight: 730,
    border: false,

    /**
     * One of 'page'/'template'. Defaults to 'page'
     *
     * @type {String}
     * @property mode
     */
    mode: 'page',

    /**
     * The unit that this window acts upon. Use {@link #setOwnerUnit} to set after initialization
     *
     * @type {CMS.data.UnitRecord}
     * @property ownerUnit
     */
    ownerUnit: null,

    /**
     * Default choice for insertion position.
     * Possible values:
     * <ul>
     *   <li>-1 - above</li>
     *   <li> 0 - inside (default)</li>
     *   <li> 1 - below</li>
     * </ul>
     * If the unit cannot be inserted at the given position, this parameter is ignored
     *
     * @type {Number}
     * @property position
     */
    position: 0,

    initComponent: function () {
        this.title = this.mode === 'page' ? CMS.i18n(null, 'insertunitwindow.title.page') : CMS.i18n(null, 'insertunitwindow.title.tpl');

        this.ghostSiblingStore = new CMS.data.UnitStore();
        this.ghostChildrenStore = new CMS.data.UnitStore();

        this.radioAbove = new Ext.form.Radio({
            boxLabel: CMS.i18n('Oberhalb'),
            name: 'position',
            ctCls: 'above',
            inputValue: 'above'
        });
        this.radioInside = new Ext.form.Radio({
            boxLabel: CMS.i18n('Innerhalb'),
            name: 'position',
            ctCls: 'inside',
            inputValue: 'inside'
        });
        this.radioBelow = new Ext.form.Radio({
            boxLabel: CMS.i18n('Unterhalb'),
            name: 'position',
            ctCls: 'below',
            inputValue: 'below'
        });

        this.items = {
            xtype: 'container',
            layout: 'hbox',
            layoutConfig: {
                align: 'stretch'
            },
            border: false,
            padding: 10,
            items: [{
                xtype: 'form',
                cls: 'CMSsidebar',
                layout: 'fit',
                items: {
                    xtype: 'radiogroup',
                    cls: 'radiogroup',
                    value: 'below',
                    listeners: {
                        change: this.bindStores,
                        scope: this
                    },
                    ref: '../../radioGroup',
                    width: 280,
                    columns: 1,
                    items: [this.radioInside, this.radioBelow, this.radioAbove]
                }
            }, {
                xtype: 'dataview',
                ref: '../unitSelection',
                store: this.ghostChildrenStore,
                cls: 'CMSunitselection',
                overClass: 'x-view-over',
                itemSelector: 'div.CMSunit',
                flex: 1,
                singleSelect: true,
                tpl: [
                    '<tpl for=".">',
                    '<div class="CMSunit">',
                    '<span class="CMSname">{name}</span><br>',
                    '<span class="CMSdescription">{description}</span>',
                    '</div>',
                    '</tpl>'
                ],
                listeners: {
                    selectionchange: function (cmp, selections) {
                        this.okButton.setDisabled(selections.length === 0);
                    },
                    dblclick: function () {
                        this.insertUnit();
                    },
                    scope: this
                },
                prepareData: (function (data) {
                    // prepare store items for view (get fallbacks, translate names, ...)
                    // WARNING: make sure not to modify the original data object or the
                    //          changes will effect the data store too!
                    return Ext.applyIf({
                        name: this.getUnitUIName(data)
                    }, data);
                }).createDelegate(this)
            }]
        };

        this.buttons = [{
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            handler: this.destroy,
            scope: this
        }, {
            text: CMS.i18n('Einfügen', 'insertunitwindow.insertBtn'),
            iconCls: 'ok',
            cls: 'primary',
            ref: '../okButton',
            disabled: true,
            handler: this.insertUnit,
            scope: this
        }];

        this.on('afterrender', function () {
            this.setOwnerUnit(this.ownerUnit);
        }, this, { single: true });

        CMS.liveView.InsertUnitWindow.superclass.initComponent.apply(this, arguments);
    },

    /**
     * handler for the insert button
     * @private
     */
    insertUnit: function () {
        var unit = this.unitSelection.getSelectedRecords()[0];
        if (unit) {
            var pos = this.radioGroup.getValue().inputValue;
            var cfg = CMS.liveView.InsertUnitHelper.getInstance().getInsertObject(unit, pos, this.ownerUnit);
            this.fireEvent('CMSinsertunit', cfg);
            this.destroy();
        }
    },

    /**
     * Set {@link #ownerUnit}
     */
    setOwnerUnit: function (unit) {
        if (!unit) {
            throw '[InsertUnitWindow] no ownerUnit set';
        }
        this.ownerUnit = unit;
        this.processSiblings(unit);
        this.processChildren(unit);
        this.bindStores();

        switch (this.position) {
        case -1:
            if (!this.radioAbove.disabled) {
                this.radioGroup.setValue('above');
            }
            break;
        case 0:
            if (!this.radioInside.disabled) {
                this.radioGroup.setValue('inside');
            }
            break;
        case 1:
            if (!this.radioBelow.disabled) {
                this.radioGroup.setValue('below');
            }
            break;
        default:
            this.console.warn('[InsertUnitWindow] Invalid insertion position ', this.position);
            break;
        }
    },

    /**
     * Returns an UI ready name of a unit;
     * This method will work even if unit.getUIName fails because they are not in a unit store an
     * do not know the websiteId
     *
     * @param {Object} data The unit's record data object
     * @return {String} the UI-ready (i.e. translated) name
     * @private
     */
    getUnitUIName: function (data) {
        var name = data.name;
        if (!name && data.moduleId) {
            // get module name if unit name is empty
            var websiteId = this.ownerUnit && this.ownerUnit.store && this.ownerUnit.store.websiteId;
            var moduleStore = websiteId && CMS.data.StoreManager.get('module', websiteId);
            var module = moduleStore && moduleStore.getById(data.moduleId);
            name = module && module.get('name');
        }
        return CMS.translateInput(name);
    },

    /**
     * Comperator method to sort units by their UI name
     * @private
     */
    compareUnitNames: function (u1, u2) {
        var name1 = String(this.getUnitUIName(u1.data)).toLowerCase();
        var name2 = String(this.getUnitUIName(u2.data)).toLowerCase();

        if (name1 < name2) {
            return -1;
        } else {
            return 1;
        }
    },

    /**
     * Determine which siblings can be inserted
     * @private
     */
    processSiblings: function () {
        var siblingParent = this.ownerUnit.store.getParentUnit(this.ownerUnit);
        var insertableSiblings = CMS.liveView.InsertUnitHelper.getInstance().getInsertableSiblings(this.ownerUnit, this.mode, true);
        if (siblingParent && insertableSiblings && insertableSiblings.length) {
            insertableSiblings.sort(this.compareUnitNames.createDelegate(this));
            this.radioAbove.enable();
            this.radioBelow.enable();
            Ext.each(siblingParent.get('children'), function (child, index) {
                if (child.id === this.ownerUnit.id) {
                    this.siblingIndex = index;
                }
            }, this);
            this.ghostSiblingStore.removeAll();
            this.ghostSiblingStore.add(insertableSiblings);
        } else {
            this.radioAbove.disable();
            this.radioBelow.disable();
            this.radioGroup.setValue('inside');
            siblingParent = null;
            this.ghostSiblingStore.removeAll();
        }
        this.siblingParent = siblingParent;
    },

    /**
     * Determine which children can be inserted
     * @private
     */
    processChildren: function () {
        var insertableChildren = CMS.liveView.InsertUnitHelper.getInstance().getInsertableChildren(this.ownerUnit, this.mode, true);
        if (insertableChildren && insertableChildren.length) {
            insertableChildren.sort(this.compareUnitNames.createDelegate(this));
            this.radioInside.enable();
            this.ghostChildrenStore.removeAll();
            this.ghostChildrenStore.add(insertableChildren);
        } else {
            this.radioInside.disable();
            if (this.radioGroup.getValue().inputValue === 'inside') {
                this.radioGroup.setValue('below');
            }
            this.ghostChildrenStore.removeAll();
        }
    },

    /**
     * bind the combobox's store according to where the ghost children should be inserted
     * @private
     */
    bindStores: function (group, item) {
        group = group || this.radioGroup;
        item = item || group.getValue();
        if (item === this.radioInside) {
            this.unitSelection.bindStore(this.ghostChildrenStore);
        } else {
            this.unitSelection.bindStore(this.ghostSiblingStore);
        }
    },

    beforeDestroy: function () {
        Ext.Window.prototype.beforeDestroy.apply(this, arguments);
        this.ghostChildrenStore.destroy();
        this.ghostSiblingStore.destroy();
    }
});
