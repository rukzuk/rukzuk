Ext.ns('CMS.richTextEditor');

/**
 * @class CMS.richTextEditor.RichTextEditorToolbar
 * @extends Ext.Panel
 */
CMS.richTextEditor.RichTextEditorToolbar = Ext.extend(Ext.Panel, {
    border: false,

    isMouseOver: false, //track if mouse is currently over this panel

    initComponent: function () {
        this.items = [
            this.generateLine()
        ];


        CMS.richTextEditor.RichTextEditorToolbar.superclass.initComponent.apply(this, arguments);

        this.on('afterrender', function () {
            var element = this.getEl();
            this.mon(element, 'mouseover', this.handleMouseOver.createDelegate(this));
            this.mon(element, 'mouseout', this.handleMouseOut.createDelegate(this));
        }, this, {single: true});

    },

    /**
     * Helper to create a toolbar line without items
     * @private
     */
    generateGenericLine: function () {
        return {
            xtype: 'toolbar',
            height: '24',
            cls: 'CMSrichtexteditortoolbar',
            defaults: {
                text: '&nbsp;',
                tooltipType: 'ext:qtip',
                xtype: 'button',
                height: '24',
                handler: this.handleItemClick,
                scope: this
            }
        };
    },

    /**
     * Helper to create first toolbar line
     * @private
     */
    generateLine: function () {
        var result = this.generateGenericLine();

        result.items = [{
            xtype: 'combo',
            itemId: 'style',
            disabled: true,
            editable: false,
            emptyText: CMS.i18n('Standardformat'),
            width: 120,
            triggerAction: 'all',
            lazyRender: true,
            mode: 'local',
            store: new Ext.data.ArrayStore({
                fields: ['myId', 'displayText']
            }),
            valueField: 'myId',
            displayField: 'displayText',
            listeners: {
                select: function (combo, record) {
                    this.scope.fireEvent('style', combo.value);
                }
            },
            scope: this
        }, ' ', '-', {
            iconCls: 'undo',
            tooltip: {
                text: CMS.i18n('Rückgängig (Strg+Z)')
            },
            itemId: 'undo',
            disabled: true,
            event: 'undo'
        }, {
            iconCls: 'redo',
            tooltip: {
                text: CMS.i18n('Wiederholen (Strg+Y)')
            },
            itemId: 'redo',
            disabled: true,
            event: 'redo'
        }, '-', {
            iconCls: 'charmap',
            tooltip: {
                text: CMS.i18n('Sonderzeichen einfügen')
            },
            itemId: 'charmap',
            disabled: true,
            menu: new CMS.richTextEditor.InsertCharacterMenu({
                handler: function (menu, e, character) {
                    this.fireEvent('charmap', character);
                },
                scope: this
            })
        }, {
            iconCls: 'bold',
            tooltip: {
                text: CMS.i18n('Fett (Strg+B)')
            },
            itemId: 'bold',
            disabled: true,
            event: 'bold'
        }, {
            iconCls: 'italic',
            tooltip: {
                text: CMS.i18n('Kursiv (Strg+I)')
            },
            itemId: 'italic',
            disabled: true,
            event: 'italic'
        }, {
            iconCls: 'underline',
            tooltip: {
                text: CMS.i18n('Unterstrichen (Strg+U)')
            },
            itemId: 'underline',
            disabled: true,
            event: 'underline'
        }, {
            iconCls: 'strikethrough',
            tooltip: {
                text: CMS.i18n('Durchgestrichen')
            },
            itemId: 'strikethrough',
            disabled: true,
            event: 'strikethrough'
        }, {
            iconCls: 'subscript',
            tooltip: {
                text: CMS.i18n('Tiefgestellt')
            },
            itemId: 'subscript',
            disabled: true,
            event: 'subscript'
        }, {
            iconCls: 'superscript',
            tooltip: {
                text: CMS.i18n('Hochgestellt')
            },
            itemId: 'superscript',
            disabled: true,
            event: 'superscript'
        }, '-', {
            iconCls: 'bullist',
            tooltip: {
                text: CMS.i18n('Liste')
            },
            itemId: 'bullist',
            disabled: true,
            event: 'bullist'
        }, {
            iconCls: 'numlist',
            tooltip: {
                text: CMS.i18n('Nummerierte Liste')
            },
            itemId: 'numlist',
            disabled: true,
            event: 'numlist'
        }, {
            iconCls: 'link',
            tooltip: {
                text: CMS.i18n('Link einfügen')
            },
            itemId: 'link',
            disabled: true,
            event: 'link'
        }, {
            iconCls: 'unlink',
            tooltip: {
                text: CMS.i18n('Link entfernen')
            },
            itemId: 'unlink',
            disabled: true,
            event: 'unlink'
        }, '-', {
            iconCls: 'tableMenu',
            tooltip: {
                text: CMS.i18n('Tabelle einfügen/bearbeiten')
            },
            itemId: 'tableMenu',
            disabled: true,
            menu: {
                cls: 'CMStablemenu',
                defaults: {
                    xtype: 'menuitem',
                    handler: this.handleItemClick,
                    scope: this
                },
                items: [{
                    iconCls: 'tableInsert',
                    text: CMS.i18n('Tabelle einfügen'),
                    itemId: 'tableInsert',
                    disabled: false,
                    hideOnClick: false,
                    menu: new CMS.richTextEditor.InsertTableMenu({
                        handler: function (menu, e, col, row) {
                            this.fireEvent('tableInsert', col, row);
                        },
                        scope: this
                    })
                }, {
                    iconCls: 'tableDelete',
                    text: CMS.i18n('Tabelle löschen'),
                    itemId: 'tableDelete',
                    disabled: true,
                    event: 'tableDelete'
                }, '-', {
                    iconCls: 'tableInsertColAfter',
                    text: CMS.i18n('Spalte danach einfügen'),
                    itemId: 'tableInsertColAfter',
                    disabled: true,
                    event: 'tableInsertColAfter'
                }, {
                    iconCls: 'tableInsertColBefore',
                    text: CMS.i18n('Spalte davor einfügen'),
                    itemId: 'tableInsertColBefore',
                    disabled: true,
                    event: 'tableInsertColBefore'
                }, {
                    iconCls: 'tableDeleteCol',
                    text: CMS.i18n('Spalte löschen'),
                    itemId: 'tableDeleteCol',
                    disabled: true,
                    event: 'tableDeleteCol'
                },  '-', {
                    iconCls: 'tableInsertRowAfter',
                    text: CMS.i18n('Zeile danach einfügen'),
                    itemId: 'tableInsertRowAfter',
                    disabled: true,
                    event: 'tableInsertRowAfter'
                }, {
                    iconCls: 'tableInsertRowBefore',
                    text: CMS.i18n('Zeile davor einfügen'),
                    itemId: 'tableInsertRowBefore',
                    disabled: true,
                    event: 'tableInsertRowBefore'
                }, {
                    iconCls: 'tableDeleteRow',
                    text: CMS.i18n('Zeile löschen'),
                    itemId: 'tableDeleteRow',
                    disabled: true,
                    event: 'tableDeleteRow'
                }]
            }
        }];
        return result;
    },

    /**
     * The default click handler for a menu item
     * @private
     */
    handleItemClick: function (item) {
        if (item.event) {
            this.fireEvent(item.event);
        }
    },

    /**
     * Handle when mouse moves over toolbar
     * @private
     */
    handleMouseOver: function () {
        this.isMouseOver = true;
    },

    /**
     * Handle when mouse leaves the toolbar
     * @private
     */
    handleMouseOut: function () {
        this.isMouseOver = false;
    },

    /**
     * Show or hide buttons, depending on the section's editor configuration
     * @function
     * @param {Array} enabledControls all enabled controls
     * @param {Array} customStyles all available styles
     */
    syncControls: (function () {
        var enabledControls;
        var customStyles;

        // helper method to iterate through all items of a container
        var processItemContainer = function (container) {
            //console.log('[RTET] processItemContainer', container);
            Ext.each(container.items, processItem, this);
        };

        // helper method to enable/disable a single toolbar button, menu item, ...
        var processItem = function (item) {
            if (!item.isXType('button') && !item.isXType('combo') && !item.isXType('menuitem')) {
                // don't process separators and spacers...
                return;
            }

            var itemId = item.getItemId();
            var isAvailable = enabledControls.indexOf(itemId) != -1;
            // console.log('[RTET] processItem', itemId, isAvailable);

            if (isAvailable) {
                item.enable();

                if (item.menu) {
                    // item is a container itself (e.g. a menu button, sub menu, ...)
                    // -> step into recursively
                    processItemContainer.call(this, item.menu);
                }
            } else {
                item.disable();
            }

            if (item.isXType('button')) {
                item.toggle(false, true);
            }

            if (itemId == 'style') {
                if (isAvailable) {
                    var data = [['', CMS.i18n('Standardformat')]];
                    Ext.each(customStyles, function (oneStyle) {
                        data.push(oneStyle);
                    });
                    // SBCMS-296: Workaround to prevent combobox from re-expanding after loading store.
                    // TODO: Fix focus issue with TinyMCE to make this obsolete.
                    item.hasFocus = false;

                    item.store.loadData(data);
                } else {
                    item.store.removeAll();
                    item.reset();
                }
            }
        };

        return function (enabledControlsParam, customStylesParam) {
            console.log('[RTET] syncControls', enabledControls, customStyles);

            enabledControls = enabledControlsParam;
            customStyles = customStylesParam;

            Ext.each(this.items, processItemContainer, this);
            this.doLayout();
        };

    }()),

    /**
     * Checks if the panel is currently being used (mouse is over the panel)
     */
    isActive: function () {
        return this.isMouseOver;
    }

});

Ext.reg('CMSrichtexteditortoolbar', CMS.richTextEditor.RichTextEditorToolbar);
