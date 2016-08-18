define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {

    var setVideo = function(unitId) {
        var unitData = CMS.get(unitId);
        var mp4 = unitData.formValues.cssMp4.value;

        if (mp4 != null) {
            var mp4Url = CMS.getMediaUrl(unitData.formValues.cssMp4.value, false);
            var $parentUnit = $('#' + unitData.parentUnitId);
            $parentUnit.vide({
                mp4: mp4Url
            },{
                playbackRate: unitData.formValues.cssSpeed.value,
                muted: unitData.formValues.cssMute.value,
                loop: unitData.formValues.cssLoop.value
            });
        }
    };

    return {
        init: function (data) {
            var eventFilter = {moduleId: data.moduleId};
            CMS.on('formValueChange', eventFilter, function (cfg) {
                setVideo(cfg.unitId);
            });

        }
    };
});
