Ext.ns('CMS.moduleEditor');

/**
* @class CMS.moduleEditor.DeveloperInfo
* @extends Ext.Container
* Information for module developers like FTP login data
*/
CMS.moduleEditor.DeveloperInfo = Ext.extend(Ext.Container, {
    layout: 'form',
    cls: 'CMSmoduledeveloperinfo',

    initComponent: function () {

        this.defaults = {
            anchor: '100%'
        };

        this.webdavHost = window.location.protocol + '//' + window.location.host + '/dav';
        var user = CMS.app.userInfo.get('email');
        var formEditorLink = window.location.protocol + '//' + window.location.host + '/app/formeditor.html';

        this.items = [{
            xtype: 'ux-multilinelabel',
            hideLabel: true,
            autoLink: true,
            text: CMS.i18n(null, 'modulemanagementpanel.developerInfo.commentWebdav'),
            cls: 'commentWebdav'
        }, {
            xtype: 'textfield',
            fieldLabel: CMS.i18n(null, 'modulemanagementpanel.developerInfo.server'),
            cls: 'plain',
            readOnly: true,
            selectOnFocus: true,
            value: this.webdavHost
        }, {
            xtype: 'textfield',
            fieldLabel: CMS.i18n(null, 'modulemanagementpanel.developerInfo.username'),
            cls: 'plain',
            readOnly: true,
            selectOnFocus: true,
            value: user
        }, {
            xtype: 'displayfield',
            fieldLabel: CMS.i18n(null, 'modulemanagementpanel.developerInfo.password'),
            value: CMS.i18n(null, 'modulemanagementpanel.developerInfo.passwordValue')
        }, {
            xtype: 'textarea',
            fieldLabel: CMS.i18n(null, 'modulemanagementpanel.developerInfo.pathToModule'),
            cls: 'plain',
            readOnly: true,
            selectOnFocus: true,
            height: 50,
            value: '',
            ref: 'pathToModuleField'
        }, {
            xtype: 'ux-multilinelabel',
            hideLabel: true,
            autoLink: true,
            text: CMS.i18n(null, 'modulemanagementpanel.developerInfo.commentApi')
        }, {
            xtype: 'ux-multilinelabel',
            hideLabel: true,
            autoLink: true,
            text: '\n'+ CMS.i18n(null, 'modulemanagementpanel.developerInfo.commentFormEditor') + ' ' + formEditorLink
        }];

        CMS.moduleEditor.DeveloperInfo.superclass.initComponent.apply(this, arguments);

    },

    /**
    * Updates the module infos
    */
    loadData: function (data) {
        var pathToModule = this.webdavHost + '/' + data.websiteId + '/';
        this.pathToModuleField.setValue(pathToModule);
    }

});

Ext.reg('CMSmoduledeveloperinfo', CMS.moduleEditor.DeveloperInfo);
