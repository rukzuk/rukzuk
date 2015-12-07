Ext.ns('CMS');

/**
* A window with a form that users can utilize for sending feedback
*/
CMS.FeedbackWindow = Ext.extend(Ext.Window, {
    modal: true,
    width: 500,
    height: 400,
    resizable: false,
    layout: 'fit',
    closable: false,
    padding: 10,

    initComponent: function () {
        this.items = {
            xtype: 'form',
            ref: 'formpanel',
            bodyStyle: 'background: transparent',
            monitorValid: true,
            border: false,
            defaults: {
                anchor: '-' + Ext.getPreciseScrollBarWidth()
            },
            labelWidth: 130,
            items: [{
                xtype: 'textfield',
                emptyText: CMS.i18n('Betreff'),
                name: 'subject',
                hideLabel: true,
                allowBlank: true
            }, {
                xtype: 'textarea',
                emptyText: CMS.i18n('Mitteilung') + '*',
                name: 'body',
                height: 250,
                hideLabel: true,
                allowBlank: false
            }, {
                xtype: 'textfield',
                name: 'email',
                fieldLabel: CMS.i18n('E-Mail für Rückfragen'),
                vtype: 'email',
                value: CMS.app.userInfo.get('email'),
                allowBlank: true
            }],
            buttons: [{
                xtype: 'label',
                text: CMS.i18n('{marker} = Pflichtfeld').replace('{marker}', '*')
            }, '->', {
                text: CMS.i18n('Abschicken'),
                iconCls: 'send',
                formBind: true,
                handler: this.sendHandler,
                scope: this
            }, {
                text: CMS.i18n('Schließen'),
                iconCls: 'cancel',
                handler: this.closeHandler,
                scope: this
            }],
            buttonAlign: 'left'
        };

        Ext.Window.prototype.initComponent.apply(this, arguments);
    },

    /**
    * @private
    * Handler for send button
    */
    sendHandler: function () {
        CMS.app.trafficManager.sendRequest({
            action: 'sendFeedback',
            data: this.gatherData(),
            success: function () {
                CMS.Message.toast(CMS.i18n('Vielen Dank'), CMS.i18n('Mitteilung wurde gesendet'));
            },
            failureTitle: CMS.i18n('Konnte Feedback nicht versenden')
        });
        this.destroy();
    },

    /**
    * @private
    * Handler for close button
    */
    closeHandler: function () {
        if (!this.formpanel.getForm().isValid()) {
            this.destroy();
        } else {
            Ext.MessageBox.confirm(CMS.i18n('Mitteilung senden?'), CMS.i18n('Die Mitteilung wurde noch nicht versandt. Soll sie jetzt versendet werden?'), function (btnId) {
                if (btnId == 'yes') {
                    this.sendHandler();
                } else {
                    this.destroy();
                }
            }, this);
        }
    },

    /**
    * @private
    */
    gatherData: function () {
        var result = this.formpanel.getForm().getFieldValues();
        result.errors = CMS.app.ErrorManager.getErrorHistory();
        result.userAgent = navigator.userAgent;
        result.platform = navigator.platform;
        return result;
    }
});
