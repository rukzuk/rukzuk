Ext.ns('Ext.ux.panel');

/**
 * @class Ext.ux.panel.DataCarousel
 * @extends Ext.Panel
 *
 * Component for displaying items in a carousel-like view, i.e. a certain
 * amount of items is displayed at a time and the user can scroll through
 * the total amount by using scroll buttons.
 */
Ext.ux.panel.DataCarousel = Ext.extend(Ext.Panel, {
    /**
     * @cfg {Ext.data.Store} store
     * a store that holds the images to show in the carousel
     * The store's items must have the following fields:
     * <ul>
     *     <li> 'url'
     *     <li> 'name'
     * </ul>
     */
    store: null,

    /**
     * @cfg {Integer} imgPerPage
     * number of images to show at a time. Must be an integer > 1.
     * If {@link center} is <b>true</b>, imgPerPage must be an odd number.
     * Defaults to 5
     */
    imgPerPage: 5,

    /**
     * @cfg {Integer} scrollStep
     * Specify the number of images to scroll per click.
     * (Defaults to  <code>1</code>)
     */
    scrollStep: 10,

    /**
     * @cfg {Float} scrollSpeed
     * the animatino speed. (Defaults to <code>10</code>)
     */
    scrollSpeed: 10,


    /**
     * @cfg {Integer} imgWidth
     * width of the images (Defaults to <code>100</code>)
     */
    imgWidth: 100,

    /**
     * @cfg {Integer} clickArea
     * Width of the panels wrapping the scroll buttons (Defaults to <code>50</code>)
     */
    clickArea: 50,

    /**
     * @cfg {Integer} frameSize
     * With of the wrapping DIVs, used to apply background images
     * (Defaults to <code>10</code>)
     */
    frameSize: 10,

    /**
     * @cfg {Integer} borderWidth
     * Width of the image container's border
     * (Defaults to <code>1</code>)
     */
    borderWidth: 1,

    /**
     * @cfg {Integer} imgHeight
     * height of the images (Defaults to <code>10</code>)
     */
    imgHeight: 100,

    /**
     * @cfg {Boolean} center
     * This will center the first or last image in the carousel when
     * scrolling to the very left/right respectively. If center is
     * <b>false</b>, the first/last image will be shown to the very
     * left/right of the carousel, resp.
     */
    center: false,

    /**
     * @cfg {Object} prepareDataScope
     * The scope for the {@link #prepareData} function
     * (defaults to the current instance)
     */
    prepareDataScope: undefined,

    /**
     * @cfg {Object} dataPlugins
     * a set of plugins that will passed to the underlying {@link Ext.DataView}
     * instance
     */
    dataPlugins: undefined,


    /**
    * @cfg {Boolean} singleSelect
    * Will be passed to the underlying {@link Ext.DataView}. Defaults to <tt>true</tt>.
    */
    singleSelect: true,

    // default values, properties inherited from Ext.Panel
    height: 300,
    width: 600,
    cls: 'x-datacarousel',

    initComponent: function () {
        var containerWidth = 2 * this.frameSize + this.imgWidth;
        var containerHeight = 2 * this.frameSize + this.imgHeight;
        var even = this.imgPerPage % 2;
        if (this.center && !even) {
            this.imgPerPage++;
        }

        this.westPanel = {
            region: 'west',
            cls: 'x-datacarousel-panelleft',
            style: {
                cursor: 'pointer'
            },
            width: this.clickArea,
            items: {
                xtype: 'box',
                cls: 'button',
                autoEl: {
                    tag: 'div',
                    html: ''
                }
            }
        };
        this.eastPanel = Ext.apply(Ext.apply({},
        this.westPanel), {
            region: 'east',
            cls: 'x-datacarousel-panelright'
        });

        this.westPanel = new Ext.Panel(this.westPanel);
        this.eastPanel = new Ext.Panel(this.eastPanel);

        this.netWidth = this.width - this.westPanel.width - this.eastPanel.width;

        var maxImgPerPage = parseInt(this.netWidth / (containerWidth + 10), 10);

        if (this.imgPerPage > maxImgPerPage) {
            this.imgPerPage = maxImgPerPage;
        }

        if (this.scrollStep > this.imgPerPage) {
            this.scrollStep = this.imgPerPage;
        }

        var marginTop = (this.height - this.imgHeight) / 2 - 30;
        var marginHor = (this.netWidth - this.imgPerPage * containerWidth) / this.imgPerPage / 2;

        if (marginHor < 5) {
            marginHor = 5;
        }
        marginHor = parseInt(marginHor + 0.5, 10);
        this.scrollDistance = containerWidth + 2 * marginHor;

        var tpl = this.tpl || new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="thumb-wrap" style="float: left; cursor: pointer; position: relative; display: inline;', ' margin: ' + marginTop + 'px ' + marginHor + 'px 0 ' + marginHor + 'px; overflow: hidden; width: ' + containerWidth + 'px;">',
                    '<div class="thumb-bg" style="position: relative; overflow: hidden; height: ' + (containerHeight - 2 * this.borderWidth) + 'px; width: ' + (containerWidth - 2 * this.borderWidth) + 'px;">',
                        '<img class="thumb" style="position: relative; left: ' + (this.frameSize - this.borderWidth) + 'px; top: ' + (this.frameSize - this.borderWidth) + 'px;"', ' src="{url}" title="{name}" width="' + this.imgWidth + '" height="' + this.imgHeight + '">',
                    '</div>',
                    '<span class="thumb-title" style="position: relative; text-align: center; display: block;" title="{name}">',
                        '{name}',
                    '</span>',
                '</div>',
            '</tpl>'
        );

        this.view = new Ext.DataView({
            tpl: tpl,
            overClass: 'x-view-over',
            itemSelector: 'div.thumb-wrap',
            style: {
                position: 'relative',
                overflowX: 'visible',
                left: this.netWidth + 'px'
            },
            width: 10000,
            height: this.height,
            autoHeight: false,
            singleSelect: this.singleSelect,
            plugins: this.dataPlugins,
            trackOver: true,
            listeners: {
                'selectionchange': {
                    fn: function (dv, selectedNodes) {
                        this.fireEvent('selectionchange', dv, selectedNodes);
                    },
                    scope: this
                },
                'click': {
                    fn: function (dv, index, node, evt) {
                        this.fireEvent('click', dv, index, node, evt);
                    },
                    scope: this
                },
                'dblclick': {
                    fn: function (dv, index, node, evt) {
                        this.fireEvent('dblclick', dv, index, node, evt);
                    },
                    scope: this
                },
                'mouseenter': {
                    fn: function (dv, index, node, evt) {
                        this.fireEvent('mouseover', evt, node, index);
                    }
                },
                'mouseleave': {
                    fn: function (dv, index, node, evt) {
                        this.fireEvent('mouseout', evt, node);
                    }
                }
            },

            prepareData: this.prepareData.createDelegate(this.prepareDataScope || this)
        });

        this.refresh();

        var contentPanel = {
            region: 'center',
            cls: 'x-datacarousel-panelcenter',
            items: this.view
        };

        var conf = {
            layout: 'border',
            minHeight: 200,
            minWidth: 500,
            border: false,
            defaults: {
                border: false
            },
            items: [this.westPanel, contentPanel, this.eastPanel],
            currentIm: 0
        };

        Ext.apply(this, conf);
        Ext.ux.panel.DataCarousel.superclass.initComponent.apply(this, arguments);

        this.on('afterlayout', function () {
            this.getLayout().west.panel.getEl().on('click', function () {
                this.scrollLeft();
            },
            this);
            this.getLayout().east.panel.getEl().on('click', function () {
                this.scrollRight();
            },
            this);
        },
        this, {single: true});
    },

    /**
     * Function which can be overridden to provide custom formatting for each Record
     * that is used by this DataView's {@link #tpl template} to render each node.
     *
     * @param {Array/Object} data
     *      The raw data object that was used to create the Record.
     *
     * @return Array/Object
     *      The formatted data in a format expected by the internal {@link #tpl template}'s
     *      overwrite() method. (either an array if your params are numeric (i.e. {0})
     *      or an object (i.e. <code>{foo: 'bar'}</code>))
     */
    prepareData: function (data) {
        return data;
    },


    /**
     * Use this method to change the store
     *
     * @param {Ext.data.Store} store
     */
    setStore: function (store) {
        var dvEl = this.view.getEl();
        //        console.log(dvEl);
        if (dvEl) {
            dvEl.setStyle('left', this.netWidth + 'px');
        }
        if (this.view.getTemplateTarget()) {
            this.view.bindStore(store);
        } else {
            this.view.store = store;
        }
        if (store) {
            this.mon(this.view.store, 'clear', function () {
                this.dataModified();
            },
            this);
            this.mon(this.view.store, 'load', function () {
                this.dataModified();
            },
            this);
            this.mon(this.view.store, 'datachanged', function () {
                this.dataModified();
            },
            this);
        }
        this.dataModified();
    },

    /**
     * Refresh the view
     */
    refresh: function () {
        this.setStore(this.store);
    },

    /**
     * @private
     * called when the store is modified
     */
    dataModified: function () {
        if (this.view.store) {
            this.totalIms = this.view.store.getCount();
        } else {
            this.totalIms = 0;
        }
        if (this.center) {
            this.scrollMax = this.totalIms - 1;
        } else {
            this.scrollMax = this.totalIms - this.imgPerPage;
            if (this.scrollMax < 0) {
                this.scrollMax = 0;
            }
        }

        this.view.setWidth(this.totalIms * this.scrollDistance);

        this.scrolling = this.totalIms > this.imgPerPage;

        if (this.el && this.el.dom) {
            if (this.scrolling) {
                this.removeClass('no-scrolling');
            } else {
                this.addClass('no-scrolling');
            }
        } else {
            this.on('render', function () {
                if (this.scrolling) {
                    this.removeClass('no-scrolling');
                } else {
                    this.addClass('no-scrolling');
                }
            }, this);
        }
        //this.scrollTaskId = this.scrollTo.defer(200, this, [0, true]);
    },

    /**
     * scroll the carousel by the specified amount
     *
     * @param {Integer} amount
     *      The number of images to scroll
     *      Positive amount: scroll forward,
     *      Negative amount: scroll backward
     */
    scrollBy: function (amount) {
        var nextIm = this.currentIm + amount;
        if (nextIm > this.scrollMax) {
            nextIm = this.scrollMax;
        } else if (nextIm < 0) {
            nextIm = 0;
        }
        if (nextIm == this.currentIm) { // nothing to do
            return true;
        } else {
            return this.scrollTo(nextIm);
        }
    },

    // convenience method
    scrollRight: function (count) {
        if (typeof count == 'undefined') {
            count = this.scrollStep;
        }
        return this.scrollBy(count);
    },

    // convenience method
    scrollLeft: function (count) {
        if (typeof count == 'undefined') {
            count = this.scrollStep;
        }
        return this.scrollBy(-count);
    },

    /**
     * Scroll directly to the specified item. Will fail silently if the
     * specified item does not exist
     *
     * @param {Integer} itemNumber
     *      The number of the item to scroll to
     *
     * @param {Boolean} override
     *      Do not check for invalid item Numbers. override: true allows for
     *      scrolling out of view. Defaults to false.
     *
     * @param {Boolean} noAnim
     */
    scrollTo: function (itemNumber, override, noAnim) {
        //console.log('scrollTo '+ itemNumber);
        if ((Ext.isNumber(itemNumber) && itemNumber >= 0 && itemNumber <= this.scrollMax) || override) {
            var dvEl = this.view.getEl();
            if (dvEl && dvEl.dom) {
                var from = parseInt(dvEl.getStyle('left'), 10);
                var to;
                if (this.center) {
                    to = (this.netWidth - this.scrollDistance) / 2 - this.scrollDistance * itemNumber;
                } else {
                    to = -this.scrollDistance * (itemNumber);
                }
                if (this.previousAnimation && this.previousAnimation.isAnimated) {
                    this.previousAnimation.stop();
                    this.acceleration++;
                } else {
                    this.acceleration = 1;
                }
                var duration = Math.abs(from - to) / (10 * this.scrollSpeed * this.scrollSpeed * this.acceleration + 1);
                if (noAnim) {
                    dvEl.setStyle('left', to);
                } else {
                    this.previousAnimation = dvEl.anim({
                        left: {
                            to: to,
                            unit: 'px'
                        }
                    }, {
                        duration: duration,
                        easing: 'easeOut'
                    });
                }
                if (!override) {
                    this.currentIm = itemNumber;
                }

                this.eastPanel.setDisabled(itemNumber >= this.scrollMax);
                this.westPanel.setDisabled(itemNumber <= 0);

                return true;
            } else {
                // console.log('defering');
                this.view.on('render', function () {
                    this.scrollTo(itemNumber, override);
                },
                this, {
                    single: true
                });
            }
        }
        return false;
    },

    /**
     * Finds the index of the passed node.
     *
     * @param {HTMLElement/String/Number/Record} dom
     *      An HTMLElement template node, index of a template node, the id of a template node or a record associated with a node
     *
     * @return Number
     *      The index of the node or -1
     */
    indexOf: function (dom) {
        return this.view.indexOf(dom);
    },

    /**
    * Selects the item and scrolls to it
    *
    * @param {Integer} itemNumber The number of the item to select
    * @param {Boolean} noAnim <tt>true</tt> to disable scroll animation. Defaults to <tt>false</tt>
    */
    select: function (itemNumber, noAnim) {
        if (this.view) {
            this.view.select(itemNumber);
            this.scrollTo(itemNumber, false, noAnim);
        } else {
            this.on('afterrender', function () {
                this.select(itemNumber, noAnim);
            }, this, { single: true });
        }
    },

    /**
    * Clears the selection
    */
    clearSelections: function () {
        if (this.view) {
            this.view.clearSelections();
        } else {
            this.on('afterrender', function () {
                this.clearSelections();
            }, this, { single: true });
        }
    },

    destroy: function () {
        if (this.scrollTaskId) {
            window.clearTimeout(this.scrollTaskId);
        }
        Ext.ux.panel.DataCarousel.superclass.destroy.apply(this, arguments);
    }
});

Ext.reg('ux-datacarousel', Ext.ux.panel.DataCarousel);
