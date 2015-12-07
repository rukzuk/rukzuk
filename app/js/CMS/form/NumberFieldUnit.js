Ext.ns('CMS.form');

/**
 * @class CMS.form.SpliderField
 * @extends Ext.Container
 *
 * A component consisting of a numberField and a unit combobox
 */
CMS.form.NumberFieldUnit = Ext.extend(Ext.Container, {
    // overridden suoer properties
    layout: 'hbox',

    // config
    /**
     * @cfg {Integer} spacing
     * The width of the space between infinite spinner field and unit select box. Defaults to 5.
     */
    spacing: 5,

    /**
     * @cfg {Array} unitList
     * list of allowed (selectable) units
     */
    unitList: [],

    /**
     * cfg {String} value
     * (consists of value and unit; like 10px)
     */
    value: '0',

    /**
     * @cfg {Number} numberFieldWidth
     * width of the number field (defaults to 40)
     */
    numberFieldWidth: 40,

    /**
     * @cfg {Number} unitFieldWidth
     * width of the unit field (defaults to 40)
     */
    unitFieldWidth: 40,

    /**
     * @cfg {Number} alternateIncrementFactor
     * The multiplier when using the slider with having the {@link alternateKey} pressed
     */
    alternateIncrementFactor: 5,

    /**
     * @cfg {String} alternateKey
     * modifier key which multiplies changes by alternateIncrementFactor value must be a property of {Ext.EventObject}
     * (defaults to <code>'shiftKey'</code>
     */
    alternateKey: 'shiftKey',

    /**
     * @cfg {Number} increment
     * increment value for spinner and slider (defaults to 1)
     */
    increment: 1,

    /**
     * @cfg {Number} minValue
     */
    minValue: null,

    /**
     * @cfg {Number} maxValue
     */
    maxValue: null,

    /**
     * The number part of the value
     *
     * @property valueNum
     * @type Number
     * @private
     */
    valueNum: 0,

    /**
     * The unit of the value
     *
     * @property valueUnit
     * @type String
     * @private
     */
    valueUnit: '',

    initComponent: function () {
        this.cls = (this.cls || '') + ' CMSnumberfieldunit';

        // fix min/max values
        if (!Ext.isNumber(this.maxValue)) {
            this.maxValue = undefined;
        }
        if (!Ext.isNumber(this.minValue)) {
            this.minValue = undefined;
        }

        // remember default/inital values
        this.defaultMaxValue = this.maxValue;
        this.defaultMinValue = this.minValue;
        this.defaultIncrement = this.increment;

        this.items = this.buildItems();

        CMS.form.NumberFieldUnit.superclass.initComponent.call(this);

        // events
        this.mon(this.numberField, 'change', this.onNumberFieldChangeHandler, this);
        this.mon(this.numberField, 'specialkey', this.onNumberFieldSpecialKeyHandler, this);
        this.mon(this.numberField, 'spin', this.onSpinHandler, this);

        this.mon(this.infiniteSlider, 'beforemoveslider', this.onInfiniteSliderBeforeSlide, this);
        this.mon(this.infiniteSlider, 'change', this.onInfiniteSliderChangeHandler, this);

        // unit field events (change fires only after blur change)
        this.mon(this.unitField, 'select', this.onUnitFieldSelect, this);

        if (this.unitList.length === 1) {
            // there is exactly one avaliale unit
            // -> always use this unit because you cannot change the value
            var parsedValues = this.parseValue(this.value);
            var numberValue = (parsedValues && parsedValues[0]) || this.valueNum;
            this.value = numberValue + '' + this.unitList[0][0];
        }

        // parse inital value
        this.setValue(this.value);
    },

     /**
     * @private
     * Builds the spinner and slider item
     */
    buildItems: function () {

        // create the spinner configuration
        var spinnerCfg = Ext.copyTo({
            wrapperCls: 'CMSnumberfield-infinite-spinner'
        }, this.initialConfig, ['increment', 'alternateIncrementFactor', 'alternateKey', 'defaultValue']);

        var displayUnitValue;
        this.invisibleSpinner = new SB.form.InvisibleSpinner(spinnerCfg);
        this.unitList = this.unitList || [];
        if (this.unitList.length === 1) {
            displayUnitValue = this.unitList[0][1] || this.unitList[0][0];
        }

        // slider field
        var items = [{
            id: this.id + '-spinner',
            xtype: 'numberfield',
            ref: 'numberField',
            plugins: [this.invisibleSpinner],
            validateBlur: this.validateBlur,
            value: this.valueNum,
            minValue: this.minValue,
            maxValue: this.maxValue,
            maxText: '',
            minText: '',
            width: this.numberFieldWidth
        }, {
            xtype: 'displayfield',
            hidden: !displayUnitValue,
            value: displayUnitValue,
            margins: '0 0 0 ' + this.spacing
        }, {
            xtype: 'combo',
            ref: 'unitField',
            editable: false,
            hidden: (this.unitList.length < 2), // don't show drop down if there are not at least 2 possible options
            noSelectionValue: '',
            noSelectionText: '-',
            triggerAction: 'all',
            mode: 'local',
            margins: '0 0 0 ' + this.spacing,
            displayField: 'text',
            valueField: 'id',
            store: new Ext.data.ArrayStore({
                id: 0,
                fields: [
                    'id',
                    'text',
                    'config'
                ],
                data: this.unitList
            }),
            width: this.unitFieldWidth
        }, {
            xtype: 'ux-infiniteslider',
            ref: 'infiniteSlider',
            margins: '0 0 0 ' + this.spacing,
            flex: 1
        }];

        return items;
    },

    /**
     * empty validation function that is required by the spinner field
     */
    validateBlur: function () {
        return true;
    },

    /**
     * Utitlity method to set the new valueUnit on select of the unitField
     * @private
     */
    onUnitFieldSelect: function (field, record) {

        if (record.data && Ext.isString(record.data.config)) {
            this.updateUnitConfig(record.data.config);
        }

        // update value - also valueNum because min/max could have changed (see updateUnitConfig)
        var newValue = record.id;
        var fixedValue = this.fixBoundries(this.valueNum);
        this.numberField.setValue(fixedValue);
        this.setRawValue(fixedValue, newValue);
    },

    /**
     * handles a config string of increment/minValue-maxValue (e.g. 0.5/0-100)
     * @private
     */
    updateUnitConfig: function (configString) {
        var split = configString.split('/', 3);
        var increment = Ext.num(split[0], this.defaultIncrement);

        this.setIncrement(increment);

        var min = Ext.num(split[1], this.defaultMinValue);
        var max = Ext.num(split[2], this.defaultMaxValue);

        this.setMinValue(min);
        this.setMaxValue(max);
    },

    /**
     * Utility method to update the value when numberField changed
     * @private
     */
    onNumberFieldChangeHandler: function (cmp, v) {
        this.setRawValue(v);
    },

    /**
     * Handler for the "specialkey" event of the number field;
     * It updates current value with the value of the number field on [ENTER] and resets
     * the number field on ESC
     * @private
     */
    onNumberFieldSpecialKeyHandler: function (cmp, e) {
        var key = e.getKey();
        if (key === e.ENTER) {
            this.setRawValue(cmp.getValue());
        } else if (key === e.ESC) {
            cmp.setValue(this.valueNum);
        }
    },

    /**
     * Handler for the spin events
     * @private
     */
    onSpinHandler: function () {
        var v = this.numberField.getValue();
        this.setRawValue(v);
    },

    /**
     * Utility method to update the numberField value when slider moved (changed)
     * @private
     */
    onInfiniteSliderBeforeSlide: function (cmp, steps) {
        // calculate slider change
        var v = this.calculateSliderChange(steps);

        // do not animate slider
        if (v >= this.maxValue || v <= this.minValue) {
            return false;
        }
    },

    /**
     * Utility method to update the numberField value when slider moved (changed)
     * @private
     */
    onInfiniteSliderChangeHandler: function (cmp, steps) {
        var v = this.calculateSliderChange(steps);
        this.numberField.setValue(v);
        this.setRawValue(v);
    },

    /**
     * Helper method to calculate new value with given slider steps
     * @return {Number} new value
     * @private
     */
    calculateSliderChange: function (steps) {
        var factor = Ext.EventObject[this.alternateKey] === true ? this.increment * this.alternateIncrementFactor : this.increment;

        // calculate change
        var v = this.valueNum + (steps * factor);
        v = this.fixBoundries(v);

        return v;
    },

    /**
     * Enable the slider when the field is enabled.
     * @private
     */
    onEnable: function () {
        CMS.form.NumberFieldUnit.superclass.onEnable.call(this);
        this.numberField.enable();
        this.unitField.enable();
        this.infiniteSlider.enable();
    },

    /**
     * Disable the slider when the field is disabled.
     * @private
     */
    onDisable: function () {
        CMS.form.NumberFieldUnit.superclass.onDisable.call(this);
        this.numberField.disable();
        this.unitField.disable();
        this.infiniteSlider.disable();
    },

    /**
     * Sets the minimum field value.
     *
     * @param {Number} v
     *      The new minimum value.
     *
     * @return CMS.form.NumberFieldUnit
     *      this
     */
    setMinValue: function (v) {
        this.minValue = v;
        this.numberField.minValue = v;
        return this;
    },


    /**
     * Sets the maximum field value.
     *
     * @param {Number} v
     *      The new maximum value.
     *
     * @return CMS.form.NumberFieldUnit
     *      this
     */
    setMaxValue: function (v) {
        this.maxValue = v;
        this.numberField.maxValue = v;
        return this;
    },

    /**
     * Sets the value for this field.
     *
     * @param {String} value
     *      The new value consiting of number concatenated with unit (e.g. 23px or 55%).
     *
     */
    setValue: function (value) {
        //console.log('[CMSNumberFieldUnit] setValue', value);

        // parse values
        var parsedValues = this.parseValue(value);
        if (!parsedValues) {
            //console.info('[NumberFieldUnit] setValue invalid value', value);
            return this;
        }
        var valueNum = parsedValues[0];
        var unitValue = parsedValues[1];

        // update UI
        this.numberField.setValue(valueNum);
        this.unitField.setValue(unitValue);
        // update min/max and increment (aka unitConfig)
        var unitFieldStore = this.unitField.store.getById(unitValue);
        if (unitFieldStore && unitFieldStore.data && Ext.isString(unitFieldStore.data.config)) {
            this.updateUnitConfig(unitFieldStore.data.config);
        }

        // update internal values
        this.setRawValue(valueNum, unitValue);
        return this;
    },

    /**
     * Sets the value directly without any parsing
     * @param {Number} value
     * @param {String} [valueUnit] unit of the value
     * @return {*}
     * @private
     */
    setRawValue: function (value, valueUnit) {
        //console.info('[NumberFieldUnit] setRawValue value', value, 'valueUnit', valueUnit);

        var oldVal = this.value;

        // set internal values
        if (Ext.isNumber(value)) {
            this.valueNum = value;
        }

        if (Ext.isString(valueUnit)) {
            this.valueUnit = valueUnit;
        }

        // update concatenated value
        this.value = this.valueNum + '' + this.valueUnit;

        /**
         * @event change
         * See {@link Ext.form.Field#change}.
         */
        if (oldVal != this.value) {
            this.fireEvent('change', this, this.value, oldVal);
        }

        return this;
    },

    parseValue: function (value) {
        if (!value) {
            return false;
        }

        var valueRe = /^([+\-]?(?:\d+|\d*\.\d+))([^\.]*)$/;

        var match = String(value).match(valueRe);
        //console.info('[NumberFieldUnit] parseValue', match);

        if (!match) {
            return false;
        }

        var numberValue = Number(match[1]);
        var unit = match[2];

        return [numberValue, unit];
    },

    /**
     * Gets the current value for this field.
     *
     * @return String
     *      The current value with selected unit.
     */
    getValue: function () {
        //console.info('[NumberFieldUnit] getValue', this.value);
        return this.value;
    },


    /**
     * Bound the given value to the current min and max values
     * @private
     */
    fixBoundries: function (value) {
        var v = value;
        var min = parseInt(this.numberField.minValue, 10);
        var max = parseInt(this.numberField.maxValue, 10);

        if (Ext.isNumber(min) && v < min) {
            v = min;
        }
        if (Ext.isNumber(max) && v > max) {
            v = max;
        }

        return this.fixPrecision(v);
    },

    /**
     * @private
     */
    fixPrecision: function (value) {
        var nan = isNaN(value);
        if (!this.numberField.allowDecimals || this.numberField.decimalPrecision == -1 || nan || !value) {
            return nan ? '' : value;
        }
        return parseFloat(parseFloat(value).toFixed(this.numberField.decimalPrecision));
    },

    /**
     * @private
     */
    setIncrement: function (increment) {
        this.increment = increment;
        this.invisibleSpinner.increment = increment;
    }

});

Ext.reg('CMSnumberfieldunit', CMS.form.NumberFieldUnit);
