Ext.ns('SB.form');

(function () {

    var ttClass = Ext.form.TwinTriggerField.prototype;
    var cbClass = Ext.form.ComboBox.prototype;

    /**
    * @class SB.form.TwinTriggerComboBox
    * @extends Ext.form.ComboBox
    * A combination of an {@link Ext.form.ComboBox} with an {@link Ext.form.TwinTriggerField}.
    * By default, the additional trigger button clears the field's input value.
    * See {@link Ext.form.TwinTriggerField} for additional documentation.
    */
    SB.form.TwinTriggerComboBox = Ext.extend(Ext.form.ComboBox, {

        cls: 'x-form-twin-trigger-box',

        /**
        * @cfg {String} trigger1Class See {@link Ext.form.TwinTriggerField#trigger1Class}
        */
        trigger1Class: 'x-form-clear-trigger',

        /**
        * @cfg {String} trigger2Class See {@link Ext.form.TwinTriggerField#trigger1Class}
        */
        trigger2Class:  cbClass.triggerClass,

        /**
        * @cfg {Boolean} hideTrigger1
        * <tt>true</tt> to hide the additional trigger. Defaults to <tt>false</tt>
        */
        hideTrigger1: false,

        initComponent: function () {
            cbClass.initComponent.apply(this, arguments);

            this.triggerConfig = {
                tag: 'span',
                cls: 'x-form-twin-triggers',
                cn: [{
                    tag: 'img',
                    src: Ext.BLANK_IMAGE_URL,
                    cls: 'x-form-trigger ' + this.trigger1Class
                }, {
                    tag: 'img',
                    src: Ext.BLANK_IMAGE_URL,
                    cls: 'x-form-trigger ' + this.trigger2Class
                }]
            };

            this.on('select', function () {
                this.getTrigger(0).show();
            }, this);
            if (!this.forceSelection) {
                this.enableKeyEvents = true;
                // should be 'keyup', but the latter is buggy
                // so let's fire on 'keydown' after a small delay
                // http://www.extjs.com/forum/showthread.php?p=217683
                this.on('keydown', function () {
                    if (this.getRawValue()) {
                        this.fireEvent('fill');
                        this.getTrigger(0).show();
                    } else {
                        this.fireEvent('clear', this);
                        this.store.clearFilter();
                        this.getTrigger(0).hide();
                    }
                }, this, { delay: 50 });
            }
        },

        clearValue: function () {
            if (this.rendered) {
                this.getTrigger(0).hide();
            }
            cbClass.clearValue.apply(this, arguments);
        },

        onRender: function () {
            cbClass.onRender.apply(this, arguments);
            if (!this.value) {
                this.getTrigger(0).hide();
            } else {
                this.getTrigger(0).show();
            }
        },

        setValue: function () {
            cbClass.setValue.apply(this, arguments);

            if (!this.rendered) {
                return;
            }

            if (this.value) {
                this.getTrigger(0).show();
            } else {
                this.getTrigger(0).hide();
            }
        },

        onTrigger1Click: function () {
            if (this.disabled) {
                return;
            }
            if (this.isExpanded()) {
                this.collapse();
            }
            this.clearValue();
            this.store.clearFilter();
            this.fireEvent('clear');
            this.fireEvent('select', this, null, null);
        },

        initTrigger: function () {
            var ts = this.trigger.select('.x-form-trigger', true);
            var triggerCount = ts.getCount() || 1;
            var wrapperWidth = 0;
            var triggerWidth = 0;
            var triggerField = this;
            ts.each(function (t, all, index) {
                var triggerIndex = 'Trigger' + (index + 1);
                t.hide = function () {
                    if (this.dom.style.display == 'none') {
                        return;
                    }
                    triggerCount--;
                    var w = triggerField.wrap.getWidth();
                    this.dom.style.display = 'none';
                    wrapperWidth = w || wrapperWidth;
                    triggerWidth = (triggerCount ? (triggerField.trigger.getWidth() / triggerCount) : 0) || triggerWidth;
                    triggerField.el.setWidth(wrapperWidth - triggerCount * triggerWidth);
                    this['hidden' + triggerIndex] = true;
                };
                t.show = function () {
                    if (this.dom.style.display != 'none') {
                        return;
                    }
                    triggerCount++;
                    var w = triggerField.wrap.getWidth();
                    this.dom.style.display = '';
                    wrapperWidth = w || wrapperWidth;
                    triggerWidth = triggerField.trigger.getWidth() / triggerCount || triggerWidth;
                    triggerField.el.setWidth(wrapperWidth - triggerCount * triggerWidth);
                    this['hidden' + triggerIndex] = false;
                };

                if (this['hide' + triggerIndex]) {
                    t.dom.style.display = 'none';
                    triggerCount--;
                    this['hidden' + triggerIndex] = true;
                }
                this.mon(t, 'click', this['on' + triggerIndex + 'Click'], this, {
                    preventDefault: true
                });
                t.addClassOnOver('x-form-trigger-over');
                t.addClassOnClick('x-form-trigger-click');
            }, this);
            this.triggers = ts.elements;
        }
    });

    Ext.override(SB.form.TwinTriggerComboBox, {
        getTrigger: ttClass.getTrigger,
        onTrigger2Click: cbClass.onTriggerClick
    });

})();

Ext.reg('sb-twintriggercombo', SB.form.TwinTriggerComboBox);
