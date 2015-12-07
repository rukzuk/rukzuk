Ext.ns('SB.form');
/**
 * @class SB.form.InfiniteSlider
 * @extends Ext.BoxComponent
 *
 * Slider element which has no start or end value. Register to the 'change' event to use this component
 */
SB.form.InfiniteSlider = Ext.extend(Ext.BoxComponent, {

    /**
     * The width and hight of the proxy element that forces the drag cursor
     *
     * @property proxySize
     * @type Number
     * @private
     */
    proxySize: 200,

    /**
     * The modifier for different moving deltas
     * slow mouse movement -> small changes
     * fast movement -> big changes
     *
     * @property modifierSteps
     * @type Array
     * @private
     */
    modifierSteps: [{
        max: 20,
        modifier: 0.1
    }, {
        min: 21,
        max: 50,
        modifier: 0.3
    }, {
        min: 51,
        max: 70,
        modifier: 0.5
    }, {
        min: 71,
        max: 100,
        modifier: 0.65
    }, {
        min: 101,
        modifier: 0.8
    }],

    initComponent: function () {
        this.autoEl = {
            tag: 'div',
            'class': 'ux-infiniteslider'
        };

        SB.form.InfiniteSlider.superclass.initComponent.call(this);

        this.addEvents(
            /**
             * @event change
             * Triggers a change of the component
             * @param {Ext.Component} this
             * @param {Number} steps positive or negative number
             */
            'change',

            /**
             * @event beforemoveslider
             * Fires before change - can stop visual change (animation) of slider
             * @param {Ext.Component} this
             * @param {Number} steps positive or negative number
             */
            'beforemoveslider');

        this.afterMethod('onRender', this.doRender, this);

    },

    doRender: function () {
        var el = this.getEl();
        // register events to handle draggig and mousewheel
        el.on('mousedown', this.startDrag, this);
        //el.on('mousewheel', this.mousewheelHandler, this);
    },

    //private
    startDrag: function (e) {
        if (this.disabled) {
            return;
        }

        if (this.dragRunning) {
            // drag is running (there should not be another mousedown event, but who knows?)
            return;
        }

        this.endDrag(); // clean up, just to be sure there are no old references left

        var xy = e.getXY();
        this.dragRunning = true;

        this.mouseX = xy[0];
        this.lastMouseX = this.mouseX;

        if (!this.updateTask) {
            this.updateTask = {
                run: this.updateSliderWhileDragging,
                scope: this,
                interval: 40
            };
            Ext.TaskMgr.start(this.updateTask);
        }

        // create listener mousemove mouseup and mouseout
        this.onDragDelegate = this.onDrag.createDelegate(this);
        this.onMouseOutHandlerDelgate = this.mouseOutHandler.createDelegate(this);

        window.addEventListener('mousemove', this.onDragDelegate, true);
        window.addEventListener('mouseout', this.onMouseOutHandlerDelgate, false);

        Ext.getBody().on('mouseup', this.endDrag, this);

        // create proxy (prevents other events and mouse cursor change)
        this.createProxy();
        this.moveProxy(xy[0], xy[1]);
    },


    //private
    // creates a proxy to prevent other mouse cursors
    createProxy: function () {
        this.proxy = Ext.getBody().createChild({
            cls: 'ux-infiniteslider-proxy',
            style: 'width: ' + this.proxySize + 'px; height: ' + this.proxySize + 'px; position: absolute; background-color: transparent; top:0; left:0; z-index: 10000'
        });
    },

    //private
    moveProxy: function (x, y) {
        var offset = this.proxySize / 2;
        this.proxy.setX(x - offset);
        this.proxy.setY(y - offset);
    },

    //private
    mouseOutHandler: function (e) {
        var target = e.toElement || e.relatedTarget; // "toElement" for WebKit & IE, "relatedTarget" for FF
        if (!target) {
            // stop drag if mouse moves out of window!
            this.endDrag();
        }
    },

    //private
    endDrag: function () {
        if (this.onDragDelegate) {
            window.removeEventListener('mousemove', this.onDragDelegate, true);
            delete this.onDragDelegate;
        }

        if (this.onMouseOutHandlerDelgate) {
            window.removeEventListener('mouseout', this.onMouseOutHandlerDelgate, false);
            delete this.onMouseOutHandlerDelgate;
        }

        Ext.getBody().un('mouseup', this.endDrag, this);

        this.dragRunning = false;

        if (this.proxy) {
            this.proxy.remove();
            delete this.proxy;
        }

        if (this.updateTask) {
            Ext.TaskMgr.stop(this.updateTask);
            this.updateTask = null;
        }
    },

    //private
    onDrag: function (e) {
        if (this.disabled || !this.dragRunning) {
            return;
        }

        var x = e.pageX;
        var y = e.pageY;

        // move the proxes that forces the drag cursor to current the mouse position
        this.moveProxy(x, y);
        // move the slider background to visualize the dragging
        this.animateBackground(x - this.mouseX);
        // remember the x position to calculate the draging delta
        this.mouseX = x;
    },

    //private
    animateBackground: function (steps) {
        if (this.fireEvent('beforemoveslider', this, steps) === false) {
            // let the using component decide if we should animate or not
            return;
        }

        // calculate a mean value of the last changes to provide a smooth sliding experience and
        // decide if switching the scroll direction (it may jump without a mean value)
        if (!this.sliderBGDeltas) {
            this.sliderBGDeltas = this.sliderBGDeltas || [steps, steps, steps, steps];
        }
        this.sliderBGDeltas.shift();
        this.sliderBGDeltas.push(steps);
        var meanValue = Ext.sum(this.sliderBGDeltas) / this.sliderBGDeltas.length;

        // update pos of bg
        this.bgpos = this.bgpos || 0; // init bg pos
        this.bgpos += meanValue;
        if (meanValue >= 0) {
            // sliding right
            this.getEl().dom.style.backgroundPosition = this.bgpos + 'px -9px';
        } else {
            // sliding left (used the background repeat to emulate an arrow to the left
            this.getEl().dom.style.backgroundPosition = this.bgpos + 'px 11px';
        }
    },

    /**
     * The handler method the task which monitors the dragging
     * @private
     */
    updateSliderWhileDragging: function () {
        if (this.disabled || !this.dragRunning) {
            return;
        }

        var change = 0;
        var dx = this.mouseX - this.lastMouseX;
        var delta = Math.abs(dx);
        var modifier = this.modifierSteps[0].modifier;

        if (delta > 0) {
            // determine the modifier for the current delta
            for (var i = 0; i < this.modifierSteps.length; i++) {
                var ms = this.modifierSteps[i];
                if ((!ms.min || delta > ms.min) && (!ms.max || delta <= ms.max)) {
                    modifier = ms.modifier;
                    break;
                }
            }
            // calculate the actual change based on the draggen width and the modifier
            if (dx > 0) {
                change = Math.floor(modifier * dx);
            } else {
                change = Math.ceil(modifier * dx);
            }
            // trigger a change if the was one
            if (change !== 0) {
                // update last mouse position only if there was an actual change (there may be none
                // if |dx * modifier| < 0); if not then it would be possible to drag forever without
                // triggering a change
                this.lastMouseX = this.mouseX;
                this.fireEvent('change', this, change);
            }
        }
    },

    /**
     * Handler for mouse wheel event
     * @private
     *
     * Disabled because of bug SBCMS-1403:
     * It is likely to change unit settings accidently when trying to scroll unit form panel
     * -> It will be disabled until we found a more sofisticated way to use the wheel

    mousewheelHandler: function (e) {
        e.stopEvent();
        var delta = e.getWheelDelta();
        this.animateBackground(5 * delta); // move background faster than than the actual change to provide a better user experience
        this.fireEvent('change', this, delta);
    },
    */

    // override superclass to destroy all draggen listeners and running tasks
    destroy: function () {
        this.endDrag();
        SB.form.InfiniteSlider.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('ux-infiniteslider', SB.form.InfiniteSlider);
