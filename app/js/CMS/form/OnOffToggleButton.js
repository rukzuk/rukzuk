Ext.ns('CMS.form');

/**
 * On Off toggle button (similar to checkbox)
 * @class CMS.form.OnOffToggleButton
 * @extends Ext.Container
 */
CMS.form.OnOffToggleButton = Ext.extend(Ext.Container, {

    cls: 'CMSonofftogglebutton off',
    value: false,
    onCls: 'on',
    offCls: 'off',
    width: 88,
    autoEl: {
        html: '<div class="inner"></div>'
    },

    initComponent: function () {
        CMS.form.OnOffToggleButton.superclass.initComponent.apply(this, arguments);

        // initial value
        this.updateView(this.value);

        this.on('afterrender', function () {
            this.el.down('.inner').on('click', this.handleOnOffClick, this);
        }, this);
    },

    /**
     * @private
     */
    handleOnOffClick: function () {
        var newValue = !this.value;
        var oldValue = this.value;
        this.fireEvent('change', this, newValue, oldValue);
        this.setValue(newValue);
    },

    /**
     * @private
     */
    updateView: function (newValue) {
        if (this.rendered) {
            // set class or remove class
            this.removeClass(this.onCls);
            this.removeClass(this.offCls);
            var cls = newValue ? this.onCls : this.offCls;
            this.addClass(cls);
        } else {
            this.on('afterrender', function () {
                this.updateView(newValue);
            }, this);
        }
    },

    /**
     * Set the value
     * @param {Boolean} value
     * @override
     */
    setValue: function (value) {
        this.value = value;
        this.updateView(value);
    },

    /**
     * Get the value
     * @returns {boolean}
     */
    getValue: function () {
        return this.value;
    }

});

CMS.form.OnOffToggleButton.prototype.setRawValue = CMS.form.OnOffToggleButton.prototype.setValue;
CMS.form.OnOffToggleButton.prototype.getRawValue = CMS.form.OnOffToggleButton.prototype.getValue;

Ext.reg('CMSonofftogglebutton', CMS.form.OnOffToggleButton);
