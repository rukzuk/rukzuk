Ext.ns('CMS.home');

/**
 * Window which will be shown if the user creates a new page
 * Fires 'pagecreated' Event which holds the server response
 * if the page has been created succesfully
 *
 * @class CMS.home.CreatePageWindow
 * @extends Ext.Window
 */
CMS.home.CreatePageWindow = Ext.extend(CMS.MainWindow, {
    /** @lends CMS.home.CreatePageWindow.prototype */

    cls: 'CMSinsertwindow CMSinsertpagewindow',
    modal: true,
    resizable: false,
    buttonAlign: 'center',

    /**
     * The currently opened website's id
     * @property websiteId
     * @type String
     */
    websiteId: '',

    /**
     * The page that should act as a parent/sibling to the new page
     * @property node
     * @type Ext.tree.TreeNode
     */
    node: null,

    layout: 'border',

    maxWidth: 800,

    /** @protected */
    initComponent: function () {
        this.addEvents(
            /**
             * Fired after successfully saving the new page
             *
             * @event
             * @name pagecreated
             * @param {Object} response The server respose of the create page request
             */
            'pagecreated'
        );
        this.parentNode = this.node && this.node.parentNode;

        var items = [{
            xtype: 'CMSfilteredtemplateselection',
            ref: 'templateChooser',
            region: 'west',
            websiteId: this.websiteId,
            title: CMS.i18n(null, 'createPageWindow.chooseLayoutHeaderTitle'),
            startWithDummyTpl: false,
            showInsertTemplates: false,
            width: 317,
            listeners: {
                select: this.templateSelected,
                scope: this
            }
        } ,{
            /**
             * The form panel with the page meta data form elements
             * @property
             * @name form
             * @type CMS.home.PagePropertyForm
             * @memberOf CMS.home.EditPagePropertiesWindow
             * @private
             */
            xtype: 'CMSpagepropertyform',
            style: 'background-color: rgba(0, 0, 0, 0.13);',
            ref: 'form',
            region: 'center',
            width: 500,
            websiteId: this.websiteId,
            listeners: {
                clientvalidation: this.validationHandler,
                CMSsubmitpageform: this.submitForm,
                scope: this
            }
        }];

        Ext.apply(this, {
            title: CMS.i18n('Neue Page erstellen'),
            items: items,
            buttons: [{
                text: CMS.i18n(null, 'createPageWindow.createBtn'),
                cls: 'primary',
                iconCls: 'ok',
                formBind: true,
                scope: this,
                handler: this.submitForm
            }, {
                text: CMS.i18n('Abbrechen'),
                iconCls: 'cancel',
                scope: this,
                handler: this.close
            }]
        });

        CMS.home.CreatePageWindow.superclass.initComponent.call(this);
        this.templateChooser.setWebsiteId(this.websiteId);
        this.mon(this.templateChooser.dataView, 'afterrender', function () {
            this.templateChooser.selectFirstTemplate();
        }, this, {single: true});
    },

    /**
     * Updates internal data and generated form
     * @param el
     * @param tplRecord
     */
    templateSelected: function (tplRecord) {
        if (!tplRecord) {
            this.form.resetForm();
            return;
        }
        this.templateId = tplRecord.get('id');
        this.pageType = tplRecord.get('pageType');
        this.form.setPageMetaData({
            websiteId: this.websiteId,
            inNavigation: true,
            date: Math.floor(Date.now() / 1000),
            pageType: tplRecord.get('pageType'),
        });
    },

    /**
     * Toggles the save button depending on the form valid state
     * @private
     */
    validationHandler: function (editor, valid) {
        var saveButton = this.buttons[0];
        saveButton.setDisabled(!valid);
    },

    /**
     * @private
     */
    submitForm: function () {
        if (!this.form.isValid()) {
            return;
        }

        var data = this.form.getActualValues();
        var insertBeforeId;
        var parentNode;

        if (this.position === 'child') {
            // add new page as a child of this.node
            parentNode = this.node;
        } else {
            // add new page as sibling of this.node (and after this.node)
            insertBeforeId = (this.node && this.node.nextSibling) ? this.node.nextSibling.id : null;
            parentNode = this.parentNode;
        }

        delete data.id;
        delete data.position;

        Ext.apply(data, {
            parentId: parentNode ? parentNode.id : 'root',
            pageType: this.pageType,
            templateId: this.templateId,
            websiteId: this.websiteId,
            insertBeforeId: insertBeforeId
        });

        CMS.app.trafficManager.sendRequest({
            action: 'insertPage',
            data: data,
            successCondition: 'data',
            success: function (response) {
                this.fireEvent('pagecreated', response);
                this.close();
            },
            failureTitle: CMS.i18n('Fehler beim anlegen einer neuen Page'),
            scope: this
        });
    }
});

Ext.reg('CMScreatepagewindow', CMS.home.CreatePageWindow);
