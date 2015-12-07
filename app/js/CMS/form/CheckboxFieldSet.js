Ext.ns('CMS.form');

/**
* @class CMS.form.CheckboxFieldSet
* A fieldset that can be used as a form element.
* It provides setValue and getValue methods
* @extends Ext.form.FieldSet
*/
CMS.form.CheckboxFieldSet = Ext.extend(Ext.form.FieldSet, {

    checkboxToggle: true,
    value: true,
    removeFieldLabel: true,

    initComponent: function () {
        if (this.removeFieldLabel) {
            this.fieldLabel = null;
        }
        CMS.form.CheckboxFieldSet.superclass.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * @property swallowChangeEvent
    * @type Boolean
    * Used to expand/collapse the fieldset without firing change event
    */
    swallowChangeEvent: false,

    setValue: function (checked) {
        this.value = this.rawValue = !!checked;

        if (!this.rendered) {
            this.collapsed = !this.value;
            return;
        }

        if (this.value) {
            this.expand();
        } else {
            this.collapse();
        }
    },

    getValue: function () {
        return this.value;
    },

    afterRender: function () {
        this.swallowChangeEvent = true;
        CMS.form.CheckboxFieldSet.superclass.afterRender.call(this);
        this.el.addClass('CMScheckboxfieldset');

        if (this.checkbox && this.header && this.headerAsText) {
            var span = this.header.child('span');
            span.wrap({
                tag: 'label',
                'for': this.checkbox.id
            });
        }
        this.setValue(this.getValue());
        this.swallowChangeEvent = false;
    },

    onCollapse: function () {
        CMS.form.CheckboxFieldSet.superclass.onCollapse.apply(this, arguments);
        this.value = false;
        if (!this.swallowChangeEvent) {
            this.fireEvent('change', this, false, true);
        }
    },

    onExpand: function () {
        CMS.form.CheckboxFieldSet.superclass.onExpand.apply(this, arguments);
        this.value = true;
        if (!this.swallowChangeEvent) {
            this.fireEvent('change', this, true, false);
        }
    }
});

CMS.form.CheckboxFieldSet.prototype.setRawValue = CMS.form.CheckboxFieldSet.prototype.setValue;
CMS.form.CheckboxFieldSet.prototype.getRawValue = CMS.form.CheckboxFieldSet.prototype.getValue;

Ext.reg('CMScheckboxfieldset', CMS.form.CheckboxFieldSet);
