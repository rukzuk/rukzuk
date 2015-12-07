Ext.ns('CMS.userManagement');

/**
 * The CMS.userManagement.EditUserWindow will be displayed whenever the user wants
 * to edit existing users. It will provide the following form fields:
 * - first name
 * - last name
 * - email address
 * - super user
 *
 * @class       CMS.userManagement.EditUserWindow
 * @extends     Ext.Window
 */
CMS.userManagement.EditUserWindow = Ext.extend(Ext.Window, {
    /** @lends CMS.userManagement.EditUserWindow.prototype */

    /**
     * The edited user
     * @property userRecord
     * @type Ext.data.Record
     */
    userRecord: null,

    initComponent: function () {

        var firstNameField = Ext.apply({
            /**
             * The input field for the user's first name
             *
             * @property
             * @name firstnamefield
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('Vorname'),
            name: 'firstname',
            width: '100%',
            ref: '../firstnamefield',
            value: this.userRecord && this.userRecord.get('firstname') || '',
            allowBlank: false,
            listeners: {
                valid: this.validHandler,
                invalid: this.invalidHandler,
                scope: this
            }
        }, CMS.config.validation.userFirstName);

        var lastNameField = Ext.apply({
            /**
             * The input field for the user's last name
             *
             * @property
             * @name lastnamefield
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('Nachname'),
            name: 'lastname',
            value: this.userRecord && this.userRecord.get('lastname') || '',
            width: '100%',
            ref: '../lastnamefield',
            allowBlank: false,
            listeners: {
                valid: this.validHandler,
                invalid: this.invalidHandler,
                scope: this
            }
        }, CMS.config.validation.userLastName);

        var emailField = Ext.apply({
            /**
             * The input field for the user's email address
             *
             * @property
             * @name emailfield
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('E-Mail-Adresse'),
            name: 'email',
            value: this.userRecord && this.userRecord.get('email') || '',
            width: '100%',
            ref: '../emailfield',
            allowBlank: false,
            listeners: {
                valid: this.validHandler,
                invalid: this.invalidHandler,
                scope: this
            },
            validator: CMS.config.validation.emailValidator,
            invalidText: CMS.i18n('Keine gültige E-Mail-Adresse')
        }, CMS.config.validation.userEmail);

        var languageSelection = {
            /**
             * The language selection combobox
             *
             * @property
             * @name languageField
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.Combobox
             * @private
             */
            fieldLabel: CMS.i18n('Sprache'),
            name: 'language',
            xtype: 'combo',
            width: 300,
            forceSelection: true,
            editable: false,
            mode: 'local',
            triggerAction: 'all',
            ref: '../languageField',
            store: new Ext.data.ArrayStore({
                id: 0,
                fields: ['value', 'display'],
                data: CMS.language.available
            }),
            valueField: 'value',
            displayField: 'display',
            listeners: {
                select: this.validHandler,
                scope: this
            },
            value: this.userRecord && this.userRecord.get('language')
        };

        var passwordField = Ext.apply({
            /**
             * The input to enter the password
             *
             * @property
             * @name passwordfield
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('Passwort'),
            name: 'password',
            emptyText: '*****',
            inputType: 'password',
            width: '100%',
            ref: '../passwordfield',
            listeners: {
                valid: this.validHandler,
                invalid: this.invalidHandler,
                scope: this
            }
        }, CMS.config.validation.userPassword);

        var repeatPasswordField = Ext.apply({
            /**
             * The input field to repeat the password
             *
             * @property
             * @name repeatpasswordfield
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('Passwort bestätigen'),
            name: 'repeatpassword',
            emptyText: '*****',
            inputType: 'password',
            invalidText: CMS.i18n('Passwörter stimmen nicht überein.'),
            validateValue: this.validateRepeatedPassword.createDelegate(this),
            width: '100%',
            ref: '../repeatpasswordfield',
            listeners: {
                valid: this.validHandler,
                invalid: this.invalidHandler,
                scope: this
            }
        }, CMS.config.validation.userPassword);

        var superUserField = {
            /**
             * The "superuser"-checkbox
             *
             * @property
             * @name superuserfield
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.Checkbox
             * @private
             */
            xtype: 'checkbox',
            ref: '../superuserfield',
            width: '100%',
            name: 'superuser',
            value: this.userRecord && this.userRecord.get('superuser'),
            boxLabel: CMS.i18n('ist Superuser'),
            hideLabel: true,
            listeners: {
                check: this.validHandler,
                scope: this
            }
        };

        var formPanel = {
            /**
             * The internal form panel
             *
             * @property
             * @name formPanel
             * @memberOf CMS.userManagement.EditUserWindow
             * @type Ext.form.FormPanel
             * @private
             */
            bodyStyle: 'padding:15px',
            border: false,
            xtype: 'form',
            ref: 'formPanel',
            labelAlign: 'top',
            monitorValid: true,
            defaultType: 'textfield',
            items: [
                firstNameField,
                lastNameField,
                emailField,
                languageSelection,
                passwordField,
                repeatPasswordField,
                superUserField
            ]
        };

        Ext.apply(this, {
            title: CMS.i18n('Benutzer bearbeiten'),
            width: 330,
            modal: true,
            draggable: true,
            resizable: false,
            items: [formPanel],
            buttonAlign: 'center',
            buttons: [{
                /**
                 * The save button
                 *
                 * @property
                 * @name savebutton
                 * @memberOf CMS.userManagement.EditUserWindow
                 * @type Ext.Button
                 * @private
                 */
                text: CMS.i18n('Speichern'),
                ref: '../savebutton',
                cls: 'primary',
                iconCls: 'save',
                disabled: true
            }, {
                text: CMS.i18n('Abbrechen'),
                handler: this.closeHandler,
                iconCls: 'cancel',
                scope: this
            }]
        });

        CMS.userManagement.EditUserWindow.superclass.initComponent.call(this);

        this.savebutton.addListener('click', this.saveHandler, this);

        this.addEvents(
            /**
             * Fires when the user has clicked the save button
             * @event
             * @name userEdited
             * @param {CMS.userManagement.EditUserWindow} this This CMS.userManagement.EditUserWindow object
             * @param {Object} userRecord The edited user
             */
            'userEdited'
        );
    },

    /**
     * Closes the whole CMS.userManagement.EditUserWindow window
     * @private
     */
    closeHandler: function () {
        this.close();
    },

    /**
     * The validHandler function checks if the user has correctly filled in all
     * required fields. If this is the case, the next button is enabled
     * @private
     */
    validHandler: function () {
        if (this.firstnamefield.isValid()
                && this.lastnamefield.isValid()
                && this.emailfield.isValid()
                && this.passwordfield.isValid()
                && this.repeatpasswordfield.isValid()) {
            this.savebutton.enable();
        }
    },

    /**
     * If a field fires the invalid event, the next button is disabled
     * @private
     */
    invalidHandler: function () {
        this.savebutton.disable();
    },

    /**
     * The saveHandler sends the user object to the server and fires the
     * 'userEdited' event which will inform the CMS.userManagement.UserManagementPanel
     * about the edited user, passing the edited user along
     * @private
     */
    saveHandler: function () {
        var values = this.formPanel.getForm().getValues(); //using getValues since getFieldValues can't handle radio groups
        values.id = this.userRecord.id;
        values.superuser = values.superuser == 'on'; //HACK to fix wrong value of checkbox created by getForm().getValues()
        values.language = this.languageField.getValue(); // getForm().getValues() returns always the plain text
        //only send password if the user entered one
        if (values.password == this.passwordfield.emptyText) {
            delete values.password;
        }

        CMS.app.trafficManager.sendRequest({
            action: 'editUser',
            data: values,
            scope: this,
            success: function (response) {
                CMS.Message.info(
                    CMS.i18n('Benutzer wurde bearbeitet.'),
                    CMS.i18n('Alle Benutzer-Einstellungen wurden erfolgreich gespeichert. Bitte beachte, dass Änderungen der Sprache sich erst beim Nächten Login auswirken.')
                );
                this.userRecord.beginEdit();
                Ext.iterate(values, function (key, value) {
                    this.userRecord.set(key, value);
                }, this);
                this.userRecord.endEdit();
                this.close();
            },
            failureTitle: CMS.i18n('Fehler beim Bearbeiten des Benutzers')
        }, this);
    },

    /**
     * Validates the input of the repeat password field by checking whether it
     * is identical to the value entered in the password field above
     * @param {String} value The input entered by the user which is to be validated
     * @private
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

Ext.reg('CMSedituserwindow', CMS.userManagement.EditUserWindow);
