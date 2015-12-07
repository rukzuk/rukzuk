/**
* Overwrite Ext.Shadow to disable shading globally
*/
(function () {
    // disable shadows
    var dummyShadow = {
        show: Ext.emptyFn,
        isVisible: Ext.emptyFn,
        realign: Ext.emptyFn,
        hide: Ext.emptyFn,
        setZIndex: Ext.emptyFn
    };
    Ext.Shadow = function () {
        return dummyShadow;
    };
})();
