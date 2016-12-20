define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {

    var styleSets = [];

    var getStyleSets = function() {
        styleSets = [];
        $('.rz_styleset').each(function() {
            styleSet = $(this).attr('data-styleset');
            styleSetName = $(this).attr('data-stylesetname');
            $(this).parents('.rz_styleset').each(function() {
                if ($(this).attr('data-stylesetname') != '') {
                    styleSetName = $(this).attr('data-stylesetname') + " > " + styleSetName;
                }
            });
            var option = [];
            option.push(styleSet, styleSetName);
            styleSets.push(option);
        });
        return styleSets;
    };

    var updateName = function (cfg) {
        var styleSetName = '';
        var cssStyleSet = CMS.get(cfg.unitId).formValues.cssStyleSets.value;
        styleSets.forEach(function(element, index, array) {
            if (cssStyleSet.match(element[0])) {
                var names = element[1].split(' > ');
                styleSetName += names[names.length -1] + ", ";
            }
        });
        CMS.setInfo(cfg.unitId, 'state', styleSetName.substring(0, styleSetName.length -2));

    };

    var updateDropDown = function (cfg) {
        CMS.updateFormFieldConfig(cfg.unitId, 'cssStyleSets', {
            options: styleSets
        });
    };

    var setStyleSet = function(cfg) {
        var parentUnitId = CMS.get(cfg.unitId).parentUnitId;
        var $parentUnit = $('#' + parentUnitId);
        var oldClasses = cfg.oldValue.replace(/,/g, " ");
        var newClasses = cfg.newValue.replace(/,/g, " ");
        $parentUnit.removeClass(oldClasses);
        $parentUnit.addClass(newClasses);

        CMS.getAllUnitIds('rz_style_styleset').forEach(function (unitId) {
           var unitData = CMS.get(unitId, true);
           if ((unitData.parentUnitId == parentUnitId) && (unitData.id != cfg.unitId)) {
               $parentUnit.addClass(unitData.formValues.cssStyleSets.value);
           }
        });

    };

    return {
        init: function (data) {

            var eventFilter = {moduleId: data.moduleId};

            var SelectedUnit = CMS.getSelected();
            if (SelectedUnit.moduleId == 'rz_style_styleset') {
                styleSets = getStyleSets();
                updateDropDown({unitId: SelectedUnit.id});
            }

            CMS.on('afterRenderUnit', {moduleId: 'rz_styleset'}, function (cfg) {
                styleSets = getStyleSets();
                CMS.getAllUnitIds(data.moduleId).forEach(function (unitId) {
                    updateDropDown({unitId: unitId});
                    updateName({unitId: unitId});
                }, this);
            });

            // formValue change
            CMS.on('formValueChange', eventFilter, function (cfg) {
                styleSets = getStyleSets();
                updateName({unitId: cfg.unitId});
                setStyleSet(cfg);
            });
            CMS.on('unitSelect', eventFilter, function (cfg) {
                styleSets = getStyleSets();
                updateDropDown({unitId: cfg.unitId});
            });

            var removedUnitData;
            CMS.on('beforeRemoveUnit', function (unitId) {
                removedUnitData = CMS.get(unitId, false);
            });

            CMS.on('afterRemoveUnit', function (unitId) {
                if (removedUnitData.moduleId == 'rz_styleset') {
                    styleSets = getStyleSets();
                    CMS.getAllUnitIds(data.moduleId).forEach(function (unitId) {
                        updateDropDown({unitId: unitId});
                        updateName({unitId: unitId});
                    }, this);
                }
            });
        }
    };
});
