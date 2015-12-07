// add browser check for IE9 and 10, fix check for IE6
(function () {
    var ua = navigator.userAgent.toLowerCase();
    var check = function (r) {
        return r.test(ua);
    };

    Ext.apply(Ext, {
        isIE6: Ext.isIE && check(/msie 6/),
        isIE9: Ext.isIE && check(/msie 9/),
        isIE10: Ext.isIE && check(/msie 10/)
    });
})();

// clear activeMenuBtn cache when that button is deleted
// http://www.sencha.com/forum/showthread.php?108267&p=506214#post506214
// Also, hide "more" tool when invisible button is removed
Ext.override(Ext.Toolbar, {
    onRemove: function (c) {
        if (c == this.activeMenuBtn) {
            delete this.activeMenuBtn;
        }
        Ext.Toolbar.superclass.onRemove.call(this);
        this.trackMenu(c, true);
        if (this.layout.hiddenItems) {
            this.layout.hiddenItems.remove(c);
        }
    }
});


// http://www.sencha.com/forum/showthread.php?91111
Ext.getPreciseScrollBarWidth = function () {
    return Ext.getScrollBarWidth() - 2;
};


Ext.override(Ext.Component, {
    // http://www.sencha.com/forum/showthread.php?p=491039#post491039
    findParentByType: function (xtype, shallow) {
        return this.findParentBy(function (c) {
            return c.isXType(xtype, shallow);
        });
    },

    // don't interrupt search for xtypes if one Class in the inheritance chain does not provide an xtype
    getXTypes: function () {
        var tc = this.constructor;
        if (!tc.xtypes) {
            var c = [],
                sc = this;
            while (sc && sc.constructor) {
                if (sc.constructor.xtype) {
                    c.unshift(sc.constructor.xtype);
                }
                sc = sc.constructor.superclass;
            }
            //tc.xtypeChain = c;
            tc.xtypes = c.join('/');
        }
        return tc.xtypes;
    }
});


// http://www.sencha.com/forum/showthread.php?81302&p=420286#post420286
Ext.override(Ext.Button, {
    setText: function (text) {
        this.text = text;
        if (this.el) {
            this.btnEl.update('<span>' + (text || '&#160;') + '</span>');
            this.setButtonClass();
        }
        this.doAutoWidth();
        return this;
    }
});

