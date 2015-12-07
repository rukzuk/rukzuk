Ext.ns('CMS');

/**
* @class CMS.ToggleRowSelectionModel
* @extends Ext.grid.RowSelectionModel
* A selectionmodel where each entry's selected state can be toggled by a single left-click.
*/
CMS.ToggleRowSelectionModel = Ext.extend(Ext.grid.RowSelectionModel, {

    handleMouseDown: function (g, rowIndex, e) {
        var now = + new Date();
        if (now - (this.lastDown || 0) < 500 && this.lastIndex == rowIndex) {
            this.lastDown = now;
            this.lastIndex = rowIndex;
            return;
        }
        this.lastDown = now;
        this.lastIndex = rowIndex;
        if (e.button !== 0 || this.isLocked()) {
            return;
        }
        var view = this.grid.getView();
        if (e.shiftKey && !this.singleSelect && this.last !== false) {
            var last = this.last;
            this.selectRange(last, rowIndex, e.ctrlKey);
            this.last = last; // reset the last
            view.focusRow(rowIndex);
        } else {
            var isSelected = this.isSelected(rowIndex);
            if (isSelected) {
                this.deselectRow(rowIndex);
            } else {
                this.selectRow(rowIndex, true);
                view.focusRow(rowIndex);
            }
        }
    }
});
