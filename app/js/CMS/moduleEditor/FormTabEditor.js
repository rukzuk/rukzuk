Ext.ns('CMS.moduleEditor');

/**
* @class CMS.moduleEditor.FormTabEditor
* @extends CMS.form.GeneratedFormPanel
* Component for managing the form fields of a form group
*/
CMS.moduleEditor.FormTabEditor = Ext.extend(CMS.form.GeneratedFormPanel, {
    title: '&nbsp;',
    autoScroll: true,
    cls: 'CMSformgroupeditor',
    labelAlign: 'top',
    showHiddens: true,

    /**
    * The component that currently has focus. <tt>null</tt> if none is focused.
    * @property focusedCmp
    * @type Ext.Component
    */
    focusedCmp: null,

    /**
    * @cfg {String} websiteId
    * Id of the current selected website
    */
    websiteId: '',

    initComponent: function () {
        this.bbar = {
            items: [{
                tooltip: {
                    text: CMS.i18n('Neu'),
                    align: 't-b?'
                },
                ref: '../addButton',
                iconCls: 'add addformfield',
                menu: {
                    cls: 'addformfieldmenu no-icon',
                    items: this.createFormMenuItems()
                },
                destroyMenu: true,
                repeat: false
            }, {
                tooltip: {
                    text: CMS.i18n('Kopieren'),
                    align: 't-b?'
                },
                ref: '../copyButton',
                iconCls: 'copy copyformfield',
                handler: this.copyHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: {
                    text: CMS.i18n('Ausschneiden'),
                    align: 't-b?'
                },
                ref: '../cutButton',
                iconCls: 'cut cutformfield',
                handler: this.cutHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: {
                    text: CMS.i18n('Einfügen', 'formtabeditor.pasteBtn'),
                    align: 't-b?'
                },
                ref: '../pasteButton',
                iconCls: 'paste pasteformfield',
                handler: this.pasteHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: {
                    text: CMS.i18n('Duplizieren'),
                    align: 't-b?'
                },
                ref: '../cloneButton',
                iconCls: 'clone cloneformfield',
                handler: this.cloneHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: {
                    text: CMS.i18n('Löschen'),
                    align: 't-b?'
                },
                ref: '../deleteButton',
                iconCls: 'delete deleteformfield',
                handler: this.deleteHandler,
                disabled: true,
                scope: this
            }, {
                tooltip: {
                    text: CMS.i18n('Nach oben'),
                    align: 't-b?'
                },
                ref: '../upButton',
                iconCls: 'up upformfield',
                disabled: true,
                handler: this.upHandler,
                scope: this
            }, {
                tooltip: {
                    text: CMS.i18n('Nach unten'),
                    align: 't-b?'
                },
                ref: '../downButton',
                iconCls: 'down downformfield',
                disabled: true,
                handler: this.downHandler,
                scope: this
            }]
        };
        CMS.moduleEditor.FormTabEditor.superclass.initComponent.apply(this, arguments);
        this.on('afterrender', function () {
            this.mon(this.body, 'click', function () { this.setFocus(null); }, this);
        }, this, { single: 'true' });
    }, // initComponent

    /**
    * @private
    * Generate menu items for form elements to insert
    */
    createFormMenuItems: function () {
        var result = [];
        var unsorted = [];
        var sorter = function (a, b) {
            return CMS.i18nTranslateMacroString(a.descr.text) < CMS.i18nTranslateMacroString(b.descr.text) ? -1 : 1;
        };
        Ext.each(CMS.config.formElements, function (elementCfg) {
            var newEl;
            if (!elementCfg.descr) {
                return;
            } else if (Ext.isObject(elementCfg.descr)) {
                newEl = SB.util.cloneObject(elementCfg.descr);
                var params = SB.util.cloneObject(elementCfg.params);
                if (elementCfg.descr.hasValue) {
                    // insert value field if missing
                    var hasValueField = false;
                    Ext.each(params, function (param) {
                        if (param.name == 'value') {
                            hasValueField = true;
                            return false;
                        }
                    });
                    if (!hasValueField) {
                        params.push({
                            name: 'value',
                            value: null,
                            xtype: null
                        });
                    }
                    // add variable reference property for actual form fields
                    params.unshift({
                        name: 'CMSvar',
                        value: ''
                    });
                }

                // translate form element name
                if (newEl.text) {
                    newEl.text = CMS.i18nTranslateMacroString(newEl.text);
                }

                Ext.apply(newEl, {
                    handler: this.addHandler,
                    scope: this,
                    descr: elementCfg.descr,
                    params: params
                });
                if (newEl) {
                    unsorted.push(newEl);
                }
            } else { // elementCfg.descr is a string
                unsorted.sort(sorter);
                result = result.concat(unsorted);
                unsorted = [];
                result.push(elementCfg.descr);
            }
        }, this);
        unsorted.sort(sorter);
        result = result.concat(unsorted);
        return result;
    },


    createWrapperItem: function (params, descr) {
        var result = CMS.moduleEditor.FormTabEditor.superclass.createWrapperItem.call(this, params, descr);
        // let the item know that it is in edit mode
        result.isInEditMode = true;
        var wrapped = result.items; // resolve wrapper
        // SBCMS-851 Don't allow references to MediaDB items from moduleEditor
        if (CMS.config.disalbedComponentsInFormEditor.indexOf(wrapped.xtype) != -1) {
            delete wrapped.value;
            wrapped.disabled = true;
        }
        return result;
    },


    /**
    * @private
    * Handler for click on the "clone" button
    */
    cloneHandler: function () {
        // console.log('[FormTabEditor] cloneHandler');
        var clone = this.cloneCmp(this.focusedCmp);
        var newItem = this.insertElement(clone, true);
        this.refreshForm();
        this.setFocus(newItem);
    },

    /**
    * @private
    * Handlers for click on any of the menu items
    */
    addHandler: function (menuItem, evt) {
        // console.log('[FormTabEditor] addHandler');
        var newItem = this.createWrapperItem(menuItem.params, menuItem.descr);
        this.insertElement(newItem);
    },

    cutHandler: function () {
        // console.log('[FormTabEditor] cutHandler');
        this.copyHandler.apply(this, arguments);
        this.silentDelete = true;
        this.deleteHandler.apply(this, arguments);
        this.silentDelete = false;
    },

    copyHandler: function () {
        // console.log('[FormTabEditor] copyHandler');
        CMS.app.clipboard.replace('formElement', this.cloneCmp(this.focusedCmp));
        CMS.Message.toast(CMS.i18n('Formularelement in Zwischenablage kopiert'));
        this.pasteButton.enable();
    },

    pasteHandler: function () {
        // console.log('[FormTabEditor] pasteHandler');
        var item = CMS.app.clipboard.item('formElement');
        var newItem = this.insertElement(item);
        CMS.app.clipboard.replace('formElement', this.cloneCmp(newItem));
    },

    /**
    * @private
    * Handler for click on the "delete" button
    */
    deleteHandler: function () {
        // console.log('[FormTabEditor] deleteHandler');
        var self = this;
        var go = function () {
            var container = self.focusedCmp.ownerCt;
            /**
            * @event beforedelete
            * Fired before a form field is removed
            * @param {Ext.Container} The (wrapper) component that will be removed
            */
            self.fireEvent('beforedelete', self.focusedCmp);
            container.remove(self.focusedCmp, true);
            self.refreshForm();
            self.fireEvent('paramformconfigchanged', self.getRecursiveParamFormConfig());
            self = null;
        };
        if (this.silentDelete) {
            go();
            return;
        }

        var title = this.focusedCmp.get(0).title || this.focusedCmp.get(0).fieldLabel;
        var fmtTitle = title ? (' "' + title + '"') : '';
        var msg = String.format(CMS.i18n('{0}{1} wirklich entfernen?', 'formTabEditor.deleteFormElementConfirm'), CMS.i18nTranslateMacroString(this.focusedCmp.descr.text), fmtTitle);
        Ext.MessageBox.confirm(CMS.i18n('Markiertes Formularelement löschen?', 'formTabEditor.deleteFormElementConfirmTitle'), msg, function (btnId) {
            if (btnId == 'yes') {
                go();
            }
        });
    },

    /**
    * @private
    * Handler for click on a child component
    */
    clickHandler: function (evt, cmp) {
        if (cmp == this.focusedCmp) {
            return;
        }
        this.setFocus(cmp);
    },

    /**
    * @private
    * Handler for click on the "up" button
    */
    upHandler: function () {
        this.moveSelectedItem(-1);
    },

    /**
    * @private
    * Handler for click on the "down" button
    */
    downHandler: function () {
        this.moveSelectedItem(+1);
    },

    /**
     * Get the configuration of the current form contents as a serialized object that can
     * be used to re-build this form
     * @return Object
     * @private
     */
    getRecursiveParamFormConfig: function (cmp) {
        cmp = cmp || this;
        var result = {};
        if (cmp != this) {
            result.params = SB.util.cloneObject(cmp.params);
            result.descr = SB.util.cloneObject(cmp.descr);
            Ext.each(result.params, function (param) { // remove layout and validation specific properties
                delete param.anchor;
                delete param.regex;
            });
        }
        var scanChildNodes;
        if (cmp == this) {
            scanChildNodes = true;
        } else {
            scanChildNodes = cmp.descr.allowChildNodes;
            cmp = cmp.get(0); // resolve wrappers
        }
        var childCmps = (scanChildNodes && cmp.items && cmp.items.getRange()) || [];
        if (childCmps.length || cmp == this) {
            result.items = [];
            for (var i = 0, l = childCmps.length; i < l; i++) {
                var child = childCmps[i];
                result.items.push(this.getRecursiveParamFormConfig(child));
            }
        }
        if (cmp == this) {
            return result.items;
        } else {
            return result;
        }
    },

    /**
     * Overrides the method in {@link CMS.form.GeneratedFormPanel#itemValueChangeHandler}
     * @param cmp
     * @param valueObj
     * @override
     */
    itemValueChangeHandler: function (cmp, valueObj) {
        CMS.moduleEditor.FormTabEditor.superclass.itemValueChangeHandler.apply(this, arguments);
        console.info('[FormTabEditor] itemValueChangeHandler', cmp, valueObj);
        // TODO: check if this event is needed
        this.fireEvent('paramformconfigchanged', this.getRecursiveParamFormConfig());
    },

    /**
    * @private
    * Move the selected item up or down
    * @param {Integer} offset Use positive values for moving down, negative values for moving up
    */
    moveSelectedItem: function (offset) {
        // console.log('[FormTabEditor] moveSelectedItem');
        var container = this.focusedCmp.ownerCt;
        var index = container.items.indexOf(this.focusedCmp);
        var clone = this.cloneCmp(this.focusedCmp);
        container.remove(this.focusedCmp, true);
        var newItem = container.insert(index + offset, clone);
        this.refreshForm();
        this.setFocus(newItem);
        this.fireEvent('paramformconfigchanged', this.getRecursiveParamFormConfig());
    },

    /**
    * Set the focus on a component
    * @param {Ext.Component} cmp The component to set focus on. SHOULD be a descendant component of <tt>this</tt>.
    */
    setFocus: function (cmp) {
        // console.log('[FormTabEditor] setFocus');
        if (this.focusedCmp && this.focusedCmp.el) {
            this.focusedCmp.el.removeClass('CMSfocus');
        }
        if (cmp && cmp.el) {
            cmp.el.addClass('CMSfocus');
            this.focusedCmp = cmp;
        } else {
            delete this.focusedCmp;
        }
        this.editCmp(cmp);
        this.updateButtons();
    },

    refreshForm: function () {
        // console.log('[FormTabEditor] refreshForm');
        CMS.moduleEditor.FormTabEditor.superclass.refreshForm.apply(this, arguments);
        if (this.currentComponents.indexOf(this.focusedCmp) == -1) {
            delete this.focusedCmp;
        }
        this.updateButtons();
    },


    /**
    * @private
    * Enable/disable buttons depending on the current form status
    */
    updateButtons: function () {
        // console.log('[FormTabEditor] updateButtons');
        this.cloneButton.setDisabled(!this.focusedCmp);
        this.deleteButton.setDisabled(!this.focusedCmp);
        this.copyButton.setDisabled(!this.focusedCmp);
        this.cutButton.setDisabled(!this.focusedCmp);
        if (!this.focusedCmp) {
            this.upButton.setDisabled(true);
            this.downButton.setDisabled(true);
            return;
        }
        var container = this.focusedCmp.ownerCt;
        if (!container) {
            delete this.focusedCmp;
            return;
        }
        this.upButton.setDisabled(container.get(0) == this.focusedCmp);
        this.downButton.setDisabled(container.items.last() == this.focusedCmp);
    },

    /**
    * Load a new form group into the editor.
    * @param {CMS.data.FormGroupRecord} record
    */
    loadRecord: function (record) {
        // console.log('[FormTabEditor] loadRecord');
        this.setTitle(CMS.i18n('Bedienoberfläche im Reiter „{name}“').replace('{name}', CMS.translateInput(record.get('name'))));
        var data = record.get('formGroupData');
        this.loadConfig(data);
    },

    /**
    * Open the property editor for the specified component
    * @param {Ext.Component} cmp The component that will be edited. MUST be a descendant component of <tt>this</tt>.
    */
    editCmp: function (cmp) {
        // console.log('[FormTabEditor] editCmp');
        this.fireEvent('itemfocus', cmp, this.paramChangeHandler.createDelegate(this));
    },

    expandHandler: function (cmp) {
        if (this.focusedCmp != cmp) {
            this.setFocus(cmp);
        }
    },

    collapseHandler: function (cmp) {
        if (this.focusedCmp != cmp) {
            this.setFocus(cmp);
        }
    },

    /**
    * @private
    * Called when editing of a single form element's properties is completed
    */
    paramChangeHandler: function (cmp, status, params) {
        // console.log('[FormTabEditor] paramChangeHandler');
        var oldParams = cmp.params;
        cmp.params = params;
        Ext.each(cmp.params, function (param) {
            if (param.name == 'value') {
                param.value = cmp.get(0).getValue();
                return false;
            }
        });
        var container = cmp.ownerCt;
        var index = container.items.indexOf(cmp);
        var clone = this.cloneCmp(cmp);
        var newItem = container.insert(index + 1, clone);
        container.remove(cmp, true);
        /**
        * @event paramschanged
        * Fired when the parameters of a form field are changed.
        * @param {Object} oldParams The parameters before the change
        * @param {Object} params The parameters after the change
        */
        this.fireEvent('paramschanged', oldParams, params);
        this.refreshForm();
        this.setFocus(newItem);
        /**
        * @event paramformconfigchanged
        * Fired when the form group's configuration has been changed
        * @param {String} config The new form group config
        */
        this.fireEvent('paramformconfigchanged', this.getRecursiveParamFormConfig());
    },

    /**
    * @private
    * Convenience method called by addHandler and pasteHandler
    * @return Ext.Container
    */
    insertElement: function (newItem, forceSibling) {
        // console.log('[FormTabEditor] insertElement');
        var insertionParent = this;
        var insertionIndex;
        var newCmp;
        // find insertion parent
        if (this.focusedCmp) {
            if (!forceSibling && this.focusedCmp.initialConfig.descr.allowChildNodes) {
                insertionParent = this.focusedCmp;
            } else {
                insertionParent = this.focusedCmp.findParentBy(function (parent) {
                    return parent.initialConfig.descr && parent.initialConfig.descr.allowChildNodes;
                });
            }
            insertionParent = insertionParent || this;
        }
        // resolve wrapper
        if (insertionParent != this) {
            insertionParent = insertionParent.get(0);
        }
        if (insertionParent.collapsed) {
            insertionParent.expand();
        }
        // find insertion index
        if (this.focusedCmp) {
            insertionIndex = insertionParent.items.indexOf(this.focusedCmp);
        }
        // insert
        if (typeof insertionIndex != 'undefined' && insertionIndex > -1) {
            newCmp = insertionParent.insert(insertionIndex + 1, newItem);
        } else {
            newCmp = insertionParent.add(newItem);
        }
        this.refreshForm();
        this.setFocus(newCmp);
        this.fireEvent('paramformconfigchanged', this.getRecursiveParamFormConfig());

        /**
        * @event insert
        * Fired when a new form field is inserted
        * @param {Object} cmp (Wrapper) component that was inserted
        */
        this.fireEvent('insert', newCmp);

        return newCmp;
    }
});

Ext.reg('CMSformtabeditor', CMS.moduleEditor.FormTabEditor);