/*
* permit event bubbling from iframe element
* example: <pre><code>var iframeEl = new Ext.ux.ManagedIFrame.Element();
iframeEl.relayEvents(['mousedown', 'click']);
Ext.getBody().on('mousedown', function (e) {
    console.log('mousedown detected on ', e.getTarget(), ' in pos ', e.xy);
});
*/
Ext.override(Ext.Element, {
    relayEvents: (function () {
        var cloneEvent; // we can't dispatch the original event object, since it comes from a different document.
        var getScroll = function (document) {
            var dd = document.documentElement,
                db = document.body;
            if (dd && (dd.scrollTop || dd.scrollLeft)) {
                return [dd.scrollLeft, dd.scrollTop];
            } else if (db) {
                return [db.scrollLeft, db.scrollTop];
            } else {
                return [0, 0];
            }
        };
        // FIXME: use http://github.com/dperini/nwevents/ instead of manually doing this stuff
        if (Ext.isIE) {
            cloneEvent = function (evt, target, self) {
                var result = document.createEventObject(evt.type);
                for (var i in result) {
                    try {
                        if (result.hasOwnProperty(i)) {
                            result[i] = evt[i];
                        }
                    } catch (ignore) {}
                }
                result.target = self.dom;

                if (typeof result.clientX == 'number') {
                    var repairXY = self.getXY();
                    var repairScrollXY = getScroll(document);
                    repairXY[0] -= repairScrollXY[0];
                    repairXY[1] -= repairScrollXY[1];
                    if (target.documentElement) {
                        repairScrollXY = getScroll(target);
                        repairXY[0] -= repairScrollXY[0];
                        repairXY[1] -= repairScrollXY[1];
                        // FIXME: this needs to be done for every ancestor iframe element in case of nested iframes
                    }
                    //result.clientX += result.repairX; // this does not seem to have any effect in IE
                    //result.clientY += result.repairY; // this does not seem to have any effect in IE
                    // store repairXY in the event object, so we can handle it later
                    result.repairX = repairXY[0];
                    result.repairY = repairXY[1];
                }
                return result;
            };
        } else if (document.createEvent) { // standard-compliant browsers
            var supportedEvents = ['click', 'mousedown', 'mouseup', 'mousemove', 'scroll', 'resize', 'change']; // mouseover and mouseout are fired natively
            cloneEvent = function (evt, target, self) {
                var repairXY = self.getXY();
                var repairScrollXY = getScroll(document);
                repairXY[0] -= repairScrollXY[0];
                repairXY[1] -= repairScrollXY[1];
                if (supportedEvents.indexOf(evt.type) == -1) {
                    throw new Error('Event type ' + evt.type + ' is not supported.');
                }
                var result;
                // W3C makes it really difficult to create event objects
                // http://www.w3.org/TR/2003/NOTE-DOM-Level-3-Events-20031107/events.html#event-scroll
                if (/^key/.test(evt.type)) {
                    // TODO: result = document.createEvent('KeyboardEvent');
                } else if (/^(mouse|click)/.test(evt.type)) {
                    result = document.createEvent('MouseEvent');
                    /* initMouseEvent args: type, canBubble, cancelable, view, detail, screenX, screenY, clientX, clientY, ctrlKey, altKey, shiftKey, metaKey, button, relatedTarget */
                    result.initMouseEvent(evt.type, true, true, window, evt.detail, evt.screenX, evt.screenY, evt.clientX + repairXY[0], evt.clientY + repairXY[1], evt.ctrlKey, evt.altKey, evt.shiftKey, evt.metaKey, evt.button, null);
                    // TODO: tamper clientX and clientY
                } else if (/^((un)?load)|scroll|abort|error|select|change|submit|reset|focus|blur|resize/.test(evt.type)) {
                    // TODO: test abort, error etc.
                    result = document.createEvent('MutationEvent');
                    /* initMutationEvent args: type, canBubble, cancelable, relatedNode, prevValue, newValue, attrName, attrChangeArg */
                    result.initMutationEvent(evt.type, evt.canBubble, true, evt.relatedNode, evt.prevValue, evt.newValue, evt.attrName, evt.attrChangeArg);
                } /*else {
                    // TODO: add TextEvent, MutationNameEvent if needed
                }*/
                // console.log('firing ', result.type, '[', result.screenX, ',', result.screenY, ']', '[', result.clientX, ',', result.clientY, ']');
                return result;
            };
        } else {
            throw new Error('Unable to determine the browser\'s event creation model.');
        }

        var dispatchEvent;
        if (document.documentElement.fireEvent) { // IE
            dispatchEvent = function (el, evt) {
                try { el.fireEvent('on' + evt.type, evt); } catch (ignore) {}
            };
        } else if (document.documentElement.dispatchEvent) { // standards-compliant browsers
            dispatchEvent = function (el, evt) {
                el.dispatchEvent(evt);
            };
        } else {
            throw new Error('Unable to determine the browser\'s event dispatchment model.');
        }

        /**
        * Capture and re-fire events on this element.
        * This is particularly for use with iframe elements, since mouse events
        * don't bubble up to the containing document element.
        * Does not work with nested iframes in IE.
        * @member Ext.Element
        * @method relayEvents
        * @param {Object} target Where to capture events. Usually this should be the iframe's documentElement
        * @param {Array/String} evtNames Names of the events which should be relayed
        */
        return function (target, evtNames) {
            console.log('[ext-overrides] iframe.relayEvents called for ', evtNames, target);
            if (!target) {
                throw new Error('relayEvents called with invalid target. Cross-domain frame?');
            } else if (this.dom.tagName.toLowerCase() != 'iframe') {
                throw new Error('relayEvents must be called on an iframe element');
            }
            if (!Ext.isArray(evtNames)) {
                evtNames = [evtNames];
            }
            var addListener = function (el, evt, handler) {
                if (el.attachEvent) {
                    el.attachEvent('on' + evt, handler);
                } else {
                    el.addEventListener(evt, handler, false);
                }
            };
            var self = this;
            this.relayListener = function (e) {
                // console.log('firing ' + e.type + ' with clientXY (' + e.clientX + ',' + e.clientY + ')');
                if (typeof Ext == 'undefined') { // unload
                    return false;
                }
                var evt = cloneEvent(e, target, self);
                // if (evt.type !== 'mousemove') console.log('firing ' + evt.type + ' with clientXY (' + evt.clientX + ',' + evt.clientY + ')' + ' repairXY (' + evt.repairX + ',' + evt.repairY + ')');
                dispatchEvent(self.dom, evt);
            };
            this.relayEventNames = (this.relayEventNames || []).concat(evtNames).filter(function(value, index, self) {
                    return self.indexOf(value) === index;
            });
            for (var i = 0; i < this.relayEventNames.length; i++) {
                console.log('adding ' + this.relayEventNames[i] + ' listener');
                addListener(target, this.relayEventNames[i], this.relayListener);
            }
            Ext.EventManager.addListener(window, 'unload', function () {
                this.unrelayEvents(target);
            }, this);
        };
    })(),

    unrelayEvents: function (target) {
        if (!target || !this.relayListener) {
            return;
        }
        var removeListener = function (el, evt, handler) {
            if (el.attachEvent) {
                el.detachEvent('on' + evt, handler);
            } else {
                el.removeEventListener(evt, handler, false);
            }
        };
        for (var i = 0; i < this.relayEventNames.length; i++) {
            removeListener(target, this.relayEventNames[i], this.relayListener);
        }
        delete this.relayEventNames;
        delete this.relayListener;
    }
});
// now we need to handle the repairXY;
if (Ext.isIE) {
    Ext.EventObject.setEvent = function (e) {
        Ext.EventObjectImpl.prototype.setEvent.call(this, e);
        if (e && typeof e.repairX == 'number') {
            // e.type != 'mousemove' && console.log('applying repairXY ', e.repairX, ' ', e.repairY, ' to ', this.xy);
            this.xy[0] += e.repairX || 0;
            this.xy[1] += e.repairY || 0;
        }
        return this;
    };
}


