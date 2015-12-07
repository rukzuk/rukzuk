define(['rz_root/notlive/js/baseJsModule', 'CMS', 'jquery', 'rz_container_collapsible/collapsibleHelper'], function (JsModule, CMS, $, collapsibleHelper) {

    return JsModule.extend({
        initUnit: function (collapsibleUnitId) {
            // init collapsible
            collapsibleHelper.initCollapsible(collapsibleUnitId);
        }
    });
});
