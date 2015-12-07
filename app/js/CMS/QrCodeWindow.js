/*global QRCode:true*/
Ext.ns('CMS');
/**
* @class       CMS.QrCodeWindow
* @extends     Ext.Window
*
* A window to show a QrCode of a specific page/template.
*/
CMS.QrCodeWindow = Ext.extend(Ext.Window, {

    /**
    * @cfg  websiteId
    * @type String
    * The edited website
    */
    websiteId: null,

    /**
    * @cfg  mode
    * @type String
    * Whether its a page or template
    */
    mode: null,

    /**
    * @cfg  recordId
    * @type String
    * The id of the page or template
    */
    recordId: null,

    // how long the ticket should be valid (in seconds)
    ticketLifetime: 60 * 60,

    // when the ticket ticket should be refreshed (in seconds)
    refreshBeforeTicketLifetime: 1 * 60,

    // how often the ticket can be used (there is no infinity number; set it to a big number)
    remainingCalls: 999,


    initComponent: function () {
        Ext.apply(this, {
            title: CMS.i18n('Vorschau auf mobilem Gerät'),
            cls: 'CMSqrcodewindow',
            modal: true,
            draggable: true,
            resizable: false,
            items: [{
                xtype: 'container',
                cls: 'CMSqrcodecontainer',
                items: {
                    xtype: 'container',
                    ref: '../qrCodeElement',
                    cls: 'CMSqrcodeelement'
                }
            }, {
                xtype: 'container',
                layout: 'form',
                labelAlign: 'top',
                items: {
                    xtype: 'textfield',
                    fieldLabel: CMS.i18n('Vorschau-Link zum Kopieren ({lifetime} Minuten gültig)').replace('{lifetime}', this.ticketLifetime / 60),
                    ref: '../urlField',
                    readOnly: true,
                    selectOnFocus: true
                }
            }]
        });

        CMS.QrCodeWindow.superclass.initComponent.call(this);

        this.previewTicketHelper = new CMS.app.PreviewTicketHelper();
        this.requestPreviewTicket();
    },

    // overriding focus()-method, set focus to urlField
    focus: function () {
        this.urlField.focus(true);
    },

    /**
     * @private
     * Requests a new preview ticket
     */
    requestPreviewTicket: function () {
        window.clearTimeout(this.previewTicketRefreshTimeout);

        this.previewTicketHelper.createPreviewTicket(this.websiteId, this.mode, this.recordId, this.updateView, {
            protect: false,
            ticketLifetime: this.ticketLifetime,
            remainingCalls: this.remainingCalls
        }, this);
    },

    /**
     * @private
     * Updates the view (qrCode and urlField) after a new ticket was received. Also starts a timeout to
     * refresh the ticket before it expires.
     * @param {Object} ticket
     */
    updateView: function (ticket) {
        // generate the qrCode
        if (!this.qrCode) {
            this.qrCode = new QRCode(this.qrCodeElement.el.dom, {
                text: ticket.url,
                width: 310,
                height: 310,
                colorDark: '#2B2928',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.L // can be: L,M,Q,H
            });
        } else {
            this.qrCode.makeCode(ticket.url);
        }

        // update the url field
        this.urlField.setValue(ticket.url);
        this.urlField.focus(true);

        // request a new ticket before the current one expires
        var self = this;
        this.previewTicketRefreshTimeout = window.setTimeout(function () {
            self.requestPreviewTicket();
            self = null;
        }, (ticket.ticketLifetime - this.refreshBeforeTicketLifetime) * 1000);
    },

    destroy: function () {
        window.clearTimeout(this.previewTicketRefreshTimeout);

        this.previewTicketHelper.destroy();
        this.previewTicketHelper = null;
        this.qrCode = null;

        CMS.QrCodeWindow.superclass.destroy.call(this);
    }
});

Ext.reg('CMSqrcodewindow', CMS.QrCodeWindow);
