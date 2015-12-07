Ext.ns('SB.form');

/**
* An image that can be used as a (read-only) form field
*/

SB.form.ImageField = Ext.extend(Ext.form.Field, {
    submitValue: false,

    value: Ext.BLANK_IMAGE_URL,
    width: 100,
    height: 100,

    initComponent: function () {
        SB.form.ImageField.superclass.initComponent.apply(this, arguments);
        this.autoCreate = {
            tag: 'img',
            alt: this.alt || '',
            src: this.value,
            style: 'width:' + this.width + 'px;height:' + this.height + 'px;' + this.style || '',
            cls: this.cls
        };
    },

    setValue: function (val) {
        this.value = val || Ext.BLANK_IMAGE_URL;
        if (this.rendered) {
            this.el.dom.src = this.value;
        } else {
            if (this.autoCreate) {
                this.autoCreate.src = this.value;
            }
        }
    }
});

Ext.reg('ux-imagefield', SB.form.ImageField);
