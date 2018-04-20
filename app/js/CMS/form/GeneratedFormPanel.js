Ext.ns('CMS.form');

/**
 * A form panel for displaying a user-designed form
 *
 * @class CMS.form.GeneratedFormPanel
 * @extends Ext.Panel
 * @requires SB.util.cloneObject
 */
CMS.form.GeneratedFormPanel = Ext.extend(Ext.Panel, {
    /** @lends CMS.form.GeneratedFormPanel.prototype */

    layout: 'anchor',
    bodyStyle: { padding: '5px' },
    autoScroll: true,
    cls: 'CMSgeneratedformpanel',

    /**
    * @cfg {Array} cfg
    * Configuration data for the form panel.
    * See {@link CMS.config#formElements} for a description
    */
    cfg: null,

    /**
    * @cfg {Ext.data.Record} (optional)
    * A record containing default values.
    */
    record: null,

    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {Boolean} showHiddens
    * If <tt>true</tt>, hidden input fields will be rendered as textfields
    */
    showHiddens: false,

    constructor: function () {
        CMS.form.GeneratedFormPanel.superclass.constructor.apply(this, arguments);
        this.formItemCls = Ext.id(null, '' + (+new Date()));
        // reserve space for the scroll bar
        this.defaults = { anchor: '-' + Ext.getScrollBarWidth() };
        /** @type CMS.form.FormFieldWrapperResponsive[] */
        this.currentComponents = [];
        if (this.cfg) {
            this.loadConfig(this.cfg);
            if (this.record) {
                // we need this after Component.prototype.constructor, since that's where plugins are initialized
                // and we want the createwrapper event to be captured by plugins
                this.loadRecord(this.record);
            }
        }
    },

    /** @private */
    reportConvertionErrors: function (errors, paramArray) {
        Ext.each(errors, function (error) {
            if (error.type === 'unknown') {
                var msg = CMS.i18n('Ungültiges Feld:\n----------------\n{source}');
                msg = msg.replace('{source}', JSON.stringify(paramArray, undefined, 2));
                CMS.Message.error(CMS.i18n('Ungültiges Modul'), CMS.i18n('Nicht kompatibel mit aktueller CMS-Version'));
                CMS.app.ErrorManager.push(msg);
            }
        });
    },

    /**
     * wrap form elements in a container because FormLayout messes around with items
     * @private
     */
    createWrapperItem: (function () {
        // helper function to translate the texts of a set of options
        var translateOption = function (option) {
            if (!Ext.isArray(option)) {
                return;
            }
            var result = [];
            for (var i = 0; i < option.length; i++) {
                result[i] = CMS.translateInput(option[i]);
            }
            return result;
        };

        var getClonedArray = function (convertedObject) {
            var clonedArray = convertedObject.parameters;

            // generate var field
            if (clonedArray[0] && clonedArray[0].name === 'CMSvar') {
                Ext.apply(clonedArray[0], {
                    xtype: 'textfield'
                }, CMS.config.validation.CMSvar);
                Ext.applyIf(clonedArray[0], {
                    emptyText: '__i18n_generatedFormPanel.varNameEmptyText',
                    fieldLabel: '__i18n_generatedFormPanel.varNameLabel'
                });
            }
            return clonedArray;
        };

        return function (paramArray, descr) {
            var convertedObject = CMS.form.LegacyComponentConverter.convert(paramArray, descr);
            if (convertedObject.errors) {
                this.reportConvertionErrors(convertedObject.errors, paramArray);
            }

            var clonedArray = getClonedArray(convertedObject);
            var innerCfg = {};

            // convert config params to actual form element config format
            Ext.each(clonedArray, function (param) {
                var key = param.name;
                var value = param.value;

                switch (key) {
                case 'xtype':
                    if (value === 'hidden' && this.showHiddens) {
                        value = 'textfield';
                    }
                    break;

                case 'options':
                case 'unitList':
                    // arrays for combo-boxes with the following form
                    // [['value-1', 'Text for Value 1'], ['value-2', ...], ...]
                    // The text is translatable
                    if (Ext.isArray(value)) {
                        var orgOptions = value;
                        var cloneOptions = [];

                        for (var i = 0; i < orgOptions.length; i++) {
                            cloneOptions[i] = translateOption(orgOptions[i]);
                        }
                        value = cloneOptions;
                    }
                    break;


                default:
                    if (Ext.isString(value)) {
                        try {
                            value = CMS.translateInput(JSON.parse(value));
                        } catch (e) {
                            value = CMS.i18nTranslateMacroString(value);
                        }
                    }
                }

                innerCfg[key] = value;
            }, this);
            var wrapperCls = innerCfg.xtype ? innerCfg.xtype + '-wrapper' : null;

            // remove the element
            if (innerCfg.remove || innerCfg.remove === true) {
                return {};
            }

            // HACK SBCMS-143 SBCMS-139 SBCMS-174
            innerCfg.websiteId = this.websiteId;
            innerCfg.idSuffix = this.idSuffix;

            // needed for SBCMS-704
            if (this.record) {
                innerCfg.recordId = this.record.id;
            }

            // hide the label, but enforce top position (even if its bottom/left ...)
            innerCfg.hideLabel = innerCfg.hideLabel || innerCfg.labelAlign === 'hide';

            // different wrapper classes for responsive form elements
            var wrapperXType = innerCfg.isResponsive ? 'CMSformfieldwrapperresponsive' : 'CMSformfieldwrapper';

            // generate wrapper component
            var result = {};
            Ext.apply(result, {
                xtype: wrapperXType,
                layout: 'form',
                defaults: {
                    labelSeparator: ''
                },
                labelAlign: 'top',
                border: false,
                labelWidth: 120,
                id: Ext.id(null, 'FormItem' + (+new Date())),
                params: clonedArray,
                descr: descr,
                style: {
                    'overflow': 'hidden'
                },
                items: Ext.apply(innerCfg, {
                    anchor: '100%' /* Ext Anchor layout handles ALL children not only his own items, we want to be full width */
                }),
                cls: wrapperCls,
                formItemCls: this.formItemCls
            });
            paramArray = null;

            /**
             * @event createwrapper
             * Fired when a form field wrapper is created
             * @param {Object} wrapperCfg The configuration object of the wrapper
             * @param {Object} varCfg The configuration object of the form field's variable
             */
            this.fireEvent('createwrapper', result, clonedArray[0]);

            return result;
        };
    }()),

    /**
     * Updates the param with the name "value" to the current value of the component
     * @private
     */
    updateValueParam: function (cmp) {
        cmp.setParamValue(cmp.getValue());
    },

    /**
     * Clone a component.
     */
    cloneCmp: function thisFn(cmp) {
        // update params with the current value of the object
        this.updateValueParam(cmp);

        // console.log('[FormTabEditor] cloneCmp');
        var result = this.createWrapperItem(cmp.params, cmp.descr);
        var inner = result.items; // resolve wrapper
        var formfield = cmp.get(0);

        if (cmp.descr.allowChildNodes && formfield.items) {
            inner.items = [];
            var children = formfield.items.getRange();
            Ext.each(children, function (childCmp) {
                inner.items.push(thisFn.call(this, childCmp));
            }, this);
        }
        return result;
    },

    /** @private */
    setupEvents: function (cmp) {
        // component is already 'rendered' (has a DOM node/element)
        if (cmp.el) {
            this.mon(cmp.el, 'click', function (evt, el, evtCfg) { this.clickHandler(evt, cmp); }, this, { stopPropagation: true });
            this.mon(cmp.el, 'dblclick', function (evt, el, evtCfg) { this.doubleclickHandler(evt, cmp); }, this, { stopPropagation: true });

            this.mon(cmp, 'valuechanged', this.itemValueChangeHandler, this);
            this.mon(cmp, 'expand', this.expandHandler, this);
            this.mon(cmp, 'collapse', this.collapseHandler, this);
        } else {
            cmp.on('afterrender', function () { this.setupEvents(cmp); }, this, { single: true });
        }
    },

    /**
    * update event handlers and layout after the form has been modified
    */
    refreshForm: function () {
        this.doLayout();
        var newFormElements = this.find('formItemCls', this.formItemCls);
        Ext.each(newFormElements, function (formCmp) {
            if (this.currentComponents.indexOf(formCmp) == -1) {
                this.setupEvents(formCmp);
                this.updateValueParam(formCmp);
                this.currentComponents.push(formCmp);
            }
        }, this);
        this.currentComponents = newFormElements;
    },

    /**
     * Load a form config into the editor.
     * @function
     * @param {Array} data A config object like {@link #data}
     */
    loadConfig: (function () {
        var makeRecursiveWrapper = function (dataArray) {
            var result = [];
            for (var i = 0, l = dataArray.length; i < l; i++) {
                var item = dataArray[i];
                var childResult = this.createWrapperItem(item.params, item.descr);
                if (item.items) {
                    childResult.items.items = makeRecursiveWrapper.call(this, item.items);
                }
                result.push(childResult);
            }
            return result;
        };

        return function (data) {
            this.removeAll();
            console.log('[GeneratedFormPanel] got formGroupData:', data);
            this.add(makeRecursiveWrapper.call(this, data));
            this.refreshForm();
        };
    }()),

    /**
    * Load data from the specified record
    */
    loadRecord: function (record) {
        console.log('[GeneratedFormPanel]', this.id, 'loadRecord', record);
        this.setValues(record.get('formValues'));
    },

    /**
    * Load value data from config object.
    * Does not change the form items, only their values.
    */
    setValues: function (formValues) {
        Ext.each(this.currentComponents, function (/** @param CMS.form.FormFieldWrapperResponsive */ cmp) {
            var varName = cmp.getParamValueKey();
            if (varName && formValues.hasOwnProperty(varName)) {
                cmp.setValue(formValues[varName]);
            }
        });
    },

    /**
    * Returns the currently entered form values
    * @return Object
    */
    getValues: function () {
        var result = {};
        Ext.each(this.currentComponents, function (/** @param CMS.form.FormFieldWrapperResponsive */ cmp) {
            var varName = cmp.getParamValueKey();
            if (varName)  {
                result[varName] = cmp.getValue();
            }
        });
        return result;
    },

    /**
     * Updates the configuration of the current form contents and re-build this form
     * @param Object formConfig formConfig[CMSvar] = {'key': 'value', 'otherKey': 'otherValue'}
     */
    updateFormConfig: function (formConfig) {
        Ext.each(this.currentComponents, function (/** @param CMS.form.FormFieldWrapperResponsive */ cmp) {

            if (cmp.isDestroyed) {
                return;
            }

            var varName = cmp.getParamValueKey();

            if (varName && formConfig.hasOwnProperty(varName)) {
                var updateParams = formConfig[varName];
                console.log('[GeneratedForm] updateFormConfig ', cmp, updateParams);
                // update params of cmp
                Ext.iterate(updateParams, function (key, value) {
                    cmp.setParamConfig(key, value);
                });

                // clone and replace element
                var container = cmp.ownerCt;
                var index = container.items.indexOf(cmp);
                var clone = this.cloneCmp(cmp);
                container.insert(index + 1, clone);
                container.remove(cmp, true);
                this.refreshForm();
            }
        }, this);
    },

    /**
    * Abstract method called when the user expands a collapsible form element (fieldset).
    * Requires a call to {@link #refreshForm}
    * @param {Ext.Component} cmp The expanded form component
    */
    expandHandler: function (cmp) {
    },

    /**
    * Abstract method called when the user collapses a collapsible form element (fieldset)
    * Requires a call to {@link #refreshForm}
    * @param {Ext.Component} cmp The collapsed form component
    */
    collapseHandler: function (cmp) {
    },


    /**
     * Called when the user edits some of the form elements
     * @param {Ext.Component} cmp
     * @param valueObj
     * @private
     */
    itemValueChangeHandler: function (cmp, valueObj) {
        /**
         * Fired when the value of a form field is changed
         * @event valuechanged
         * @param {CMS.form.FormFieldWrapperResponsive} cmp (Wrapper) component whose value was changed
         * @param {String} valueObj.key Key of this formElement which changed (value of param with the name name CMSvar)
         * @param valueObj.newValue New value of this formElement
         * @param valueObj.oldValue Previous value of this formElement
         */
        this.fireEvent('valuechanged', cmp, valueObj);
    },

    /**
    * Abstract method called when the user clicks one of the form items
    * Requires a call to {@link #refreshForm}
    * @param {Ext.EventObject} evt The click event
    * @param {Ext.Component} cmp The clicked form component
    */
    clickHandler: function (evt, cmp) {
    },

    /**
    * Abstract method called when the user double-clicks one of the form items
    * Requires a call to {@link #refreshForm}
    * @param {Ext.EventObject} evt The doubleclick event
    * @param {Ext.Component} cmp The clicked form component
    */
    doubleclickHandler: function (evt, cmp) {
    }
});

Ext.reg('CMSgeneratedformpanel', CMS.form.GeneratedFormPanel);
