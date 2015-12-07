Ext.ns('CMS.structureEditor');

/**
 * @class CMS.TreeNodeUI
 * @extends Ext.tree.TreeNodeUI
 *
 * A basic node UI implementation for the CMS tree node
 */
CMS.TreeNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {

    /**
     * @cfg {String} actionCls
     * the style class for the action icon;
     * if missing the icon will be displayed
     */
    actionCls: undefined,

    /**
     * @cfg {String} actionText
     * the (translated) text for action icon tool tip;
     * defaults to the string "Action"
     */
    actionText: undefined,

    /**
     * @cfg {Boolean} blockHighlighting
     * if set to <code>true</code> the whole block (node and all of its subnodes)
     * will be higlighted on hover;
     * defaults to <code>true</code>
     */
    blockHighlighting: true,

    constructor: function () {
        this.actionText = this.actionText || CMS.i18n('Aktion ausführen');

        CMS.TreeNodeUI.superclass.constructor.apply(this, arguments);
    },

    renderElements: function (n, a, targetNode, bulkRender) {
        //console.log('[CMSTreeNodeUI] renderElements', this)
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';

        var cb = Ext.isBoolean(a.checked),
            dataAttributes = a.templateName ? 'data-infotext="' + a.templateName + '"' : '',
            nel,
            href = a.href || (Ext.isGecko ? '' : '#'),
            actionIcon = !this.hideAction && this.actionCls ? '<span class="CMSactionItem ' + this.actionCls + '" ext:qtip="' + this.actionText + '"></span>' : '',
            buf = ['<li class="x-tree-node">',
                        '<div ext:tree-node-id="', n.id, '" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls, '" unselectable="on" ', dataAttributes ,'>',
                            '<span class="x-tree-node-indent">',
                                this.indentMarkup,
                            '</span>',
                            '<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
                            '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon', (a.icon ? ' x-tree-node-inline-icon' : ''), (a.iconCls ? ' ' + a.iconCls : ''), '" unselectable="on" />',
                            cb ? ('<input class="x-tree-node-cb" type="checkbox" ' + (a.checked ? 'checked="checked" />' : '/>')) : '',
                            '<a hidefocus="on" class="x-tree-node-anchor" href="', href, '" tabIndex="1" ', a.hrefTarget ? ' target="' + a.hrefTarget + '"' : '',
                                '><span unselectable="on">',
                                    n.text,
                                '</span>',
                                actionIcon,
                            '</a>',
                        '</div>',
                    '<ul class="x-tree-node-ct" style="display:none;"></ul>',
                    '</li>'].join('');

        if (bulkRender !== true && n.nextSibling && (nel = n.nextSibling.ui.getEl())) {
            this.wrap = Ext.DomHelper.insertHtml('beforeBegin', nel, buf);
        } else {
            this.wrap = Ext.DomHelper.insertHtml('beforeEnd', targetNode, buf);
        }

        this.elNode = this.wrap.childNodes[0];
        this.ctNode = this.wrap.childNodes[1];
        var cs = this.elNode.childNodes;
        this.indentNode = cs[0];
        this.ecNode = cs[1];
        this.iconNode = cs[2];
        var index = 3;
        if (cb) {
            this.checkbox = cs[3];
            this.checkbox.defaultChecked = this.checkbox.checked;
            index++;
        }
        this.anchor = cs[index];
        this.textNode = cs[index].firstChild;
        if (this.classes) {
            this.addClass(this.classes.join(' '));
            delete this.classes;
        }
    },

    // called by Ext on mouseover event
    onOver: function (e) {
        if (this.elNode) {
            Ext.fly(this.elNode).addClass('x-tree-node-over');

            if (this.blockHighlighting) {
                Ext.fly(this.elNode.parentNode).addClass('x-tree-node-over');
            }
            if (e) { // suppress event on manual call
                /**
                * @event nodemouseover
                * This event is fired <b>from the containing tree</b> when a node is hovered.
                * @param {Ext.tree.TreeNode} node The hovered tree node
                */
                this.node.ownerTree.fireEvent('nodeover', this.node);
            }
        }
    },

    // called by Ext on mouseout event
    onOut: function (e) {
        if (this.elNode) {
            Ext.fly(this.elNode).removeClass('x-tree-node-over');

            if (this.blockHighlighting) {
                Ext.fly(this.elNode.parentNode).removeClass('x-tree-node-over');
            }
            if (e) { // suppress event on manual call
                /**
                * @event nodemouseout
                * This event is fired <b>from the containing tree</b> when a hovered node is un-hovered.
                * @param {Ext.tree.TreeNode} node The hovered tree node
                */
                if (this.node.ownerTree) {
                    this.node.ownerTree.fireEvent('nodeout', this.node);
                }
            }
        }
    },

    getDDRepairXY: function () {
        return Ext.lib.Dom.getXY(this.ecNode);
    }
});
