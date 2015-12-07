Ext.ns('CMS');

/**
 * Wrapper for an Ext.DataView with additional features.
 * This class takes the same config options as Ext.DataView, and relays its event.
 *
 * @class CMS.ThumbnailView
 * @extends Ext.Panel
 */
CMS.ThumbnailView = Ext.extend(Ext.Panel, {
    /** @lends CMS.ThumbnailView.prototype */

    /**
     * <tt>true</tt> (default) to allow deselection of items via click in container.
     *
     * @property containerDeselect
     * @type Boolean
     */
    containerDeselect: true,

    /**
     * Used to correct the scroll position when scrolling items into view.
     * This is required if items have a padding.
     *
     * @property scrollOffset
     * @type Integer
     */
    scrollOffset: 0,

    /**
     * The wrapped Ext.DataView instance
     *
     * @property dataView
     * @type Ext.DataView
     */
    dataView: undefined,

    /**
     * Show a ghost (typically add item)
     *
     * @property {String} ghostCfg.text text (name)
     * @property {Function} ghostCfg.cb callback function
     * @property {Object} ghostCfg.scope scope for callback
     * @type Object
     */
    ghostCfg: undefined,

    /** @protected */
    initComponent: function () {

        var dvConfig = {
            xtype: 'dataview',
            ref: 'dataView',
            style: 'overflow: hidden;',
            cls: 'dataViewContainer',
            // autoHeight: true,
            itemSelector: this.itemSelector || 'div.wrap',
            overClass: this.overClass || 'hover',
            selectedClass: this.selectedClass || 'selected',
            singleSelect: Ext.isBoolean(this.singleSelect) ? this.singleSelect : true,
            trackOver: Ext.isBoolean(this.trackOver) ? this.trackOver : true
        };

        Ext.copyTo(dvConfig, this, 'blockRefresh,deferEmptyText,emptyText,loadingText,multiSelect,simpleSelect,store,tpl');

        Ext.apply(this, {
            cls: (this.cls || '') + ' CMSthumbview',
            layout: 'fit',
            items: [dvConfig]
        });

        CMS.ThumbnailView.superclass.initComponent.apply(this, arguments);

        if (!this.containerDeselect) {
            this.dataView.onContainerClick = Ext.emptyFn;
        }

        this.relayEvents(this.dataView, SB.util.getKeys(this.dataView.events));

        // add ghost after render (only once)
        this.on('afterrender', function () {
            if (this.ghostCfg) {
                var ghostEl = this.body.createChild({
                    tag: 'div',
                    html: '<span>' + this.ghostCfg.text + '</span>',
                    cls: 'wrap ghost'
                });
                ghostEl.on('click', this.ghostCfg.cb || Ext.emptyFn, this.ghostCfg.scope || this);
            }
        }, this, {single: true});
    },

    /**
     * Pass-through method for dataView
     */
    bindStore: function (store) {
        this.store = store;
        this.dataView.bindStore(store);
    },

    /**
     * Selects and optionally scrolls an item into view
     *
     * @param {String} itemId
     *      The record's id
     *
     * @param {Boolean} show
     *      false to prevent scrolling of the newly selected item into view
     */
    selectItem: function (itemId, show) {
        var pos = this.dataView.store.indexOfId(itemId);
        if (pos >= 0) {
            this.dataView.select(pos);
            if (show !== false) {
                var node = Ext.fly(this.dataView.getNode(pos));
                this.el.dom.scrollTop = this.el.dom.scrollTop + node.getOffsetsTo(this.el.dom)[1] - this.scrollOffset;
            }
        } else {
            this.dataView.clearSelections();
        }
    }
});

Ext.reg('CMSthumbview', CMS.ThumbnailView);
