/* global module, require */
module.exports = function (grunt) {
    var name = 'importLanguageFile';
    var description = 'Imports the translations from a given language file and writes the results back intto the module data.';
    /**
     The language file is a JSON file with the following format:
     {
          "<filepath_of_moduleData>": {
            "<json_path>": {
              "de:" "<german_text>",
              "en:" "<english_text>"
            },
            "..."
          },
          ...
        }
     }
     */

    var vm = require('vm');
    var vmContext = vm.createContext();

    /**
     * Loads the translation from a given language file and returns the data object
     * @param {String} languageFile The file name of the language file
     * @return {Object} The translation data for all modules
     * @private
     */
    function loadTranslations(languageFile) {
        if (!languageFile) {
            grunt.fail.fatal('Missing language file (option "lang")');
        }

        try {
            return grunt.file.readJSON(languageFile);
        } catch (e) {
            grunt.fail.fatal('Cannot read language file "' + languageFile + '"');
        }
    }

    function escapeStrings(raw) {
        var result;
        if (raw) {
            result = raw.replace(/"/g, '').replace(/\n/g, '\\u000A');
        }
        return result;
    }

    /**
     * Writes the translation to the module data
     * @param {Object} moduleData The current module data object
     * @param {String} path The json-path; only direct (wihtout * or ..)
     * @param {Object} translationDate The module translation object (e.g. {de: "Wert", en: "Value"})
     *      for the given path
     * @return {Object} The modified module data
     * @private
     */
    function applyTranslation(moduleData, path, translationData) {
        // clear value object keys from unsupported chars (" and \n)
        if (!translationData) {
            return;
        }

        var value, code;

        if (typeof translationData === 'string') {
            value = escapeStrings(translationData);
        } else if (typeof translationData === 'object') {
            Object.keys(translationData).forEach(function (langKey) {
                translationData[langKey] = escapeStrings(translationData[langKey]);
            });
            value = JSON.stringify(translationData);
        }

        vmContext.moduleData = moduleData;

        // generate the code which will apply the give translations
        code = path.replace(/^\$/, 'this.moduleData') + ' = \'' + value.replace(/'/g, "\\'") + '\';';
        grunt.verbose.writeln('Code:', code);

        // execute
        vm.runInContext(code, vmContext);

        return moduleData;
    }

    /**
     * Writes the translations to a model's moduleData.json
     * @private
     */
    function importStringsToModuleData(filePath, data) {
        var moduleData = grunt.file.readJSON(filePath);
        var pathToLang = data;

        Object.keys(pathToLang).forEach(function (jsonPath) {
            var langValueObj = pathToLang[jsonPath];
            moduleData = applyTranslation(moduleData, jsonPath, langValueObj);
        });

        // write result to file
        grunt.file.write(filePath, JSON.stringify(moduleData, null, 4));
    }

    /**
     * Writes the translations to a model's moduleManifest.json
     * @private
     */
    function importStringsToModuleManifest(filePath, data) {
        var moduleManifest = grunt.file.readJSON(filePath);

        Object.keys(data).forEach(function (key) {
            var value = data[key];
            moduleManifest[key] = (typeof value === 'object') ? JSON.stringify(value) : value;
        });

        // write result to file
        grunt.file.write(filePath, JSON.stringify(moduleManifest, null, 4));

    }

    /**
     * Writes the translations to a model's moduleTranslation.php
     * @function
     * @private
     */
    var importStringsToModuleTranslation = (function () {
        // The template to create the moduleTranslation.php which should log as follows:
        // <?php
        // return array(
        //      "de" => array(
        //          "<key-1>" => "<translation-1-de>",
        //          "<key-2>" => "<translation-2-de>",
        //          ...
        //      ),
        //      "en" => array(
        //          ...
        //      )
        // );
        var moduleTranslationTpl = [
            '<?php\n',
            'return array(\n',
            '<% ',
            '    for (var i = 0; i < translationData.length; i++) { ',
            '       var langKey = translationData[i].key;',
            '       var translations = translationData[i].translations;',
            ' %>',
            '    \'<%= langKey %>\' => array(\n',
            '<% ',
            '       for (var j = 0; j  < translations.length; j++) { ',
            '           var key = translations[j].key;',
            '           var value = translations[j].value;',
            ' %>',
            '        \'<%= key %>\' => \'<%= value %>\'<%= (j < translations.length - 1 ? \',\' : \'\') %>\n',
            '<%   } %>',
            '    )<%= (i < translationData.length - 1 ? \',\' : \'\') %>\n',
            '<% } %>',
            ');\n'
        ].join('');

        // helper method to map the translation to a format which is more suitable
        // for the template. The original translation data looks as follows
        // {
        //   "<key>": {
        //      "de": "<translation-de>",
        //      "en": "<translation-en>"
        //   },
        //   ...
        // }
        // Whereas for the template the following for is more useful
        // [{
        //     "key": "de",
        //     "translations": [{
        //          "key": "<key>",
        //          "value": "<translation-de>"
        //     }, ...]
        // }, ... ]
        var createTemplateData = function (data) {
            var mappedData = [];
            var langIdx = 0;

            for (var key in data) {
                var lib = data[key];

                langIdx = 0;
                for (var langKey in lib) {
                    if (!mappedData[langIdx]) {
                        mappedData[langIdx] = {
                            key: langKey,
                            translations: []
                        };
                    }
                    mappedData[langIdx].translations.push({
                        key: key,
                        value: lib[langKey]
                    });
                    langIdx++;
                }
            }
            return mappedData;
        };

        return function (filePath, data) {
            var mappedData = createTemplateData(data);
            var content = grunt.template.process(moduleTranslationTpl, {
                data: {
                    translationData: mappedData
                }
            });
            grunt.file.write(filePath, content);
        };
    }());

    /**
     * Imports the translation data for a single module
     * @param {String} filePath The path to the file that contains the translatable texts
     * @param {Object} data The module translations
     * @private
     */
    function importStrings(filePath, data) {
        var fileName = filePath.match(/[^\/]*\.(json|php)$/)[0];

        switch (fileName) {
        case 'moduleData.json':
            importStringsToModuleData(filePath, data);
            break;
        case 'moduleManifest.json':
            importStringsToModuleManifest(filePath, data);
            break;
        case 'moduleTranslation.php':
            importStringsToModuleTranslation(filePath, data);
            break;
        }
    }

    grunt.registerMultiTask(name, description, function () {
        var translations = loadTranslations(this.options().lang);
        var i, j;

        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;
            grunt.log.writeln(['\nStep', (i + 1), 'of', this.files.length, ': ', sources.length, 'modules'].join(' '));

            for (j = 0; j < sources.length; j++) {
                var fileName = sources[j];
                var moduleTranslations = translations[fileName];

                grunt.log.write(fileName + '... ');
                if (moduleTranslations && typeof moduleTranslations === 'object') {
                    importStrings(fileName, moduleTranslations);
                }
                grunt.log.ok(Math.round(100 * (j + 1) / sources.length) + '% finished');
            }
        }
    });
};
