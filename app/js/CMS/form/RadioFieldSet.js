Ext.ns('CMS.form');

/**
* @class CMS.form.RadioFieldSet
* A fieldset that can be used as a form element.
* It provides setValue and getValue methods
* @extends CMS.form.CheckboxFieldSet
*/
CMS.form.RadioFieldSet = Ext.extend(CMS.form.CheckboxFieldSet, {

    value: true,

    /**
    * @cfg {Boolean} setAdditionalCls
    * <tt>true</tt> to add the CSS class specified in {@link #additionalCls}. Defafults to <tt>false</tt>
    * This is useful to mix RadioFieldSets with what looks like normal radioboxes
    */
    setAdditionalCls: false,

    /**
    * @cfg {String} additionalCls
    * A string that is added to the component's CSS class if {@link #setAdditionalCls} is <tt>true</tt>
    */
    additionalCls: 'hideBox',

    /**
    * @cfg {Array} parentXtypes
    * The possible xtypes that this component may be used in.
    * This is required to make sure radioboxes from different components don't interfere with each other
    */
    parentXtypes: [
        'CMSformeditor',
        'CMStemplateuniteditor',
        'CMSpageuniteditor',
        'CMSrichtexteditorlinkwindow',
        'CMSrichtexteditorlinkwindow',
        'CMScreatetemplatesnippetwindow'
    ],

    initComponent: function () {

        // remove field Label
        this.fieldLabel = null;
        this.checkboxToggle = {
            tag: 'input',
            type: 'radio'
        };

        CMS.form.RadioFieldSet.superclass.initComponent.apply(this, arguments);
    },

    afterRender: function () {
        if (this.setAdditionalCls) {
            this.el.addClass(this.additionalCls);
        }
        this.el.addClass('CMSradiofieldset');
        CMS.form.RadioFieldSet.superclass.afterRender.call(this);
        this.el.removeClass('CMScheckboxfieldset');
        this.checkbox.dom.checked = (this.value === this.groupValue);
        var xtypes = this.parentXtypes;
        var l = xtypes.length;
        var owner = this.findParentBy(function (c) {
            for (var i = 0; i < l; i++) {
                if (c.isXType(xtypes[i])) {
                    return true;
                }
            }
            return false;
        });
        if (!owner) {
            console.warn('[RadioFieldSet] Must be contained in one of', this.parentXtypes, '. No such component found in parent hierarchy');
        }
        this.checkbox.dom.name = (this.checkboxName || this.id) + owner.id + '-radio';
    },

    setValue: function (value) {
        console.log('[RadioFieldSet] setValue', this, value);
        var oldValue = this.value;
        var expand = value == this.groupValue;
        this.value = this.rawValue = value;
        if (!this.rendered) {
            this.collapsed = !expand;
            return;
        }

        this.checkbox.dom.checked = expand;

        if (expand) {
            this.expand();
        } else {
            this.collapse();
        }
        if (!this.swallowChangeEvent) {
            this.fireEvent('change', this, value, oldValue);
        }
    },

    getValue: function () {
        return this.value;
    },

    onCheckClick: function () {
        this.setValue(this.groupValue);
    },

    onCollapse: Ext.form.FieldSet.prototype.onCollapse,
    onExpand: Ext.form.FieldSet.prototype.onExpand
});

CMS.form.RadioFieldSet.prototype.setRawValue = CMS.form.RadioFieldSet.prototype.setValue;
CMS.form.RadioFieldSet.prototype.getRawValue = CMS.form.RadioFieldSet.prototype.getValue;

Ext.reg('CMSradiofieldset', CMS.form.RadioFieldSet);
