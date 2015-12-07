Ext.ns('CMS');

/**
* @class CMS.PromptWindow
* @extends Ext.Window
*
* Custom PromtDialog which has the following features
* - Allow textfield validation
* - Submit using the 'ENTER' key
* - Focus is automatically set to textfield
*/
CMS.PromptWindow = Ext.extend(Ext.Window, {

    /**
    * @cfg {String} title
    * Dialog title
    */
    title: '',

    /**
    * @cfg {String} (optional) value
    * Default value
    */
    value: '',

    /**
    * @cfg {String} msg
    * Message which describes the requtested text
    */
    msg: '',

    /**
    * @cfg {Object} callback
    * Callback config object
    * <pre><code>{
    fn: callback function
    scope: scope in which the callback is executed
}</code></pre>
    */
    callback: null,

    /**
    * @cfg {Object} (optional) fieldconfig
    * Additional textfield params (e.g. validator, regexp....)
    */
    fieldconfig: null,

    /**
    * @cfg {Boolean} (optional) selectOnFocus
    * True to automatically select text when displaying the dialog.
    * Default: true
    */
    selectOnFocus: true,

    /**
    * @property {Ext.form.TextField} textField
    * Holds a reference to the textfield
    */
    textField: null,


    initComponent: function () {
        this.fieldconfig = Ext.apply({
            selectOnFocus: this.selectOnFocus
        }, this.fieldconfig);

        this.textField = this.generateTextField();

        Ext.apply(this, {
            plain: true,
            border: false,
            autoCreate: true,
            resizable: false,
            constrain: true,
            constrainHeader: true,
            minimizable: false,
            maximizable: false,
            stateful: false,
            modal: true,
            shim: true,
            buttonAlign: 'center',
            layout: 'fit',
            width: 250,
            height: 150,
            minHeight: 80,
            footer: true,
            cls: 'x-window-dlg',
            items: [{
                xtype: 'form',
                border: false,
                unstyled: true,
                monitorValid: true,
                labelAlign: 'top',
                items: this.textField,
                listeners: {
                    scope: this,
                    clientvalidation: this.validationHandler
                }
            }],
            focusId: this.textField.id,
            fbar: this.generateToolBar(),
            closeAction: 'cancelDialog'
        });

        CMS.PromptWindow.superclass.initComponent.call(this);
    },

   /**
    * @private
    * activates/deactivates the 'ok' button if form becomes valid/invalid
    */
    validationHandler: function (editor, valid) {
        var okButton = this.getFooterToolbar().get(0);
        okButton.setDisabled(!valid);
    },


    /**
    * we need to extend the original focus method to allow
    * setting a element-id as focusId param which will be
    * focused if the window is displayed
    */
    focus: function () {
        this.focusEl = Ext.getCmp(this.focusId);
        CMS.PromptWindow.superclass.focus.call(this);
    },


    /**
    * deletes callback and fieldConfig properties
    */
    destroy: function () {
        CMS.PromptWindow.superclass.destroy.apply(this, arguments);
        delete this.callback;
        delete this.fieldconfig;
    },


    /**
    * @private
    *
    * Generates a TextField component with an attached specialkey listener
    * which allows submitting the dialog using the 'ENTER' key
    *
    * @return {Ext.form.TextField} TextField component
    */
    generateTextField: function () {
        return Ext.ComponentMgr.create(Ext.apply({
            xtype: 'textfield',
            name: 'textfield',
            width: '100%',
            cls: 'ext-mb-input',
            fieldLabel: this.msg,
            labelSeparator: '',
            value: this.value,
            listeners: {
                scope: this,
                specialkey: function (field, e) {
                    if (e.getKey() == e.ENTER) {
                        this.confirmDialog();
                    }
                }
            }
        }, this.fieldconfig));
    },

    /**
    * @private
    *
    * Generates a Ext.Toolbar component which contains a 'ok' and 'cancel' button
    *
    * @return {Ext.Toolbar} Ext.Toolbar component
    */
    generateToolBar: function () {
        return new Ext.Toolbar({
            defaults: {
                scope: this
            },
            items: [{
                text: CMS.i18n('OK'),
                handler: this.confirmDialog
            }, {
                text: CMS.i18n('Abbrechen'),
                handler: this.cancelDialog
            }],
            enableOverflow: false
        });
    },

    /**
    * @private
    *
    * Executes the callback function if set with the given param
    *
    * @param {String} status May be eighter 'ok' or 'cancel'
    */
    executeCallback: function (status) {
        //execute handler function
        if (this.callback && this.callback.fn) {
            this.callback.fn.call(this.callback.scope || window, status, this.textField.getValue(), this);
        }
    },

    /**
    * @private
    *
    * Calls the executeCallback method with 'ok' status if textField ist valid.
    * Ater that the dialog will be closed
    */
    confirmDialog: function () {
        //Check if form is valid
        if (!this.textField.isValid()) {
            return;
        }
        this.executeCallback('ok');
        this.destroy();
    },

    /**
    * @private
    *
    * Calls the executeCallback method with 'cancel' status.
    * Ater that the dialog will be closed
    */
    cancelDialog: function () {
        this.executeCallback('cancel');
        this.destroy();
    }
});

Ext.reg('CMSPromptWindow', CMS.PromptWindow);
