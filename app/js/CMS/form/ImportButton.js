/**
* @class CMS.form.ImportButton
* @extends Ext.ux.form.BrowseButton
* A button for importing files. Upload will start immediately when a file is selected.
* Upon successful upload, the {@link #success} handler is called, otherwise the {@link #failure} handler
* NOTE: It is not possible to detect server errors (404, 500, etc.) using JavaScript, so the failure
* handler is only called when the server returns something.
* <a href="http://stackoverflow.com/questions/35240">http://stackoverflow.com/questions/35240</a>
* <a href="http://stackoverflow.com/questions/375710">http://stackoverflow.com/questions/375710</a>
*/
Ext.reg('CMSimportbutton', Ext.ns('CMS.form').ImportButton = Ext.extend(Ext.ux.form.FileUploadField, {

    buttonOnly: true,

    name: 'file',

    /**
    * @cfg {Function} success
    * Success handler for upload
    */
    success: Ext.emptyFn,

    /**
    * @cfg {Function} failure
    * Failure handler for upload
    */
    failure: Ext.emptyFn,

    /**
    * @cfg {String} failureTitle
    * Will be passed to {@link CMS.app.TrafficManager}'s <tt>sendRequest</tt> method
    */
    failureTitle: '',

    /**
    * @property siteId
    * @type String
    * The {CMS.data.WebsiteRecord}'s id that will be sent with the upload
    */
    siteId: '',

    /**
     * @cfg {String} allowedType
     * The allowed import type (see CMS.config.importTypes)
     */
    allowedType: undefined,

    /**
    * @cfg {Boolean} logFailure
    * Passed to TrafficManager to determine wether upload failures should be logged automatically. Defaults to <tt>true</tt>.
    * If set to <tt>false</tt>, the failure should be logged in <tt>failure</tt> callback.
    */
    logFailure: true,

    /**
    * @cfg {String} wrapperCls
    * (optional) An additional CSS class to be added to the wrapper div that Ext.ux.form.FileUploadField generates
    */
    wrapperCls: '',

    /**
    * @cfg {String} windowTitle
    * The title of the progress window.
    */
    windowTitle: '',

    initComponent: function () {
        this.buttonCfg = this.buttonCfg ||  { iconCls: 'x-btn-icon import' };
        if (this.iconCls) {
            this.buttonCfg.iconCls = (this.buttonCfg.iconCls || '') + ' ' + this.iconCls;
        }
        CMS.form.ImportButton.superclass.initComponent.apply(this, arguments);
        this.enableBubble();
        this.on('fileselected', function (self) {
            var clonedButton = new Ext.ux.form.FileUploadField();
            clonedButton.suspendEvents();

            // create dummy form, which is needed for AJAX-like submit
            var panel = new Ext.form.FormPanel({
                hidden: true,
                fileUpload: true,
                standardSubmit: false,
                clientValidation: false,
                items: clonedButton
            });
            panel.render(Ext.getBody());

            // move file input field over to form
            clonedButton.fileInput.remove();
            clonedButton.fileInput = clonedButton.wrap.appendChild(self.fileInput);

            var failureHandler;
            var successHandler = function () {
                self.frame = null;
                self.throbberWin.destroy();
                if (!self.cancelling) {
                    self.success.apply(self, arguments);
                }
                panel.destroy();
                panel = failureHandler = successHandler = null;
            };

            self.showThrobberWindow();

            failureHandler = function () {
                self.frame = null;
                self.throbberWin.destroy();
                if (!self.cancelling) {
                    self.failure.apply(self, arguments);
                }
                panel.destroy();
                panel = successHandler = failureHandler = null;
            };

            CMS.app.trafficManager.sendRequest({
                action: 'importFile',
                form: panel.getForm().getEl().dom,
                success: successHandler,
                failure: failureHandler,
                logFailure: this.logFailure,
                failureTitle: self.failureTitle,
                data: {
                    websiteId: self.siteId,
                    allowedType: self.allowedType
                }
            });
            // Ext.Ajax.doFormUpload() creates a hidden iframe, which is not reachable via API.
            var iframes = Ext.query('iframe.x-hidden');
            // It must be this one:
            self.frame = iframes[iframes.length - 1];

            // re-create file input field
            self.reset();
        });
    },

    showThrobberWindow: function () {
        var self = this;
        this.throbberWin = new Ext.Window({
            title: this.windowTitle || CMS.i18n('Importvorgang'),
            modal: true,
            closable: false,
            cls: 'CMSprogress CMSuploadprogress',
            width: 200,
            plain: true,
            border: false,
            items: {
                plain: true,
                border: false,
                html: '<span class="spinner">' + CMS.i18n('Datei wird hochgeladen') + '</span>'
            },
            buttons: [{
                text: CMS.i18n('Abbrechen'),
                iconCls: 'cancel',
                handler: this.cancelUpload,
                scope: this
            }],
            destroy: function () {
                Ext.Window.prototype.destroy.apply(this, arguments);
                self = null;
            }
        });
        this.throbberWin.show();
    },

    /**
    * Cancel the currently running upload, if any.
    */
    cancelUpload: function () {
        if (this.cancelling) {
            return;
        }
        this.cancelling = true;
        if (this.frame) {
            this.frame.src = 'blank.html'; // this should cancel the upload, see http://stackoverflow.com/a/3654202/27862
        }
        if (this.throbberWin) {
            this.throbberWin.destroy();
        }
        /**
        * @event cancel
        * Fired when the currently running upload is cancelled
        * @param {Object} this
        */
        this.fireEvent('cancel', this);
    },

    onRender: function () {
        CMS.form.ImportButton.superclass.onRender.apply(this, arguments);
        if (this.wrapperCls) {
            this.wrap.addClass(this.wrapperCls);
        }
    },

    destroy: function () {
        if (this.throbberWin) {
            this.throbberWin.destroy();
        }
        CMS.form.ImportButton.superclass.destroy.apply(this, arguments);
    }

}));
