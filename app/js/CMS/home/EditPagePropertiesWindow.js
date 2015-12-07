Ext.ns('CMS.home');

/**
 * Window which will be shown if the user creates a new page
 * Fires 'pagecreated' Event which holds the server response
 * if the page has been created succesfully
 *
 * @class CMS.home.EditPagePropertiesWindow
 * @extends Ext.Window
 */
CMS.home.EditPagePropertiesWindow = Ext.extend(CMS.MainWindow, {
    /** @lends CMS.home.EditPagePropertiesWindow.prototype */

    modal: true,
    resizable: false,
    maxWidth: 480,
    minWidth: 300,
    maxHeight: 650,
    minHeight: 500,
    buttonAlign: 'center',

    /**
     * The currenty opened website's id
     * @property websiteId
     * @type String
     */
    websiteId: null,

    /**
     * The currenty loaded page's id
     * @property pageId
     * @type String
     */
    pageId: null,

    /**
     * Set to <code>true</code> to show the page properties readonly
     * @property readonly
     * @type Boolean
     */
    readonly: false,

    /** @protected */
    initComponent: function () {
        this.addEvents(
            /**
             * Fired if the user aborts his meta data editing and wishes to cancel them
             *
             * @event
             * @name cancel
             * @param {Object} this The current CMS.home.EditPagePropertiesWindow instance
             */
            'cancel',

            /**
             * Fired after successfully changing and saving the page metadata
             *
             * @event
             * @name metadataupdated
             * @param {Object} data The new page metadata
             * @param {Object} this The current CMS.home.EditPagePropertiesWindow instance
             */
            'metadataupdated'
        );

        this.title = CMS.i18n('Seiteneigenschaften');
        this.items = [{
            /**
             * The form panel with the page meta data form elements
             * @property
             * @name form
             * @type CMS.home.PagePropertyForm
             * @memberOf CMS.home.EditPagePropertiesWindow
             * @private
             */
            xtype: 'CMSpagepropertyform',
            ref: 'form',
            disabled: true, // initially disabled until page meta data are loaded
            websiteId: this.websiteId,
            listeners: {
                clientvalidation: this.validationHandler,
                CMSsubmitpageform: this.submitMetaData,
                scope: this
            }
        }];

        this.buttons = [{
            text: CMS.i18n('Speichern'),
            iconCls: 'save',
            cls: 'primary',
            scope: this,
            handler: this.submitMetaData
        }, {
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            scope: this,
            handler: function () {
                this.fireEvent('cancel', this);
            }
        }];

        CMS.home.EditPagePropertiesWindow.superclass.initComponent.call(this);

        CMS.app.trafficManager.sendRequest({
            action: 'getPage',
            data: {
                id: this.pageId,
                websiteId: this.websiteId
            },
            success: function (response) {
                var tempRecord = new CMS.data.PageRecord(Ext.apply(response.data, {
                    websiteId: this.websiteId
                }));
                this.form.setPageMetaData(tempRecord.data);
                this.form.setDisabled(this.readonly);
            },
            scope: this,
            failureTitle: CMS.i18n('Fehler beim Laden der Page')
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
     * Submits the entered metadata and fires the 'metadataupdated' on success handler
     * @private
     */
    submitMetaData: function () {
        if (!this.form.isValid()) {
            //Form validation failed. aborting submit
            return;
        }

        var data = this.form.getActualValues();
        data.id = this.pageId;
        data.websiteId = this.websiteId;
        this.sendRequest(data);
    },

    /**
     * Actually sends the request for saving the given data
     * @private
     */
    sendRequest: function (data) {
        CMS.app.trafficManager.sendRequest({
            modal: true,
            action: 'editPageMeta',
            data: data,
            success: function (result) {
                CMS.Message.toast(CMS.i18n('Speichern erfolgreich.'));
                this.fireEvent('metadataupdated', data, this);
            },
            failure: function () {
                this.destroy();
            },
            failureTitle: CMS.i18n('Fehler beim Speichern.'),
            scope: this
        });
    }
});

