/* global module, CMS: true, require */
CMS = {};
CMS.config = {};

// do not show errors when inspecting task but file is not generated yet (grunt dev / grunt build)
try {
    require('../../../js/CMS/config/formElements.js');
} catch(e) {
}

var convertFormElement = function thisFn(formData, formConfigV2, isModule) {
    formConfigV2 = formConfigV2 || [];

    // type and xtype are ignored (type is only used in legacy ImagePickers
    // and can cause extjs problems, we use it to define the type of the form element)
    var ignoredParamNames = ['xtype', 'type'];
    // non module forms
    if (!isModule) {
        ignoredParamNames.push('isMeta');
        ignoredParamNames.push('isResponsive');
    }

    formData.forEach(function (fd) {
        // build params map for faster and easier access of params
        var paramsMap = {};
        fd.params.forEach(function(param) {
            paramsMap[param.name] = param;
        });

        // do we have a xtype?
        if (!paramsMap.xtype) {
            console.log('paramsMap.xtype', paramsMap.xtype);
            return;
        }

        if (paramsMap.xtype.value === null) {
            console.log('skip', paramsMap.xtype);
            return;
        }

        // find formElement definition by xtype
        var formDef = getFormDefByXType(paramsMap.xtype.value);
        if (!formDef) {
            console.log('no form element of xtype', paramsMap.xtype.value, 'found');
            return;
        }
        var formDefId = formDef.descr.id;

        // set type
        var formElementV2 = {
            type: formDefId
        };


        // extract params
        Object.keys(paramsMap).forEach(function (key) {

            // ignore these param names
            if (ignoredParamNames.indexOf(key) > -1) {
                // continue
                return;
            }

            // always copy these param names
            if (['CMSvar', 'value'].indexOf(key) === -1) {
                // check for already defined values
                var defValue = getFormDefParam(formDef.params, formDefId, key);
                if (defValue) {
                    // compare default value with
                    if (JSON.stringify(paramsMap[key].value) === JSON.stringify(defValue)) {
                        console.log('param is identical', key);
                        // continue
                        return;
                    }
                } else {
                    console.warn('custom param:', paramsMap[key]);
                }
            }

            // simply copy the value
            formElementV2[key] = JSON.parse(JSON.stringify(paramsMap[key].value));

        });

        formConfigV2.push(formElementV2);

        // handle items (child)
        if (fd.items) {
            formElementV2._items = [];
            thisFn(fd.items, formElementV2._items);
        }

    });

    return formConfigV2;
};

var getFormDefParam = function (params, formElementId, paramName) {
    try {
        return params[CMS.config.formElementsIndex[formElementId].paramIdx[paramName]];
    } catch(e) {
        return {};
    }
};

var getFormDefByXType = function thisFn(xtype) {
    var result = null;
    // TODO: warn if we find more than one xtype -> formDev
    CMS.config.formElements.some(function (fe) {
        var paramIdx = CMS.config.formElementsIndex[fe.descr.id].paramIdx;
        var xtParam = fe.params[paramIdx.xtype];
        if (xtParam.value == xtype)  {
            result = fe;
            return true;
        }
    });
    return result;
};

module.exports = function (grunt) {

    grunt.registerMultiTask('convertForm', 'Converts v1 form definitions to formConfig (v2)', function () {

        var i, j;
        var count = 0;

        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;

            for (j = 0; j < sources.length; j++) {
                var source = sources[j];
                var formDefinition = grunt.file.readJSON(source);

                if (!formDefinition.form || !formDefinition.form.length) {
                    grunt.log.warn('no valid form found in', source);
                    continue;
                }

                if (!formDefinition.form[0].params) {
                    grunt.log.warn(source, 'seems to be a non-legacy format');
                    continue;
                }


                // is module form (with form groups) or websiteSettings / pageType form
                if (formDefinition.form[0].formGroupData)  {
                    grunt.log.ok('convert module (multi) form in (disabled)', source);
                    /*
                    // module multi form (form groups)
                    formDefinition.form.forEach(function (fg) {
                        fg.formGroupData = convertFormElement(fg.formGroupData, null, true);
                    });
                    */
                } else {
                    grunt.log.ok('convert single form in', source);
                    // single form
                    formDefinition.form = convertFormElement(formDefinition.form, null, false);
                }

                grunt.file.write(source, JSON.stringify(formDefinition, null, 4));
                count++;
            }
        }
        grunt.log.ok(count + ' file' + (count === 1 ? '' : 's') + ' forms converted.');
    });
};
