Ext.ns('CMS');

/**
* @class CMS.TreePanel
* @extends Ext.tree.TreePanel
* A general treepanel enhancement that permits to pass a class to dragConfig/dropConfig
*/
CMS.TreePanel = Ext.extend(Ext.tree.TreePanel, {
    useArrows: false,
    autoScroll: true,
    animate: false,
    enableDD: true,
    containerScroll: true,
    border: false,
    disabled: true,

    rootVisible: false,

    initComponent: function () {
        this.root = {
            text: 'root',
            id: 'root',
            expanded: true,
            allowDrag: false,
            allowChildren: true,
            leaf: false
        };
        CMS.TreePanel.superclass.initComponent.apply(this, arguments);
    },

    initEvents: function () {

        if ((this.enableDD || this.enableDrop) && !this.dropZone) {
            var DropZoneClass = Ext.tree.TreeDropZone;
            if (this.dropConfig && this.dropConfig.Class) {
                DropZoneClass = this.dropConfig.Class;
                delete this.dropConfig.Class;
            }
            Ext.applyIf(this.dropConfig, {
                ddGroup: this.ddGroup || 'TreeDD',
                appendOnly: this.ddAppendOnly === true,
                allowContainerDrop: true
            });
            this.dropZone = new DropZoneClass(this, this.dropConfig);
        }
        if ((this.enableDD || this.enableDrag) && !this.dragZone) {
            var DragZoneClass = Ext.tree.TreeDragZone;
            if (this.dragConfig && this.dragConfig.Class) {
                DragZoneClass = this.dragConfig.Class;
                delete this.dragConfig.Class;
            }
            this.dragZone = new DragZoneClass(this, this.dragConfig || {
                ddGroup: this.ddGroup || 'TreeDD',
                scroll: this.ddScroll
            });
        }
        CMS.TreePanel.superclass.initEvents.call(this);
    }
});
