define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {


    var anchorList = [];
    var updateDropDown = function (cfg) {
        $('.rz_anchor').each(function(){
            var unitData = CMS.get($(this).attr('id'));
            var option = [];
            option.push(unitData.formValues.anchorId.value, unitData.formValues.anchorName.value);
            anchorList.push(option);
        });
        CMS.updateFormFieldConfig(cfg.unitId, 'anchorId', {
            options: anchorList
        });
    };

    return {
        init: function (data) {

            var eventFilter = {moduleId: data.moduleId};
            CMS.on('unitSelect', eventFilter, function (cfg) {
                updateDropDown({unitId: cfg.unitId});
            });

        }
    };
});
