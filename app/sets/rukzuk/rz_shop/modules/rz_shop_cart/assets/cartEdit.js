define(['jquery', 'CMS', 'rz_root/notlive/js/baseJsModule'], function ($, CMS, JsModule) {
    return JsModule.extend({

        initUnit: function (unitId) {
            // disable POST in edit mode (would break the edit mode)
            var msg = this.translate('error.editSubmit');
            $('#' + unitId + ' form[method=POST]').submit(function (e) {
                alert(msg);
                e.preventDefault();
                e.stopPropagation();
            });
            // prevent links (tos)
            $('#' + unitId + ' a').click(function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        },
    });
});