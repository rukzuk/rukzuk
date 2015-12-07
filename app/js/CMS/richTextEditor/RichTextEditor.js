/*global tinymce:true, tinyMCE:true*/
Ext.ns('CMS.richTextEditor');

/*
TODO: prevent key shortcuts like Ctrl+B when the action isn't allowed
*/

/**
 * @class CMS.richTextEditor.RichTextEditor
 * @extends Ext.util.Observable
 */
CMS.richTextEditor.RichTextEditor = Ext.extend(Ext.util.Observable, {

    /**
     * Reference to richTextEditorToolbar MUST be passed when creating
     * @property toolbar
     * @type CMS.richTextEditor.RichTextEditorToolbar
     */
    toolbar: null,

    /**
     * The node that the cursor currently is in
     * @property currentNode
     * @type DOMNode
     */
    currentNode: null,

    /**
     * websiteId MUST be passed when creating
     * @property websiteId
     * @type String
     */
    websiteId: null,

    /**
     * The id of the current page or template. pageOrTemplateId MAY be passed when creating
     * @property pageOrTemplateId
     * @type String
     */
    pageOrTemplateId: null,



    /**
     * @constructor
     * @param {Object} config A configuration object containing the following attributes:<ul>
     <li><tt>frame {SB.TheIframe}</tt>: Passed by the documentloaded event.</li>
     <li><tt>toolbar {CMS.richTextEditor.RichTextEditorToolbar}</tt>: The toolbar which has to be synced with the editor</li>
     <li><tt>frame {HTMLElement}</tt>: Iframe to initialize tinyMCE in. Required for FallbackRichTextEditor only.</li>
     </ul>
     */
    constructor: function (args) {
        //Assign params from constructor call to current instance
        //This allows us to pass a frame and a toolbar as constructor param
        Ext.apply(this, args);
        Ext.apply(this, CMS.config.tinyMCE);

        //if the editor is initialized in page or template mode, get the store where additional RTE configs are stored
        if (this.pageOrTemplateId) {
            this.apiRTEConfigStore = CMS.data.StoreManager.get('RichTextEditorConfig', this.websiteId, {
                disableLoad: true,
                idSuffix: this.pageOrTemplateId
            });
        }

        this.registerToolbarEvents();
    },

    /**
     * Sets the events for the rich text editor toolbar
     * @private
     */
    registerToolbarEvents: function () {
        //add event listeners
        Ext.iterate(this.controlsConfig, function (oneControlId, oneControl) {
            var customHandler = CMS.richTextEditor.RichTextEditor.handlers[oneControlId];
            if (Ext.isFunction(customHandler)) {
                this.toolbar.on(oneControlId, customHandler, this);
            } else {
                // default handler for other buttons
                this.toolbar.on(oneControlId, function () {
                    tinyMCE.activeEditor.execCommand(oneControl.execCommand);
                }, this);
            }
        }, this);
    },

    /**
     * Synchronizes the rich text editor toolbar buttons according to DOMNode of the current cursor position
     * @function
     * @private
     * @param {Object} editor The tinyMCE instance
     * @param {DOMNode} node The focused node
     * @param {Object} options TinyMCE's mysterious options object
     */
    syncButtonStates: (function () {
        var editor;
        var node;
        var options;
        var currentCustomFormat;
        var isCursorInTable;
        var isCursorInLink;

        var getParent = function (name) {
            var i, parents = options.parents, func = name;
            if (typeof name == 'string') {
                func = function (node) {
                    return node.nodeName == name;
                };
            }
            for (i = 0; i < parents.length; i++) {
                if (func(parents[i])) {
                    return parents[i];
                }
            }
        };

        var processContainer = function (container) {
            Ext.each(container.items, processItem, this);
        };

        var processItem = function (item) {
            var itemId = item.getItemId();

            //skip if item isn't visible or not in enabledControls
            if (item.hidden || editor.settings.CMSenabledControls.indexOf(itemId) === -1) {
                return;
            }

            console.log('[RTE] processItem', itemId);

            if (item.isXType('button') || item.isXType('menuitem')) {

                //undo button
                if (itemId == 'undo') {
                    if (!editor.undoManager.hasUndo() && !editor.undoManager.typing) {
                        item.disable();
                    } else {
                        item.enable();
                    }

                //redo button
                } else if (itemId == 'redo') {
                    if (!editor.undoManager.hasRedo()) {
                        item.disable();
                    } else {
                        item.enable();
                    }

                //bullist button
                } else if (itemId == 'bullist') {
                    item.toggle((getParent('UL') ? true : false), true);

                //numlist button
                } else if (itemId == 'numlist') {
                    item.toggle((getParent('OL') ? true : false), true);

                //link button
                } else if (itemId == 'link') {
                    if (!Ext.isDefined(isCursorInLink)) {
                        isCursorInLink = getParent('A') ? true : false;
                    }
                    if (!editor.selection.isCollapsed() || isCursorInLink) {
                        item.enable();
                    } else {
                        item.disable();
                    }

                    item.toggle(isCursorInLink, true);

                //unlink button
                } else if (itemId == 'unlink') {
                    if (isCursorInLink) {
                        item.enable();
                    } else {
                        item.disable();
                    }

                //table buttons
                } else if (itemId.substr(0, 5) == 'table' && itemId != 'tableMenu') {
                    if (!Ext.isDefined(isCursorInTable)) {
                        isCursorInTable = getParent('TABLE') ? true : false;
                    }
                    if (itemId === 'tableInsert') {
                        // disable "insert table" option if and only if in table
                        item.setDisabled(isCursorInTable);
                    } else {
                        // disable other table options (e.g. "delete table",
                        // "add row", ...) if and only if NOT in table
                        item.setDisabled(!isCursorInTable);
                    }

                //all other buttons
                } else {
                    var state = editor.queryCommandState(editor.settings.CMScontrolsConfig[itemId].execCommand);
                    if (state) {
                        item.toggle(true, true);
                    } else {
                        item.toggle(false, true);
                    }
                }

                if (item.menu && !item.disabled) {
                    // item is a container itself (e.g. a menu button, sub menu, ...)
                    // -> step into recursively
                    processContainer.call(this, item.menu);
                }

            } else if (item.isXType('combo')) {
                if (itemId == 'style') {
                    if (currentCustomFormat) {
                        item.setValue(currentCustomFormat);
                    } else {
                        item.reset();
                    }
                }
            }
        };

        return function (editorParam, nodeParam, optionsParam) {
            editor = editorParam;
            node = nodeParam;
            options = optionsParam;
            currentCustomFormat = editor.formatter.matchAll(this.formatIds)[0];

            // reset cached values
            isCursorInTable = undefined;
            isCursorInLink = undefined;

            //toggle toolbar buttons
            Ext.each(this.toolbar.items, processContainer, this);
        };
    }()),

    /**
     * Returns additional TinyMCE settings which will be merged with {@link #getTinyMCEConfigDefaults} later on
     * Must be overwritten
     * @private
     * @return {Object} TinyMCE settings
     */
    getTinyMCEConfig: function () {
        return {};
    },

    /**
     * Returns the default config for TinyMCE; will be extended in {@link #generateTinyMCESettings}
     * @private
     * @return {Object} TinyMCE settings
     */
    getTinyMCEConfigDefaults: function () {
        var rte = this;
        return {
            mode: 'none',
            theme: 'sbcms',
            skin: 'o2k7',
            object_resizing: false,
            entity_encoding: 'raw',
            submit_patch: false,
            language: 'en',
            relative_urls: false,
            //fix_list_elements: true, //doesn't work?!

            plugins: 'paste,table',

            //validate_children: true,
            forced_root_block: '', //otherwise TinyMCE creates a wrapping P tag

            setup: function (editor) {
                rte.tinyMCEInternalSetup(editor);
                rte.onTinyMCESetup(editor);
            },

            //add data-cms-link attributes to links inserted via copy&paste
            paste_postprocess: function (plugin, options) {
                var editor = plugin.editor;
                var elements = options.node.getElementsByTagName('A');

                tinymce.each(elements, function (node) {
                    var href = editor.dom.getAttrib(node, 'href');

                    //check if link isn't already an CMS link
                    if (!editor.dom.getAttrib(node, CMS.config.rteLinkHTMLAttribute) && href) {
                        //unfortunately it's difficult to detect internal links here, so only external and mail is supported
                        var linkType = CMS.config.rteLinkTypeHTMLAttributeValues.external;
                        if (href.indexOf('mailto:') === 0) {
                            href = href.replace('mailto:', '');
                            linkType = CMS.config.rteLinkTypeHTMLAttributeValues.mail;
                        }

                        var attribs = {};
                        attribs[CMS.config.rteLinkHTMLAttribute] = href;
                        attribs[CMS.config.rteLinkTypeHTMLAttribute] = linkType;

                        editor.dom.setAttribs(node, attribs);
                    }
                });
            }
        };
    },


    /**
     * Called as tinymce's setup method to register some special key listeners, e.g. preventing linebreaks and drag&drop
     * @private
     * @param {Object} editor The tinyMCE instance
     */
    tinyMCEInternalSetup: function (editor) {
        var sectionConfig = editor.settings.CMSsectionConfig;
        var rte = this;

        //cache the dynamic format ids so they don't have to be processed on every nodeChange
        this.formatIds = SB.util.getKeys(editor.settings.formats || {});

        editor.onNodeChange.add(function (editor, controlManager, node, collapsed, options) {
            // for some reason, nodeChange sometimes fires twice on the same node
            if (node === rte.lastNode && collapsed == rte.lastCollapsed) {
                return;
            }
            rte.lastNode = node;
            rte.lastCollapsed = !!collapsed;
            rte.syncButtonStates(editor, node, options);
        });

        //maybe linebreaks are not allowed? disable the enter-key
        if (!sectionConfig.enterKey || sectionConfig.enterKey == 'none') {
            editor.onKeyPress.addToTop(function (ed, e) {
                if ((e.charCode == 13 || e.keyCode == 13)) {
                    return tinymce.dom.Event.cancel(e);
                }
            });
        }

        //handle tab-key
        editor.onKeyDown.add(function (ed, e) {
            // Firefox uses the e.which event for keypress
            // While IE and others use e.keyCode, so we look for both
            var code;
            if (e.keyCode) {
                code = e.keyCode;
            } else if (e.which) {
                code = e.which;
            }

            if (code == 9 && !e.altKey && !e.ctrlKey) {
                //only allow Out-/Indent when caret is placed in a list
                if (ed.queryCommandState('InsertUnorderedList') || ed.queryCommandState('InsertOrderedList')) {
                    // toggle between Indent and Outdent command, depending on if SHIFT is pressed
                    if (e.shiftKey) {
                        ed.execCommand('Outdent');
                    } else {
                        ed.execCommand('Indent');
                    }
                }

                // prevent tab key from leaving editor in some browsers
                if (e.preventDefault) {
                    e.preventDefault();
                }
                return false;
            }
        });

        //disable drag&drop since content won't get filtered by TinyMCE
        editor.onDrop.addToTop(function (ed, e) {
            return tinymce.dom.Event.cancel(e);
        });

        editor.onDragEnter.addToTop(function (ed, e) {
            return tinymce.dom.Event.cancel(e);
        });

        editor.onDragOver.addToTop(function (ed, e) {
            return tinymce.dom.Event.cancel(e);
        });

        editor.onDragLeave.addToTop(function (ed, e) {
            return tinymce.dom.Event.cancel(e);
        });

        editor.onRemove.add(function () {
            rte = null;
            sectionConfig = null;
        });
    },


    /**
     * This method will be called after each TinyMCE is initialized. Use this e.g. to add custom event listeners.
     * Must be overwritten
     * @private
     * @param {tinyMCE.Editor} editor
     */
    onTinyMCESetup: function (editor) {
    },

    /**
     * Generates the TinyMCE settings according to the given sectionConfig (generated by {@link CMS.richTextEditor.RichTextEditorConfigField})
     * @param {Object} sectionConfig
     * @return {Object} TinyMCE settings
     */
    generateTinyMCESettings: function (sectionConfig, unitId, section) {
        sectionConfig = sectionConfig || {};
        var that = this;

        //merge the default settings with concrete settings
        var tinyMCEsettings = Ext.apply({}, this.getTinyMCEConfig(), this.getTinyMCEConfigDefaults());

        //if store available, check if there is a additional RTE config which was set via the JS-API
        if (this.apiRTEConfigStore) {
            var apiConfigRecord = this.apiRTEConfigStore.getById(unitId + '_' + section);
            if (apiConfigRecord) {
                var apiCfg = apiConfigRecord.get('config');
                console.log('[RTE] applying additional RichTextEditor config set by JS-API', unitId, section, apiCfg);
                sectionConfig = Ext.apply({}, apiCfg, sectionConfig);
            }
        }

        //check the configuration and set valid elements and controls
        //uses tinyMCE valid_elements syntax (http://wiki.moxiecode.com/index.php/TinyMCE:Configuration/valid_elements)
        var validElements = [];
        var extendedValidElements = [];
        var customStyleItems = [];
        var enabledControls = this.alwaysEnabledControls.slice(0); //slice copies the array

        function enableControl(name, additionalEnabled) {
            var elements = that.controlsConfig[name].validElements;
            if (elements) {
                elements = elements.split(',');
                Ext.each(elements, function (oneElement) {
                    if (validElements.indexOf(oneElement) == -1) {
                        validElements.push(oneElement);
                    }
                });
            }

            enabledControls.push(name);
            if (typeof additionalEnabled == 'string') {
                enabledControls.push(additionalEnabled);
            } else if (additionalEnabled && additionalEnabled.length) {
                enabledControls.push.apply(enabledControls, additionalEnabled);
            }
        }

        Ext.each(['bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', 'bullist', 'numlist'], function (id) {
            if (sectionConfig[id]) {
                enableControl(id);
            }
        });
        if (sectionConfig.link) {
            enableControl('link', 'unlink');
        }

        if (sectionConfig.table) {
            enableControl('tableMenu', ['tableInsert', 'tableDelete', 'tableDeleteCol', 'tableDeleteRow', 'tableInsertColAfter', 'tableInsertColBefore', 'tableInsertRowAfter', 'tableInsertRowBefore']);
        }

        if (sectionConfig.enterKey == 'paragraph') {
            validElements.push('br');
            validElements.push('-p'); //TODO: add # so empty p will be filled with &nbsp; (SBCMS-528)
            tinyMCEsettings.forced_root_block = 'p';
        } else if (sectionConfig.enterKey == 'linebreak') {
            validElements.push('br');
            tinyMCEsettings.force_br_newlines = true;
            tinyMCEsettings.force_p_newlines = false;
        }

        if (!Ext.isEmpty(sectionConfig.customStyles)) {
            enableControl('style');
            tinyMCEsettings.formats = {};

            Ext.each(sectionConfig.customStyles, function (oneStyle) {
                var format = {};

                var validElement = oneStyle.element;
                if (validElement == 'p') {
                    validElement = '-p'; //TODO: add # so empty p will be filled with &nbsp; (SBCMS-528)
                }

                if (validElement == 'span') {
                    //HACK SBCMS-564
                    var validElementStyles = [];
                    if (sectionConfig.underline) {
                        validElementStyles.push('text-decoration: underline;?text-decoration:underline;?text-decoration: underline; ');
                    }
                    if (sectionConfig.strikethrough) {
                        validElementStyles.push('text-decoration: line-through;?text-decoration:line-through;?text-decoration: line-through; ');
                    }
                    var validElementAddon = '';
                    if (validElementStyles.length) {
                        validElementAddon = '|style<' + validElementStyles.join('?');
                    }

                    validElement += '[class' + validElementAddon + ']';
                }

                //TODO: SBCMS-564 more flexible solution to add validElements and attributes!
                //allow only the defined classes
                if (extendedValidElements.indexOf(validElement) == -1) {
                    extendedValidElements.push(validElement);
                }

                //decide if wrapper-element is a block or inline element
                if (that.allHTMLElementTypes[oneStyle.element]) {
                    format.block = oneStyle.element;
                } else {
                    format.inline = oneStyle.element;
                }

                if (oneStyle.classes.length > 1 && oneStyle.classes[0] !== '') {
                    format.classes = oneStyle.classes;
                }

                var id = Ext.id(); //just a random id to match the format of the combobox to the format in TinyMCE settings
                tinyMCEsettings.formats[id] = format;

                customStyleItems.push([id, oneStyle.label]);
            });
        }

        if (Ext.isEmpty(validElements)) {
            //this space character is very important, otherwise TinyMCE will allow all elements
            tinyMCEsettings.valid_elements = ' ';
        } else {
            //add all valid elements to tinyMCE settings
            tinyMCEsettings.valid_elements = validElements.join(',');
        }

        tinyMCEsettings.extended_valid_elements = extendedValidElements.join(',');
        tinyMCEsettings.CMScontrolsConfig = this.controlsConfig;
        tinyMCEsettings.CMSsectionConfig = sectionConfig;
        tinyMCEsettings.CMSenabledControls = enabledControls;
        tinyMCEsettings.CMScustomStyles = customStyleItems;

        return tinyMCEsettings;
    }

});

