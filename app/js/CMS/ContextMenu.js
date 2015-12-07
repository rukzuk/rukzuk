Ext.ns('CMS');

/**
 * An abstract plugin to provide a context menu for various components
 * A concrete implementation has to implement a listener for the respected
 * event of its component and call the {@link #showMenu} method (e.g.
 * see {@link CMS.structureEditor.StructureEditorContextMenu})
 *
 * @class CMS.ContextMenu
 * @extends Ext.utils.Observable
 */
CMS.ContextMenu = Ext.extend(Ext.util.Observable, {

    /**
     * The configuration of the context menu items; Allows all options
     * of {@link Ext.menu.Item} plus the following properties:
     * <ul>
     *   <li>event: The name of an event which should be triggered at the component on click</li>
     * </ul>
     *
     * @property items
     * @type Array
     */
    items: undefined,

    /**
     * The plugin's component; Read-only
     * E.g. structureEditor for {@link CMS.structureEditor.StructureEditorContextMenu}
     *
     * @property component
     * @type Object
     */
    component: undefined,

    /**
     * The id of the item to open the context menu for; Read-only
     *
     * @property contextItemId
     * @type String
     */
    contextItemId: undefined,

    constructor: function (cfg) {
        CMS.ContextMenu.superclass.constructor.apply(this, arguments);
        Ext.apply(this, cfg);
    },

    init: function (cmp) {
        // store a reference to the component
        this.component = cmp;

        // create menu component
        this.menu = new Ext.menu.Menu({
            cls: 'CMScontextmenu',
            items: this.createMenuItems()
        });

        // collect all component events from menu items and enable bubbling
        var bubbleEvents = [];
        this.menu.items.each(function (menuItem) {
            if (menuItem.event) {
                bubbleEvents.push(menuItem.event);
            }
        });
        if (bubbleEvents.length > 0) {
            this.component.enableBubble(bubbleEvents);
        }

        // register listener to cleanup when component is destroyed
        this.component.on('beforedestroy', this.destroy, this);
    },

    /**
     * Opens the context menu for the given item
     * NOTICE: The method is not called automatically; A implementation has to register
     * an event listener (e.g. "contextmenu", "containercontextmenu", ...) at its component
     * and call this method
     *
     * @param {String} itemId The id of the item to call the context menu for (e.g. a unit of the structur editor)
     * @param {Object} event The Ext event object to determine the screen position of the menu
     */
    showContextMenu: function (itemId, event) {
        var showMenu = false;
        /**
         * Triggered before the context menu is shown; Return <code>false</code> to abort
         * @event
         * @name beforeshowmenu
         *
         * @param {String} itemId The id of item to open the context menu for
         * @param {Object} this The context menu plugin instance
         */
        if (this.fireEvent('beforeshowmenu', itemId, this) === false) {
            return;
        }

        this.contextItemId = itemId;
        this.menu.items.each(function (menuItem) {
            var showItem = true;
            var condition = menuItem.condition;
            if (Ext.isObject(condition)) {
                showItem = condition.fn.call(condition.scope || menuItem.scope, itemId);
            } else if (Ext.isFunction(condition)) {
                showItem = condition.call(menuItem.scope, itemId);
            }

            // hide menu entries for unavailable options
            menuItem.setVisible(showItem);
            // disable hidden items to avoid activating them using the keyboard (SBCMS-1567)
            menuItem.setDisabled(!showItem);
            // show menu if there is at least one option available
            showMenu = showMenu || showItem;
        });

        if (showMenu) {
            event.preventDefault();

            /**
             * Triggered when the context menu is shown
             * @event
             * @name showmenu
             *
             * @param {String} itemId The id of item to open the context menu for
             * @param {Object} this The context menu plugin instance
             */
            this.fireEvent('showmenu', itemId, this);
            this.menu.showAt(event.getXY());
        }
    },

    /**
     * Helper to build the items of the context menu
     * @private
     */
    createMenuItems: function () {
        var result = [];
        for (var i = 0, l = this.items.length; i < l; i++) {
            var item = this.items[i];

            if (Ext.isObject(item)) {
                // create default click handler that fires the given event
                // with the id of the selected node
                item.handler = this.createItemClickHandler(item);
            }
            result.push(item);
        }
        return result;
    },

    /**
     * Creates the generic click handler method for the menu items
     * This handler...
     * a) ...triggers/checks the "beforeitemclick" event
     * b) ...calls the handler method which has been configuered with the item
     * c) ...triggers an event at the component if item.event has been configured#
     * d) ...and finally triggers the "itemclick" event
     *
     * @param {Object} menuItem The initial menu item configuration
     * @return {Function} The click handler
     * @private
     */
    createItemClickHandler: function (menuItem) {
        var handler = menuItem.handler;
        var eventName = menuItem.event;
        var scope = menuItem.scope;

        return (function (item, e) {
            if (!this.contextItemId) {
                return;
            }

            /**
             * Triggered before the click handle of a context menu is executed; Return <code>false</code> to abort
             * @event
             * @name beforeitemclick
             *
             * @param {Object} item The context menu item
             * @param {Object} e The ext event object of the click event
             * @param {String} itemId The id of item to open the context menu for
             */
            if (this.fireEvent('beforeitemclick', item, e, this.contextItemId) === false) {
                return;
            }
            if (handler) {
                handler.call(scope, item, e, this.contextItemId);
            }
            if (eventName) {
                this.component.fireEvent(eventName, this.contextItemId);
            }

            /**
             * Triggered when the click handle of a context menu is executed
             * @event
             * @name itemclick
             *
             * @param {Object} item The context menu item
             * @param {Object} e The ext event object of the click event
             * @param {String} itemId The id of item to open the context menu for
             */
            this.fireEvent('itemclick', item, e, this.contextItemId);
        }).createDelegate(this);
    },

    /**
     * A cleanup method, called when the underlying component is destroyed
     * Can be overridden;
     * @protected
     */
    destroy: function () {
        this.menu.destroy();
        this.menu = null;
        this.component.un('beforedestroy', this.destroy, this);
        this.component = null;
        this.contextItemId = null;
        this.purgeListeners();
    }
});
