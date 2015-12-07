/*jslint evil: true*/

// http://www.extjs.com/forum/showthread.php?p=418233

Ext.ns('Ext.ux.grid');

/**
 * @class Ext.ux.grid.EncodingGridPanel
 * @extends Ext.grid.GridPanel
 * Just like Ext.grid.GridPanel, but raw text can be used as input from a store
 * The panel takes care of encodings.
 */
Ext.ux.grid.EncodingGridPanel = Ext.extend(Ext.grid.GridPanel, {
    initComponent: function () {
        if (Ext.isArray(this.columns)) {
            this.colModel = new Ext.ux.grid.EncodingColumnModel(this.columns);
            delete this.columns;
        }
        Ext.ux.grid.EncodingGridPanel.superclass.initComponent.apply(this, arguments);
        this.autoEncode = false;
    }
});
Ext.reg('ux-encodinggrid', Ext.ux.grid.EncodingGridPanel);

/**
 * @class Ext.ux.grid.EncodingEditorGridPanel
 * @extends Ext.grid.EditorGridPanel
 * Just like Ext.grid.EditorGridPanel, but raw text can be used as input from a store.
 * The panel takes care of encodings.
 */
Ext.ux.grid.EncodingEditorGridPanel = Ext.extend(Ext.grid.EditorGridPanel, {
    initComponent: function () {
        if (Ext.isArray(this.columns)) {
            this.colModel = new Ext.ux.grid.EncodingColumnModel(this.columns);
            delete this.columns;
        }
        Ext.ux.grid.EncodingEditorGridPanel.superclass.initComponent.apply(this, arguments);
        this.autoEncode = false;
    }
});
Ext.reg('ux-encodingeditorgrid', Ext.ux.grid.EncodingEditorGridPanel);

/**
 * @class Ext.ux.grid.EncodingColumnModel
 * @extends Ext.grid.ColumnModel
 * This is the column model for use with {@link Ext.ux.grid.EncodingGridPanel} or {@link Ext.ux.grid.EncodingEditorGridPanel}
 */
Ext.ux.grid.EncodingColumnModel = Ext.extend(Ext.grid.ColumnModel, {
    getRenderer: function (col) {
        var renderer = this.config[col].renderer || Ext.grid.ColumnModel.defaultRenderer;
        /**
        * @param {Boolean} autoEncode Set this to <b>false</b> to disable encoding. Undefined by default
        */
        if (this.config[col].autoEncode === false) {
            return renderer;
        } else {
            return function (input, meta, record, rowIndex, colIndex, store) {
                return renderer(Ext.util.Format.htmlEncode(input), meta, record, rowIndex, colIndex, store);
            };
        }
    }
});


/**
 * @class Ext.ux.EncodingDataView
 * @extends Ext.DataView
 * Just like Ext.DataView, but raw text can be used as input from a store.
 * The view takes care of encodings.
 */
Ext.ux.EncodingDataView = Ext.extend(Ext.DataView, {
    refresh: function () {
        this.clearSelections(false, true);
        var el = this.getTemplateTarget();
        el.update('');
        var records = this.store.getRange();
        if (records.length < 1) {
            if (!this.deferEmptyText || this.hasSkippedEmptyText) {
                el.update(this.emptyText);
            }
            this.all.clear();
        } else {
            this.tpl.overwrite(el, this.collectAndEncodeData(records, 0));
            this.all.fill(Ext.query(this.itemSelector, el.dom));
            this.updateIndexes(0);
        }
        this.hasSkippedEmptyText = true;
    },

    bufferRender: function (records) {
        var div = document.createElement('div');
        this.tpl.overwrite(div, this.collectAndEncodeData(records));
        return Ext.query(this.itemSelector, div);
    },

    collectAndEncodeData: function (records) {
        var data = this.collectData(records);
        var encodedData = [];
        Ext.each(data, function (item) {
            var encodedItem = {};
            for (var prop in item) {
                if (item.hasOwnProperty(prop)) {
                    var unencodedVal = item[prop];
                    encodedItem[prop] = Ext.isString(unencodedVal) ? Ext.util.Format.htmlEncode(unencodedVal) : unencodedVal;
                }
            }
            encodedData.push(encodedItem);
        });
        return encodedData;
    }
});
Ext.reg('ux-encodingdataview', Ext.ux.EncodingDataView);

Ext.ns('Ext.ux.tree');

/**
 * @class Ext.ux.tree.EncodingTreeNodeUI
 * @extends Ext.tree.TreeNodeUI
 * Just like Ext.tree.TreeNodeUI, but raw text can be used as a label.
 * The tree takes care of encodings.
 */
Ext.ux.tree.EncodingTreeNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
    onTextChange: function (node, text, oldText) {
        if (this.rendered) {
            this.textNode.innerHTML = Ext.util.Format.htmlEncode(text);
        }
    },

    renderElements: function (node, attributes, targetNode, bulkRender) {
        var convertedNode = Ext.apply({}, node);
        convertedNode.text = Ext.util.Format.htmlEncode(node.text);
        Ext.ux.tree.EncodingTreeNodeUI.superclass.renderElements.call(this, convertedNode, attributes, targetNode, bulkRender);
    }
});
Ext.reg('ux-encodingtreenodeui', Ext.ux.tree.EncodingTreeNodeUI);
/**
 * @class Ext.ux.tree.EncodingTreeNode
 * @extends Ext.tree.TreeNode
 * Just like Ext.tree.TreeNode, but raw text can be used as a label.
 * The tree takes care of encodings.
 */
Ext.ux.tree.EncodingTreeNode = Ext.extend(Ext.tree.TreeNode, {
    defaultUI: Ext.ux.tree.EncodingTreeNodeUI
});

