Ext.ns('CMS.form');

/**
 * @class CMS.form.SpliderField
 * @extends Ext.Container
 *
 * A component consisting of a spinnerField and a sliderField
 */
CMS.form.SpliderField = Ext.extend(Ext.Container, {
    // private override
    layout: 'hbox',
    layoutConfig: {
        align: 'middle'
    },

    /**
     * @cfg {Boolean} useTips
     * True to use an Ext.slider.Tip to display tips for the value. Defaults to true.
     */
    useTips: true,

    /**
     * @cfg {String} displayMode
     * Whether to show the spinner and slider or only one of both. Possible values are 'splider', 'slider', 'spinner'. Defaults to 'splider'.
     */
    displayMode: 'splider',

    /**
     * @cfg {Function} tipText
     * A function used to display custom text for the slider tip. Defaults to null, which will
     * use the default on the plugin.
     */
    tipText: null,

    /**
     * @cfg {Number} fieldWidth
     * the width of the spinner field. Defaults to 50.
     */
    fieldWidth: 50,

    /**
     * @cfg {Integer} spacing
     * The width of the space between spinner field and slider. Defaults to 10.
     */
    spacing: 10,

    value: 0,

    initComponent: function () {
        //console.log('[CMSSpliderField] initComponent')
        this.cls = (this.cls || '') + ' CMSsplider';

        var showSpinner = true;
        var showSlider = true;
        switch (this.displayMode) {
        case 'spinner':
            showSlider = false;
            break;
        case 'slider':
            showSpinner = false;
        }

        this.items = this.buildItems(showSpinner, showSlider);

        CMS.form.SpliderField.superclass.initComponent.call(this);

        // add event handler to synchronize the spinner field and the slider
        if (this.spinnerField) {
            this.mon(this.spinnerField, 'change', this.onInfiniteSliderChangeHandler, this);
            this.mon(this.spinnerField, 'spin', this.onSpinHandler, this);

            // add event handler to stop the double click at the spinner (annoying for module editor)
            this.mon(this.spinnerField, 'afterrender', function () {
                if (this.spinnerField.wrap) {
                    this.mon(this.spinnerField.wrap, 'dblclick', function (ev, html) {
                        ev.stopEvent();
                    });
                }
            }, this, {single: true});
        }
        if (this.slider) {
            this.mon(this.slider, 'change', this.onSliderChangeHandler, this);

            // set initial values (for some reason the slider does not react with a config value)
            this.on('afterlayout', this.slider.syncThumb, this.slider);
        }
    },

     /**
     * @private
     * Builds the spinner and slider item
     */
    buildItems: function (showSpinner, showSlider) {
        var items = [];

        if (showSpinner) {
            // create the spinner configuration
            var spinnerCfg = Ext.copyTo({
                wrapperCls: 'CMSsplider-spinner',
                incrementValue: this.increment
            }, this.initialConfig, ['incrementValue', 'alternateIncrementValue', 'accelerate', 'defaultValue', 'triggerClass', 'splitterClass']);

            items.push({
                xtype: 'container',
                actionMode: 'wrap',
                ref: 'spinnerFieldCt',
                items: [{
                    id: this.id + '-spinner',
                    xtype: 'numberfield',
                    ref: '../spinnerField',
                    bubbleEvents: ['specialkey'],
                    plugins: [new Ext.ux.Spinner(spinnerCfg)],
                    validateBlur: this.validateBlur,
                    width: this.fieldWidth,
                    value: this.value
                }],
                width: this.fieldWidth,
                margins: '0 ' + (this.spacing || 0) + ' 0 0'
            });
        }

        if (showSlider) {
            // create the slider configuration
            var sliderCfg = Ext.copyTo({
                id: this.id + '-slider',
                cls: 'CMSsplider-slider'
            }, this.initialConfig, ['vertical', 'minValue', 'maxValue', 'decimalPrecision', 'keyIncrement', 'increment', 'clickToChange', 'animate']);
            if (this.useTips) {
                // only can use it if it exists.
                var plug = this.tipText ? { getText: this.tipText } : {};
                sliderCfg.plugins = [new Ext.slider.Tip(plug)];
            }

            items.push({
                xtype: 'container',
                items: [{
                    xtype: 'box',
                    autoEl: {
                        tag: 'span',
                        style: { 'float': 'right' },
                        html: this.minValue || '0'
                    }
                }],
                width: 20
            }, {
                xtype: 'container',
                items: Ext.apply({
                    xtype: 'slider',
                    ref: '../slider',
                    value: this.value
                }, sliderCfg),
                flex: 1,
                margins: '0 5 0 5'
            }, {
                xtype: 'container',
                items: {
                    xtype: 'box',
                    autoEl: {
                        tag: 'span',
                        style: { 'float': 'right' },
                        html: this.maxValue
                    }
                },
                width: 25
            });
        }

        return items;
    },

    /**
     * empty validation function that is required by the spinner field
     */
    validateBlur: function () {
        return true;
    },

    /**
     * @private
     * Utility method to set the value of the field when the slider changes.
     */
    onSliderChangeHandler: function (slider, v) {
        //console.log('[CMSSpliderField] onSliderChangeHandler', v)
        this.setValue(v, undefined);
    },

    /**
     * @private
     * Utility method to set the value of the slider when the field changes.
     */
    onInfiniteSliderChangeHandler: function (spinner, v, oldVal) {
        //console.log('[CMSSpliderField] onSpinnerChangeHandler', v)
        this.setValue(v, this.animate);
    },

    /**
     * @private
     * Utility method to set the value of the slider when the spinner is clicked.
     */
    onSpinHandler: function (spinner) {
        var v = this.spinnerField.getValue();
        this.setValue(v, this.animate);
    },

    /**
     * Enable the slider when the field is enabled.
     * @private
     */
    onEnable: function () {
        CMS.form.SpliderField.superclass.onEnable.call(this);

        if (this.slider) {
            this.slider.enable();
        }
        if (this.spinnerField) {
            this.spinnerField.enable();
        }
    },

    /**
     * Disable the slider when the field is disabled.
     * @private
     */
    onDisable: function () {
        CMS.form.SpliderField.superclass.onDisable.call(this);

        if (this.slider) {
            this.slider.disable();
        }
        if (this.spinnerField) {
            this.spinnerField.disable();
        }
    },

    /**
     * Ensure the slider is destroyed when the field is destroyed.
     * @private
     */
    beforeDestroy: function () {
        if (this.slider) {
            Ext.destroy(this.slider);
        }
        CMS.form.SpliderField.superclass.beforeDestroy.call(this);
    },

    /**
     * @private
     * If a side icon is shown, do alignment to the slider
     */
    alignErrorIcon: function () {
        if (this.slider) {
            this.errorIcon.alignTo(this.slider.el, 'tl-tr', [2, 0]);
        }
    },


    /**
     * Sets the minimum field value.
     *
     * @param {Number} v
     *      The new minimum value.
     *
     * @return CMS.form.SpliderField
     *      this
     */
    setMinValue: function (v) {
        if (this.slider) {
            this.slider.setMinValue(v);
        }
        return this;
    },


    /**
     * Sets the maximum field value.
     *
     * @param {Number} v
     *      The new maximum value.
     *
     * @return CMS.form.SpliderField
     *      this
     */
    setMaxValue: function (v) {
        if (this.slider) {
            this.slider.setMaxValue(v);
        }
        return this;
    },


    /**
     * Sets the value for this field.
     *
     * @param {Number} value
     *      The new value.
     *
     * @param {Boolean} animate
     *      (optional) Whether to animate the transition. If not specified, it will default to the animate config.
     *
     * @return CMS.form.SpliderField
     *      this
     */
    setValue: function (value, animate) {
        //console.log('[CMSSpliderField] setValue', value, animate)
        var oldVal = this.value;
        if (value != oldVal) {
            if (this.spinnerField) {
                this.spinnerField.suspendEvents();
                this.spinnerField.setValue(value);
                this.spinnerField.resumeEvents();
            }
            if (this.slider) {
                this.slider.suspendEvents();
                this.slider.setValue(value, animate);
                this.slider.resumeEvents();
            }
            /**
            * @event change
            * See {@link Ext.form.Field#change}.
            */
            this.fireEvent('change', this, value, oldVal);
        }
        this.value = value;
        return this;
    },

    /**
     * Gets the current value for this field.
     *
     * @return Number
     *      The current value.
     */
    getValue: function () {
        return this.value;
    }
});

Ext.reg('CMSspliderfield', CMS.form.SpliderField);