// http://www.sencha.com/forum/showthread.php?83615
if (Ext.isGecko) {
    (function () {
        var blurEventObject;
        Ext.EventObject.setEvent = function (e) {
            if (e.type == 'blur') {
                blurEventObject = blurEventObject || new Ext.EventObjectImpl();
                return blurEventObject.setEvent.apply(blurEventObject, arguments);
            } else {
                return Ext.EventObjectImpl.prototype.setEvent.apply(this, arguments);
            }
        };
    })();
}


// checkbox requires the "checked" attribute to render correctly
Ext.override(Ext.form.Checkbox, {
    initComponent: function () {
        this.checked = !!this.value;
        Ext.form.Checkbox.superclass.initComponent.call(this);
        this.addEvents('check');
    }
});


// make sliderfield fire 'change' event
// http://www.sencha.com/forum/showthread.php?107827
Ext.override(Ext.form.SliderField, {
    onChange: function (slider, v, oldValue) {
        this.setValue(v, undefined, true);
        this.fireEvent('change', this, v, oldValue);
    }
});


// http://www.sencha.com/forum/showthread.php?82021
Ext.override(Ext.grid.GridDragZone, {
    onInitDrag: function (x, y) {
        var data = this.dragData;
        this.ddel.innerHTML = this.grid.getDragDropText();
        this.proxy.update(this.ddel);
        // http://www.sencha.com/forum/showthread.php?110405
        this.grid.fireEvent('startdrag', this.grid, data.selections);
    },
    onEndDrag: function (data, e) {
        this.grid.fireEvent('enddrag', this.grid, data.selections, e);
    },
    onValidDrop: function (dd, e, id) {
        this.grid.fireEvent('dragdrop', this.grid, this.dragData.selections, dd, e);
        this.hideProxy();
    }
});


// overwrite GridPanel's totally useless autoExpandMax value
Ext.grid.GridPanel.prototype.autoExpandMax = Number.MAX_VALUE;


// By default, there is no xtype for Ext.form.TwinTriggerField
Ext.reg('twintrigger', Ext.form.TwinTriggerField);


/*
* By default, Ext.data.ArrayReader fires a "loadexception" event with type="response"
* if the root property is missing.
* Here, we overwrite this behaviour. The "loadexception" event is now fired with
* type="remote" if the response is valid JSON with success==false
*/
Ext.override(Ext.data.ArrayReader, {
    readRecords: function (o) {
        this.arrayData = o;
        var s = this.meta,
            sid = s ? Ext.num(s.idIndex, s.id) : null,
            RecordType = this.recordType,
            fields = RecordType.prototype.fields,
            records = [],
            success = true,
            v;

        var root = this.getRoot(o);
        var totalRecords;
        if (root) {
            for (var i = 0, len = root.length; i < len; i++) {
                var n = root[i],
                    values = {},
                    id = ((sid || sid === 0) && n[sid] !== undefined && n[sid] !== '' ? n[sid] : null);
                for (var j = 0, jlen = fields.length; j < jlen; j++) {
                    var f = fields.items[j],
                        k = f.mapping !== undefined && f.mapping !== null ? f.mapping : j;
                    v = n[k] !== undefined ? n[k] : f.defaultValue;
                    v = f.convert(v, n);
                    values[f.name] = v;
                }
                var record = new RecordType(values, id);
                record.json = n;
                records[records.length] = record;
            }

            totalRecords = records.length;

            if (s.totalProperty) {
                v = parseInt(this.getTotal(o), 10);
                if (!isNaN(v)) {
                    totalRecords = v;
                }
            }
        } else {
            totalRecords = 0;
        }

        if (s.successProperty) {
            v = this.getSuccess(o);
            if (v === false || v === 'false') {
                success = false;
            }
        }

        return {
            success: success,
            records: records,
            totalRecords: totalRecords
        };
    }
});

