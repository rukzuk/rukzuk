define(['jquery', 'CMS', 'rz_root/notlive/js/cssHelper'], function ($, CMS, cssHelper) {

    var styleSets = [];

    var getStyleSets = function() {
        var allCssClassModules = [];
        styleSets = [];
        CMS.getAllUnitIds('rz_styleset').forEach(function (unitId) {
            var unitData = CMS.get(unitId);
            if (unitData.parentModuleId != 'rz_styleset') {
                delete unitData.parentUnitId;
            }
            delete unitData.children;
            if (unitData.formValues.cssStyleSetName.value !== '') {
                allCssClassModules.push(unitData);
            }

        });
        allCssClassModules.forEach(function (node) {
            var breadcrumb  = findParents(allCssClassModules, node.id, true);
            var option = [];
            option.push(node.id.replace(/MUNIT/g, 'STS'), breadcrumb.reverse().join(' > '));
            styleSets.push(option);
        });
        styleSets.sort(sortBreadcrumbs);
        return styleSets;
    };


    var breadcrumbs = [];

    var findParents = function findParents(data, parentId, reset) {
        // reset path on every new lookup
        if (reset === true) {
            breadcrumbs = [];
        }
        data.forEach(function (node) {
            if (parentId == node.id) {
                var styleSetName = node.formValues.cssStyleSetName.value;
                if (styleSetName !== '') {
                    breadcrumbs.push(styleSetName);
                }
                // if a parent exists recursively call this function until no more parent is found
                if (node.parentUnitId) {
                    findParents(data, node.parentUnitId, false);
                }
            }
        });
        // because the function goes the path from in to out, the array needs to be reversed
        return breadcrumbs;
    };

    var sortBreadcrumbs = function(a,b) {
        a = a[1];
        b = b[1];
        return a == b ? 0 : (a < b ? -1 : 1);
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

            CMS.on('formValueChange', {moduleId: 'rz_styleset'}, function (cfg) {
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
