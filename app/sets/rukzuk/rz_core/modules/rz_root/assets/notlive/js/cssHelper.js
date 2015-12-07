define(['CMS', 'DynCSS'], function (CMS, DynCSS) {

    // module global config object
    var configObj = null;
    var initialized = false;
    var bufferedUnitIds = [];

    var getDynCSSConfig = function () {
        // load less config object from DOM only once
        if (!configObj) {
            configObj = {};
            var stylesNodeList = document.getElementsByClassName('generatedStyles');
            for (var i = 0; i < stylesNodeList.length; i++) {
                var style = stylesNodeList[i];
                var id = style.getAttribute('data-unit-id');
                var tree = JSON.parse(style.getAttribute('data-tree'));

                configObj[id] = {tree: tree};
            }
        }
        return configObj;
    };

    var getFormValueImpl = function (unitId) {
        var formValues = {};
        var fv = CMS.get(unitId).formValues;
        Object.keys(fv).forEach(function (key) {
            formValues[key] = fv[key].value;
        });
        return formValues;
    };

    var getResolutionsImpl = function () {
        return CMS.getResolutions();
    };

    var loadPluginCode = function (cb) {
        var pluginSources = [];
        try {
            pluginSources = JSON.parse(document.getElementById('dyncss-plugins').innerHTML);
        } catch (e) {

        }
        // use require to load dyncss plugins
        require(pluginSources, cb);
    };

    var getNextRegularUnit = function (unit) {
        if (typeof unit === 'string') {
            unit = CMS.get(unit);
        }

        if (CMS.getModule(unit.moduleId).extensionModule) {
            var parentUnit = CMS.get(unit.parentUnitId, false);
            return getNextRegularUnit(parentUnit);
        } else {
            return unit;
        }
    };

    return {
        /**
         * Initializes the dynamic CSS generation
         */
        initDynCSS: function () {
            if (initialized) {
                return;
            }
            DynCSS.setFormValueImpl(getFormValueImpl);
            DynCSS.setResolutionsImpl(getResolutionsImpl);
            DynCSS.registerGetColorById(CMS.getColorById);
            DynCSS.registerGetImageUrl(CMS.getImageUrl);
            DynCSS.registerGetMediaUrl(CMS.getMediaUrl);
            loadPluginCode((function () {
                initialized = true;
                bufferedUnitIds.forEach(this.refreshCSS, this);
            }).bind(this));
        },

        /**
         * Checks if a formValue is used in any module DynCSS
         * @param {String} key
         * @returns {boolean}
         */
        isCssFormValue: function (key) {
            return !!key.match(/^css(.+)/);
        },

        /**
         * Re-creates the css code for the given unit
         * @param {String|Object} unit The unit data object or its id
         */
        refreshCSS: function (unit) {
            var updateUnit = getNextRegularUnit(unit);
            var unitId = updateUnit.id;
            if (!initialized) {
                if (bufferedUnitIds.indexOf(unitId) < 0) {
                    bufferedUnitIds.push(unitId);
                }
                return;
            }

            //console.time('DynCSSRefresh');
            var config = getDynCSSConfig();
            var json = config[unitId].tree;

            DynCSS.add(json);
            DynCSS.compile(function (err, css) {
                document.getElementById('generatedStyle_' + unitId).innerHTML = css;
            });
            //console.timeEnd('DynCSSRefresh');
        }
    };
});

