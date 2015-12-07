Ext.ns('CMS.userManagement');

/**
 * Panel for managing the user groups privileges which are not associated with single pages:
 * - publish websites
 * - create modules
 * - create templates
 *
 * @class CMS.userManagement.OtherPrivilegesPanel
 * @extends Ext.form.FormPanel
 */
CMS.userManagement.OtherPrivilegesPanel = Ext.extend(Ext.form.FormPanel, {
    /**
     * The currently opened website's id
     * @property websiteId
     * @type String
     */
    websiteId: '',

    /**
     * @cfg {CMS.data.UserGroupRecord} record
     * The userGroup that is being displayed
     */
    record: null,

    /**
     * An object which maps the different types of privileges
     * to boolean values
     * @property  mapRights
     * @type Object
     */
    mapRights: {
        website: {
            'true': 'publish',
            'false': 'none',
            publish: true,
            none: false
        },
        templates: {
            'true': 'all',
            'false': 'none',
            all: true,
            none: false
        },
        modules: {
            'true': 'all',
            'false': 'none',
            all: true,
            none: false
        },
        readlog: {
            'true': 'all',
            'false': 'none',
            all: true,
            none: false
        },
        colorscheme: {
            'true': 'all',
            'false': 'none',
            all: true,
            none: false
        }

    },

    initComponent: function () {
        this.header = true;
        this.border = false;
        this.title = CMS.i18n('Zugewiesene Benutzer dürfen:');
        this.fieldNames = SB.util.getKeys(this.mapRights);
        this.items = [{
            xtype: 'checkbox',
            name: 'website',
            labelStyle: 'width:250px',
            fieldLabel: CMS.i18n('Website publizieren')
        }, {
            xtype: 'checkbox',
            name: 'modules',
            labelStyle: 'width:250px',
            fieldLabel: CMS.i18n('Module erstellen/bearbeiten/löschen')
        }, {
            xtype: 'checkbox',
            name: 'templates',
            labelStyle: 'width:250px',
            fieldLabel: CMS.i18n('Templates erstellen/bearbeiten/löschen')
        }, {
            xtype: 'checkbox',
            name: 'colorscheme',
            labelStyle: 'width:250px',
            fieldLabel: CMS.i18n('Farbschema bearbeiten')
        }, {
            xtype: 'checkbox',
            name: 'readlog',
            labelStyle: 'width:250px',
            fieldLabel: CMS.i18n('Logdatei betrachten')
        }];
        this.bbar = ['->', {
            text: CMS.i18n('Speichern'),
            iconCls: 'save savegrouprights',
            cls: 'primary',
            scope: this,
            handler: this.onSavePrivileges
        }];

        CMS.userManagement.OtherPrivilegesPanel.superclass.initComponent.apply(this, arguments);

        this.setValues(this.record);
    },

    /**
    * @private
    * Called whenever the users clicks on the save button. The privilege properties of the
    * group objects are then updated based on the user input and the objects is then send to the server.
    */
    onSavePrivileges: function () {

        var data = {
            id: this.record.id,
            websiteId: this.websiteId,
            rights: this.createRightObjects()
        };
        CMS.app.trafficManager.sendRequest({
            action: 'editGroup',
            data: data,
            success: function (response) {
                CMS.Message.toast(CMS.i18n('Rechte wurden gespeichert'));
                this.record.data.rights = response.data.rights;
                this.items.each(function (item) { // reset dirty state of form fields
                    item.originalValue = item.getValue();
                });
            },
            successCondition: 'data.rights',
            failureTitle: CMS.i18n('Fehler beim Speichern der Rechte'),
            scope: this
        });
    },

    /**
    * @private
    * Creates an array of objects holding the group privileges for website, templates and modules
    * @param {Array} fieldNames An array containing all fieldNames which are displayed on the form.
    */
    createRightObjects: function () {
        var rights = [],
            form = this.getForm();

        Ext.each(this.fieldNames, function (fieldName) {
            rights.push({
                area: fieldName,
                privilege: this.mapRights[fieldName][form.findField(fieldName).getValue()],
                ids: []
            });
        }, this);
        return rights;
    },

    /**
    * @private
    * Sets the values of the group privileges contained in the UserGroupRecord object
    * @param {CMS.data.UserGroupRecord} record The userGroup that is being displayed
    */
    setValues: function (record) {
        if (!record || !record.data) {
            this.form.getForm().reset();
            return;
        }
        var rightsColl = new Ext.util.MixedCollection(),
            getPrivilege,
            self = this;

        rightsColl.addAll(record.data.rights);

        getPrivilege = function (area) {
            var tempColl = rightsColl.filterBy(function (x) {
                return x.area === area;
            });
            if (tempColl.length === 1) {
                return self.mapRights[area][tempColl.items[0].privilege];
            }
        };
        var formValues = {};
        Ext.each(this.fieldNames, function (fieldName) {
            formValues[fieldName] = getPrivilege(fieldName);
        });
        this.getForm().setValues(formValues);
        self = null;
    },

    /**
    * Resets the form to the initially loaded values.
    */
    refresh: function () {
        this.setValues(this.record);
    },

    /**
    * Checks whether the form is dirty.
    * @return Boolean <code>true</code> if the form is dirty, otherwise <code>false</code>
    */
    isDirty: function () {
        return this.form.isDirty();
    }
});

Ext.reg('CMSotherprivilegespanel', CMS.userManagement.OtherPrivilegesPanel);