CMS.richTextEditor.RichTextEditor.handlers = {
    'style': function (newFormatId) {
        var ed = tinyMCE.activeEditor;

        //remove all formats
        Ext.iterate(ed.settings.formats, function (formatId) {
            ed.formatter.remove(formatId);
        });

        //apply new format if set
        if (newFormatId) {
            ed.formatter.apply(newFormatId);
        }

        //dirty HACK for SBCMS-488 so the element gets focussed again after style select
        if (ed.settings.content_editable) {
            (function () {
                ed.settings.CMSsuspendFocus = true;
                ed.getElement().focus();
                ed.settings.CMSsuspendFocus = false;
            }).defer(1);
        }

    },

    'link': function () {
        var win = new CMS.richTextEditor.LinkWindow({
            editor: tinyMCE.activeEditor,
            websiteId: this.websiteId
        });
        win.show();
    },

    'charmap': function (character) {
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, character);
    },

    'tableInsert': function (columns, rows) {
        //console.log('insertTable', columns, rows);
        var html = ['<table>'];

        for (var y = 0; y < rows; y++) {
            html.push('<tr>');
            for (var x = 0; x < columns; x++) {
                html.push('<td><br></td>');
            }
            html.push('</tr>');
        }
        html.push('</table>');
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, html.join(''));
    }
};
