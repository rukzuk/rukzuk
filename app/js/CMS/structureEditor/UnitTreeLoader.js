Ext.ns('CMS.structureEditor');

/**
 * A tree loader specifically designed for loading CMS JSON objects
 *
 * @class CMS.structureEditor.UnitTreeLoader
 * @extends Ext.tree.TreeLoader
 */
CMS.structureEditor.UnitTreeLoader = Ext.extend(Ext.tree.TreeLoader, {
    preloadChildren: true,
    baseAttrs: {
        loaded: true
    },

    /**
     * The currently opened website's id
     * @property websiteId
     * @type String
     */
    websiteId: '',

    constructor: function (config) {
        Ext.apply(this, config);
        this.baseAttrs.websiteId = this.websiteId;

        CMS.structureEditor.UnitTreeLoader.superclass.constructor.call(this, config);
    },

    /**
     * @param {Ext.tree.TreeNode} node The node whose child nodes should be replaced
     * @param {Array} newChildren An array of new node configs that will replace the existing childnodes
     */
    replaceChildNodes: function (node, newChildren) {
        node.beginUpdate();
        node.removeAll();
        Ext.each(newChildren, function (c) {
            var cn = node.appendChild(this.createNode(c));
            if (this.preloadChildren) {
                this.doPreload(cn);
            }
        }, this);
        node.endUpdate();
    },

    /**
     * Removes the websiteId from the dataSet obj
     * @param {Obj} attr
     */
    createNode: function (attr) {
        var cleanedAttr = Ext.copyTo({}, attr, CMS.config.treeNodeAttributeData);

        if (cleanedAttr.moduleId) {
            var moduleStore = CMS.data.StoreManager.get('module', this.websiteId);
            var module = moduleStore && moduleStore.getById(cleanedAttr.moduleId);

            if (module) {
                cleanedAttr.moduleName = CMS.translateInput(module.get('name'));
                cleanedAttr.text = CMS.translateInput(cleanedAttr.name) || cleanedAttr.moduleName;
            }
        }

        return CMS.structureEditor.UnitTreeLoader.superclass.createNode.call(this, cleanedAttr);
    }
});
