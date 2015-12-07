Ext.ns('CMS');

/**
 * A window to edit the resolutions of a website.
 *
 * @class       CMS.IframeResolutionEditWindow
 * @extends     Ext.Window
 */
CMS.IframeResolutionEditWindow = Ext.extend(Ext.Window, {

    /**
     * The edited website
     * @property  websiteRecord
     * @type CMS.data.WebsiteRecord
     */
    websiteRecord: null,

    /**
     * A callback function which will be called after closing the window
     * @property callback
     * @type Function
     */
    callback: undefined,

    /**
     * The execution context of the callback function (defaults to the
     * current window instance)
     * @property scope
     * @type Object
     */
    scope: undefined,

    /**
     * Current iframe width
     */
    resolutionWidth: 0,

    /**
     * Merge data and dataDisabled with enabled = true/false flags
     * @param resolutions
     * @returns {Array.<T>}
     */
    convertResolutionData: function (resolutions) {
        var resolutionsData = SB.util.cloneObject(resolutions.data);

        resolutionsData = resolutionsData.map(function (i) {
            // use global enabled flag to keep legacy data working
            i.enabled = resolutions.enabled;
            return i;
        });


        var resolutionsDataDisabled = SB.util.cloneObject(resolutions.dataDisabled || []);
        if (resolutionsDataDisabled) {
            resolutionsDataDisabled = resolutionsDataDisabled.map(function (i) {
                i.enabled = false;
                return i;
            });
        }

        resolutionsData = resolutionsData.concat(resolutionsDataDisabled);

        // sort resolutions by width
        resolutionsData.sort(function (a, b) {
            var w = b.width - a.width;
            if(w !== 0) {
                return w;
            }
            // sort by name
            if(a.name < b.name) {
                return -1;
            }
            if(a.name > b.name) {
                return 1;
            }
            return 0;
        });

        return resolutionsData;
    },

    /**
     * Add default values to resolutionsData
     * @param {Object} resolutionsData
     * @param {Boolean} [disableAllDefaultResolutions]
     */
    addDefaultsToResolutionData: function (resolutionsData, disableAllDefaultResolutions) {
        // fill data with defaults
        var resolutionsDataIds = resolutionsData.map(function (i) {
            return i.id;
        });

        this.convertResolutionData(CMS.config.defaultWebsiteResolutions).forEach(function (defResData) {
            if (resolutionsDataIds.indexOf(defResData.id) < 0) {
                if (disableAllDefaultResolutions) {
                    defResData.enabled = false;
                }
                resolutionsData.push(defResData);
            }
        });
    },

    /** @protected */
    initComponent: function () {
        var resolutions = this.websiteRecord.get('resolutions') || CMS.config.defaultWebsiteResolutions;
        var resolutionsData = this.convertResolutionData(resolutions);
        // adds res4, res5, res6
        this.addDefaultsToResolutionData(resolutionsData, !resolutions.enabled);

        var resolutionFields = [];

        for (var i = 0; i < resolutionsData.length; i++) {
            resolutionFields.push({
                header: false,
                layout: 'hbox',
                resolutionData: resolutionsData[i],
                style: {
                    paddingTop: 20
                },
                items: [{
                    xtype: 'checkbox',
                    ref: 'enabledField',
                    value: resolutionsData[i].enabled,
                    margins: {top: 0, right: 4, bottom: 0, left: 0},
                    listeners: {
                        check: this.toggleEnabled,
                        scope: this,
                    }
                }, {
                    xtype: 'box',
                    autoEl: {
                        tag: 'div',
                        cls: 'inidcator-' + resolutionsData[i].id,
                        style: {
                            width: 19,
                            height: 20,
                            'margin-top': 1,
                        }
                    },
                }, {
                    xtype: 'textfield',
                    tooltip: CMS.i18n('Bezeichnung'),
                    ref: 'nameField',
                    value: resolutionsData[i].name,
                    flex: 1,
                    margins: {top: 0, right: 1, bottom: 0, left: 1},
                    disabled: !resolutionsData[i].enabled,
                }, {
                    xtype: 'numberfield',
                    tooltip: CMS.i18n('Breite'),
                    ref: 'widthField',
                    value: resolutionsData[i].width,
                    width: 50,
                    disabled: !resolutionsData[i].enabled,
                }, {
                    xtype: 'button',
                    handler: this.setCurrentResolutionButtonHandler,
                    ref: 'setCurrentResBtn',
                    cls: 'CMSbtnsmall lightButton',
                    text: '',
                    scope: this,
                    tooltip: String.format(CMS.i18n(null, 'iframeResolutionEditWindow.setCurrentResolutionTooltip'), this.resolutionWidth),
                    iconCls: 'currentRes',
                    disabled: !resolutionsData[i].enabled,
                    width: 20,
                    height: 23,
                    margins: {top: 0, right: 2, bottom: 0, left: 5},
                }]
            });
        }

        Ext.apply(this, {
            title: CMS.i18n('Auflösungen bearbeiten'),
            width: 330,
            bodyStyle: 'padding:15px 20px 35px 20px',
            modal: true,
            draggable: true,
            resizable: false,
            cls: 'resolutionEditForms',
            items: resolutionFields,
            buttons: [{
                text: CMS.i18n('Speichern'),
                ref: '../savebutton',
                cls: 'primary',
                iconCls: 'save',
                handler: this.saveHandler,
                scope: this
            }]
        });

        CMS.IframeResolutionEditWindow.superclass.initComponent.call(this);

        if (this.callback) {
            this.on('destroy', this.callback, this.scope || this);
        }
    },

    setCurrentResolutionButtonHandler: function (btn) {
        btn.ownerCt.widthField.setValue(this.resolutionWidth);
    },

    toggleEnabled: function (checkbox, checked) {
        if (checked) {
            checkbox.ownerCt.nameField.enable();
            checkbox.ownerCt.widthField.enable();
            checkbox.ownerCt.setCurrentResBtn.enable();
            if (checkbox.ownerCt.widthField.getValue() === 0) {
                checkbox.ownerCt.widthField.setValue(this.resolutionWidth);
            }
        } else {
            checkbox.ownerCt.nameField.disable();
            checkbox.ownerCt.widthField.disable();
            checkbox.ownerCt.setCurrentResBtn.disable();
        }
    },

    /**
     * The saveHandler sends the user object to the server and fires the
     * 'userEdited' event which will inform the CMS.userManagement.UserManagementPanel
     * about the edited user, passing the edited user along
     * @private
     */
    saveHandler: function () {
        var resolutionData = [];
        var resolutionDataDisabled = [];

        // update values of resolutions
        this.items.each(function (fieldset) {
            fieldset.resolutionData.width = fieldset.widthField.getValue();
            fieldset.resolutionData.name = fieldset.nameField.getValue();
            delete fieldset.resolutionData.enabled;
            if (fieldset.enabledField.getValue() === true) {
                resolutionData.push(fieldset.resolutionData);
            } else {
                resolutionDataDisabled.push(fieldset.resolutionData);
            }
        });

        // sort resolutions by width
        resolutionData.sort(function (a, b) {
            return b.width - a.width;
        });

        var resolutions = {
            enabled: (resolutionData.length > 0),
            data: resolutionData,
            dataDisabled: resolutionDataDisabled
        };

        CMS.app.trafficManager.sendRequest({
            action: 'editResolution',
            data: {
                id: this.websiteRecord.id,
                resolutions: resolutions
            },
            scope: this,
            success: function (response) {
                CMS.Message.toast(CMS.i18n('Auflösungen gespeichert'));

                this.websiteRecord.set('resolutions', resolutions);

                this.destroy();
            },
            failureTitle: CMS.i18n('Fehler beim Speichern der Auflösungen')
        }, this);
    }
});

Ext.reg('CMSiframeresolutioneditwindow', CMS.IframeResolutionEditWindow);
