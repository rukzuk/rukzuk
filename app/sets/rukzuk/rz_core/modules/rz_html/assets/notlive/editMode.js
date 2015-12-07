define(['rz_root/notlive/js/baseJsModule', 'CMS'], function (JsModule, CMS) {
    return JsModule.extend({

        onFormValueChange: function (cfg) {
            // force page reload when head code was changed
            if (cfg.key === 'headCode') {
                CMS.refresh();
            }
        }

    });
});