/**
 * @class Ext.ux.tree.EncodingAsyncTreeNode
 * @extends Ext.tree.AsyncTreeNode
 * Just like Ext.tree.AsyncTreeNode, but raw text can be used as a label.
 * The tree takes care of encodings.
 */
Ext.ux.tree.EncodingAsyncTreeNode = Ext.extend(Ext.tree.AsyncTreeNode, {
    defaultUI: Ext.ux.tree.EncodingTreeNodeUI
});

/**
 * @class Ext.ux.tree.EncodingTreeLoader
 * @extends Ext.tree.TreeLoader
 * Just like Ext.tree.TreeLoader, but generates
 * Ext.ux.tree.EncodingTreeNodes instead of Ext.tree.TreeNodes
 * Ext.ux.tree.EncodingAsyncTreeNodes instead of Ext.tree.AsyncTreeNodes
 */
Ext.ux.tree.EncodingTreeLoader = Ext.extend(Ext.tree.TreeLoader, {
    createNode: function (attr) {
        if (this.baseAttrs) {
            Ext.applyIf(attr, this.baseAttrs);
        }
        if (this.applyLoader !== false && !attr.loader) {
            attr.loader = this;
        }
        if (Ext.isString(attr.uiProvider)) {
            attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
        }
        if (attr.nodeType) {
            return new Ext.ux.tree.EncodingTreePanel.nodeTypes[attr.nodeType](attr);
        } else {
            return attr.leaf ? new Ext.ux.tree.EncodingTreeNode(attr) : new Ext.ux.tree.EncodingAsyncTreeNode(attr);
        }
    }
});

/**
 * @class Ext.ux.tree.EncodingTreePanel
 * @extends Ext.tree.TreePanel
 * Just like Ext.tree.TreePanel, but raw text can be used as node labels
 * The panel takes care of encodings.
 */
Ext.ux.tree.EncodingTreePanel = Ext.extend(Ext.tree.TreePanel, {
    initComponent: function () {
        var l = this.loader;
        if (!l) {
            l = new Ext.ux.tree.EncodingTreeLoader({
                dataUrl: this.dataUrl,
                requestMethod: this.requestMethod
            });
        } else if (Ext.isObject(l) && !l.load) {
            l = new Ext.ux.tree.EncodingTreeLoader(l);
        }
        this.loader = l;
        Ext.ux.tree.EncodingTreePanel.superclass.initComponent.apply(this, arguments);
    }
});
Ext.ux.tree.EncodingTreePanel.nodeTypes = {
    node: Ext.ux.tree.EncodingTreeNode,
    async: Ext.ux.tree.EncodingAsyncTreeNode
};
Ext.reg('ux-encodingtreepanel', Ext.ux.tree.EncodingTreePanel);

/**
 * @class Ext.ux.form.EncodingComboBox
 * @extends Ext.form.ComboBox
 * Just like Ext.form.ComboBox, but raw text can be used as input from a store
 * The combobox takes care of encodings.
 */
Ext.ns('Ext.ux.form');
Ext.ux.form.EncodingComboBox = Ext.extend(Ext.form.ComboBox, {
    initList: function () {
        if (!this.list) {
            var cls = 'x-combo-list';

            this.list = new Ext.Layer({
                parentEl: this.getListParent(),
                shadow: this.shadow,
                cls: [cls, this.listClass].join(' '),
                constrain: false,
                zindex: 12000
            });

            var lw = this.listWidth || Math.max(this.wrap.getWidth(), this.minListWidth);
            this.list.setSize(lw, 0);
            this.list.swallowEvent('mousewheel');
            this.assetHeight = 0;
            if (this.syncFont !== false) {
                this.list.setStyle('font-size', this.el.getStyle('font-size'));
            }
            if (this.title) {
                this.header = this.list.createChild({
                    cls: cls + '-hd',
                    html: this.title
                });
                this.assetHeight += this.header.getHeight();
            }

            this.innerList = this.list.createChild({
                cls: cls + '-inner'
            });
            this.mon(this.innerList, 'mouseover', this.onViewOver, this);
            this.mon(this.innerList, 'mousemove', this.onViewMove, this);
            this.innerList.setWidth(lw - this.list.getFrameWidth('lr'));

            if (this.pageSize) {
                this.footer = this.list.createChild({
                    cls: cls + '-ft'
                });
                this.pageTb = new Ext.PagingToolbar({
                    store: this.store,
                    pageSize: this.pageSize,
                    renderTo: this.footer
                });
                this.assetHeight += this.footer.getHeight();
            }

            if (!this.tpl) {
                this.tpl = '<tpl for="."><div class="' + cls + '-item">{' + this.displayField + '}</div></tpl>';
            }

            // this is the only thing that changes, compared to the original implementation
            this.view = new Ext.ux.EncodingDataView({
                applyTo: this.innerList,
                tpl: this.tpl,
                singleSelect: true,
                selectedClass: this.selectedClass,
                itemSelector: this.itemSelector || '.' + cls + '-item',
                emptyText: this.listEmptyText
            });

            this.mon(this.view, 'click', this.onViewClick, this);

            this.bindStore(this.store, true);

            if (this.resizable) {
                this.resizer = new Ext.Resizable(this.list, {
                    pinned: true,
                    handles: 'se'
                });
                this.mon(this.resizer, 'resize', function (r, w, h) {
                    this.maxHeight = h - this.handleHeight - this.list.getFrameWidth('tb') - this.assetHeight;
                    this.listWidth = w;
                    this.innerList.setWidth(w - this.list.getFrameWidth('lr'));
                    this.restrictHeight();
                }, this);

                this[this.pageSize ? 'footer' : 'innerList'].setStyle('margin-bottom', this.handleHeight + 'px');
            }
        }
    }
});
