/**
* @class CMS.form.MultiColumnLayout
* @extends Ext.layout.ColumnLayout
* A column layout that automatically arranges its items in n equally wide columns.
* If no items are present, the container gets an additional class "x-column-layout-ct-empty"
* Likewise, "x-column-layout-ct-n", where n is the number of columns.
*/
CMS.form.MultiColumnLayout = Ext.extend(Ext.layout.ColumnLayout, {

    /**
    * @cfg columnCount
    * This config option must be set in the <em>container</em> that uses this layout.
    * It defines the number of columns, so the columnWidth of each child item will be set to 1 / columnCount
    */
    // columnCount: 2,

    setContainer: function (ct) {
        if (!SB.util.isInteger(ct.columnCount) || ct.columnCount < 2) {
            ct.columnCount = 2;
        }
        ct.el.addClass('x-column-layout-ct-' + ct.columnCount);
        CMS.form.MultiColumnLayout.superclass.setContainer.apply(this, arguments);
    },

    onLayout: function (ct, target) {
        var children = ct.items.items;
        Ext.each(children, function (item) {
            item.columnWidth = 1 / ct.columnCount;
        });
        CMS.form.MultiColumnLayout.superclass.onLayout.apply(this, arguments);
        if (children.length) {
            ct.el.removeClass('x-column-layout-ct-empty');
        } else {
            ct.el.addClass('x-column-layout-ct-empty');
        }
    }
});

Ext.Container.LAYOUTS.multicolumn = CMS.form.MultiColumnLayout;
