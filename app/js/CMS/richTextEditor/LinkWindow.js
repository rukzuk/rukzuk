/*global tinymce*/
Ext.ns('CMS.richTextEditor');

/**
* @class CMS.richTextEditor.LinkWindow
* @extends Ext.Window
* A window containing a form to insert a link into a RichTextEditor
*/
CMS.richTextEditor.LinkWindow = Ext.extend(Ext.Window, {
    width: 400,
    modal: true,
    resizable: false,

    /**
    * @cfg {tinyMCE.Editor} editor
    */
    editor: null,

    /**
    * @cfg {String} websiteId
    */
    websiteId: null,

    originalValues: null,

    initComponent: function () {
        this.title = CMS.i18n('Link einfügen');
        this.items = {
            xtype: 'container',
            layout: 'form',
            cls: 'x-panel-body',
            style: {
                padding: '15px',
                border: 'none'
            },
            defaults: {
                anchor: '100%'
            },
            items: [{
                xtype: 'container',
                style: {
                    height: '230px'
                },
                items: [{
                    xtype: 'CMSradiofieldset',
                    checkboxName: 'linkType',
                    groupValue: CMS.config.rteLinkTypeHTMLAttributeValues.internalPage,
                    title: CMS.i18n('Interne Page'),
                    listeners: {
                        expand: this.fieldsetExpandHandler,
                        scope: this
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: [{
                        xtype: 'CMSinternallinkchooser',
                        noSelectionValue: '',
                        noSelectionText: CMS.i18n('(aktuelle Seite)'),
                        websiteId: this.websiteId,
                        hideLabel: true,
                        ref: '../internalLinkPageField'
                    }, {
                        xtype: 'textfield',
                        fieldLabel: CMS.i18n('Sprungmarke'),
                        emptyText: CMS.i18n('z.B. #sprungmarke'),
                        ref: '../../../internalLinkAnchorField'
                    }]
                }, {
                    xtype: 'CMSradiofieldset',
                    checkboxName: 'linkType',
                    groupValue: CMS.config.rteLinkTypeHTMLAttributeValues.external,
                    title: CMS.i18n('Externer Link'),
                    listeners: {
                        expand: this.fieldsetExpandHandler,
                        scope: this
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: {
                        xtype: 'textfield',
                        emptyText: 'http://…',
                        hideLabel: true,
                        ref: '../externalLinkField'
                    }
                }, {
                    xtype: 'CMSradiofieldset',
                    checkboxName: 'linkType',
                    groupValue: CMS.config.rteLinkTypeHTMLAttributeValues.internalMedia,
                    title: CMS.i18n('Interne Datei aus MediaDB'),
                    listeners: {
                        expand: this.fieldsetExpandHandler,
                        scope: this
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: [{
                        xtype: 'CMSclearablemediabutton',
                        isSelector: true,
                        text: CMS.i18n('Datei auswählen'),
                        websiteId: this.websiteId,
                        hideLabel: true,
                        ref: '../internalLinkMediaDBField'
                    }, {
                        xtype: 'checkbox',
                        boxLabel: CMS.i18n('„Speichern unter“-Dialog anzeigen'),
                        hideLabel: true,
                        ref: '../../../internalLinkMediaForceDownloadCheckbox',
                        value: true,
                        style: {
                            marginTop: '10px'
                        }
                    }]
                }, {
                    xtype: 'CMSradiofieldset',
                    checkboxName: 'linkType',
                    groupValue: CMS.config.rteLinkTypeHTMLAttributeValues.mail,
                    title: CMS.i18n('E-Mail'),
                    listeners: {
                        expand: this.fieldsetExpandHandler,
                        scope: this
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: {
                        xtype: 'textfield',
                        validator: CMS.config.validation.emailValidator,
                        invalidText: CMS.i18n('Keine gültige E-Mail-Adresse'),
                        emptyText: 'name@domain.de',
                        hideLabel: true,
                        ref: '../mailLinkField'
                    }
                }]
            }, {
                xtype: 'combo',
                fieldLabel: CMS.i18n('Ziel'),
                ref: '../targetField',
                lazyRender: true,
                editable: false,
                triggerAction: 'all',
                value: '',
                store: this.editor.settings.CMSsectionConfig.linkTargets || [
                    ['', CMS.i18n('Gleiches Fenster')],
                    ['_blank', CMS.i18n('Neues Fenster')]
                ]
            }, {
                xtype: 'textfield',
                fieldLabel: CMS.i18n('Titel'),
                ref: '../titleField'
            }]
        };

        this.buttonAlign = 'center';
        this.buttons = [{
            text: CMS.i18n('Übernehmen'),
            cls: 'primary',
            iconCls: 'ok',
            scope: this,
            handler: this.saveHandler
        }, {
            text: CMS.i18n('Abbrechen'),
            iconCls: 'cancel',
            scope: this,
            handler: this.close
        }];

        this.close = this.closeHandler;

        this.editor.settings.CMSsuspendFocus = true;

        CMS.richTextEditor.LinkWindow.superclass.initComponent.apply(this, arguments);

        this.on('afterrender', this.loadFromTinyMCE, this);
        this.on('destroy', function () {
            this.editor.focus();
            this.editor.settings.CMSsuspendFocus = false;
        });
    },

    focus: function () { //overriding focus()-method, otherwise it will steal the focus
        Ext.each(this.findByType('CMSradiofieldset'), function (oneFieldset) {
            if (!oneFieldset.collapsed) {
                oneFieldset.items.get(0).focus();
                return false;
            }
        });
    },

    fieldsetExpandHandler: function (panel) {
        Ext.each(this.findByType('CMSradiofieldset'), function (oneFieldset) {
            if (panel !== oneFieldset) {
                oneFieldset.collapse();
            }
        });
    },

    /**
    * @private
    */
    saveHandler: function () {
        this.updateToTinyMCE(this.generateValues());

        this.destroy();
    },

    /**
    * @private
    * Fetches the entered field values
    */
    generateValues: function () {
        var link;
        var linkType;
        var linkAnchor;
        var href;

        var configLinkTypes = CMS.config.rteLinkTypeHTMLAttributeValues;

        Ext.each(this.findByType('CMSradiofieldset'), function (oneFieldset) {
            if (!oneFieldset.collapsed) {
                linkType = oneFieldset.groupValue;
                var firstField = oneFieldset.items.get(0);
                link = firstField.getValue();
                if (link) {
                    link = link.trim();
                    if (linkType == configLinkTypes.internalMedia) {
                        if (this.internalLinkMediaForceDownloadCheckbox.getValue()) {
                            linkType = configLinkTypes.internalMediaDownload;
                        }
                    }
                }
                return false;
            }
        }, this);

        var params;
        switch (linkType) {
        case configLinkTypes.internalPage:
            linkAnchor = this.internalLinkAnchorField.getValue();
            if (Ext.isEmpty(link)) {
                href = linkAnchor;
            } else {
                params = CMS.app.trafficManager.createPostParams(Ext.apply({
                    pageId: link,
                    websiteId: this.websiteId
                }, CMS.config.params.previewPageById));
                href = Ext.urlAppend(CMS.config.urls.previewPageById, Ext.urlEncode(params)) + linkAnchor;
            }
            break;
        case configLinkTypes.internalMedia:
            if (!Ext.isEmpty(link)) {
                params = CMS.app.trafficManager.createPostParams(Ext.apply({
                    id: link,
                    websiteId: this.websiteId
                }, CMS.config.params.streamMedia));
                href = Ext.urlAppend(CMS.config.urls.streamMedia, Ext.urlEncode(params));
            }
            break;
        case configLinkTypes.internalMediaDownload:
            if (!Ext.isEmpty(link)) {
                params = CMS.app.trafficManager.createPostParams(Ext.apply({
                    id: link,
                    websiteId: this.websiteId
                }, CMS.config.params.downloadMedia));
                href = Ext.urlAppend(CMS.config.urls.downloadMedia, Ext.urlEncode(params));
            }
            break;
        case configLinkTypes.external:
            href = link;
            break;
        case configLinkTypes.mail:
            if (!Ext.isEmpty(link)) {
                href = 'mailto:' + link;
            }
            break;
        }

        var target = this.targetField.getValue();
        var title = this.titleField.getValue();

        return {
            link: link,
            linkType: linkType,
            linkAnchor: linkAnchor,
            href: href,
            target: target,
            title: title
        };
    },

    /**
    * @private
    * Code borrowed from TinyMCEs Link Plugin (tiny_mce/themes/advanced/js/link.js)
    */
    loadFromTinyMCE: function () {
        var ed = this.editor;
        var e = ed.dom.getParent(ed.selection.getNode(), 'A');
        var link;
        var linkType;

        var configLinkTypes = CMS.config.rteLinkTypeHTMLAttributeValues;

        if (e) {
            link = ed.dom.getAttrib(e, CMS.config.rteLinkHTMLAttribute);
            linkType = ed.dom.getAttrib(e, CMS.config.rteLinkTypeHTMLAttribute);

            this.internalLinkAnchorField.setValue(ed.dom.getAttrib(e, CMS.config.rteLinkAnchorHTMLAttribute));

            var target = ed.dom.getAttrib(e, 'target');
            if (target) {
                this.targetField.setValue(target);
            }
            this.titleField.setValue(ed.dom.getAttrib(e, 'title'));
        } else {
            linkType = configLinkTypes.internalPage;
        }

        if (linkType == configLinkTypes.internalMedia) {
            this.internalLinkMediaForceDownloadCheckbox.setValue(false);
        } else if (linkType == configLinkTypes.internalMediaDownload) {
            this.internalLinkMediaForceDownloadCheckbox.setValue(true);
            linkType = configLinkTypes.internalMedia;
        }

        Ext.each(this.findByType('CMSradiofieldset'), function (oneFieldset) {
            if (oneFieldset.groupValue == linkType) {
                oneFieldset.get(0).setValue(link);
                oneFieldset.expand();
                return false;
            }
        });

        //save original values for destroy-method
        this.originalValues = this.generateValues();
    },

    /**
    * @private
    * Code borrowed from TinyMCEs Link Plugin (tiny_mce/themes/advanced/js/link.js)
    */
    updateToTinyMCE: function (values) {
        var ed = this.editor;
        values.href = values.href ? values.href.replace(/ /g, '%20') : '';

        var e = ed.dom.getParent(ed.selection.getNode(), 'A');

        // Remove element if there is no href
        if (!values.href) {
            if (e) {
                var b = ed.selection.getBookmark();
                ed.dom.remove(e, 1);
                ed.selection.moveToBookmark(b);
                ed.execCommand('mceEndUndoLevel', false, null, {skip_focus: 1});
                return;
            }
        }

        var attributes = {
            href: values.href,
            title: values.title,
            target: values.target
        };
        attributes[CMS.config.rteLinkHTMLAttribute] = values.link;
        attributes[CMS.config.rteLinkTypeHTMLAttribute] = values.linkType;
        attributes[CMS.config.rteLinkAnchorHTMLAttribute] = values.linkAnchor;

        // Create new anchor elements
        if (e === null) {
            ed.getDoc().execCommand('unlink', false, null);
            ed.execCommand('mceInsertLink', false, '#mce_temp_url#', {skip_undo: 1, skip_focus: 1});

            tinymce.each(ed.dom.select('a'), function (n) {
                if (ed.dom.getAttrib(n, 'href') == '#mce_temp_url#') {
                    ed.dom.setAttribs(n, attributes);
                }
            });
        } else {
            ed.dom.setAttribs(e, attributes);
        }

        ed.execCommand('mceEndUndoLevel', false, null, {skip_focus: 1});
    },

    /**
    * @private
    * Destroy the editor windows if no unsaved changes are present
    * In case of unsaved changes, present a confirmation dialog to the user.
    */
    closeHandler: function () {
        var values = SB.json.sortedEncode(this.generateValues());
        var orgValues = SB.json.sortedEncode(this.originalValues);

        if (values != orgValues) {
            Ext.MessageBox.show({
                closable: false,
                title: CMS.i18n('Änderungen übernehmen?'),
                msg: CMS.i18n('Ungespeicherte Änderungen übernehmen?'),
                icon: Ext.Msg.QUESTION,
                buttons: {yes: true, no: true, cancel: true},
                fn: function (btnId) {
                    if (btnId === 'yes') {
                        this.saveHandler();
                    } else if (btnId === 'no') {
                        this.destroy();
                    }
                },
                scope: this
            });
        } else {
            this.destroy();
        }
    }
});

Ext.reg('CMSrichtexteditorlinkwindow', CMS.richTextEditor.LinkWindow);
