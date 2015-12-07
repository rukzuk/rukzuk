Ext.ns('CMS');
/**
* @class       CMS.PasswordChangeWindow
* @extends     Ext.Window
*
* The CMS.PasswordChangeWindow will be displayed whenever the user wants
* to set a new password.
*
*/
CMS.PasswordChangeWindow = Ext.extend(Ext.Window, {

    initComponent: function () {

        var formPanel = {
            bodyStyle: 'padding:15px',
            border: false,
            xtype: 'form',
            ref: 'formPanel',
            labelAlign: 'top',
            monitorValid: true,
            defaults: Ext.apply({
                xtype: 'textfield',
                inputType: 'password',
                width: '100%',
                allowBlank: false,
                listeners: {
                    valid: this.validHandler,
                    invalid: this.invalidHandler,
                    scope: this
                }
            }, CMS.config.validation.userPassword),
            items: [{
                xtype: 'label',
                text: CMS.i18n('Benutzer: {user}').replace('{user}', CMS.app.userInfo.get('email')),
                style: 'margin-bottom: 15px; display: block;'
            }, {
                fieldLabel: CMS.i18n('Bisheriges Passwort'),
                name: 'oldpassword',
                ref: '../oldpasswordfield'
            }, {
                fieldLabel: CMS.i18n('Neues Passwort'),
                name: 'password',
                ref: '../passwordfield'
            }, {
                fieldLabel: CMS.i18n('Neues Passwort bestätigen'),
                name: 'repeatpassword',
                ref: '../repeatpasswordfield',
                invalidText: CMS.i18n('Passwörter stimmen nicht überein.'),
                validateValue: this.validateRepeatedPassword.createDelegate(this)
            }]
        };

        Ext.apply(this, {
            title: CMS.i18n('Passwort ändern'),
            width: 330,
            modal: true,
            draggable: true,
            resizable: false,
            items: [formPanel],
            buttonAlign: 'center',
            buttons: [{
                text: CMS.i18n('Speichern'),
                ref: '../savebutton',
                cls: 'primary',
                iconCls: 'save savepassword',
                disabled: true,
                handler: this.saveHandler,
                scope: this
            }, {
                text: CMS.i18n('Abbrechen'),
                handler: this.closeHandler,
                iconCls: 'cancel',
                scope: this
            }]
        });

        CMS.PasswordChangeWindow.superclass.initComponent.call(this);

        //oldpasswordfield should get the focus
        this.defaultButton = this.oldpasswordfield;
    },

    /**
    * @private
    * Closes the whole CMS.EditUserWindow window
    */
    closeHandler: function () {
        this.close();
    },

    /**
    * @private
    * The validHandler function checks if the user has correctly filled in all
    * required fields. If this is the case, the next button is enabled
    */
    validHandler: function () {
        if (this.oldpasswordfield.isValid() && this.passwordfield.isValid() && this.repeatpasswordfield.isValid()) {
            this.savebutton.enable();
        }
    },

    /**
    * @private
    * If a field fires the invalid event, the next button is disabled
    */
    invalidHandler: function () {
        this.savebutton.disable();
    },

    /**
    * @private
    */
    saveHandler: function () {
        var data = {
            id: CMS.app.userInfo.get('id'),
            oldpassword: this.oldpasswordfield.getValue(),
            password: this.passwordfield.getValue()
        };

        CMS.app.trafficManager.sendRequest({
            action: 'changeUserPassword',
            data: data,
            scope: this,
            success: function () {
                CMS.Message.toast(CMS.i18n('Passwort wurde geändert.'));
                this.close();
            },
            failureTitle: CMS.i18n('Fehler beim Ändern des Passworts')
        }, this);
    },

    /**
    * @private
    * Validates the input of the repeat password field
    * by checking whether it is identical to the value
    * entered in the password field above
    * @param {String} value The input entered by the user
    * which is to be validated
    */
    validateRepeatedPassword: function (value) {
        if (this.passwordfield.getValue() === value) {
            return true;
        } else {
            // HACK: The second password field should
            // be marked invalid even if the
            // user changes the password in the first
            // password field
            this.repeatpasswordfield.preventMark = false;
            this.repeatpasswordfield.markInvalid();
            return false;
        }
    }
});

Ext.reg('CMSpasswordchangewindow', CMS.PasswordChangeWindow);
