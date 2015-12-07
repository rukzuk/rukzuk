/* global module, require */
/* jshint es5: true */
module.exports = function (grunt) {
    var name = 'exportLanguageFileToTarget';
    var description = [
        'Extracts the translatable texts from each module and create a language file.',
        'The language file can be imported again wit "importLanguageFile" (see this task for a detailed description of the language file format)'
    ].join('\n');

    var vm = require('vm');
    var jsonpath = require('JSONPath');
    var vmContext = vm.createContext();

    // define jsonPath queries for translateable content
    var queries = [
        // Fieldlabels and Text (e.g. Button)
        '$..params[?((@.xtype=="textfield" || @.xtype=="textarea") && (@.name=="fieldLabel" || @.name=="text" || @.name=="title" || @.name=="boxLabel" || @.name=="emptyText"))].value',
        // options (e.g. combobox)
        '$..params[?(@.xtype=="CMSkeyvaluetextarea" && @.name=="options")].value[*][1]',

        // Tab-Name ("Bezeichnung")
        '$.form[*].name',

        // Default values are not translated until there is suitable GUI component
        // to enter multi-language-texts conveniently (-> SBCMS-2007)
        //
        // TextField values (default values)
        // '$..items[?(@.descr.text == "__i18n_formElements.elementTextField")]..params[?(@.name=="value"].value',
        // '$..formGroupData[?(@.descr.text=="__i18n_formElements.elementTextField")]..params[?(@.name=="value")].value',

        // TextArea values
        // '$..items[?(@.descr.text == "__i18n_formElements.elementTextArea")]..params[?(@.name=="value"].value',
        // '$..formGroupData[?(@.descr.text=="__i18n_formElements.elementTextArea")]..params[?(@.name=="value")].value'
    ];

    /**
     * Helper to parse the translation JSON; If string is valid JSON-encoded
     * object and has a non-empty German text
     * @param {String} str The raw value
     * @returns {Object} Object or null
     * @private
     */
    function getTranslationObject(str) {
        var result = str;
        if (str && typeof str === 'string') {
            try {
                result = JSON.parse(str);
            } catch (e) {}
        }
        return result;
    }

    /**
     * Extracts the translatable values form the module data object
     * @param {String[]} paths The direct JSON paths
     * @param {Object} moduleData The module data object
     * @param {Object} target The result target
     * @private
     */
    function extractPathValues(paths, moduleData, target) {
        if (!(paths && paths.length > 0)) {
            return;
        }

        vmContext.data = moduleData;

        for (var i = 0, l = paths.length; i < l; i++) {
            var path = paths[i];
            // generate the code which will extract the translatable values
            var code = 'this.value = ' + path.replace(/^\$/, 'this.data') + ';';
            grunt.verbose.writeln('extract code >>>\n' +  code);

            // run code and get the translatable texts
            vm.runInContext(code, vmContext);
            grunt.verbose.writeln('extracted value >>>\n TYPE: ' + (typeof vmContext.value) + '; VALUE: ' + vmContext.value);

            // write result back
            var value = getTranslationObject(vmContext.value);
            if (value) {
                target[path] = value;
            }
        }

        return target;
    }

    /**
     * Exports translatable strings from moduleData.json
     * @private
     */
    function exportStringsFromModuleData(moduleDataFilePath) {
        var json = grunt.file.readJSON(moduleDataFilePath);
        var formPaths;
        var result = {};

        for (var i = 0; i < queries.length; i++) {
            var query = queries[i];

            /* jshint evil: true */
            grunt.verbose.writeln('evaluate query ' + query);
            formPaths = jsonpath.eval(json, query, {
                resultType: "PATH"
            });
            grunt.verbose.writeln(((formPaths && formPaths.length) || 0) + ' paths found');
            /* jshint evil: false */

            extractPathValues(formPaths, json, result);
        }

        return result;
    }

    /**
     * Exports translatable strings from moduleManifest.json
     * @private
     */
    function exportStringsFromModuleManifest(fileName) {
        var json = grunt.file.readJSON(fileName);
        var result = {};

        ['name', 'description', 'category'].forEach(function (key) {
            result[key] = getTranslationObject(json[key]);
        });

        return result;
    }

    var langKeyLineRE = /'\S*'\s*\=\>\s*array\s*\(/;
    var tranlationLineRE = /'.+'\s*\=\>\s*'.*'/;


    /**
     * Exports translatable strings from moduleTranslation.php
     * @private
     */
    function exportStringsFromModuleTranslation(fileName) {
        var lines = grunt.file.read(fileName).split('\n');
        var result = {};
        var langKey;

        for (var i = 0, l = lines.length; i < l; i++) {
            var line = lines[i].trim();

            //console.log(line, langKeyLineRE.test(line), tranlationLineRE.test(line));
            if (langKeyLineRE.test(line)) {
                langKey = line.replace(/^'/, '').replace(/'.*$/, '');
            } else if (langKey && tranlationLineRE.test(line)) {
                var key = line.replace(/^'/, '').replace(/'\s*=>.*/, '');
                var value = line.replace(/^.*=>\s*'/, '').replace(/'.*$/, '');

                result[key] = result[key] || {};
                result[key][langKey] = value;
            }
        }

        return result;
    }

    /**
     * Extracts all translatable text for a single module
     * @param {String} filePath The path to file that contains the texts to be exported
     * @return {Object} An object containing all translatable strings of the form:
     *      <code>
     *      {
     *          "<json_path>": {
     *              "de": "<german_text>",
     *              "en": "<english_text>"
     *          },
     *          ...
     *      }
     *      </code>
     * @private
     */
    function exportStrings(filePath) {
        var fileName = filePath.match(/[^\/]*\.(json|php)$/)[0];
        var result;

        switch (fileName) {
        case 'moduleData.json':
            result = exportStringsFromModuleData(filePath);
            break;
        case 'moduleManifest.json':
            result = exportStringsFromModuleManifest(filePath);
            break;
        case 'moduleTranslation.php':
            result = exportStringsFromModuleTranslation(filePath);
            break;
        }

        return result;
    }

    grunt.registerMultiTask(name, description, function () {
        var targetFile = this.options().target;
        var i, j;
        var data = {};

        if (!targetFile) {
            grunt.fail.fatal('Missing target file name (option "target")');
            return;
        }

        grunt.verbose.writeln('Write results to "' + targetFile + '"');
        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;
            grunt.log.writeln(['\nStep', (i + 1), 'of', this.files.length, ':', sources.length, 'modules'].join(' '));

            for (j = 0; j < sources.length; j++) {
                var fileName = sources[j];

                grunt.log.write(fileName + '... ');
                data[fileName] = exportStrings(fileName);
                grunt.log.ok(Math.round(100 * (j + 1) / sources.length) + '% finished');
            }
        }

        grunt.file.write(targetFile, JSON.stringify(data, null, 4));
    });
};
