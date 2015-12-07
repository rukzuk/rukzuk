Ext.ns('CMS.form');

/**
 * A wrapper for form fields (used in GeneratedFormPanel)
 *
 * @class CMS.form.FormFieldWrapper
 * @extends Ext.Container
 */
CMS.form.FormFieldWrapper = Ext.extend(Ext.Container, {

    /**
     * The form field params
     * @property params
     * @type Array
     */
    params: undefined,
    autoHeight: true,
    isInEditMode: false,


    initComponent: function () {

        // params
        this.paramsCollection = new Ext.util.MixedCollection(false, function (p) {
            return p.name;
        });

        if (this.params) {
            this.paramsCollection.addAll(this.params);
        }

        this.addClass('CMSformfieldwrapper');

        /** add class to wrapped item if it is in edit mode {@link CMS.form.FormTabEditor} */
        if (this.isInEditMode) {
            this.addClass('CMSformfieldwrapper-inEditMode');
        }

        // ensure there is always a field label (add an empty one if its missing)
        // HACK to prevent multi-column box, comment having a label
        var xType = this.getParamConfigValue('xtype');
        if (xType !== 'container' && xType !== 'ux-multilinelabel') {
            this.items.fieldLabel = this.items.fieldLabel ? this.items.fieldLabel : '&#160;';
        }

        CMS.form.FormFieldWrapper.superclass.initComponent.call(this);

        // get reference to actual component
        this.initFieldEventListener(this.getFormField());

    },

    /**
     * Get the Params as they are
     * @param {Boolean} [clone] copy (true, default) or reference (false)
     * @returns {Array|*}
     */
    getParams: function (clone) {
        var params = this.paramsCollection.getRange();
        if (clone !== false) {
            params = SB.util.cloneObject(params);
        }
        return params;
    },

    /**
     * Variable Name (key) of this form element
     * @returns {String|null} the key (or name) of this form element, if not found null
     */
    getParamValueKey: function () {
        return this.getParamConfigValue('CMSvar');
    },

    /**
     * The value represented in the params array
     * @returns {*|null} the value if found, otherwise null
     */
    getParamValue: function () {
        return this.getParamConfigValue('value');
    },

    /**
     * Returns the value of a given key in the params array (key is defined by the value of the 'name: CMSvar' object),
     * if key is not found null is returned
     * @param {String} key
     * @returns {*|null}
     * @protected
     */
    getParamConfigValue: function (key) {
        var paramObj = this.paramsCollection.get(key);
        return paramObj ? paramObj.value : null;
    },

    /**
     * The value of the underlying component
     * @returns {*|null} calls the underlying getValue of the component if
     *                   the component has this method, otherwise it returns null
     */
    getValue: function () {
        var cmp = this.getFormField();
        if (cmp && cmp.getValue) {
            return cmp.getValue();
        }
    },

    /**
     * Updates the param array
     * If the key is not available a new simple non-user-editable key, value param object is created
     * @param {String} key
     * @param {*} value
     */
    setParamConfig: function (key, value) {
        var paramsObj = this.paramsCollection.get(key);

		// create param
        if (!paramsObj) {
            paramsObj = {
                name: key,
                value: value,
                xtype: null
            };
			// add new paramsObj to both params representations
            this.paramsCollection.add(paramsObj);
			// support for direct access to params property (legacy, use getParams)
			this.params.push(paramsObj);
        } else {
			// update param
			paramsObj.value = value;
		}
    },

    /**
     * Sets the value
     * @param value
     */
    setParamValue: function (value) {
        this.setParamConfig('value', value);
    },

    /**
     * Sets the value of the underlying form field (if present)
     * Is called by owing components to change the value of a form field
     * @param value either a responsive value object or just he plain value
     * @public
     */
    setValue: function (value) {
        var cmp = this.getFormField();
        if (cmp && cmp.setValue) {
            cmp.setValue(value);
        }
    },

    /**
     * Only updates the value of the underlying component, nothing else is changed
     * Attention: Use with care!
     * @param value
     * @protected
     */
    setValueRaw: function (value) {
        var cmp = this.getFormField();
        if (cmp && cmp.setValue) {
            cmp.suspendEvents(false);
            cmp.setValue(value);
            cmp.resumeEvents();
        }
    },

    /**
     * Add CSS Class (if not present, also checks this.cls - lazy rendering)
     * @param {string} cls
     */
    addClass: function (cls) {
        if (this.el || (this.cls && this.cls.split && this.cls.split(' ').indexOf(cls) < 0)) {
            CMS.form.FormFieldWrapper.superclass.addClass.call(this, cls);
        }
    },

    /**
     * @returns the form field
     * @protected
     */
    getFormField: function () {
        return this.get(0);
    },

    /** @protected */
    initFieldEventListener: function (formField) {
        var oldValue;

        if (formField.isXType('checkbox')) {
            this.mon(formField, 'check', function (field, checked) {
                if (!oldValue) {
                    oldValue = field.startValue;
                }
                this.itemValueChangeHandler(checked, oldValue);
                oldValue = checked;
            }, this);
        } else if (formField.isXType('combo')) {
            this.mon(formField, 'select', function (field, record, index) {
                if (!oldValue) {
                    oldValue = field.startValue;
                }
                this.itemValueChangeHandler(field.getValue(), oldValue);
                oldValue = field.getValue();
            }, this);
        } else {
            this.mon(formField, 'change', function (field, value, oldValue) {
                this.itemValueChangeHandler(value, oldValue);
            }, this);
        }

        // Keys ENTER and ESC support
        this.mon(formField, 'specialkey', function (field, e) {
            if (e.getKey() == e.ENTER) {

                if (field.getValue() !== field.startValue) {
                    // handle item changed (normally done onBlur)
                    this.itemValueChangeHandler(field.getValue(), field.startValue);

                    // update startValue (normally done onFocus)
                    field.startValue = field.getValue();
                }

            } else if (e.getKey() == e.ESC) {
                field.setValue(field.startValue);
            }
        }, this);

        if (formField.collapsible) {
            this.mon(formField, 'collapse', this.collapseHandler(), this);
            this.mon(formField, 'expand', this.expandHandler(), this);
        }
    },

    /**
     * Handler for value change events of the underlying component (form element)
     * @param newVal
     * @param oldVal
     * @protected
     */
    itemValueChangeHandler: function (newVal, oldVal) {

        // Setting the value (param name: value) in the params object is
        // only required as this class is also used in the module editor (where you can define a default value this way)
        this.setParamValue(newVal);

        this.fireValueChangedEvent(newVal, oldVal);
    },

    /**
     * Fires the valueChange event used by the system (X-doc-API)
     * @param newValue needs to be a copy of the new value (take care of this if using objects)
     * @param oldValue needs to be a copy of the new value (take care of this if using objects)
     * @protected
     */
    fireValueChangedEvent: function (newValue, oldValue) {

        var valueObj = {
            key: this.getParamValueKey(),
            newValue: newValue,
            oldValue: oldValue
        };

        console.log('[FormFieldWrapper] fireValueChangedEvent', valueObj);

        /**
         * @event valuechanged
         * @see {@link CMS.form.GeneratedFormPanel#itemValueChangeHandler}
         */
        this.fireEvent('valuechanged', this, valueObj);

    },

    collapseHandler: function () {
        this.fireEvent('collapse', this);
    },

    expandHandler: function () {
        this.fireEvent('expand', this);
    },

	afterRender: function () {
		CMS.form.FormFieldWrapper.superclass.afterRender.call(this);

		// support for locked
		var locked = this.paramsCollection.get('locked');
		if (locked && locked.value) {
			this.el.addClass('CMSformfieldwrapper-locked');
			this.el.mask().addClass('CMSformfieldwrapper-mask');
		}
	}

});

Ext.reg('CMSformfieldwrapper', CMS.form.FormFieldWrapper);
