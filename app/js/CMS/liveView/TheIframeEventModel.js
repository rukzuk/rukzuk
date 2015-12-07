Ext.ns('CMS.liveView');

/**
* @class CMS.liveView.TheIframeEventModel
* @extends Ext.util.Observable
* Helper class for {@link CMS.liveView.TheEditableIframe}
* @constructor
* @param {CMS.liveView.TheEditableIframe} frame
*/
CMS.liveView.TheIframeEventModel = function (frame) {
    /**
    * The event model's owner
    * @property owner
    * @type CMS.liveView.TheEditableIframe
    */
    this.owner = frame;
    this.unitElements = [];
    this.mouseEnterWrappers = {};
    this.mouseLeaveWrappers = {};

    var self = this;
    this.mouseenterHandler = function (e) {
        if (typeof Ext == 'undefined') { return; } // unload

        //check if event was already processed, so further actions are only applied to the real target
        if (!e.processed) {
            //console.log('[IEV] mouseenter', this);
            self.owner.handleMouseEnter(self.getTarget(e, this));
            e.processed = true;
        }
    };

    this.mouseleaveHandler = function (e) {
        if (typeof Ext == 'undefined') { return; } // unload
        //console.log('[IEV] mouseleave', this);
        if (!!Ext.lib.Event.getRelatedTarget(e)) { // same document
            self.owner.handleMouseLeave(self.getTarget(e, this));
        }
    };

    this.mousedownHandler = function (e) {
        if (typeof Ext == 'undefined') { return; } // unload

        //check if event was already processed, so further actions are only applied to the real target
        if (!e.processed) {
            //console.log('[IEV] mousedown', this);
            self.owner.handleMouseDown(self.getTarget(e, this), e);
            e.processed = true;
        }
    };

    if (!Ext.isIE) { // http://phrogz.net/node/whocaughtme
        this.getTarget = function (e, thisObj) {
            return thisObj;
        };
    } else {
        var re = new RegExp('(\\s|^)' + CMS.config.unitElClassName + '(\\s|$)');
        this.getTarget = function (e, thisObj) {
            e = e.srcElement;
            while (e && !re.test(e.className)) {
                e = e.parentNode;
            }
            return e;
        };
    }

    frame.on('destroy', function () {
        self.teardownEvents();
        delete self.owner;
        delete self.mouseenterHandler;
        delete self.mouseleaveHandler;
        delete self.mousedownHandler;
        delete self.getTarget;
        self = null;
    });

};

Ext.extend(CMS.liveView.TheIframeEventModel, Ext.util.Observable, {
    /**
    * Sets up the mouseover and mouseout handlers on the loaded iframe
    * @param {MIF.Element} el Passed by the domready event.
    */
    initEvents: function (el) {
        this.teardownEvents();
        if (!el) {
            return;
        }
        var unitElements = this.owner.getElementsByAttribute(el.dom.contentDocument, 'class', CMS.config.unitElClassName, CMS.config.unitElTagName);
        Ext.each(unitElements, function (unitElement) {
            var unit = this.owner.unitStore.getById(unitElement.id);
            if (!unit) {
                return;
            }
            this.unitElements.push(unitElement);
            // we cannot use this.mouseleaveHandler and this.mouseenterHandler directly, since Ext.lib.Event.addListener wraps the
            // handler functions and returns the wrapped functions. So we need to store those in order to remove the listeners later on.
            this.mouseLeaveWrappers[unitElement.id] = Ext.lib.Event.addListener(unitElement, 'mouseleave', this.mouseleaveHandler);
            this.mouseEnterWrappers[unitElement.id] = Ext.lib.Event.addListener(unitElement, 'mouseenter', this.mouseenterHandler);
            Ext.lib.Event.addListener(unitElement, 'mousedown', this.mousedownHandler);
        }, this);
    },

    /**
    * The opposite of {@link #initEvents}
    * Call this to do some memory cleanup prior to loading new iframe contents
    */
    teardownEvents: function () {
        if (!this.unitElements.length) {
            return;
        }
        console.log('[PreviewIframe] teardownEvents');
        try {
            Ext.each(this.unitElements, function (unitElement) {
                Ext.lib.Event.removeListener(unitElement, 'mouseleave', this.mouseLeaveWrappers[unitElement.id]);
                delete this.mouseLeaveWrappers[unitElement.id];
                Ext.lib.Event.removeListener(unitElement, 'mouseenter', this.mouseEnterWrappers[unitElement.id]);
                delete this.mouseEnterWrappers[unitElement.id];
                Ext.lib.Event.removeListener(unitElement, 'mousedown', this.mousedownHandler);
            }, this);
        } catch (ie8) {}
        this.unitElements = [];
    }
});
