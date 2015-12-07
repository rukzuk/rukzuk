/* global module */

// define all lang codes here
var supportedLangCodes = ['de', 'en'];

var convertObjStructure = function (i18n) {
    var converted = {};
    Object.keys(i18n).forEach(function (langKey) {
        if (supportedLangCodes.indexOf(langKey) >= 0) {
            Object.keys(i18n[langKey]).forEach(function (key) {
                converted[key] = converted[key] || {};
                converted[key][langKey] = i18n[langKey][key];
            });
        }
    });
    return converted;
};

module.exports = function (grunt) {

    grunt.registerMultiTask('convertManifestLang', 'Converts i18n data in moduleManifest.json from i18n.<langcode>.key to i18n.key.<langcode>', function () {

        for (var i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;

            for (var j = 0; j < sources.length; j++) {
                var json = grunt.file.readJSON(sources[j]);

                if (json && json.xm && json.xm.i18n) {
                    json.xm.i18n = convertObjStructure(json.xm.i18n);
                }

                grunt.file.write(sources[j], JSON.stringify(json, null, 4));
            }
        }

    });
};
