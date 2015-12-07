Ext.ns('CMS.userManagement');
/**
 * @class CMS.userManagement.PagePrivilegesTreeNodeUI
 * @extends CMS.TreeNodeUI
 *
 * TreeNode UI for the {@link CMS.userManagement.PagePrivilegesTreePanel}
 *
 */
CMS.userManagement.PagePrivilegesTreeNodeUI = Ext.extend(CMS.TreeNodeUI, {

    renderElements: function (n, a, targetNode, bulkRender) {
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';
        n.expanded = true;
        var edit = Ext.isBoolean(a.rights.edit.value);
        var subAll = Ext.isBoolean(a.rights.subAll.value);
        var subEdit = Ext.isBoolean(a.rights.subEdit.value);
        var editInherited = a.rights.edit.inherited;
        var subAllInherited = a.rights.subAll.inherited;
        var subEditInherited = a.rights.subEdit.inherited;
        var nel;
            //var isLeaf = n.isLeaf();
        var href = a.href || (Ext.isGecko ? '' : '#');
        var actionIcon = !this.hideAction && this.actionCls ? '<span class="CMSactionItem ' + this.actionCls + '" ext:qtip="' + this.actionText + '"></span>' : '';
        var editId = edit ? Ext.id() : null;
        var subEditId = subEdit ? Ext.id() : null;
        var subAllId = subAll ? Ext.id() : null;
        var buf = [
            '<li class="x-tree-node">',
            '<div ext:tree-node-id="', n.id, '" class="x-tree-node-el x-tree-node-leaf x-unselectable ', a.cls, '" unselectable="on">',
            '<span class="x-tree-node-indent">',
            this.indentMarkup,
            '</span>',
            '<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
            '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon', (a.icon ? ' x-tree-node-inline-icon' : ''), (a.iconCls ? ' ' + a.iconCls : ''), '" unselectable="on" />',
            '<a hidefocus="on" class="x-tree-node-anchor" href="', href, '" tabIndex="1" ', a.hrefTarget ? ' target="' + a.hrefTarget + '"' : '',
            '><span unselectable="on">',
            n.attributes.name,
            '</span>',
            actionIcon,
            '</a>',
            '<div class="x-tree-node-cbs">',
            edit ? ('<input class="x-tree-node-cb-edit" type="checkbox" id="' + editId + '" ext:qtip="' + CMS.i18n('Page editieren') + '"' + (a.rights.edit.value ? 'checked="checked"' : '') + (editInherited ? 'disabled' : '') + '></input><label class="x-tree-node-cb-label-edit" for="' + editId + '">&nbsp;</label>'): '',
            subEdit ? ('<input class="x-tree-node-cb-subEdit" type="checkbox" id="' + subEditId + '" ext:qtip="' + CMS.i18n('Unterhalb editieren') + '"' + (a.rights.subEdit.value ? 'checked="checked"' : '') + (subEditInherited ? 'disabled' : '') + '></input><label class="x-tree-node-cb-label-subEdit" for="' + subEditId + '">&nbsp;</label>'): '',
            subAll ? ('<input class="x-tree-node-cb-subAll" type="checkbox" id="' + subAllId + '" ext:qtip="' + CMS.i18n('Unterhalb anlegen') + '"' + (a.rights.subAll.value ? 'checked="checked"' : '') + (subAllInherited ? 'disabled' : '') + '></input><label class="x-tree-node-cb-label-subAll" for="' + subAllId + '">&nbsp;</label>'): '',
            '</div>',
            '</div>',
            '<ul class="x-tree-node-ct" style="display:none;"></ul>',
            '</li>'
        ].join('');

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
        var index = 1;
        if (edit) {
            this.checkboxEdit = cs[4].childNodes[0];
            this.checkboxEdit.defaultChecked = this.checkboxEdit.checked;
        }
        if (subEdit) {
            this.checkboxSubEdit = cs[4].childNodes[2];
            this.checkboxSubEdit.defaultChecked = this.checkboxSubEdit.checked;
        }
        if (subAll) {
            this.checkboxSubAll = cs[4].childNodes[4];
            this.checkboxSubAll.defaultChecked = this.checkboxSubAll.checked;
        }
        this.anchor = cs[index];
        this.textNode = cs[index].firstChild;
        if (this.classes) {
            this.addClass(this.classes.join(' '));
            delete this.classes;
        }
    },

    /*
    * @private
    * Fires the 'checkChange' event to which other components can subscribe to and passes the
    * attributeName of the checkbox along.
    */
    onCheckChange: function (event) {
        if (event && event.target) {
            var target = Ext.fly(event.target);
            if (target.dom.tagName.toLowerCase() == 'label') {
                target = Ext.fly(target.dom.previousSibling);
            }
            /* the name of the attribute which has been changed by the user is retrieved from the CSS class
            of the check box. Adding more CSS classes to the check box of the node will break this
            rather fragile arrangment. */
            var attributeName = target.getAttribute('class').replace('x-tree-node-cb-', '').trim();
            this.fireEvent('checkchange', {
                node: this.node,
                event: event,
                attributeName: attributeName
            });
        }
    },

    /*
    * @private
    * prevent expand/collapse on doubleClick
    */
    onDblClick: function (e) {
        e.preventDefault();
    }

});

Ext.reg('CMSpageprivilegestreenodeui', CMS.userManagement.PagePrivilegesTreeNodeUI);
