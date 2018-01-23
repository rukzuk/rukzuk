define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {
    return {
        init: function (data) {
            var eventFilter = {moduleId: data.moduleId};
            CMS.on('formValueChange', eventFilter, function (cfg) {
                if (cfg.key == 'cssType') {
                    if (cfg.newValue == 'standard') {
                        CMS.set(cfg.unitId, 'imgHeight', '100%');
                    } else {
                        CMS.set(cfg.unitId, 'imgHeight', '0%');
                    }
                    CMS.refresh(cfg.unitId);
                }
            });
        }
    };
});
