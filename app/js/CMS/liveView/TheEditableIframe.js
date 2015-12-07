Ext.ns('CMS.liveView');

/**
* @class CMS.liveView.TheEditableIframe
* @extends SB.TheIframe
* An iframe that can be used for editing. It is attached to a unit store, and can handle mouse events.
*/
CMS.liveView.TheEditableIframe = Ext.extend(SB.TheIframe, {
    /**
    * @property websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {Object} outlineStyle
    * A style declaration used to outline editable elements
    */
    outlineStyle: {
        outline: '1px dotted ' + ((Ext.isIE || Ext.isOpera) ? 'invert' : 'gray')
    },

    /**
    * @cfg {string} type
    * One of 'template'/'page'. This determines the service used for rendering
    */
    type: 'template',

    /**
    * @cfg {Integer} scrollThreshold
    * If less than this amount of Pixels in width/height of an element is visible,
    * it will be scrolled into view when focused.
    */
    scrollThreshold: 20,

    /**
    * <tt>false</tt> to disable editing mode. Defaults to <tt>true</tt>
    * @property editingEnabled
    * @type Boolean
    */
    editingEnabled: true,

    /**
    * @property unitStore
    * @type CMS.data.UnitStore
    * reference to the unitstore from the current panel
    * Property must be set if editingEnabled is set to true
    */
    unitStore: null,

    loadMask: true,

    bubbleEvents: ['CMSframemouseenter', 'CMSframemouseout', 'CMSunitframedeselect', 'CMSunitframeselect'],

    initComponent: function () {
        console.log('[TheEditableIframe] initComponent');
        this.plugins = this.plugins || [];
        this.plugins.push(CMS.liveView.TheIframeSelectionPlugin);
        var self = this;
        this.beforeunloadHandler = function () {
            console.log('[TheEditableIframe] beforeUnload');
            if (self.unitStore.isDirty && !self.suppressWarning) {
                return CMS.i18n('Änderungen gehen verloren');
            }
        };
        this.proxyEvents = this.proxyEvents.concat(['mousedown', 'mousemove', 'mouseup']); // mousedown for menu; mousemove for D&D ghost
        this.on('domready', function (self, el) {
            console.log('[TheEditableIframe] domready!', el);
            try {
                // Enable bubbling of mouse events from within the iframe element
                // Requires extension on Ext.Element
                el.relayEvents(this.getFrameDocument(), this.proxyEvents);

            } catch (e) {
                console.error('[TheEditableIframe] Could not initialize iframe. SOP issue?');
            }
        }, this);

        this.on('scroll', this.onScroll, this);

        this.on('destroy', function () {
            self = null;
        });

        CMS.liveView.TheEditableIframe.superclass.initComponent.apply(this, arguments);
        this.scrollPosCache = [0, 0];
        this.replaceRequestIds = {};
        this.eventModel = new CMS.liveView.TheIframeEventModel(this);
        this.addEvents('rendered', 'beforerender');
    },


    /**
    * @private
    * Handler for the scroll event
    */
    onScroll: function () {
        var doc = this.getFrameDocument();
        this.scrollPosCache = [(doc.documentElement.scrollLeft || doc.body.scrollLeft || 0), (doc.documentElement.scrollTop || doc.body.scrollTop || 0)];
    },

    /**
    * @param {CMS.data.PageRecord|CMS.data.TemplateRecord|String} record The record containing the to-be-rendered page/template, or the ID of a server-stored page/template
    * @param {Function} callback (optional) A function to execute after rendering
    * @param {Object} scope (optional) The callback's scope
    * @param {String} replaceId (optional) A {CMS.data.UnitRecord}'s id. If set, the iframe will not replace the whole DOM with the rendered content,
    * but only the unit's DOM element and/or its style declaration.
    */
    renderPageOrTemplate: function (record, callback, scope, replaceId, recursive) {
        console.group('[TheEditableIframe] renderPageOrTemplate');
        /**
        * @event beforerender
        * Fired before a page or template is rendered
        */
        this.fireEvent('beforerender');
        var options = {
            method: 'POST',
            callback: function (el) {
                this.renderCallback(el, scope, callback, replaceId);
            },
            scope: this,
            discardUrl: true,
            nocache: false,
            timeout: 10
        };

        var type = (this.type == 'template') ? 'Template' : 'Page';
        this.setRenderURLAndParams(record, options, type);

        if (replaceId && !this.loading) { // && !this.loading (?) -> SBCMS-348
            console.log('[TheEditableIframe] replacing contents of ', replaceId);
            var styleOnly = !this.getFrameDocument().getElementById(replaceId); // SBCMS-690: do not replace body if unit has no renderer
            this.replaceContents(record, options, type, replaceId, styleOnly);
        } else {
            console.log('[TheEditableIframe] renderPageOrTemplate replacing everything');

            // the page is loading and we are not in a recursive call (i.e. not called by beforedomready handler)
            if (this.loading && !recursive) {

                if (this.waitForDomReady) {
                    console.log('[TheEditableIframe] renderPageOrTemplate waitForDomReady - cancel refresh!!');
                    console.groupEnd();
                    return;// end here
                }

                console.log('[TheEditableIframe] renderPageOrTemplate page is loading; bind next reload on beforedomready');

                // convert arguments to real array
                var callArgs = [].slice.apply(arguments);

                // add recursive = true
                callArgs[4] = true;

                this.on('beforedomready', function () {

                    console.log('[TheEditableIframe] renderPageOrTemplate render on beforedomready - cancel domready event');
                    this.renderPageOrTemplate.apply(this, callArgs);

                    // stop domready (!) (see TheIframe)
                    return false;
                }, this, {single: true});

                // prevent multiple listeners on beforedomready
                this.waitForDomReady = true;

                console.groupEnd();
                return; // end here!
            } else {

                // do the actual work
                options.params = CMS.app.trafficManager.createPostParams(options.params);
                this.getFrameWindow().onunload = ''; // break bfcache: http://groups.google.com/group/jquery-dev/browse_thread/thread/13e8f300f1ce1893/3a3abe5cbb1bfe3a
                Ext.each(this.replaceRequestIds, function (id) {
                    CMS.app.trafficManager.abortRequest(id);
                });
                this.replaceRequestIds = {};
                this.suppressWarning = true;

                this.submitAsTarget(options);

                /* reset waitForDomReady on recursive call */
                if (recursive) {
                    console.log('[TheEditableIframe] renderPageOrTemplate reset waitForDomReady!!');
                    this.waitForDomReady = false;
                }
            }

        }
        console.groupEnd();
    },

    /**
    * @private
    * Called after the render process is finished.
    * @param {Ext.Element} el The Ext.Element which has been rendered
    * @param {Function} callback (optional) A function to execute after rendering
    * @param {Object} scope (optional) The callback's scope
    * @param {String} replaceId (optional) A {CMS.data.UnitRecord}'s id.
    */
    renderCallback: function (el, scope, callback, replaceId) {
        console.group('[TheEditableIframe] running renderPageOrTemplate callback');
        this.removeEvents();
        this.suppressWarning = false;

        if (!this.selectIdAfterLoad) {
            // scroll to cached position
            this.scrollToView(null);
        }

        var win = this.getFrameWindow();
        win.onbeforeunload = this.beforeunloadHandler;

        /* overwrite console */
        // This is probably not necessary since ApiAdapter takes care of that.
        //  Do we have any pages without ApiAdapter?
        /*
        if (typeof win.console == 'undefined') {
            win.console = {};
        }
        var consoleMethods = 'log,warn,info,debug'.split(',');
        if (!win.console.CMS) {
            Ext.each(consoleMethods, function (method) {
                win.console[method] = function () {
                    var args = Ext.toArray(arguments);
                    args.unshift('[impl ' + method + ']');
                    CMS.console.debug.apply(CMS.console, args);
                };
            });
            win.addEventListener('unload', function self() {
                win.removeEventListener('unload', self, false);
                Ext.each(consoleMethods, function (method) {
                    win.console[method] = null;
                });
            }, false);
        }
        */


        if (this.editingEnabled) {
            this.eventModel.initEvents(el);
        }
        if (typeof callback == 'function') {
            callback.call(scope || window);
        }
        if (this.selectIdAfterLoad) {
            (function () {
                this.selectUnitById(this.selectIdAfterLoad);
                delete this.selectIdAfterLoad;
            }).defer(10, this);
        }
        this.el.relayEvents(this.getFrameDocument(), this.proxyEvents);

        /**
         * @event rendered
         * Fired after a page or template is rendered
         * @param {String} replaceId (optional) The id of the unit whose contents have been replaced, if applicable
         */
        this.fireEvent('rendered', replaceId);
        console.groupEnd();
    },

    /**
    * @private
    * Helper used to remove events on iframe
    */
    removeEvents: function () {
        console.log('[TheEditableIframe] unrelaying events');
        this.el.unrelayEvents(this.el.dom.contentDocument);
        if (!this.selectIdAfterLoad && this.selectedUnitEl) {
            this.selectIdAfterLoad = this.selectedUnitEl.id;
        }
        this.setHoveredUnitEl(null);
        this.setSelectedUnitEl(null);
        this.eventModel.teardownEvents();
    },

    /**
    * @private
    * Helper used to replace one unit's content
    * Takes the same params as {@link #renderPageOrTemplate} and additionally the following:
    * @param {Boolean} styleOnly <tt>true</tt> to replace the unit's CSS style only, not the DOM contents. Defaults to <tt>false</tt>
    * Has no effect if <tt>replaceId</tt> is not set
    */
    replaceContents: function (record, options, type, replaceId, styleOnly) {
        if (this.loading) {
            // FIXME: here, we should wait for the request to finish, and after that, send the replace request -> SBCMS-348
            ///this.getFrameWindow().stop();
            //this.loading = false;
        }
        if (this.replaceRequestIds[record.id]) {
            CMS.app.trafficManager.abortRequest(this.replaceRequestIds[record.id]);
        }
        this.replaceRequestIds[record.id] = CMS.app.trafficManager.sendRequest({
            action: 'render' + type,
            data: options.params,
            rawText: true,
            success: function (response, opt) {
                var newDoc = SB.string.toHTMLDocument(response);
                var newNode = newDoc.getElementById(replaceId);
                var newStyle = this.getStyleDef(newDoc, replaceId);
                var doc = this.getFrameDocument();
                var oldNode = doc.getElementById(replaceId);
                var oldStyle = this.getStyleDef(doc, replaceId);

                // we're trying to replace style and body DOM nodes if we find them
                // Otherwise, fall back to replacing everything
                if (((!newNode || !oldNode) && !styleOnly) || (!newStyle && oldStyle)) {
                    if (!newNode) {
                        console.warn('Element #', replaceId, ' not found in response', response);
                    } else if (!newStyle) {
                        console.warn('CSS not found in response', response);
                    }
                    this.getFrameWindow().onbeforeunload = '';
                    doc.open();
                    doc.write(response);
                    doc.close();
                    return;
                }

                if (oldStyle) {
                    oldStyle.parentNode.replaceChild(SB.Element.adoptNode(newStyle), oldStyle);
                }
                if (!styleOnly) {
                    oldNode.parentNode.replaceChild(SB.Element.adoptNode(newNode), oldNode);
                }

                oldNode = newNode = oldStyle = newStyle = null;
                options.callback.call(options.scope || window, this.el, null, null, options);
            },
            callback: function () {
                delete this.replaceRequestIds[record.id];
            },
            callbackFirst: true,
            failureTitle: CMS.i18n('Fehler beim Rendern'),
            scope: this
        });
    },

    /**
    * @private
    * Helper, only used by renderPage
    */
    setRenderURLAndParams: function (record, options, type) {
        if (typeof record == 'string') {
            options.params = Ext.apply({
                websiteId: this.websiteId
            }, CMS.config.params['render' + type]);
            options.params[this.type + 'Id'] = record;
        } else {
            options.params = Ext.apply({
                websiteId: this.websiteId
            }, CMS.config.params['render' + type]);
            options.params[this.type + 'Id'] = record.get('id');
            options.params.data = record.data.content || CMS.config['default' + type + 'Content'];
            //console.log('[TheEditableIframe] Site Data:', data.content);
        }
        options.url = CMS.config.urls['render' + type];

        //keep the current hash after render
        options.url += '#' + this.getFrameWindow().location.hash.replace(/^#/, '');
    },


    /**
    * @private
    * Get a unit's style definition
    * @param {HTMLDocument} doc The newly renderd document that contains the new style rules
    * @param {String} id The id of the unit to get the style of
    * @return {HTMLElement} The &lt;style&gt; element that contains the unit's style rules
    * If no style is found, <tt>null</tt> is returned.
    */
    getStyleDef: function (doc, id) {
        var sep1 = CMS.config.unitElStyleMarker.start.replace('{unitId}', id);
        var sep2 = CMS.config.unitElStyleMarker.end.replace('{unitId}', id);
        var styles = doc.getElementsByTagName('style');
        for (var i = 0, l = styles.length; i < l; i++) {
            var all = styles[i].textContent || styles[i].innerText;
            if (all && (all.indexOf(sep1) != -1) && (all.indexOf(sep2) != -1)) {
                console.log('[TheEditableIframe] CSS of', id, 'found in ', styles[i]);
                return styles[i];
            }
        }
        return null;
    },

    setSrc: function (url, discardUrl, callback, scope) {
        var win = this.getFrameWindow();
        win.onunload = ''; // break bfcache: http://groups.google.com/group/jquery-dev/browse_thread/thread/13e8f300f1ce1893/3a3abe5cbb1bfe3a
        win.onbeforeunload = '';
        this.setHoveredUnitEl(null);
        this.setSelectedUnitEl(null);
        CMS.liveView.TheEditableIframe.superclass.setSrc.apply(this, arguments);
    },

    destroy: function () {
        this.removeEvents();
        var win = this.getFrameWindow();
        try {
            win.onbeforeunload = '';
        } catch (e) {} // user might have navigated away to some other domain
        delete this.beforeunloadHandler;
        delete this.scrollPosCache;
        delete this.replaceRequestIds;
        delete this.proxyEvents;
        delete this.previouslySelectedUnitEl;
        delete this.eventModel;
        CMS.liveView.TheEditableIframe.superclass.destroy.apply(this, arguments);
    },

    /**
    * scroll the iframe so that the specified element becomes visible; if element is null, scroll to cached scroll position
    * @param {Ext.Element} element
    */
    scrollToView: function (element) {
        if (!element) {
            var scrollPos = this.scrollPosCache;
            if (scrollPos) {
                this.getFrameWindow().scrollTo(scrollPos[0], scrollPos[1]);
            }
            return;
        }

        element = Ext.fly(element, 'PI');
        var x = element.getXY();
        var y = x[1];
        x = x[0];
        var thisWidth = this.getWidth();
        var thisHeight = this.getHeight();
        var elWidth = element.getWidth();
        var elHeight = element.getHeight();
        if (x > (thisWidth - this.scrollThreshold) || (x + elWidth < this.scrollThreshold)) {
            if (y > (thisHeight - this.scrollThreshold) || y + elHeight < this.scrollThreshold) {
                this.getFrameWindow().scrollTo(x - this.scrollThreshold, y - this.scrollThreshold);
            } else {
                this.getFrameWindow().scrollBy(x - this.scrollThreshold, 0);
            }
        } else if (y > (thisHeight - this.scrollThreshold) || y + elHeight < this.scrollThreshold) {
            this.getFrameWindow().scrollBy(0, y - this.scrollThreshold);
        }
    },

    /**
    * @private
    * Required for nested elements, when the mouse leaves the inner element
    * @param {HTMLElement} unitEl The unit's element
    */
    handleMouseLeave: function (unitEl) {
        var re = new RegExp('(\\s|^)' + CMS.config.unitElClassName + '(\\s|$)');
        for (unitEl = unitEl.parentNode; !!unitEl; unitEl = unitEl.parentNode) {
            if (re.test(unitEl.className)) {
                this.setHoveredUnitEl(unitEl, false, this.isEditable(unitEl.id));
                return;
            }
        }
        this.setHoveredUnitEl(null);
    },

    /**
    * @private
    * Required for nested elements, when the mouse leaves the inner element
    * @param {HTMLElement} unitEl The unit's element
    */
    handleMouseEnter: function (unitEl) {
        var re = new RegExp('(\\s|^)' + CMS.config.unitElClassName + '(\\s|$)');
        do {
            if (re.test(unitEl.className)) {
                this.setHoveredUnitEl(unitEl, false, this.isEditable(unitEl.id));
                break;
            }
            unitEl = unitEl.parentNode;
        } while (!!unitEl);
    },

    /**
    * @private
    * Handles mouse down events within the unit element
    * @param {HTMLElement} unitEl The unit's element
    * @param {MouseEvent} e The browser's mousedown event
    */
    handleMouseDown: function (unitEl, e) {
        var clickedSectionEl = null;

        if (!Ext.isEmpty(unitEl.getAttribute(CMS.config.inlineSectionHTMLAttribute))) {
            clickedSectionEl = unitEl;
        } else {
            //unitEl isn't the section itself, so iterate over child elements
            var inlineEditorElements = this.getElementsByAttribute(unitEl, CMS.config.inlineSectionHTMLAttribute, null, CMS.config.inlineSectionTagName);

            if (inlineEditorElements.length) {
                if (e) {
                    // check if click event was within an editable section
                    e = Ext.EventObject.setEvent(e);
                    for (var i = 0, l = inlineEditorElements.length; i < l; i++) {
                        if (e.within(inlineEditorElements[i], false, true)) {
                            clickedSectionEl = inlineEditorElements[i];
                            break;
                        }
                    }
                }
            }
        }
        this.setSelectedUnitEl(unitEl, false, this.isEditable(unitEl.id));

        if (clickedSectionEl) {
            this.fireEvent('selectSection', unitEl, clickedSectionEl, e);
        }
    },

    /**
    * Focus the currently hovered unit
    */
    selectHoveredUnitEl: function () {
        this.setSelectedUnitEl(this.hoveredUnitEl, false, this.isEditable(this.hoveredUnitEl.id));
    },

    /**
    * Focus the unit with the specified ID
    * @param {String} id The unit's ID
    * @param {Boolean} [hoverOnly] <tt>true</tt> to prevent selecting the unit, as if it had been clicked
    * Defaults to <tt>false</tt>
    */
    selectUnitById: function (id, hoverOnly) {
        var unit = this.unitStore.getById(id);
        if (!unit) {
            return;
        }
        if (this.loading || !SB.util.isEmptyObject(this.replaceRequestIds) || !this.rendered) {
            this.selectIdAfterLoad = id;
            return;
        }
        if (!this.getFrameDocument()) {
            console.info('[TheEditableIframe] Frame not accessible. Returning.');
            return;
        }

        // element is an extension module
        if (unit.isExtensionUnit()) {
            // select parent unit which is not an extension unit
            if (!hoverOnly) {
                var parent = this.unitStore.getNextParentNonExtensionUnit(unit);
                this.selectUnitById(parent.id, hoverOnly);
            }
        } else {
            var dom = this.getFrameDocument().getElementById(id);
            // SBCMS-1265 HACK: element not found? try to find it after load (fixes missing selection after newly inserted unit)
            //                  this should be only done if there is a render running (Start: CMSrender Event End: RenderCallbac
            if (!dom) {
                this.selectIdAfterLoad = id;
                return; // end here
            }

            var action = hoverOnly ? this.setHoveredUnitEl : this.setSelectedUnitEl;
            action.call(this, dom, true, this.isEditable(unit));
        }


    },

    /**
    * Outline all units in the current page
    * @param {Boolean} activate <tt>true</tt> to turn outlines on, <tt>false</tt>
    * to turn off
    */
    markAllUnitEls: function (activate) {
        console.log('[TheEditableIframe] markAllUnitEls');
        if (!this.getFrameDocument()) {
            console.info('[TheEditableIframe] Frame not accessible. Returning.');
            return;
        }
        var allUnitEls = this.getElementsByAttribute(null, 'class', CMS.config.unitElClassName, CMS.config.unitElTagName);
        if (!!activate) {
            this.previouslySelectedUnitEl = this.selectedUnitEl;
            this.setSelectedUnitEl(null);
            Ext.each(allUnitEls, function (unitEl) {
                SB.Element.forceStyle(unitEl, this.outlineStyle);
            }, this);
        } else {
            Ext.each(allUnitEls, SB.Element.restoreStyle);
            this.setSelectedUnitEl(this.previouslySelectedUnitEl);
        }
    },

    /**
    * @private
    * Checks if the unit is editable
    * @param {String|Object} unit The unit to be checked or its id
    * @return {Boolean} Whether the unit is
    * editable or not.
    */
    isEditable: function (unit) {
        if (Ext.isString(unit)) {
            unit = this.unitStore.getById(unit);
        }
        return unit && (unit.isEditableInMode(this.type)
                     || unit.isDeletableInMode(this.type)
                     || unit.hasInsertableChildrenInMode(this.type));
    }
});

Ext.reg('CMStheeditableiframe', CMS.liveView.TheEditableIframe);
