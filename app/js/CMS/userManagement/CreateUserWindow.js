Ext.ns('CMS.userManagement');

/**
 * The CMS.userManagement.CreateUserWindow will be displayed whenever the user wants
 * to create new users. It will provide the following form fields:
 * - first name
 * - last name
 * - email address
 * - is super user
 *
 * @class       CMS.userManagement.CreateUserWindow
 * @extends     Ext.Window
 */
CMS.userManagement.CreateUserWindow = Ext.extend(Ext.Window, {
    /** @lends CMS.userManagement.CreateUserWindow.prototype */

    /**
     * The freshly created user
     * @property  selectedGroup
     * @type Ext.data.Record
     */
    userRecord: null,

    /**
     * The currently opened website's id; if null add to group functions are disabled
     * @property websiteId
     * @type {String}
     */
    websiteId: null,

    /** @protected */
    initComponent: function () {
        this.cls = (this.cls || '') + ' CMScreateuserwindow';

        var firstNameField = Ext.apply({
            /**
             * The input field for the user's first name
             *
             * @property
             * @name firstnamefield
             * @memberOf CMS.userManagement.CreateUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('Vorname'),
            name: 'firstname',
            width: '100%',
            ref: '../../firstnamefield',
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
             * @memberOf CMS.userManagement.CreateUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('Nachname'),
            name: 'lastname',
            width: '100%',
            ref: '../../lastnamefield',
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
             * @memberOf CMS.userManagement.CreateUserWindow
             * @type Ext.form.TextField
             * @private
             */
            fieldLabel: CMS.i18n('E-Mail-Adresse'),
            name: 'email',
            width: '100%',
            ref: '../../emailfield',
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
             * @name languagefield
             * @memberOf CMS.userManagement.CreateUserWindow
             * @type Ext.form.Combobox
             * @private
             */
            fieldLabel: CMS.i18n('Sprache'),
            name: 'language',
            xtype: 'combo',
            width: 290,
            forceSelection: true,
            editable: false,
            mode: 'local',
            triggerAction: 'all',
            ref: '../../languagefield',
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
            value: CMS.app.lang
        };

        var superUserField = {
            /**
             * The "superuser"-checkbox
             *
             * @property
             * @name superuserfield
             * @memberOf CMS.userManagement.CreateUserWindow
             * @type Ext.form.Checkbox
             * @private
             */
            xtype: 'checkbox',
            ref: '../../superuserfield',
            width: '100%',
            name: 'isSuperUser',
            boxLabel: CMS.i18n('ist Superuser'),
            hideLabel: true
        };

        var cardWizard = {
            border: false,
            layout: 'card',
            layoutConfig: {
                layoutOnCardChange: true
            },
            ref: 'card',
            height: 305,
            activeItem: 0,
            bodyStyle: 'padding:15px',
            defaults: {
                border: false
            },

            items: [{
                xtype: 'form',
                labelAlign: 'top',
                monitorValid: true,
                defaultType: 'textfield',
                items: [
                    firstNameField,
                    lastNameField,
                    emailField,
                    languageSelection,
                    superUserField
                ]
            }, {
                layout: 'vbox',
                layoutConfig: {
                    align: 'stretch'
                },
                items: [{
                    height: 23,
                    xtype: 'label',
                    cls: 'x-form-item',
                    ref: '../../labelfield'
                }, {
                    flex: 1,
                    xtype: 'CMSaddtogrouppanel',
                    ref: '../../groupPanel',
                    websiteId: this.websiteId,
                    hidden: !this.websiteId
                }, {
                    flex: 1,
                    xtype: 'displayfield',
                    value: CMS.i18n('Gruppenzuordnungen können pro Website vorgenommen werden (im Menü „Gruppen und Rechte“).'),
                    hidden: !!this.websiteId
                }]
            }]
        };

        Ext.apply(this, {
            title: CMS.i18n('Neuen Benutzer anlegen'),
            width: 330,
            modal: true,
            resizable: false,
            items: [
                cardWizard
            ],
            bbar: [{
                text: CMS.i18n('Zurück'),
                ref: '../backbutton',
                hidden: true,
                handler: this.backHandler,
                scope: this
            }, '->', {
                text: CMS.i18n('Weiter'),
                ref: '../nextbutton',
                cls: 'primary',
                disabled: true
            }]
        });

        CMS.userManagement.CreateUserWindow.superclass.initComponent.call(this);

        this.nextbutton.addListener('click', this.nextHandler, this);

        this.addEvents(
            /**
             * Fires when the user has clicked the save button
             * @event
             * @name userCreated
             * @param {CMS.userManagement.CreateUserWindow} this This CMS.userManagement.CreateUserWindow object
             * @param {Object} userRecord The newly created user
             */
            'userCreated'
        );
    },

    /**
     * The validHandler function checks if the user has correctly filled in all
     * required fields. If this is the case, the next button is enabled
     * @private
     */
    validHandler: function () {
        if (this.firstnamefield.isValid() && this.lastnamefield.isValid() && this.emailfield.isValid()) {
            this.nextbutton.enable();
        }
    },

    /**
     * If a field fires the invalid event, the next button is disabled
     * @private
     */
    invalidHandler: function () {
        this.nextbutton.disable();
    },

    /**
     * The nextHandler will display the second step of the user creation process.
     * Additionally the next button is transformed into a cute little save button.
     * The values the user has entered into the first step are now harvested and
     * a user object is created using the values.
     * Finally the back button is shown.
     * @private
     */
    nextHandler: function () {
        this.card.getLayout().setActiveItem(1);
        this.nextbutton.setText(CMS.i18n('Speichern'));
        this.nextbutton.setIconClass('save');

        this.userRecord = {
            email: this.emailfield.getValue(),
            lastname: this.lastnamefield.getValue(),
            firstname: this.firstnamefield.getValue(),
            superuser: this.superuserfield.getValue(),
            language: this.languagefield.getValue(),
            password: SB.util.UUID()
        };

        this.labelfield.setText(CMS.i18n('Benutzer: {user}').replace('{user}', this.userRecord.firstname + ' ' + this.userRecord.lastname));
        this.backbutton.show();
        this.nextbutton.removeListener('click', this.nextHandler, this);
        this.nextbutton.addListener('click', this.saveHandler, this);
    },

    /**
     * The backHandler will display the fist step of the user creation process again.
     * Additionally the cute little save button is regrettably
     * transformed back to a boring next button.
     * The listeners on the next button are switched again.
     * Finally the back button is hidden.
     * @private
     */
    backHandler: function () {
        this.backbutton.hide();
        this.card.getLayout().setActiveItem(0);
        this.nextbutton.setText(CMS.i18n('Weiter'));
        this.nextbutton.setIconClass('');
        this.nextbutton.removeListener('click', this.saveHandler, this);
        this.nextbutton.addListener('click', this.nextHandler, this);
        this.backbutton.hide();
    },

    /**
     * The saveHandler sends the user object to the server and fires the
     * 'userCreated' event which will inform the CMS.userManagement.UserManagementPanel
     * about the newly created user, passing the newly created user along
     * @private
     */
    saveHandler: function () {
        CMS.app.trafficManager.sendRequest({
            action: 'createUser',
            data: this.userRecord,
            scope: this,
            success: function (response) {
                this.userRecord.id = response.data.id;
                this.addGroups();
            },
            failureTitle: CMS.i18n('Fehler beim Erstellen des Benutzers')
        }, this);
    },

    /**
     * Adds the selected groups (if any) to the new user.
     * @private
     */
    addGroups: function () {
        var groups = Ext.pluck(this.groupPanel.groupGrid.getStore().getRange(), 'id');

        if (Ext.isEmpty(groups)) {
            this.finish();
        } else {
            CMS.app.trafficManager.sendRequest({
                action: 'addGroupsToUser',
                data: {
                    id: this.userRecord.id,
                    websiteId: this.websiteId,
                    groupIds: groups
                },
                scope: this,
                success: function () {
                    // reload group store
                    CMS.data.StoreManager.get('group', this.websiteId).reload();
                    this.finish();
                },
                failureTitle: CMS.i18n('Fehler beim Erstellen des Benutzers')
            }, this);
        }
    },

    /**
     * Show success message, fires event and closes the window.
     * @private
     */
    finish: function () {
        CMS.Message.toast(CMS.i18n('Benutzer wurde erstellt.'));
        this.fireEvent('userCreated', this, this.userRecord);
        this.close();
    },

    /** @private */
    focus: function () {
        this.firstnamefield.focus();
    }
});

Ext.reg('CMScreateuserwindow', CMS.userManagement.CreateUserWindow);
