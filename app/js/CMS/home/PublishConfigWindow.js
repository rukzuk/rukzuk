Ext.ns('CMS.home');
/**
* @class       CMS.home.PublishConfigWindow
* @extends     Ext.Window
*
* Displays a window with a form to edit the publish config of a website.
*
*/
CMS.home.PublishConfigWindow = Ext.extend(Ext.Window, {

    /**
     * @cfg  websiteRecord
     * @type CMS.data.WebsiteRecord
     * The website to be edited
     */
    websiteRecord: null,

    initComponent: function () {
        Ext.apply(this, {
            title: CMS.i18n('Live-Server', 'publish.config.windowTitle'),
            width: 330,
            modal: true,
            resizable: false,
            close: this.closeHandler,
            items: {
                ref: 'typePanel',
                xtype: 'panel',
                items: [
                    // live hosting
                    {
                        title: CMS.i18n('rukzuk-Webhosting', 'publish.config.internalHosting'),
                        height: 275,
                        bodyStyle: 'padding:10px',
                        border: false,
                        hidden: true,
                        xtype: 'form',
                        ref: '../formPanelInternal',
                        labelAlign: 'top',
                        items: [
                            {
                                fieldLabel: '',
                                xtype: 'label',
                                style: 'display: inline-block; padding: 15px 4px;',
                                html: String.format(CMS.i18n('Hilfe: {0}', 'publish.config.cnameNotes'), CMS.config.connectDomainHelp)
                            },
                            {
                                xtype: 'fieldset',
                                defaultType: 'textfield',
                                defaults: {
                                    anchor: '100%'
                                },
                                items: [
                                    {
                                        xtype: 'displayfield',
                                        fieldLabel: CMS.i18n('rukzuk-Domain', 'publish.config.internalDomain'),
                                        name: '_domain'
                                    },
                                    {
                                        fieldLabel: CMS.i18n('Eigene Domain verbinden', 'publish.config.cnameDomain'),
                                        name: 'cname',
										validationEvent: 'blur',
										stripCharsRe: /(^http(s?):\/\/)|([:\/][^$]*)|([^a-zA-Z0-9.-])/g //this is additionally needed to filter chars added via copy&paste / drag&drop
									}
                                ]
                            },
                            {
                                fieldLabel: '',
                                xtype: 'label',
                                style: 'display: inline-block; padding: 15px 4px; font-weight: normal',
                                text: CMS.i18n(null, 'publish.config.changesApplyAfterRepublish')
                            }
                        ]
                    },
                    // custom FTP/SFTP
                    {
                        title: CMS.i18n('Externes Hosting', 'publish.config.externalHosting'),
                        height: 425,
                        bodyStyle: 'padding:10px',
                        border: false,
                        xtype: 'form',
                        ref: '../formPanelExternal',
                        labelAlign: 'top',
                        hidden: true,
                        items: [
                            {
                                fieldLabel: '',
                                xtype: 'label',
                                style: 'display: inline-block; padding: 15px 4px;',
                                html: String.format(CMS.i18n('Hilfe: {0}', 'publish.config.ftpNotes'), CMS.config.externalFtpHostingHelp)
                            },
                            {
                                xtype: 'fieldset',
                                defaultType: 'textfield',
                                defaults: {
                                    anchor: '100%'
                                },
                                items: [
                                    {
                                        fieldLabel: CMS.i18n('URL', 'publish.config.url'),
                                        name: 'url',
                                        emptyText: 'http://...'
                                    },
                                    {
                                        fieldLabel: CMS.i18n('Protokoll', 'publish.config.protocol'),
                                        name: 'protocol',
                                        xtype: 'combo',
                                        forceSelection: true,
                                        editable: false,
                                        mode: 'local',
                                        triggerAction: 'all',
                                        store: [
                                            ['ftp', CMS.i18n('FTP', 'publish.config.protocol.ftp')],
                                            ['sftp', CMS.i18n('SFTP', 'publish.config.protocol.sftp')]
                                        ],
                                        allowBlank: false
                                    },
                                    {
                                        fieldLabel: CMS.i18n('Host', 'publish.config.host'),
                                        name: 'host',
										validationEvent: 'blur',
										stripCharsRe: /(^http(s?):\/\/)|([:\/][^$]*)|([^a-zA-Z0-9.-])/g //this is additionally needed to filter chars added via copy&paste / drag&drop
                                    },
                                    {
                                        fieldLabel: CMS.i18n('Benutzername', 'publish.config.username'),
                                        name: 'username'
                                    },
                                    {
                                        fieldLabel: CMS.i18n('Passwort', 'publish.config.password'),
                                        inputType: 'password',
                                        name: 'password'
                                    },
                                    {
                                        fieldLabel: CMS.i18n('Basis-Verzeichnis', 'publish.config.basedir'),
                                        name: 'basedir'
                                    }
                                ]
                            }
                        ]
                    }
                ]
            },

            bbar: ['->', {
                text: CMS.i18n('Speichern'),
                ref: '../savebutton',
                cls: 'primary',
                iconCls: 'save',
                handler: this.saveHandler,
                scope: this
            }]
        });

        CMS.home.PublishConfigWindow.superclass.initComponent.call(this);

        this.preparePanel();

    },

    /**
     * Displays the correct panel and changes the global this.formPanel
     * @param {boolean} [newType]
     * @private
     */
    preparePanel: function (newType) {
        // make sure that internal is the default type
        var publishData = {};

        if (newType) {
            publishData.type = newType;
        } else {
            publishData = Ext.apply({}, this.websiteRecord.data.publish);
            if (!publishData.type) {
                publishData.type = 'internal';
            }
        }

        // make sure ftp is the default protocol
        if (!publishData.protocol) {
            publishData.protocol = 'ftp';
        }

        this.publishType = publishData.type;

        // fill the form panels with data
        this.formPanelExternal.getForm().reset();
        this.formPanelInternal.getForm().reset();

        this.formPanelExternal.getForm().setValues(publishData);
        this.formPanelInternal.getForm().setValues({
            // required fields
            'cname': publishData.cname,
            // display only
            '_domain': this.websiteRecord.data.publishInfo.internalDomain
        });

        this.formPanelInternal.hide();
        this.formPanelExternal.hide();

        // choose the form panel based on the mode
        if (publishData.type === 'internal') {
            this.formPanel = this.formPanelInternal;
        } else {
            this.formPanel = this.formPanelExternal;
        }
        this.formPanel.show();
    },

    /**
     * @private
     * Closes the whole window
     */
    closeHandler: function () {
        if (this.formPanel.getForm().isDirty()) {
            Ext.MessageBox.confirm(
                /* title -> */ CMS.i18n('Änderungen übernehmen?'),
                /* message -> */ CMS.i18n('Ungespeicherte Änderungen übernehmen?'),
                /* callback -> */ function (btnId) {
                    if (btnId == 'yes') {
                        this.saveHandler();
                    } else if (btnId == 'no') {
                        this.destroy();
                    }
                },
                /* scope -> */ this
            );
        } else {
            this.destroy();
        }
    },

    /**
     * The saveHandler, saves and closes the window (destroy)
     * @private
     */
    saveHandler: function () {
        // save and destroy after successful save
        this.saveAction(function () {
            this.destroy();
        });
    },

    /**
     * Save Action: sends the website object to the server
     * @param {Function} [cb] callback
     * @private
     */
    saveAction: function (cb) {
        // prepare data for xhr request
        var publishData = Ext.apply({}, this.formPanel.getForm().getFieldValues());

        // remove display only fields by name convention
        // (there is no way to have a Ext Field which does not provide its value via getFieldValues)
        Object.keys(publishData).forEach(function (k) {
            if (k.indexOf('_') === 0) {
                publishData[k] = undefined;
            }
        });

        // set the type
        publishData.type = this.publishType;

        var data = {
            id: this.websiteRecord.id,
            publish: publishData
        };


        CMS.app.trafficManager.sendRequest({
            action: 'editWebsite',
            data: data,
            modal: true,
            scope: this,
            success: function (response) {
                CMS.Message.toast(CMS.i18n('Live-Server-Konfiguration wurde bearbeitet.', 'publish.config.successToast'));

                this.websiteRecord.beginEdit();
                this.websiteRecord.set('publish', response.data.publish);
                this.websiteRecord.set('publishInfo', response.data.publishInfo);
                this.websiteRecord.endEdit();

                if (cb && cb.apply) {
                    cb.apply(this, arguments);
                }
            },
            failureTitle: CMS.i18n('Fehler beim Bearbeiten der Live-Server-Konfiguration', 'publish.config.failureTitle')
        }, this);
    }

});

Ext.reg('CMSpublishconfigwindow', CMS.home.PublishConfigWindow);
