/*global tinymce:true, tinyMCE:true*/
Ext.ns('CMS.richTextEditor');

/**
* @class CMS.richTextEditor.InlineRichTextEditor
* @extends CMS.richTextEditor.RichTextEditor
*/
CMS.richTextEditor.InlineRichTextEditor = Ext.extend(CMS.richTextEditor.RichTextEditor, {
    /**
    * @property editor
    * @type tinyMCE.Editor
    */
    editor: null,

    /**
    * @property {SB.Element.iFrame} frame
    * Reference to frame MUST be passed when creating InlineEditor obj
    */
    frame: null,

    /**
    * @constructor
    * @param {SB.TheIframe} frame Passed by the documentloaded event.
    */
    constructor: function (args) {
        this.toolbarWindow = new Ext.Window({
            resizable: false,
            cls: 'CMSrtetoolbarwindow',
            closable: false,
            onEsc: Ext.emptyFn,
            height: 24,
            items: {
                xtype: 'CMSrichtexteditortoolbar',
                ref: 'rteToolbar',
                width: 582,
                style: {
                    marginLeft: 'calc(50% - 291px)'
                }
            },
            listeners: {
                beforeshow: function (win) {
                    // The Ext.Window is focused (defered) on show. This hack
                    // hack to avoid killing the RTE by show the window
                    win.focusEl.focus = Ext.emptyFn;
                }
            }
        });
        this.toolbar = this.toolbarWindow.rteToolbar;

        CMS.richTextEditor.InlineRichTextEditor.superclass.constructor.call(this, args);

        this.addEvents('CMSinlinesectionchanged', 'CMSinlinesectionblurred');
    },

    /**
    * @private
    * Returns additional TinyMCE settings which will be merged with {@link #getTinyMCEConfigDefaults} later on
    * @return {Object} TinyMCE settings
    */
    getTinyMCEConfig: function () {
        var rte = this;
        return {
            content_document: rte.frame.getFrameDocument(),
            content_window: rte.frame.getFrameWindow(),
            window: rte.frame.getFrameWindow(),
            render_ui: false,
            content_editable: true
        };
    },

    /**
    * @private
    * This method will be called after each TinyMCE is initialized. Use this e.g. to add custom event listeners.
    * @param {tinyMCE.Editor} editor
    */
    onTinyMCESetup: function (editor) {
        var rte = this;

        //add own windowManager to use Ext.Window instead of a popup
        //code borrowed from Ext.ux.TinyMCE
        var WindowManager = Ext.extend(tinymce.WindowManager, {
            isActive: false,

            constructor: function (cfg) {
                WindowManager.superclass.constructor.call(this, cfg.editor);

                // Set window group
                this.manager = cfg.manager;
            },

            alert: function (txt, cb, s) {
                Ext.MessageBox.alert('', txt, function () {
                    if (!Ext.isEmpty(cb)) {
                        cb.call(this);
                    }
                }, s);
            },

            confirm: function (txt, cb, s) {
                Ext.MessageBox.confirm('', txt, function (btn) {
                    if (!Ext.isEmpty(cb)) {
                        cb.call(this, btn == 'yes');
                    }
                }, s);
            },

            open: function (s, p) {
                this.isActive = true;

                s = s || {};
                p = p || {};

                if (!s.type) {
                    this.bookmark = editor.selection.getBookmark('simple');
                }

                s.width = parseInt((s.width || 320), 10);
                s.height = parseInt((s.height || 240), 10) + (tinymce.isIE ? 8 : 0);
                s.min_width = parseInt((s.min_width || 150), 10);
                s.min_height = parseInt((s.min_height || 100), 10);
                s.max_width = parseInt((s.max_width || 2000), 10);
                s.max_height = parseInt((s.max_height || 2000), 10);
                s.movable = true;
                s.resizable = true;
                p.mce_width = s.width;
                p.mce_height = s.height;
                p.mce_inline = true;

                this.features = s;
                this.params = p;

                var win = new Ext.Window({
                    title: s.name,
                    width: s.width,
                    height: s.height,
                    minWidth: s.min_width,
                    minHeight: s.min_height,
                    resizable: true,
                    maximizable: s.maximizable,
                    minimizable: s.minimizable,
                    modal: true,
                    stateful: false,
                    constrain: true,
                    manager: this.manager,
                    layout: 'fit',
                    items: [new Ext.BoxComponent({
                        autoEl: {
                            tag: 'iframe',
                            src: s.url || s.file
                        },
                        style: 'border-width: 0px;'
                    })]
                });

                p.mce_window_id = win.getId();

                win.show(null, function () {
                    if (s.left && s.top) {
                        win.setPagePosition(s.left, s.top);
                    }
                    var pos = win.getPosition();
                    s.left = pos[0];
                    s.top = pos[1];
                    this.onOpen.dispatch(this, s, p);
                }, this);

                return win;
            },

            close: function (win) {
                this.isActive = false;

                // Probably not inline
                if (!win.tinyMCEPopup || !win.tinyMCEPopup.id) {
                    WindowManager.superclass.close.call(this, win);
                    return;
                }

                var w = Ext.getCmp(win.tinyMCEPopup.id);
                if (w) {
                    this.onClose.dispatch(this);
                    w.close();
                }
            },

            setTitle: function (win, ti) {
                // Probably not inline
                if (!win.tinyMCEPopup || !win.tinyMCEPopup.id) {
                    WindowManager.superclass.setTitle.call(this, win, ti);
                    return;
                }

                var w = Ext.getCmp(win.tinyMCEPopup.id);
                if (w) {
                    w.setTitle(ti);
                }
            },

            resizeBy: function (dw, dh, id) {
                var w = Ext.getCmp(id);
                if (w) {
                    var size = w.getSize();
                    w.setSize(size.width + dw, size.height + dh);
                }
            },

            focus: function (id) {
                var w = Ext.getCmp(id);
                if (w) {
                    w.setActive(true);
                }
            }
        });


        editor.onPostRender.add(function (ed) {
            //change window manager
            ed.windowManager = new WindowManager({
                editor: ed,
                manager: ed.manager
            });
        });

        editor.onInit.add(function (ed) {
            var s = ed.settings;
            console.log('[iRTE] editor.onInit', ed);

            rte.editor = ed;
            rte.toolbar.syncControls(s.CMSenabledControls, s.CMScustomStyles);
            ed.focus();
        });

        editor.onFocus.add(function (ed) {
            var s = ed.settings;
            //clicking in the toolbar shouldn't be handled as the editor where focused, so return
            if (rte.toolbar.isActive() || ed.windowManager.isActive || s.CMSsuspendFocus) {
                return;
            }
            console.log('[iRTE] inlineEditor focused', ed);
        });

        editor.onBlur.add(function (ed) {
            //clicking in the toolbar shouldn't be handled as the editor where blurred, so return
            if (rte.toolbar.isActive() || ed.windowManager.isActive) {
                return;
            }
            console.log('[iRTE] inlineEditor blurred', ed);

            rte.saveAndRemoveEditor();
        });
    },


    /**
    * Inits the editor on the given section
    * @param  {CMS.data.UnitRecord} unit
    * @param  {HTMLElement} sectionEl
    */
    initEditorForSection: function (unit, sectionEl) {
        sectionEl = Ext.get(sectionEl); //this is needed to make sure the element has an unique id

        console.log('[iRTE] init inline editor for ', unit.id, sectionEl);

        var cmsSection = sectionEl.getAttribute(CMS.config.inlineSectionHTMLAttribute);
        var sectionConfig = unit.getModule().getRichTextEditorConfigForField(cmsSection);

        var isPlainText = false;
        if (!sectionConfig) { //if no config available, it's a plain text field
            sectionConfig = {};
            isPlainText = true;
        }

        var tinyMCEsettings = this.generateTinyMCESettings(sectionConfig, unit.id, cmsSection);
        tinyMCEsettings.element_id = sectionEl.id;
        tinyMCEsettings.CMSunit = unit;
        tinyMCEsettings.CMSsection = cmsSection;
        tinyMCEsettings.CMSisPlainText = isPlainText;

        this.toolbarWindow.show();
        this.toolbarWindow.setWidth(this.getToolbarTarget().getWidth() - 40);
        this.toolbarWindow.alignTo(this.getToolbarTarget(), 'tl-tl');

        tinyMCE.execCommand('mceAddFrameControl', false, tinyMCEsettings);
    },

    /**
     * Returns the Ext.Element the toolbar window should be aligned to
     * @private
     */
    getToolbarTarget: function () {
        if (!this.toolbarTargetEl) {
            this.toolbarTargetEl = Ext.get(Ext.DomQuery.selectNode(this.toolbarTarget)) || Ext.getBody();
        }
        return this.toolbarTargetEl;
    },

    /**
    * Saves and removes the TinyMCE editor created in this RichTextEditor instance
    */
    saveAndRemoveEditor: function () {
        var ed = this.editor;
        if (!ed) {
            return;
        }

        this.toolbarWindow.hide();

        var document = ed.getDoc();
        if (document && document !== this.frame.getFrameDocument()) {
            console.warn('[InlineRichTextEditor] TinyMCE active on removed node');
            return;
        }

        if (ed.isDirty()) {
            var s = ed.settings;
            var format = s.CMSisPlainText ? 'text' : 'html';

            this.saveSection(s.CMSunit, s.CMSsection, ed.getContent({
                format: format
            }));
        }

        this.removeEditor();
    },

    /**
    * Removes the TinyMCE editor created in this RichTextEditor instance
    */
    removeEditor: function () {
        var ed = this.editor;
        if (ed) {
            if (ed.getBody().ownerDocument !== this.frame.getFrameDocument()) {
                console.warn('[InlineRichTextEditor] TinyMCE active on removed node');
            } else {
                ed.remove();
            }
        }
        this.editor = null;
        delete this.currentNode;

        this.fireEvent('CMSinlinesectionblurred');
    },

    /**
    * @private
    * Saves the section
    * @param  {CMS.data.UnitRecord} unit
    * @param  {String} section
    * @param  {String} content
    */
    saveSection: function (unit, section, content) {
        content = content.trim();

        console.log('[iRTE] inlineEditor saved section: ', section, content);
        this.fireEvent('CMSinlinesectionchanged', unit, section, content);
    },


    destroy: function () {
        try {
            this.saveAndRemoveEditor();
        } catch (e) {
            // it is possible (especially in chrome) that the editor cannot
            // access its dom on window.unload
            // -> ignore error
            console.warn('[iRTE] destroy', e);
        }
        this.toolbarWindow.destroy();
    }
});
