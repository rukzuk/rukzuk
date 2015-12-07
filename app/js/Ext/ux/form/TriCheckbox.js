Ext.ns('Ext.ux.form');

Ext.ux.form.TriCheckbox = function (config) {
    Ext.ux.form.TriCheckbox.superclass.constructor.call(this, config);
};

Ext.extend(Ext.ux.form.TriCheckbox, Ext.form.Checkbox, {
    checkboxCls: 'x-checkbox',

    values: [null, true, false],

    checkedCls: ['x-checkbox-grayed', 'x-checkbox-checked', null],

    cbFocusCls: 'x-checkbox-focus',

    cbOverCls: 'x-checkbox-over',

    cbDownCls: 'x-checkbox-down',

    cbDisabledCls: 'x-checkbox-disabled',

    defaultAutoCreate: {
        tag: 'input',
        type: 'hidden',
        autocomplete: 'off'
    },

    onRender: function (ct, position) {
        Ext.ux.form.TriCheckbox.superclass.onRender.call(this, ct, position);
        this.wrap = this.el.wrap({
            cls: 'x-form-check-wrap x-form-tricheck-wrap'
        });
        this.checkbox = this.wrap.createChild({
            tag: 'img',
            src: Ext.BLANK_IMAGE_URL,
            cls: this.checkboxCls
        }, this.el);
        this.updateCheckCls();
    },

    initEvents: function () {
        Ext.ux.form.TriCheckbox.superclass.initEvents.call(this);

        this.checkbox.addClassOnOver(this.cbOverCls);
        this.checkbox.addClassOnClick(this.cbDownCls);
        this.checkbox.on('click', this.toggle, this);

        var label = Ext.DomQuery.selectNode('.x-form-tricheck-wrap ~ label[for=' + this.id + ']');
        if (label) {
            Ext.fly(label).on('click', this.toggle, this);
        }
    },

    onDisable: function () {
        Ext.ux.form.TriCheckbox.superclass.onDisable.call(this);
        this.checkbox.addClass(this.cbDisabledCls);
    },

    onEnable: function () {
        Ext.ux.form.TriCheckbox.superclass.onDisable.call(this);
        this.checkbox.removeClass(this.cbDisabledCls);
    },

    onFocus: function (e) {
        Ext.ux.form.TriCheckbox.superclass.onFocus.call(this, e);
        this.checkbox.addClass(this.cbFocusCls);
    },

    onBlur: function (e) {
        Ext.ux.form.TriCheckbox.superclass.onBlur.call(this, e);
        this.checkbox.removeClass(this.cbFocusCls);
    },

    setValue: function (v, suppressEvent) {
        if (!suppressEvent && this.value !== v) {
            this.fireEvent('check', this, v);
            if (this.handler) {
                this.handler.call(this.scope || this, this, v);
            }
        }
        Ext.form.Checkbox.superclass.setValue.call(this, v);
        this.updateCheckCls();
    },

    getCheckIndex: function () {
        for (var i = 0; i < this.values.length; i++) {
            if (this.value === this.values[i]) {
                return i;
            }
        }
        return 0;
    },

    updateCheckCls: function () {
        if (!this.wrap) {
            return;
        }
        var cls = this.checkedCls[this.getCheckIndex()];
        this.wrap.replaceClass(this._checkCls, cls);
        this._checkCls = cls;
    },

    toggle: function () {
        if (this.value === true) {
            this.setValue(false);
        } else {
            this.setValue(true);
        }
    }
});

Ext.reg('ux-tricheckbox', Ext.ux.form.TriCheckbox);
