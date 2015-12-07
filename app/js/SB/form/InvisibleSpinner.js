Ext.ns('SB.form');
/**
 * @class SB.form.InvisibleSpinner
 * @extends Ext.util.Observable
 * A spinner without any user interface - keyboard and mousewheel input
 * based on Ext.ux.Spinner
 */
SB.form.InvisibleSpinner = Ext.extend(Ext.util.Observable, {
    increment: 1,
    alternateIncrementFactor: 5,
    alternateKey: 'shiftKey',
    defaultValue: 0,

    /**
     * @cfg {String} wrapperCls
     * a style class that will be applied to the field wrapper
     */
    wrapperCls: undefined,

    constructor: function (config) {
        SB.form.InvisibleSpinner.superclass.constructor.call(this, config);
        Ext.apply(this, config);
        this.mimicing = false;
    },

    init: function (field) {
        this.field = field;

        field.afterMethod('onRender', this.doRender, this);
        field.afterMethod('onEnable', this.doEnable, this);
        field.afterMethod('onDisable', this.doDisable, this);
        field.afterMethod('onFocus', this.doFocus, this);

        field.beforeMethod('onDestroy', this.doDestroy, this);
    },


    doRender: function (ct, position) {
        this.el = this.field.getEl();
        this.initSpinner();
    },

    doEnable: function () {
        this.disabled = false;
    },

    doDisable: function () {
        this.disabled = true;
    },

    doFocus: function () {
        if (!this.mimicing) {
            this.mimicing = true;
            Ext.get(Ext.isIE ? document.body : document).on('mousedown', this.mimicBlur, this, {
                delay: 10
            });
            this.el.on('keydown', this.checkTab, this);
        }
    },

    // private
    checkTab: function (e) {
        if (e.getKey() == e.TAB) {
            this.triggerBlur();
        }
    },

    // private
    mimicBlur: function (e) {
        if (this.field.validateBlur(e)) {
            this.triggerBlur();
        }
    },

    // private
    triggerBlur: function () {
        this.mimicing = false;
        Ext.get(Ext.isIE ? document.body : document).un('mousedown', this.mimicBlur, this);
        this.el.un('keydown', this.checkTab, this);
        this.field.beforeBlur();
        this.field.onBlur.call(this.field);
    },

    initSpinner: function () {
        this.field.addEvents({
            'spin': true,
            'spinup': true,
            'spindown': true
        });

        this.keyNav = new Ext.KeyNav(this.el, {
            'up': function (e) {
                e.preventDefault();
                this.onSpinUp();
            },

            'down': function (e) {
                e.preventDefault();
                this.onSpinDown();
            },

            'pageUp': function (e) {
                e.preventDefault();
                this.onSpinUpAlternate();
            },

            'pageDown': function (e) {
                e.preventDefault();
                this.onSpinDownAlternate();
            },

            scope: this
        });

        this.field.mon(this.el, 'mousewheel', this.handleMouseWheel, this);
    },


    //private
    //checks if control is allowed to spin
    isSpinnable: function () {
        if (this.disabled || this.el.dom.readOnly) {
            Ext.EventObject.preventDefault(); //prevent scrolling when disabled/readonly
            return false;
        }
        return true;
    },

    handleMouseWheel: function (e) {
        if (this.disabled) {
            return;
        }

        var delta = e.getWheelDelta();
        if (delta > 0) {
            this.onSpinUp();
            e.stopEvent();
        } else if (delta < 0) {
            this.onSpinDown();
            e.stopEvent();
        }
    },

    //private
    onSpinUp: function () {
        if (this.isSpinnable() === false) {
            return;
        }
        if (Ext.EventObject[this.alternateKey] === true) {
            this.onSpinUpAlternate();
            return;
        } else {
            this.spin(false, false);
        }
        this.field.fireEvent('spin', this);
        this.field.fireEvent('spinup', this);
    },

    //private
    onSpinDown: function () {
        if (this.isSpinnable() === false) {
            return;
        }
        if (Ext.EventObject[this.alternateKey] === true) {
            this.onSpinDownAlternate();
            return;
        } else {
            this.spin(true, false);
        }
        this.field.fireEvent('spin', this);
        this.field.fireEvent('spindown', this);
    },

    //private
    onSpinUpAlternate: function () {
        if (this.isSpinnable() === false) {
            return;
        }
        this.spin(false, true);
        this.field.fireEvent('spin', this);
        this.field.fireEvent('spinup', this);
    },

    //private
    onSpinDownAlternate: function () {
        if (this.isSpinnable() === false) {
            return;
        }
        this.spin(true, true);
        this.field.fireEvent('spin', this);
        this.field.fireEvent('spindown', this);
    },

    /**
     * @private
     * Spin
     * @param {Boolean} down
     * @param {Boolean} alternate
     * @param {Number} [steps] - how many spin steps should be performed?
     */
    spin: function (down, alternate, steps) {
        var v = parseFloat(this.field.getValue());
        var incr = (alternate === true) ? this.increment * this.alternateIncrementFactor : this.increment;

        if (steps) {
            incr *= steps;
        }

        if (down === true) {
            v -= incr;
        } else {
            v += incr;
        }

        v = (isNaN(v)) ? this.defaultValue : v;
        v = this.fixBoundries(v);
        this.field.setRawValue(v);
    },


    fixBoundries: function (value) {
        var v = value;

        if (this.field.minValue !== undefined && v < this.field.minValue) {
            v = this.field.minValue;
        }
        if (this.field.maxValue !== undefined && v > this.field.maxValue) {
            v = this.field.maxValue;
        }

        return this.fixPrecision(v);
    },

    // private
    fixPrecision: function (value) {
        var nan = isNaN(value);
        if (!this.field.allowDecimals || this.field.decimalPrecision == -1 || nan || !value) {
            return nan ? '' : value;
        }
        return parseFloat(parseFloat(value).toFixed(this.field.decimalPrecision));
    },


    doDestroy: function () {

        if (this.repeater) {
            this.repeater.purgeListeners();
        }
        if (this.mimicing) {
            Ext.get(Ext.isIE ? document.body : document).un('mousedown', this.mimicBlur, this);
        }
    }
});

