Ext.ns('CMS.richTextEditor');

/**
* @class CMS.richTextEditor.RichTextEditorConfigField
* @extends Ext.Container
* Advanced configuration form for RichTextEditor. Can be used as a form field.
*/

CMS.richTextEditor.RichTextEditorConfigField = Ext.extend(Ext.Container, {

    initComponent: function () {
        var formatsCm = new Ext.grid.ColumnModel({
            columns: [{
                id: 'label',
                header: CMS.i18n('Label'),
                dataIndex: 'label',
                editor: new Ext.form.TextField({
                    allowBlank: false
                })
            }, {
                header: CMS.i18n('Element'),
                dataIndex: 'element',
                width: 60,
                editor: new Ext.form.ComboBox({
                    editable: false,
                    triggerAction: 'all',
                    lazyRender: true,
                    store: [['span', 'span'], ['p', 'p'], ['div', 'div'], ['h1', 'h1'], ['h2', 'h2'], ['h3', 'h3'], ['h4', 'h4'], ['h5', 'h5'], ['h6', 'h6']],
                    listClass: 'x-combo-list-small'
                })
            }, {
                id: 'classes',
                header: CMS.i18n('CSS-Klassen'),
                dataIndex: 'classes',
                // TODO: CSS class name validation
                editor: new Ext.form.TextField()
            }, {
                id: 'delete',
                dataIndex: '',
                header: '&#160;',
                renderer: function (value, meta, record, rowIndex, colIndex, store) {
                    meta.attr = 'ext:qtip="' + CMS.i18n('Format löschen') + '"';
                    return '<img class="delete" src="' + Ext.BLANK_IMAGE_URL + '" width="16">';
                },
                resizable: false,
                width: 26
            }]

        });

        // create the Data Store
        var formatsStore = new Ext.data.JsonStore({
            autoDestroy: true,
            root: 'customStyles',
            fields: ['label', 'element', 'classes'],
            listeners: {
                'update': function () {
                    this.fireEvent('change');
                },
                'remove': function () {
                    this.fireEvent('change');
                },
                'add': function () {
                    this.fireEvent('change');
                },
                scope: this
            }
        });

        this.items = [{
            xtype: 'fieldset',
            labelAlign: 'top',
            title: CMS.i18n('Rich Text Editor Konfiguration'),
            autoHeight: true,
            defaults: {
                listeners: {
                    'change': function () {
                        this.fireEvent('change');
                    },
                    scope: this
                }
            },
            items: [{
                xtype: 'checkboxgroup',
                fieldLabel: CMS.i18n('Standard-Formatierung'),
                columns: 3,
                items: [{
                    boxLabel: CMS.i18n('Fett'),
                    name: 'bold'
                }, {
                    boxLabel: CMS.i18n('Kursiv'),
                    name: 'italic'
                }, {
                    boxLabel: CMS.i18n('Unterstrichen'),
                    name: 'underline'
                }, {
                    boxLabel: CMS.i18n('Durchgestrichen'),
                    name: 'strikethrough'
                }, {
                    boxLabel: CMS.i18n('Tiefgestellt'),
                    name: 'subscript'
                }, {
                    boxLabel: CMS.i18n('Hochgestellt'),
                    name: 'superscript'
                }]
            }, {
                xtype: 'checkboxgroup',
                fieldLabel: CMS.i18n('Erlaubte Elemente'),
                columns: 3,
                items: [{
                    boxLabel: CMS.i18n('Liste'),
                    name: 'bullist'
                }, {
                    boxLabel: CMS.i18n('Nummerierte Liste'),
                    name: 'numlist'
                }, {
                    boxLabel: CMS.i18n('Tabelle'),
                    name: 'table'
                }, {
                    boxLabel: CMS.i18n('Link'),
                    name: 'link',
                    handler: function (cb, checked) {
                        if (checked) {
                            this.linkTargetsTextArea.enable();
                        } else {
                            this.linkTargetsTextArea.disable();
                        }
                    },
                    scope: this
                }]
            }, {
                xtype: 'CMSkeyvaluetextarea',
                fieldLabel: CMS.i18n('Link-Ziele (target)'),
                name: 'linkTargets',
                ref: '../linkTargetsTextArea',
                array: true,
                width: '100%',
                disabled: true,
                emptyText: 'WERT:TEXT\nWERT:TEXT\n…'
            }, {
                xtype: 'combo',
                fieldLabel: CMS.i18n('Enter-Taste'),
                name: 'enterKey',
                lazyRender: true,
                editable: false,
                triggerAction: 'all',
                store: [['none', CMS.i18n('deaktiviert')], ['linebreak', CMS.i18n('Zeilenumbruch (BR)')], ['paragraph', CMS.i18n('Absatz (P)')]]
            }, {
                xtype: 'editorgrid',
                fieldLabel: CMS.i18n('Benutzerdefinierte Formate'),
                store: formatsStore,
                cm: formatsCm,
                height: 150,
                autoExpandColumn: 'classes', // column with this id will be expanded
                clicksToEdit: 1,
                enableHdMenu: false,
                bbar: [{
                    text: CMS.i18n('Neues Format'),
                    handler: function () {
                        var grid = this.findParentByType('editorgrid');
                        var store = grid.getStore();
                        var Format = store.recordType;
                        var f = new Format({
                            label: CMS.i18n('Neues Format'),
                            element: 'p',
                            classes: ''
                        });
                        grid.stopEditing();
                        store.add(f);
                        grid.startEditing(store.indexOf(f), 0);
                    }
                }],
                listeners: {
                    cellclick: function (grid, rowIndex, colIndex, evt) {
                        var deleteIndex = this.getColumnModel().getIndexById('delete');

                        if (colIndex == deleteIndex) {
                            var record = this.store.getAt(rowIndex);
                            this.store.remove(record);
                        }
                    }
                }
            }]
        }];

        this.on('afterlayout', this.renderValue, this);

        CMS.richTextEditor.RichTextEditorConfigField.superclass.initComponent.apply(this, arguments);

        this.getRawValue = this.getValue;
        this.setRawValue = this.setValue;

        if (this.value) {
            this.setValue(this.value);
        }
    },

    setValue: function (value) {
        var oldVal = this.value;
        this.value = value;

        if (oldVal != this.value) { // TODO: deep compare
            this.fireEvent('change', this, this.value, oldVal);
        }
    },

    getValue: function () {
        var c = {};

        //interate over all checkboxgroups to get the single checkboxes (this.findByType('checkbox') doesn't work)
        var checkboxgroups = this.findByType('checkboxgroup');
        Ext.each(checkboxgroups, function (cbg) {
            Ext.each(cbg.items, function (cb) {
                if (cb.isXType('checkbox') && cb.getValue()) {
                    c[cb.getName()] = cb.getValue();
                }
            });
        });

        var comboboxes = this.findByType('combo');
        Ext.each(comboboxes, function (cb) {
            c[cb.getName()] = cb.getValue();
        });

        var customStylesStore = this.findByType('editorgrid')[0].getStore();
        if (customStylesStore.getCount() > 0) {
            c.customStyles = [];
            customStylesStore.each(function (r) {
                c.customStyles.push(r.data);
            }, this);
        }

        var textareas = this.findByType('CMSkeyvaluetextarea');
        Ext.each(textareas, function (f) {
            c[f.getName()] = f.getValue();
        });

        this.value = c;

        return this.value;
    },

    /**
    * @private
    */
    renderValue: function () {
        var c = this.value;

        //interate over all checkboxgroups to get the single checkboxes (this.findByType('checkbox') doesn't work)
        var checkboxgroups = this.findByType('checkboxgroup');
        Ext.each(checkboxgroups, function (cbg) {
            Ext.each(cbg.items, function (cb) {
                if (cb.isXType('checkbox')) {
                    cb.suspendEvents(); //this is needed, otherwise the change event will get fired
                    cb.setValue(c[cb.getName()]);
                    cb.resumeEvents();
                }
            });
        });

        var comboboxes = this.findByType('combo');
        Ext.each(comboboxes, function (cb) {
            cb.setValue(c[cb.getName()]);
        });

        if (c.customStyles && !SB.util.isEmptyObject(c.customStyles)) {
            var customStyles = this.findByType('editorgrid')[0];
            customStyles.getStore().loadData({
                customStyles: c.customStyles
            });
        }

        var textareas = this.findByType('CMSkeyvaluetextarea');
        Ext.each(textareas, function (f) {
            var value = c[f.getName()];

            if (value) {
                f.setValue(value);
            } else {
                f.setValue([
                    ['', CMS.i18n('Gleiches Fenster')],
                    ['_blank', CMS.i18n('Neues Fenster')]
                ]);
            }
        });
    }
});

Ext.reg('CMSrichtexteditorconfiguration', CMS.richTextEditor.RichTextEditorConfigField);
