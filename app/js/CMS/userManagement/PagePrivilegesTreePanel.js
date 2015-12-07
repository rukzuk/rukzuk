Ext.ns('CMS.userManagement');

/**
 * @class CMS.userManagement.PagePrivilegesTreePanel
 * @extends Ext.tree.TreePanel
 *
 * A TreePanel that displays which user group has
 * the rights to edit or add pages to a website.
 *
 */
CMS.userManagement.PagePrivilegesTreePanel = Ext.extend(Ext.tree.TreePanel, {

    /**
    * @cfg websiteId
    * @type String
    * The currently opened website's id
    */
    websiteId: '',

    /**
    * @cfg {CMS.data.UserGroupRecord} record
    * The userGroup that is being displayed
    */
    record: null,

    /**
    * @property  dirty
    * @type boolean
    * Stores if the panel has unsaved user changes.
    */
    dirty: false,

    initComponent: function () {
        var config = {
            loader: new CMS.userManagement.PagePrivilegesLoader({
                baseParams: {
                    websiteId: this.websiteId,
                    id: this.record.id
                },
                dataRoot: CMS.config.roots.getNavigationPrivileges,
                successCondition: 'data.allRights',
                listeners: {
                    load: function (treeLoader, node, payload, rawResponse) {
                        this.allRightsCheckbox.setValue(rawResponse.data.allRights);
                        this.dirty = false;
                    },
                    scope: this
                }
            }),
            root: {
                nodeType: 'async',
                text: CMS.i18n('Alle Rechte'),
                expanded: true
            },
            rootVisible: false,
            border: false,
            header: true,
            autoScroll: true,
            title: CMS.i18n('Pages'),
            bbar: [{
                boxLabel: CMS.i18n('Diese Gruppe hat alle Rechte'),
                xtype: 'checkbox',
                ref: '../allRightsCheckbox',
                scope: this,
                handler: this.allRightsHandler
            }, '->', {
                text: CMS.i18n('Speichern'),
                iconCls: 'save savegrouprights',
                cls: 'primary',
                scope: this,
                handler: this.onSavePrivileges
            }]
        };
        Ext.apply(this, config);

        CMS.userManagement.PagePrivilegesTreePanel.superclass.initComponent.apply(this, arguments);

        this.eventModel.delegateClick = function (e, t) {
            if (this.beforeEvent(e)) {
                if (e.getTarget('input[type=checkbox]', 1)) {
                    //for some reason we get two events in Firefox
                    //this.onCheckboxClick.defer(1, this, [e, this.getNode(e)]);
                } else if (e.getTarget('label', 1)) {
                    var ui = this.getNode(e).ui;
                    ui.onCheckChange.defer(1, ui, [e]);
                } else if (e.getTarget('.x-tree-ec-icon', 1)) {
                    this.onIconClick(e, this.getNode(e));
                } else if (this.getNodeTarget(e)) {
                    this.onNodeClick(e, this.getNode(e));
                }
            } else {
                this.checkContainerEvent(e, 'click');
            }
        };

        //adds an event listener to check or uncheck individual nodes of the tree date if the user has checked a page privileges checkbox
        this.on('checkChange', this.onCheckChange, this);
    },

    /**
    * @private
    * Is called whenever the user checks a check box inside the PagePrivilegesTreePanel tree. It contains
    * some business logic which makes sure, that the check boxes of the child nodes of the node
    * which was clicked are also checked.
    */
    onCheckChange: function (eventArgs) {
        var attributeName = eventArgs.attributeName,
            checked,
            node = eventArgs.node,
            checkBox;

        if (eventArgs.event && eventArgs.event.target) {
            switch (attributeName) {
            case 'edit':
                checkBox = node.ui.checkboxEdit;
                break;
            case 'subAll':
                checkBox = node.ui.checkboxSubAll;
                break;
            case 'subEdit':
                checkBox = node.ui.checkboxSubEdit;
                break;
            default:
                break;
            }
            checked = checkBox.checked;
            node.attributes.rights[attributeName].value = checked;
            node.attributes.isDirty = true;


            if (attributeName === 'subAll') {
                node.ui.checkboxSubEdit.disabled = checked || node.attributes.rights.edit.inherited;
                node.ui.checkboxSubEdit.checked = checked || node.attributes.rights.edit.inherited;
            }

            if (checkBox && attributeName !== 'edit') {
                this.recurse(node, node.childNodes, attributeName, checked);
            }
        }

        this.dirty = true;
    },

    /**
    * @private
    * Iterates over the nodes, setting the privileges to the correct values
    * @param {Ext.tree.TreeNode} node The original node that triggered the recursion
    * @param {Array} childNodes The child nodes of a privileges node
    * @param {String} attributeName The name of the currently edited privilege
    * @param {Boolean} checked The state of the combobox: checked or unchecked
    */
    recurse: function (node, childNodes, attributeName, checked) {
        Ext.each(childNodes, function (chldNd) {
            switch (attributeName) {
            case 'subAll':
                this.setSingleAttribute(chldNd, 'subEdit', checked || node.attributes.rights.edit.inherited);
                this.setSingleAttribute(chldNd, 'edit', checked  || node.attributes.rights.edit.inherited);
                this.setSingleAttribute(chldNd, 'subAll', checked);
                break;
            case 'subEdit':
                if (!chldNd.attributes.rights.subAll.value) {
                    this.setSingleAttribute(chldNd, 'subEdit', checked);
                }
                if (!chldNd.attributes.rights.subAll.inherited) {
                    this.setSingleAttribute(chldNd, 'edit', checked);
                }
                break;
            default:
                break;
            }

            if (chldNd.childNodes.length) {
                this.recurse(node, chldNd.childNodes, attributeName, checked);
            }
        }, this);
    },

    /**
    * @private
    * Sets the privilege attributes of a single node to the correct values
    * @param {Ext.tree.TreeNode} node The node which should be changed
    * @param {String} attributeName The name of the currently edited privilege
    * @param {Boolean} checked The state of the combobox: checked or unchecked
    */
    setSingleAttribute: function (node, attributeName, value) {
        var mapping = {
            'edit': 'checkboxEdit',
            'subEdit': 'checkboxSubEdit',
            'subAll': 'checkboxSubAll'
        };

        node.attributes.rights[attributeName].value = value;
        node.attributes.rights[attributeName].inherited = value;
        var checkBoxName = mapping[attributeName];
        node.ui[checkBoxName].disabled = value;
        node.ui[checkBoxName].checked = value;
    },

    /**
    * @private
    * Sets the privilege attributes of a single node to the correct values
    * @param {Ext.tree.TreeNode} node The node which should be changed
    * @param {String} attributeName The name of the currently edited privilege
    * @param {Boolean} checked The state of the combobox: checked or unchecked
    */
    onRender: function (ct, position) {
        CMS.userManagement.PagePrivilegesTreePanel.superclass.onRender.call(this, ct, position);
        var titleEl = this.header.child('span', true);
        Ext.DomHelper.insertAfter(titleEl, {
            tag: 'span',
            cn: CMS.i18n('Unterhalb editieren'),
            cls: 'x-tree-column-subEdit'
        });
        Ext.DomHelper.insertAfter(titleEl, {
            tag: 'span',
            cn: CMS.i18n('Unterhalb anlegen'),
            cls: 'x-tree-column-subAll'
        });
        Ext.DomHelper.insertAfter(titleEl, {
            tag: 'span',
            cn: CMS.i18n('Page editieren'),
            cls: 'x-tree-column-edit'
        });
    },

    /**
    * @private
    * Gathers all nodes with a certain privilege and sends the rights to the server.
    */
    onSavePrivileges: function () {
        var data = {
            id: this.record.id,
            websiteId: this.websiteId,
            allRights: this.allRightsCheckbox.getValue(),
            rights: this.collectNodesWithRights()
        };

        CMS.app.trafficManager.sendRequest({
            action: 'editGroupPageRights',
            data: data,
            success: function (response) {
                CMS.Message.toast(CMS.i18n('Rechte wurden gespeichert'));
                this.dirty = false;
            },
            failureTitle: CMS.i18n('Fehler beim Speichern der Rechte'),
            scope: this
        });
    },

    /**
    * @private
    * This will recursivly iterate over all nodes of the tree and
    * return an array of objects containing the id and attributes
    * of each node which has own rights set (inherited rights don't count).
    * @return nodes An array of nodes
    */
    collectNodesWithRights: function () {
        var recurseAndCollect,
            nodes = {};

        recurseAndCollect = function (childNodes) {
            Ext.each(childNodes, function (childNode) {
                var p = childNode.attributes.rights;
                if ((p.edit.value && !p.edit.inherited) || (p.subEdit.value && !p.subEdit.inherited) || (p.subAll.value && !p.subAll.inherited)) {
                    var r = nodes[childNode.attributes.id] = [];

                    if (p.edit.value) {
                        r.push('edit');
                    }
                    if (p.subEdit.value) {
                        r.push('subEdit');
                    }
                    if (p.subAll.value) {
                        r.push('subAll');
                    }
                }
                if (childNode.childNodes.length) {
                    recurseAndCollect(childNode.childNodes);
                }
            });

        };
        recurseAndCollect(this.getRootNode().childNodes);
        return nodes;
    },

    /**
    * @private
    * Sets the allRights property, masks/unmasks the tree
    */
    allRightsHandler: function (checkbox, checked) {
        if (checked) {
            this.body.mask();
        } else {
            this.body.unmask();
        }
        this.dirty = true;
    },

    /**
     * Reloads the tree.
     */
    refresh: function () {
        this.root.reload();
        this.dirty = false;
    },

    /**
     * Checks if there are any dirty nodes.
     */
    isDirty: function () {
        return this.dirty;
    }
});

Ext.reg('CMSpageprivilegestreepanel', CMS.userManagement.PagePrivilegesTreePanel);
