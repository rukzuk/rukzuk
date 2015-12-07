Ext.namespace('Ext.ux.form');

/**
* @class Ext.ux.form.ColorPickerField
* A colorpicker field
* @requires SB.ColorPickerWindow
*/

Ext.ux.form.ColorPickerField = Ext.extend(Ext.form.TextField, {

    /**
    * @cfg {Boolean} fireChangeOnDrag
    * <tt>false</tt> to disable firing of <tt>change</tt> event when the user drags the mouse inside the picker window.
    * In this case, only a single change event is fired when the picker window is closed.
    * Defaults to <tt>true</tt>
    */
    fireChangeOnDrag: true,

    cls: 'ux-colorpicker',
    enableKeyEvents: true,

    onResize: function (w, h) {
        Ext.ux.form.ColorPickerField.superclass.onResize.call(this, w, h);
        this.el.setWidth(w);
    },

    onRender: function () {
        Ext.ux.form.ColorPickerField.superclass.onRender.apply(this, arguments);
        this.wrap = this.el.wrap({
            cls: 'ux-colorpicker-wrap'
        });
        if (!this.width) {
            this.wrap.setWidth(this.el.getWidth());
        }
        this.resizeEl = this.positionEl = this.wrap;
        this.createPickerWindow();
        var pickerWindow = this.pickerWindow;
        var self = this;
        var onMouseDown = function (e) {
            if (!e.within(pickerWindow.el.dom) && !e.within(self.el.dom, false, true)) {
                pickerWindow.hide();
            }
        };
        pickerWindow.hide = pickerWindow.hide.createSequence(function () {
            Ext.getDoc().un('mousedown', onMouseDown);
        });
        pickerWindow.show = pickerWindow.show.createSequence(function () {
            Ext.getDoc().on('mousedown', onMouseDown);
        });

        this.el.dom.onmousedown = this.showPicker.createDelegate(this);
        this.el.dom.style.backgroundImage = 'none';
    },

    listeners: {
        keyup: function (self) {
            delete self.cachedValue;
            self.syncValue();
        },
        specialkey: function (self, e) {
            if (e.getKey() == e.ENTER) {
                self.pickerWindow.hide();
            }
        }
    },

    /**
    * @private
    */
    syncValue: function () {
        if (!this.pickerWindow) {
            this.createPickerWindow();
        }
        var textDelegate = this.pickerWindow.textDelegateElement;
        this.pickerWindow.textDelegateElement = null;
        this.setValue(this.getRawValue());
        this.pickerWindow.textDelegateElement = textDelegate;
        textDelegate = null;
    },

    /**
    * @private param skipWindowUpdate {Boolean} do not apply the new value to the associated window
    */
    setValue: function (value, skipWindowUpdate) {
        if (!this.el) {
            this.value = value;
            return;
        }
        if (typeof value == 'string' && /^\{/.test(value)) {
            try {
                value = Ext.decode(value);
            } catch (e) { value = ''; }
        }
        if (!skipWindowUpdate) {
            if (!this.pickerWindow) {
                this.createPickerWindow();
            }
            this.pickerWindow.setValue(value);
        }
        this.value = this.getRawValue();
    },

    getValue: function () {
        if (!this.el) {
            return this.value;
        }
        var val = SB.color.parseColor(this.getRawValue());
        if (!val) {
            return '';
        }
        return SB.color.hsvaToCssString(val, false, true);
    },

    createPickerWindow: function () {
        var windowCfg = {
            imgPath: this.imgPath,
            value: this.value,
            returnColorNames: this.returnColorNames,
            enableAlpha: this.enableAlpha,
            colorDelegateElement: this.el,
            colorDelegateBg: '#e0e0e0',
            textDelegateElement: this.el,
            constrain: true,
            listeners: {
                change: function (newVal) {
                    var oldValue = this.pickerValue;
                    this.pickerValue = newVal;
                    if (this.fireChangeOnDrag) {
                        this.fireEvent('change', this, this.pickerValue, oldValue);
                    }
                },
                beforehide: function () {
                    if (this.pickerValue) {
                        this.setValue(this.pickerValue, true);
                    }
                    return true;
                },
                scope: this
            }
        };
        this.pickerWindow = new SB.ColorPickerWindow(windowCfg);
    },

    destroy: function () {
        if (this.el && this.el.dom) {
            this.el.dom.onmousedown = '';
        }
        if (this.pickerWindow) {
            delete this.pickerWindow.animateTarget;
            this.pickerWindow.destroy();
            delete this.pickerWindow;
        }
        Ext.ux.form.ColorPickerField.superclass.destroy.apply(this, arguments);
    },

    showPicker: function () {
        if (this.pickerWindow.isVisible() || (this.pickerWindow.proxy && this.pickerWindow.proxy.hasActiveFx())) {
            return;
        }
        this.syncValue();
        var xy = this.wrap.getXY();
        xy[1] += this.wrap.getHeight();
        this.pickerWindow.setPagePosition(xy[0], xy[1]);
        this.pickerWindow.show(this.wrap.dom);
    }
});

Ext.reg('ux-colorpickerfield', Ext.ux.form.ColorPickerField);
