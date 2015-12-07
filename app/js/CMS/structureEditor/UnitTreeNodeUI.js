Ext.ns('CMS.structureEditor');

/**
 * TreeNode UI for the {@link CMS.structureEditor.UnitTreePanel}
 * @class CMS.structureEditor.UnitTreeNodeUI
 * @extends CMS.TreeNodeUI
 */
CMS.structureEditor.UnitTreeNodeUI = Ext.extend(CMS.TreeNodeUI, {

    /**
     * CSS Class which will be added to the node if record attribute
     * ghostContainer is set to true
     * @type String
     * @private
     */
    ghostContainerCls: 'ghostContainer',

    /**
     * CSS Class which will be added to the node if record is editable in page mode
     * @type String
     * @private
     */
    editableCls: 'editable',

    /**
     * CSS Class which will be added to the node if record is a extension module
     * @type String
     * @private
     */
    extensionUnitCls: 'extensionUnit',

    /**
     * Whether the action button to insert extension units should be shown or not
     * @type Boolean
     */
    allowInsertExtensions: false,

    /**
     * Whether the context menu action button should be shown or not
     * @type Boolean
     */
    hasContextMenuItems: false,

    constructor: function (cfg) {
        this.actionCls = 'CMSdelete';
        this.actionText = CMS.i18n('Unit löschen');

        var attrs = cfg.attributes;

        var classes = attrs.cls ? [attrs.cls] : [];
        if (attrs.ghostContainer) {
            classes.push(this.ghostContainerCls);
        }
        if (attrs.visibleFormGroups && attrs.visibleFormGroups.length) {
            classes.push(this.editableCls);
        }
        attrs.cls = classes.join(' ');

        attrs.icon = CMS.config.urls.moduleIconPath + attrs.icon;

        CMS.structureEditor.UnitTreeNodeUI.superclass.constructor.call(this, cfg);
    },

    render: function () {
        CMS.structureEditor.UnitTreeNodeUI.superclass.render.call(this, arguments);

        if (this.node.attributes.moduleType === CMS.config.moduleTypes.extension) {
            Ext.fly(this.wrap).addClass(this.extensionUnitCls);
        }
    },

    /* TODO make this quick hack nice ;-) */
    renderElements: function (n, a, targetNode, bulkRender) {
        //console.log('[UnitTreeNodeUI] renderElements', this)
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';

        var actionShowContextMenuIcon = '';
        if (this.hasContextMenuItems) {
            actionShowContextMenuIcon = '<span class="CMSactionItem CMSshowContextMenu"></span>';
        }

        var actionShowExtensionsIcon = '';
        if (n.attributes.moduleType !== CMS.config.moduleTypes.extension) {
            actionShowExtensionsIcon = '<span class="CMSactionItem CMSshowExtensionUnits" ext:qtip="' + CMS.i18n('Erweiterungen anzeigen') + '"></span>';
        }

        var nodeCls = 'x-tree-node-el x-tree-node-leaf x-unselectable ' + a.cls;
        var iconCls = 'x-tree-node-icon';
        if (a.icon) {
            iconCls += ' x-tree-node-inline-icon';
        }
        if (a.iconCls) {
            iconCls += ' ' + a.iconCls;
        }
        var module = n.getModule();
        var iconTip = module && CMS.translateInput(module.get('name'));
        var cb = Ext.isBoolean(a.checked);
        var nel;
        var href = a.href || (Ext.isGecko ? '' : '#');
        var target = a.hrefTarget ? ' target="' + a.hrefTarget + '"' : '';
        var buf = [
            '<li class="x-tree-node">',
            '  <div ext:tree-node-id="', n.id, '" class="', nodeCls, '" unselectable="on">',
            '    <span class="x-tree-node-indent">', this.indentMarkup, '</span>',
            '    <img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
            '    <img src="', a.icon || this.emptyIcon, '" class="', iconCls, '" unselectable="on" qtip="', iconTip, '" />',
            '    ', cb ? ('<input class="x-tree-node-cb" type="checkbox" ' + (a.checked ? 'checked="checked" />' : '/>')) : '',
            '    <a hidefocus="on" class="x-tree-node-anchor" href="', href, '" tabIndex="1" ', target, '>',
            '      <span unselectable="on">', n.text, '</span>',
            '      ', actionShowContextMenuIcon,
            '      ', actionShowExtensionsIcon,
            '    </a>',
            '  </div>',
            '  <ul class="x-tree-node-ct" style="display:none;"></ul>',
            '</li>'
        ].join('').replace(/>\s\s+</g, '><');

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

    updateExpandIcon: function () {
        CMS.structureEditor.UnitTreeNodeUI.superclass.updateExpandIcon.apply(this, arguments);

        if (this.rendered) {
            var n = this.node;
            if (n.hasDefaultChildNodes()) {
                Ext.fly(this.wrap).addClass('hasDefaultUnits');
            } else {
                Ext.fly(this.wrap).removeClass('hasDefaultUnits');
            }

            if (n.hasExtensionChildNodes()) {
                Ext.fly(this.wrap).addClass('hasExtensionUnits');
            } else {
                Ext.fly(this.wrap).removeClass('hasExtensionUnits');
            }
        }
    },

    /**
     * Show or hide extension units
     * @param {Boolean} state (optional)
     */
    showExtensionUnits: function (state) {
        if (this.node.isExtensionUnit()) {
            return;
        }

        var el = Ext.fly(this.wrap);
        if (!Ext.isDefined(state)) {
            el.toggleClass('showExtensionUnits');
        } else if (state === true) {
            el.addClass('showExtensionUnits');
        } else {
            el.removeClass('showExtensionUnits');
        }
    }
});