/*
* By default, Ext.data.JsonReader fires a "loadexception" event with type="response"
* if the root property is missing.
* Here, we overwrite this behaviour. The "loadexception" event is now fired with
* type="remote" if the response is valid JSON with success==false
*/
Ext.override(Ext.data.JsonReader, {

    readRecords: function (o) {
        this.jsonData = o;
        if (o.metaData) {
            this.onMetaChange(o.metaData);
        }
        var s = this.meta,
            //Record = this.recordType,
            //f = Record.prototype.fields,
            //fi = f.items,
            //fl = f.length,
            v;
        var root = this.getRoot(o);
        var c = root ? root.length : 0,
            totalRecords = c,
            success = true;
        if (s.totalProperty) {
            v = parseInt(this.getTotal(o), 10);
            if (!isNaN(v)) {
                totalRecords = v;
            }
        }
        if (s.successProperty) {
            v = this.getSuccess(o);
            if (v === false || v === 'false') {
                success = false;
            }
        }

        if (success === false && !root) {
            return {
                success: false,
                records: [],
                totalRecords: 0
            };
        }

        return {
            success: success,
            records: this.extractData(root, true),
            totalRecords: totalRecords
        };
    },

    // for some reason, JsonReader passes an incorrect response argument
    readResponse: function (action, response) {
        var o = (response.responseText !== undefined) ? Ext.decode(response.responseText) : response;
        if (!o) {
            throw new Ext.data.JsonReader.Error('response');
        }

        var root = this.getRoot(o);
        if (action === Ext.data.Api.actions.create) {
            var def = Ext.isDefined(root);
            if (def && Ext.isEmpty(root)) {
                throw new Ext.data.JsonReader.Error('root-empty', this.meta.root);
            } else if (!def) {
                throw new Ext.data.JsonReader.Error('root-undefined-response', this.meta.root);
            }
        }


        var res = new Ext.data.Response({
            action: action,
            success: this.getSuccess(o),
            data: (root) ? this.extractData(root, false) : [],
            message: this.getMessage(o),
            raw: response // original: "raw: o"
        });


        if (Ext.isEmpty(res.success)) {
            throw new Ext.data.JsonReader.Error('successProperty-response', this.meta.successProperty);
        }
        return res;
    },

    // prevent JSONReader from throwing errors when response does not contain required data
    createAccessor: function () {
        var re = /[\[\.]/;
        return function (expr) {
            if (Ext.isEmpty(expr)) {
                return Ext.emptyFn;
            }
            if (Ext.isFunction(expr)) {
                return expr;
            }
            var i = String(expr).search(re);
            if (i >= 0) {
                /* jshint evil: true */
                return new Function('obj', 'try { return obj' + (i > 0 ? '.' : '') + expr + '} catch (e) { return []; }');
                /* jshint evil: false */
            }
            return function (obj) {
                return obj[expr];
            };

        };
    }()
});

// allow empty and white-space only strings in Ext.util.JSON.decode
Ext.decode = Ext.util.JSON.decode = function (input) {
    var dc;
    var re = /^\s*$/;
    if (!dc) {
        if (Ext.USE_NATIVE_JSON && window.JSON && JSON.toString() == '[object JSON]') {
            dc = function (input) {
                if (re.test(input)) {
                    return '';
                }
                return JSON.parse(input);
            };
        } else {
            dc = function (input) {
                if (typeof input != 'string') {
                    console.trace();
                    throw 'Ext.util.JSON.decode called with non-string argument.';
                }
                if (re.test(input)) {
                    return '';
                }
                /* jshint evil: true */
                return eval('(' + input + ')');
                /* jshint evil: false */
            };
        }
    }
    return dc(input);
};

// disable textfield label when textfield is disabled
Ext.override(Ext.form.TextField, {
    onDisable: function () {
        Ext.form.TextField.superclass.onDisable.call(this);
        if (Ext.isIE) {
            this.el.dom.unselectable = 'on';
        }
        if (this.label) {
            this.label.addClass(this.disabledClass);
        }
    },
    onEnable: function () {
        Ext.form.TextField.superclass.onEnable.call(this);
        if (Ext.isIE) {
            this.el.dom.unselectable = '';
        }
        if (this.label) {
            this.label.removeClass(this.disabledClass);
        }
    }
});
// disable button label when button is disabled
Ext.override(Ext.Button, {
    onDisable: function () {
        this.onDisableChange(true);
        if (this.label) {
            this.label.addClass(this.disabledClass);
        }
    },
    onEnable: function () {
        this.onDisableChange(false);
        if (this.label) {
            this.label.removeClass(this.disabledClass);
        }
    }
});

/*
* allows MixedCollections and other objects that contain an own "each" method
* as the first argument to Ext.each
*/
Ext.each = function (array, fn, scope) {
    if (Ext.isEmpty(array, true)) {
        return;
    }
    if (typeof array.each == 'function') {
        return array.each(fn, scope);
    }
    if (!Ext.isIterable(array) || Ext.isPrimitive(array)) {
        array = [array];
    }
    for (var i = 0, len = array.length; i < len; i++) {
        if (fn.call(scope || array[i], array[i], i, array) === false) {
            return i;
        }
    }
};


/*
* Do not call onNodeOver/onNodeOut on node tree when mouse enters/leaves icon
* http://www.sencha.com/forum/showthread.php?116786
*/
Ext.override(Ext.tree.TreeEventModel, {
    getNodeTarget: function (e) {
        return e.getTarget('.x-tree-node-el', 6);
    },
    delegateOver: function (e, t) {
        if (!this.beforeEvent(e)) {
            return;
        }
        if (this.lastEcOver) {
            this.onIconOut(e, this.lastEcOver);
            delete this.lastEcOver;
        }
        if (e.getTarget('.x-tree-ec-icon', 1)) {
            this.lastEcOver = this.getNode(e);
            this.onIconOver(e, this.lastEcOver);
        }
        if (!!this.getNodeTarget(e)) {
            this.onNodeOver(e, this.getNode(e));
        }
    },
    delegateOut: function (e, t) {
        if (!this.beforeEvent(e)) {
            return;
        }
        if (e.getTarget('.x-tree-ec-icon', 1)) {
            var n = this.getNode(e);
            this.onIconOut(e, n);
            if (n == this.lastEcOver) {
                delete this.lastEcOver;
            }
        }
        if ((t = this.getNodeTarget(e)) && !e.within(t, true, true)) {
            this.onNodeOut(e, this.getNode(e));
        }
    },
    onNodeOver: function (e, node) {
        if (node == this.lastOverNode || !node) {
            return;
        }
        if (this.lastOverNode && this.lastOverNode.ui) {
            this.lastOverNode.ui.onOut(e);
        }
        this.lastOverNode = node;
        node.ui.onOver(e);
    },
    onNodeOut: function (e, node) {
        delete this.lastOverNode;
        node.ui.onOut(e);
    }
});


// missing featre in RowSelectionModel
Ext.override(Ext.grid.RowSelectionModel, {
    /**
    * Deselects a record.  Before deselecting a record, checks if the selection model
    * {@link Ext.grid.AbstractSelectionModel#isLocked is locked}.
    * If this check is satisfied the record will be deselected and followed up by
    * firing the {@link #rowdeselect} and {@link #selectionchange} events.
    * @member Ext.grid.RowSelectionModel
    * @param {Ext.data.Record} record The record to deselect
    * @param {Boolean} preventViewNotify (optional) Specify true to
    * prevent notifying the view (disables updating the selected appearance)
    */
    deselectRecord: function (record, preventViewNotify) {
        var index = this.grid.store.indexOf(record);
        if (index != -1) {
            this.deselectRow(index);
        }
    },

    /**
    * Deselects a set of records. Before deselecting a record, checks if the selection model
    * {@link Ext.grid.AbstractSelectionModel#isLocked is locked}.
    * If this check is satisfied the record will be deselected and followed up by
    * firing the {@link #rowdeselect} and {@link #selectionchange} events.
    * @member Ext.grid.RowSelectionModel
    * @param {Array} records The {@link Ext.data.Record records} to deselect
    * @param {Boolean} preventViewNotify (optional) Specify true to
    * prevent notifying the view (disables updating the selected appearance)
    */
    deselectRecords: function (records, preventViewNotify) {
        var deselectRecord = this.deselectRecord;
        Ext.each(records, function (record) {
            deselectRecord(record, preventViewNotify);
        });
    }
});


// fix incorrect positioning of Ext.QuickTip with positions other than tl-br
(function () {
    var originalShowAt = Ext.QuickTip.prototype.showAt;
    Ext.override(Ext.QuickTip, {
        showAt: function () {
            originalShowAt.apply(this, arguments);
            var t = this.activeTarget;
            if (t && t.align) {
                this.el.alignTo(t.el, t.align);
            }
        }
    });
})();

// Fix non-working enableToggle config in SplitButton, cf.
// http://www.sencha.com/forum/showthread.php?109656
Ext.override(Ext.Button, {
    onClick: function (e) {
        if (e) {
            e.preventDefault();
        }
        if (e.button !== 0) {
            return;
        }
        if (!this.disabled) {
            this.doToggle();
            if (this.menu && !this.hasVisibleMenu() && !this.ignoreNextClick) {
                this.showMenu();
            }
            this.fireEvent('click', this, e);
            if (this.handler) {

                this.handler.call(this.scope || this, this, e);
            }
        }
    },
    doToggle: function () {
        if (this.enableToggle && (this.allowDepress !== false || !this.pressed)) {
            this.toggle();
        }
    }
});
Ext.override(Ext.SplitButton, {
    onClick: function (e, t) {
        e.preventDefault();
        if (!this.disabled) {
            if (this.isClickOnArrow(e)) {
                if (this.menu && !this.menu.isVisible() && !this.ignoreNextClick) {
                    this.showMenu();
                }
                this.fireEvent('arrowclick', this, e);
                if (this.arrowHandler) {
                    this.arrowHandler.call(this.scope || this, this, e);
                }
            } else {
                this.doToggle();
                this.fireEvent('click', this, e);
                if (this.handler) {
                    this.handler.call(this.scope || this, this, e);
                }
            }
        }
    }
});

Ext.override(Ext.grid.GridView, {
    // prevent error when updating a store with bound GridPanel that has not been fully rendered
    onUpdate: function (ds, record) {
        if (this.hasRows()) {
            this.refreshRow(record);
        }
    },

    // do not hide invisible gridviews (with hideHeaders==true)
    layout: function () {
        if (!this.mainBody) {
            return;
        }
        var g = this.grid;
        var c = g.getGridEl();
        var csize = c.getSize(true);
        var vw = csize.width;
        var vh;

        // this is the original logic for determining GridViews with display:none... WTF??
        if (!g.hideHeaders && (vw < 20 || csize.height < 20)) {
            return;
        }
        if (!vw || !csize.height) { // this should do the trick
            return;
        }

        if (g.autoHeight) {
            this.scroller.dom.style.overflow = 'visible';
            if (Ext.isWebKit) {
                this.scroller.dom.style.position = 'static';
            }
        } else {
            this.el.setSize(csize.width, csize.height);

            var hdHeight = this.mainHd.getHeight();
            vh = csize.height - (hdHeight);

            this.scroller.setSize(vw, vh);
            if (this.innerHd) {
                this.innerHd.style.width = (vw) + 'px';
            }
        }
        if (this.forceFit) {
            if (this.lastViewWidth != vw) {
                this.fitColumns(false, false);
                this.lastViewWidth = vw;
            }
        } else {
            this.autoExpand();
            this.syncHeaderScroll();
        }
        this.onLayout(vw, vh);
    }
});

// enable selection of collapsed node's child nodes
(function () {
    var originalRenderElements = Ext.tree.TreeNodeUI.prototype.renderElements;
    Ext.override(Ext.tree.TreeNodeUI, {
        addClass: function (cls) {
            if (this.elNode) {
                Ext.fly(this.elNode).addClass(cls);
            } else {
                this.classes = this.classes || [];
                if (this.classes.indexOf(cls) == -1) {
                    this.classes.push(cls);
                }
            }
        },

        removeClass: function (cls) {
            if (this.elNode) {
                Ext.fly(this.elNode).removeClass(cls);
            } else if (this.classes) {
                this.classes.remove(cls);
            }
        },

        renderElements: function () {
            originalRenderElements.apply(this, arguments);
            if (this.classes) {
                this.addClass(this.classes.join(' '));
                delete this.classes;
            }
        }
    });
    Ext.override(Ext.tree.DefaultSelectionModel, {
        select: function (node, selectNextNode) {
            if (selectNextNode && (!node.ui.wrap || !Ext.fly(node.ui.wrap).isVisible())) {
                return selectNextNode.call(this, node);
            }
            var last = this.selNode;
            if (node == last) {
                node.ui.onSelectedChange(true);
            } else if (this.fireEvent('beforeselect', this, node, last) !== false) {
                if (last && last.ui) {
                    last.ui.onSelectedChange(false);
                }
                this.selNode = node;
                node.ui.onSelectedChange(true);
                this.fireEvent('selectionchange', this, node, last);
            }
            return node;
        }
    });
})();


// allow single item in "buttons"/"tbar"/"fbar" config
(function () {
    var originalCreateToolbar = Ext.Panel.prototype.createToolbar;
    Ext.override(Ext.Panel, {
        createToolbar: function (tb, options) {
            if (tb && !tb.events && !tb.items && !Ext.isArray(tb)) {
                tb = [tb];
            }
            return originalCreateToolbar.call(this, tb, options);
        }
    });
})();


// Improved cleanup; otherwise this causes trouble with Ext.Component.mon() when the target gets destroyed
Ext.override(Ext.util.Observable, {
    removeListener: function (eventName, fn, scope) {
        if (this.events) {
            var ce = this.events[eventName.toLowerCase()];
            if (typeof ce == 'object') {
                ce.removeListener(fn, scope);
            }
        }
    },

    purgeListeners: function () {
        var events = this.events,
            evt,
            key;
        for (key in events) {
            evt = events[key];
            if (typeof evt == 'object') {
                evt.clearListeners();
            }
        }
        this.events = {};
    }
});
Ext.util.Observable.prototype.un = Ext.util.Observable.prototype.removeListener;


// Remove resize listener when destroying viewport
Ext.override(Ext.Viewport, {
    destroy: function () {
        Ext.EventManager.removeResizeListener(this.fireResize, this);
        Ext.Viewport.superclass.destroy.apply(this, arguments);
    }
});


// Remove listeners when destroying DDMgr
Ext.dd.DragDropMgr.unregAll = (function (original) {
    return function () {
        original.call(this);
        Ext.EventManager.un(document, 'mouseup',   this.handleMouseUp, this);
        Ext.EventManager.un(document, 'mousemove', this.handleMouseMove, this);
        Ext.EventManager.un(window,   'resize',    this._onResize, this);
        Ext.dd.DragDropMgr.unregAll = Ext.emptyFn;
    };
})(Ext.dd.DragDropMgr.unregAll);


Ext.override(Ext.tree.TreePanel, {
    /**
    * setRootNode has to be overwritten because if the tree node uiProvider is
    * set, the rootVisible attribute is ignored. This will now set the ui attribute
    * of the node to RootTreeNodeUI and not to the specified uiProvider.
    * http://www.sencha.com/forum/showthread.php?105047
    */
    setRootNode: function (node) {
        this.destroyRoot();
        if (!node.render) {
            node = this.loader.createNode(node);
        }
        this.root = node;
        node.ownerTree = this;
        node.isRoot = true;
        this.registerNode(node);
        if (!this.rootVisible) {
            node.ui = new Ext.tree.RootTreeNodeUI(node);
        }
        if (this.innerCt) {
            this.clearInnerCt();
            this.renderRoot();
        }
        return node;
    }
});


// unselect multiple selections on single click
Ext.override(Ext.grid.RowSelectionModel, {
    init: function (grid) {
        this.grid = grid;
        this.initEvents();
        if (grid.enableDragDrop || grid.enableDrag) {
            grid.on('rowclick', function (grid, index, evt) {
                if (!evt.hasModifier()) {
                    grid.getSelectionModel().selectRow(index);
                }
            });
        }
    }
});


Ext.override(Ext.form.Checkbox, {
    initComponent: function () {
        // override stupid boxLabel implementation, which causes layout issues
        this.boxLabel = this.boxLabel || '&#160;';
        // WTF? Checkbox does not respect 'value' by default...
        if (this.hasOwnProperty('value')) {
            this.checked = this.value;
        }
        Ext.form.Checkbox.superclass.initComponent.call(this);
    }
});


// only store "modified" if the new value does not equal the old one
Ext.override(Ext.data.Record, {
    set: function (name, value) {
        var encode = Ext.isPrimitive(value) ? String : Ext.encode;
        if (encode(this.data[name]) == encode(value)) {
            return;
        }
        this.dirty = true;
        if (!this.modified) {
            this.modified = {};
        }
        if (this.modified[name] === undefined && this.data[name] !== value) {
            this.modified[name] = this.data[name];
        }
        this.data[name] = value;
        if (!this.editing) {
            this.afterEdit();
        }
    }
});


// Unmask element when loadmask is destroyed. Hell knows why this is not implemented
Ext.override(Ext.LoadMask, {
    destroy: function () {
        this.el.unmask(true);
        if (this.store) {
            this.store.un('beforeload', this.onBeforeLoad, this);
            this.store.un('load', this.onLoad, this);
            this.store.un('exception', this.onLoad, this);
        } else {
            var um = this.el.getUpdater();
            um.un('beforeupdate', this.onBeforeLoad, this);
            um.un('update', this.onLoad, this);
            um.un('failure', this.onLoad, this);
        }
    }
});

// Ext.data.DataReader needlessly accesses Ext.data.XmlReader.
// This dirty hack makes it possible to not include the latter.
if (!Ext.data.XmlReader) {
    Ext.data.XmlReader = Ext.emptyFn;
}

// SBCMS-631 fix for correct slider position when slider is not visible
// Ext sets innerEls width to 0 so we need to cache the old width
Ext.override(Ext.slider.MultiSlider, {
    getRatio: function () {
        var w = this.innerEl.getWidth(),
            v = this.maxValue - this.minValue;

        if (this.innerEl.isVisible(true)) {
            this.innerElCachedWidth = w;
        } else {
            w = this.innerElCachedWidth || 0;
        }

        return v === 0 ? w : (w / v);
    }
});

// SBCMS-669 fix for correct element width if the element is not visible
Ext.override(Ext.form.TriggerField, {
    onResize : function (w, h) {
        Ext.form.TriggerField.superclass.onResize.call(this, w, h);
        var tw = this.getTriggerWidth();

        if (Ext.isNumber(w)) {
            this.el.setWidth(w - tw);
            this.wrap.setWidth(w);
        } else {
            this.wrap.setWidth(this.el.getWidth() + tw);
        } // endif
    }
});

/* SBCMS-674
* http://www.sencha.com/forum/showthread.php?96686
* So, any time ExtJS attempt to compute the marginRight value of a hidden component on webkit,
* it also explicitly sets the style.display property to 'none'. Later, when show() is called,
* ExtJS removes the CSS rule x-hide-display, but this leaves the explicitly set 'none' value
* on the style.display property. This is why show() appears to have no affect.
*/
if (Ext.isWebKit) {
    (function () {
        var originalGetStyle = Ext.Element.prototype.getStyle;
        Ext.override(Ext.Element, {
            getStyle: function (prop) {
                if (prop == Ext.Element.prototype.margins.r) {
                    var old = this.dom.style.display;
                    var result = originalGetStyle.call(this, prop);
                    this.dom.style.display = old;
                    return result;
                } else {
                    return originalGetStyle.call(this, prop);
                }
            }
        });
    })();
}

/*
* Box-sizing was changed beginning with Chrome v19.  For background information, see:
* http://code.google.com/p/chromium/issues/detail?id=124816
* https://bugs.webkit.org/show_bug.cgi?id=78412
* https://bugs.webkit.org/show_bug.cgi?id=87536
* http://www.sencha.com/forum/showthread.php?198124-Grids-are-rendered-differently-in-upcoming-versions-of-Google-Chrome&p=824367
*/
if (!Ext.isDefined(Ext.webKitVersion)) {
    Ext.webKitVersion = Ext.isWebKit ? parseFloat(/AppleWebKit\/([\d.]+)/.exec(navigator.userAgent)[1], 10) : NaN;
}
if (Ext.isWebKit && Ext.webKitVersion >= 535.2) { // probably not the exact version, but the issues started appearing in chromium 19
    Ext.override(Ext.grid.ColumnModel, {
        getTotalWidth: function (includeHidden) {
            if (!this.totalWidth) {
                var boxsizeadj = 2;
                this.totalWidth = 0;
                for (var i = 0, len = this.config.length; i < len; i++) {
                    if (includeHidden || !this.isHidden(i)) {
                        this.totalWidth += (this.getColumnWidth(i) + boxsizeadj);
                    }
                }
            }
            return this.totalWidth;
        }
    });

    Ext.onReady(function () {
        Ext.get(document.body).addClass('ext-chrome-fixes');
        Ext.util.CSS.createStyleSheet('@media screen and (-webkit-min-device-pixel-ratio:0) {.x-grid3-cell{box-sizing: border-box !important;}}', 'chrome-fixes-box-sizing');
    });
}

Ext.override(Ext.form.ComboBox, {
    // Fix combobox changing its valur to an item with the same name (displayField) on blur
    // (Definitivly fixed in Ext 3.4)
    assertValue: function () {
        var val = this.getRawValue(), rec;

        if (this.valueField && Ext.isDefined(this.value)) {
            rec = this.findRecord(this.valueField, this.value);
        }
        if (!rec || rec.get(this.displayField) != val) {
            rec = this.findRecord(this.displayField, val);
        }
        if (!rec && this.forceSelection) {
            if (val.length > 0 && val != this.emptyText) {
                this.el.dom.value = Ext.value(this.lastSelectionText, '');
                this.applyEmptyText();
            } else {
                this.clearValue();
            }
        } else {
            if (rec && this.valueField) {
                // onSelect may have already set the value and by doing so
                // set the display field properly.  Let's not wipe out the
                // valueField here by just sending the displayField.
                if (this.value == val) {
                    return;
                }
                val = rec.get(this.valueField || this.displayField);
            }
            this.setValue(val);
        }
    }
});
