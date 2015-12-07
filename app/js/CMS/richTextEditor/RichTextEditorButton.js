/*global tinymce*/
Ext.ns('CMS.richTextEditor');

/**
 * A button that opens up a TinyMCE editor window. Can be used as a form field
 * @class CMS.richTextEditor.RichTextEditorButton
 * @extends Ext.Container
 * @requires Ext.ux.TinyMCE
 */
CMS.richTextEditor.RichTextEditorButton = Ext.extend(Ext.Container, {
    /** @lends CMS.richTextEditor.RichTextEditorButton.prototype */

    /**
     * <tt>false</tt> to not show the current value as a text next to the button.
     * Defaults to <tt>true</tt>. (For use as a form field)
     * @property showText
     * @type Boolean
     */
    showText: false,

    /**
     * Reference to richTextEditorToolbar. MUST be passed when creating
     * @property toolbar
     * @type CMS.richTextEditor.RichTextEditorToolbar
     */
    toolbar: null,

    /**
     * MUST be passed when creating
     * @property websiteId
     * @type String
     */
    websiteId: null,

    /**
     * The id of the current page or template. idSuffix MAY be passed when creating
     * @property idSuffix
     * @type String
     */
    idSuffix: null,

    /**
     * The id of the current unit. recordId MAY be passed when creating
     * @property recordId
     * @type String
     */
    recordId: null,

    /**
     * The CMSvar name of the field. CMSvar MAY be passed when creating
     * @property CMSvar
     * @type String
     */
    CMSvar: null,

    /**
     * A config object which will be passed to the underlying TinyMCE instance
     * @property richTextEditorConfig
     * @type Object
     */
    richTextEditorConfig: {},

    tinyDoc: null,
    tinyWin: null,

    initComponent: function () {
        this.items = [{
            xtype: 'button',
            text: this.text,
            ref: 'button',
            handler: this.buttonHandler,
            scope: this
        }];
        if (this.showText) {
            this.items.unshift({
                xtype: 'label',
                ref: 'textEl',
                margins: '0 10',
                style: {
                    'line-height': '23px',
                    'font-size': '11px'
                },
                html: this.renderValue()
            });
        }

        CMS.richTextEditor.RichTextEditorButton.superclass.initComponent.apply(this, arguments);

        this.getRawValue = this.getValue;
        this.setRawValue = this.setValue;
        if (this.value) {
            this.setValue(this.value);
        }
    },

    setValue: function (value) {
        this.value = value;
        if (this.textEl) {
            this.textEl.setText(this.renderValue(), false);
        }

        this.fireEvent('change', this, value, null);
    },

    getValue: function () {
        return this.value;
    },

    /**
     * @private
     */
    renderValue: function () {
        var content = this.value;
        if (content === '' || content === undefined || content === null || !this.showText) {
            return CMS.i18n('(kein Text)');
        }
        return Ext.util.Format.ellipsis(Ext.util.Format.stripTags(content), 500);
    },

    /**
     * Handler for the button
     * @private
     */
    buttonHandler: function () {
        if (this.editorWin && !this.editorWin.isDestroyed) {
            this.editorWin.destroy();
            delete this.editorWin;
        } else {
            //this must be set because TinyMCE sets both to the iframe; keep references to reset later on
            if (typeof tinymce === "undefined") {
                console.warn('[RichTextEditorButton] tinymce object is not present');
                return;
            }
            var tinyDoc = tinymce.DOM.doc;
            var tinyWin = tinymce.DOM.win;
            tinymce.DOM.doc = window.document;
            tinymce.DOM.win = window;

            var toolbar = new CMS.richTextEditor.RichTextEditorToolbar();

            var richTextEditor = new CMS.richTextEditor.FallbackRichTextEditor({
                toolbar: toolbar,
                websiteId: this.websiteId,
                pageOrTemplateId: this.idSuffix
            });

            this.editorWin = new Ext.Window({
                close: this.closeWindow.createDelegate(this),
                layout: 'vbox',
                layoutConfig: {
                    align: 'stretch'
                },
                items: [toolbar, {
                    xtype: 'tinymce',
                    flex: 1,
                    value: this.value,
                    ref: 'tiny',
                    tinymceSettings: richTextEditor.generateTinyMCESettings(this.richTextEditorConfig, this.recordId, this.CMSvar)
                }],
                width: 582,
                height: 400,
                modal: true,
                buttons: [{
                    text: CMS.i18n('Übernehmen'),
                    cls: 'primary',
                    iconCls: 'ok',
                    scope: this,
                    handler: this.saveHandler
                }],
                listeners: {
                    destroy: function () {
                        //set tinyMCEs doc and win to old values, maybe this is not needed
                        tinymce.DOM.doc = tinyDoc;
                        tinymce.DOM.win = tinyWin;
                    }
                },
                focus: function () { //overriding focus()-method, otherwise it will steal the focus
                    this.tiny.focus();
                }
            });
            this.editorWin.show();
        }
    },

    /**
     * Handler for the save button
     * @private
     */
    saveHandler: function () {
        this.setValue(this.editorWin.tiny.getRawValue());
        this.editorWin.destroy();
    },

    /**
     * Destroy the editor windows if no unsaved changes are present
     * In case of unsaved changes, present a confirmation dialog to the user.
     * @private
     * @param {Object} cfg
     */
    closeWindow: function (cfg) {
        if (this.editorWin.tiny.isDirty()) {
            Ext.MessageBox.confirm(
                /* title -> */ CMS.i18n('Änderungen übernehmen?'),
                /* message -> */ CMS.i18n('Ungespeicherte Änderungen übernehmen?'),
                /* callback -> */ function (btnId) {
                    if (btnId == 'yes') {
                        this.saveHandler();
                    } else if (btnId == 'no') {
                        this.editorWin.destroy();
                    }
                },
                /* scope -> */ this
            );
        } else {
            this.editorWin.destroy();
        }
    }

});

Ext.reg('CMSrichtexteditorbutton', CMS.richTextEditor.RichTextEditorButton);
