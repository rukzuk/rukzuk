Ext.ns('SB');

/**
* @class SB.ColorPickerWindow
* @extends Ext.Window
* @requires SB.color
*/
SB.ColorPickerWindow = Ext.extend(Ext.Window, {
    resizable: false,
    draggable: false,
    closable: false,
    border: false,
    header: false,
    autoHeight: true,
    defaults: {
        margins: '0 0 2 2'
    },

    cls: 'colorpickerwindow',

    /**
    * @cfg {String} imgPath
    * An absolute or relative path to the required images (crosshair.gif, hs.png, blackoverlay.png, transpoverlay.png, hline.png)
    */
    imgPath: '../images',

    /**
    * @property value
    * The currently selected color
    */
    value: '',

    /**
    * @cfg {Boolean} returnColorNames
    * <tt>true</tt> to return named colors where possible, instead of returning a hex string
    * Defaults to <tt>true</tt>.
    */
    returnColorNames: true,

    /**
    * @cfg {Ext.Element} textDelegateElement
    * An element who's value property will be set whenever the user changes the color
    * This should be an input field
    */
    textDelegateElement: null,

    /**
    * @cfg {Ext.Element} colorDelegateElement
    * An element who's background color will be set whenever the user changes the color
    */
    colorDelegateElement: null,

    /**
    * @cfg {String} colorDelegateBg
    * If {@link colorDelegateElement} is set, this value determines the underlying element's background color.
    * This is used to calculate the resulting color's brightness in order to set text color.
    * Defaults to <tt>'white'</tt>
    */
    colorDelegateBg: 'white',

    /**
    * @cfg {Boolean} enableAlpha
    * <tt>true</tt> to enable the alpha picker. Defaults to <tt>false</tt>
    */
    enableAlpha: false,

    /**
    * @cfg {Boolean} dimPalette
    * <tt>true</tt> to dim the hue/saturation palette when the lightness slider is moved.
    * Defaults to <tt>false</tt>
    */
    dimPalette: true,

    defaultValue: {
        h: 0,
        s: 0,
        v: 1,
        a: 1
    },

    initComponent: function () {
        this.width = this.enableAlpha ? 428 : 404;
        this.layout = 'hbox';
        this.layoutConfig = {
            pack: 'start',
            align: 'stretchMax'
        };
        var self = this;
        this.items = [{
            xtype: 'box',
            style: 'border-width: 1px; background: url("' + this.imgPath + '/hs.png") no-repeat scroll left bottom',
            html: '<div style="display:block; background: transparent url(\'' + this.imgPath + '/crosshair.gif\') no-repeat scroll left top; width: 100%; height: 100%;"></div>',
            width: 362,
            height: 102,
            listeners: {
                afterrender: function (box) {
                    self.initDD('palette', box.el.dom.firstChild);
                }
            }
        }, {
            xtype: 'box',
            height: 102,
            border: false,
            plain: true,
            style: 'border-width: 1px;',
            html: '<div style="display:block; background: transparent url(\'' + this.imgPath + '/blackoverlay.png\') repeat-x scroll left top; width: 100%; height: 100%;">'
                    + '<div style="display:block; background: transparent url(\'' + this.imgPath + '/hline.png\') no-repeat scroll left top; width: 100%; height: 100%;"></div>'
                + '</div>',
            width: 14,
            margins: '0 10 0 10',
            listeners: {
                afterrender: function (box) {
                    self.initDD('vSlider', box.el.dom);
                }
            }
        }];
        if (this.enableAlpha) {
            this.items.push({
                xtype: 'box',
                height: 102,
                style: 'border-width: 1px;',
                border: false,
                plain: true,
                html: '<div style="display:block; background: transparent url(\'' + this.imgPath + '/transpoverlay.png\') repeat-x scroll left top; width: 100%; height: 100%;">'
                        + '<div style="display:block; background: transparent url(\'' + this.imgPath + '/hline.png\') no-repeat scroll left top; width: 100%; height: 100%;"></div>'
                    + '</div>',
                width: 14,
                listeners: {
                    afterrender: function (box) {
                        self.initDD('aSlider', box.el.dom);
                    }
                }
            });
        }

        if (this.colorDelegateBg) {
            this.colorDelegateBg = this.hsvToRgb(this.parseColor(this.colorDelegateBg));
        }
        SB.ColorPickerWindow.superclass.initComponent.apply(this, arguments);

        this.dimPalette = this.dimPalette && !Ext.isIE7 && !Ext.isIE8;

        this.on('afterrender', function () {
            this.setValue(this.value);
        }, this, {
            single: true
        });
    },

    initEvents: function () {
        this.manager = new Ext.WindowGroup();
        this.manager.zseed = 10000;
        SB.ColorPickerWindow.superclass.initEvents.apply(this, arguments);
    },

    /**
    * @private
    */
    initDD: function (key, element) {
        var el = this[key] = Ext.get(element);
        el.tracker = new Ext.dd.DragTracker({
            onBeforeStart: this.onBeforeDragStart.createDelegate(this, [el, null]),
            onStart: this.onDragStart.createDelegate(this, [el, null]),
            onDrag: this.onDrag.createDelegate(this, [el, null]),
            onEnd: this.onDragEnd.createDelegate(this, [el, null]),
            tolerance: -1,
            autoStart: false,
            destroy: function () {
                el = null;
                this.onBeforeStart = this.onStart = this.onDrag = this.onEnd = null;
                Ext.dd.DragTracker.prototype.destroy.call(this);
            }
        });
        el.tracker.initEl(el);
        el.addListener('click', function (e) {
            this.onBeforeDragStart(el, e);
            this.onDragStart(el, e);
            this.onDrag(el, e);
            this.onDragEnd(el, e);
        }, this);
        this.elementsRendered = true;
    },

    /**
    * @private
    */
    onBeforeDragStart: function (element, e) {
        if (element == this.palette) {
            element.dom.style.cursor = 'crosshair';
        } else {
            element.dom.style.cursor = 'n-resize';
        }
    },

    /**
    * @private
    */
    onDragStart: function (element, e) {
        this.setBgImage(element, false);
        var xy = element.getXY();
        var scroll = Ext.getBody().getScroll();
        this.repairXY = [xy[0] - scroll.left, xy[1] - scroll.top];
    },

    /**
    * @private
    */
    onDrag: function (element, e) {
        var xy = e ? e.getXY() : element.tracker.getXY();
        var h = xy[0] - this.repairXY[0];
        var s = xy[1] - this.repairXY[1];
        var setBg = false;
        if (h < 0) {
            setBg = true;
            h = 0;
        }
        if (h > 360) {
            setBg = true;
            h = 360;
        }
        if (s < 0) {
            setBg = true;
            s = 0;
        }
        if (s > 100) {
            setBg = true;
            s = 100;
        }
        if (element == this.palette) {
            this.value.s = s / 100;
            this.value.h = h;
        } else if (element == this.vSlider) {
            this.value.v = (100 - s) / 100;
            setBg = true;
        } else {
            this.value.a = (100 - s) / 100;
            setBg = true;
        }
        this.renderValue(setBg);
        if (!this.lastValue || this.value.h != this.lastValue.h || this.value.s != this.lastValue.s || this.value.v != this.lastValue.v || this.value.a != this.lastValue.a) {
            /**
            * @event change
            * Fired when the user selects a different color
            * @param {String} rgbaString A color definition that can be used as a CSS value
            * @param {Object} rawValue The value as an object containing <tt>r</tt>, <tt>g</tt>, <tt>b</tt> and <tt>a</tt> properties
            * @param {Object} rawValue The value as an object containing <tt>h</tt>, <tt>s</tt>, <tt>v</tt> and <tt>a</tt> properties
            */
            this.fireEvent('change', this.hsvaToCssString(this.value, this.returnColorNames), this.hsvaToRgba(this.value), this.value);
            this.lastValue = Ext.apply({}, this.value);
        }
    },

    /**
    * @private
    */
    onDragEnd: function (element) {
        this.setBgImage(element, true);
        element.dom.style.cursor = 'default';
    },

    setValue: function (value) {
        var newValue = value && this.normalizeColor(value);
        if (!newValue) {
            this.value = this.value || this.defaultValue;
        } else {
            if (!this.enableAlpha) {
                newValue.a = 1;
            }
            this.value = newValue;
        }
        this.renderValue(null);
    },

    // @private setBg
    renderValue: function (setBg) {
        if (this.colorDelegateElement) {
            var dom = this.colorDelegateElement.dom;
            dom.style.backgroundColor = this.hsvToCssString(this.value);
            if (dom.tagName.toLowerCase() == 'input') {
                var brightness = this.calculateBrightness(this.hsvaToRgba(this.value), this.colorDelegateBg);
                dom.style.color = (brightness < 0.7) ? 'white' : 'black';
            }
        }
        if (this.textDelegateElement) {
            this.textDelegateElement.dom.value = this.hsvaToCssString(this.value, this.returnColorNames);
        }
        if (!this.elementsRendered) {
            return;
        }
        this.setBgImage(this.palette, setBg);
        this.vSlider.dom.style.backgroundColor = this.hsvToCssString({
            h: this.value.h,
            s: this.value.s,
            v: 1
        });
        this.setBgImage(this.vSlider, true);
        if (this.enableAlpha) {
            this.aSlider.dom.style.backgroundColor = this.hsvaToCssString({
                h: this.value.h,
                s: this.value.s,
                v: this.value.v,
                a: 1
            });
            this.setBgImage(this.aSlider, true);
        }

        if (this.dimPalette) {
            this.palette.dom.style.backgroundColor = 'rgba(0,0,0,' + (1 - this.value.v) + ')';
        }
    },

    /**
    * @private
    */
    setBgImage: function (element, activate) {
        if (activate !== false) {
            var x;
            var y;
            var target = element.dom;
            switch (element) {
            case this.palette:
                x = this.value.h;
                y = 100 * this.value.s;
                x -= 7; // hotspot offset of crosshair
                y -= 7; // hotspot offset of crosshair
                break;
            case this.vSlider:
                x = 0;
                y = 100 * (1 - this.value.v);
                y -= 3; // offset of horizontal line
                target = target.firstChild.firstChild;
                break;
            case this.aSlider:
                x = 0;
                y = 100 * (1 - this.value.a);
                y -= 3; // offset of horizontal line
                target = target.firstChild.firstChild;
                break;
            }
            target.style.backgroundPosition = x + 'px ' + y + 'px';
        } else {
            element.dom.style.backgroundPosition = '-100px -100px';
        }
    },

    onRender: function () {
        SB.ColorPickerWindow.superclass.onRender.apply(this, arguments);
        this.el.addClass('x-window-notitle');
        this.setValue(this.value);
    },

    destroy: function () {
        if (this.proxy) { // open/close ghost
            this.proxy.stopFx();
        }
        if (this.elementsRendered) {
            this.palette.tracker.destroy();
            this.palette.tracker = null;
            this.palette.removeAllListeners();
            delete this.palette;
            this.vSlider.tracker.destroy();
            this.vSlider.tracker = null;
            this.vSlider.removeAllListeners();
            delete this.vSlider;
            if (this.enableAlpha) {
                this.aSlider.tracker.destroy();
                this.aSlider.tracker = null;
                this.aSlider.removeAllListeners();
                delete this.aSlider;
            }
        }
        SB.ColorPickerWindow.superclass.destroy.apply(this, arguments);
    },

    hide: function () {
        if (this.proxy) { // open/close ghost
            this.proxy.stopFx();
        }
        SB.ColorPickerWindow.superclass.hide.apply(this, arguments);
    },

    focus: Ext.emptyFn
});

/**
* This class inherits all methods from {@link SB.color}
* @method SB.color
*/
Ext.copyTo(SB.ColorPickerWindow.prototype, SB.color, SB.util.getKeys(SB.color));
