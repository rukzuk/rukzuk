Ext.ns('CMS');

/**
 * @class       CMS.MainWindow
 * @extends     Ext.Window
 *
 * A generic window to be triggered by the main menu items
 * E.g. user-, group management, color schemes, publishing, ...
 * */
CMS.MainWindow = Ext.extend(Ext.Window, {

    layout: 'fit',
    modal: true,
    resizable: false,

    /**
     * Width in percent (0.0 .. 1.0) of viewport width
     * @property
     * @type {Number}
     * @memberOf CMS.MainWindow
     * @protected
     */
    widthViewPortPercent: 0.8,

    /**
     * Height in percent (0.0 .. 1.0) of viewport height
     * @property
     * @type {Number}
     * @memberOf CMS.MainWindow
     * @protected
     */
    heightViewPortPercent: 0.8,

    /**
     * If viewport viewportWidth * widthViewPortPercent is lower than minWidth the full viewport size is used
     * also used to prevent resizing lower as this value
     * @property
     * @override
     * @type {Number}
     * @memberOf CMS.MainWindow
     * @protected
     */
    minWidth: 600,

    /**
     * If viewport viewportHeight * heightViewPortPercent is lower than minHeight the full viewport size is used
     * also used to prevent resizing lower as this value
     * @property
     * @override
     * @type {Number}
     * @memberOf CMS.MainWindow
     * @protected
     */
    minHeight: 400,

    /**
     * Maximum width of the window  in px
     * @property
     * @type {Number}
     * @memberOf CMS.MainWindow
     * @protected
     */
    maxWidth: 1200,

    /**
     * Maximum height of the window in px
     * @property
     * @type {Number}
     * @memberOf CMS.MainWindow
     * @protected
     */
    maxHeight: 800,

    /**
     * Width of the window (use maxHeight if you extend this class)
     * @property width
     */
    width: undefined,

    /**
     * Height of the window (use maxHeight if you extend this class)
     * @property height
     */
    height: undefined,

    initComponent: function () {

        // support for deprecated height/width properties
        if (this.height) {
            this.maxHeight = this.height;
        }
        if (this.width) {
            this.maxWidth = this.width;
        }

        Ext.EventManager.onWindowResize(this.viewportResizeHandler, this);
        this.resizeWindow();

        CMS.MainWindow.superclass.initComponent.call(this);
    },

    /**
     * Handles resize of the viewport
     * @private
     */
    viewportResizeHandler: function () {
        this.resizeWindow(true);
    },

    /**
     * Resize the window according to the given spec
     * @private
     */
    resizeWindow: function (update) {

        // set height and width to 80% of the viewport
        var viewport = Ext.getBody();
        var viewportWidth = viewport.getWidth();
        var viewportHeight = viewport.getHeight();

        this.width = viewportWidth * this.widthViewPortPercent;
        this.height = viewportHeight * this.heightViewPortPercent;

        if (this.width > this.maxWidth) {
            this.width = this.maxWidth;
        }

        if (this.height > this.maxHeight) {
            this.height = this.maxHeight;
        }

        if (this.width < this.minWidth) {
            this.width = this.minWidth < viewportWidth ? this.minWidth : viewportWidth - 10;
        }

        if (this.height < this.minHeight) {
            this.height = this.minHeight < viewportHeight ? this.minHeight : viewportHeight - 10;
        }

        if (update) {
            this.setSize(this.width, this.height);
            this.center();
        }
    },

    /**
     * @override
     */
    destroy: function () {
        Ext.EventManager.removeResizeListener(this.viewportResizeHandler, this);
        CMS.MainWindow.superclass.destroy.apply(this, arguments);
    }
});

