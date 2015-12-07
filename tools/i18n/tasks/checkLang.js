/* global module, require */

module.exports = function (grunt) {

    var parser = require('esprima');
    var selector = require('JSONSelect');

    var ERROR_INVALID_FIRST_ARG = 'The first argument of CMS.i18n should be a string (<%= file %>: <%= line%>)';
    var ERROR_INVALID_SECOND_ARG = 'The second argument of CMS.i18n should be a valid key-string (<%= file %>: <%= line%>)';
    var ERROR_INVALID_CALL = 'Unable to parse call of CMS.i18n (<%= file %>: <%= line%>)';
    var ERROR_KEY_NOT_FOUND = 'The key "<%= key %>" (<%= file %>: <%= line%>) was not found in dictionary "<%= dicName %>" (<%= dicFile %>)';

    function pretty(s, values) {
        return grunt.template.process(s, {
            data: values
        });
    }

    function extractKeyFromCall(arg, errorMsg) {
        if (arg.type === 'Literal') {
            return arg.value;
        } else {
            grunt.fail.warn(errorMsg);
        }
    }

    /**
     * Check if all dictionaries contain the given key
     * @private
     */
    function checkDictionaries(key, dictionaries, file, line) {
        var check = true;
        for (var i = 0; i < dictionaries.length; i++) {
            var dic = dictionaries[i];

            if (!dic.dictionary[key]) {
                grunt.fail.warn(pretty(ERROR_KEY_NOT_FOUND, {
                    key: key,
                    file: file,
                    line: line,
                    dicName: dic.name,
                    dicFile: dic.file
                }));
                check = false;
            }
        }
        return check;
    }

    /**
     * Extracts the language keys which are used in a single source code file
     *
     * @param {String} filename IN: The name of the js file to be processed
     * @param {Object} keys OUT: The set of language keys
     * @param {Object} missing OUT: The set of language key which are missing in at least one dictionary
     * @param {Object} dictionaries IN: the language dictionaries
     * @private
     */
    function extractKeys(fileName, keys, missing, dictionaries) {
        if (/checkLang\.js/.test(fileName)) {
            // do not check the checker (this may occur as part of the pre-commit hook)
            return;
        }

        var file = grunt.file.read(fileName);

        if (/\.js$/.test(fileName)) {
            extractKeysFromMacroStrings(file, keys, missing, dictionaries, fileName);
            extractKeysFromI18nCall(file, keys, missing, dictionaries, fileName);
        } else if (/\.tpl$/.test(fileName)) {
            extractKeysFromHtmlTemplate(file, keys, missing, dictionaries, fileName);
        } else if (/\.json$/.test(fileName)) {
            extractKeysFromMacroStrings(file, keys, missing, dictionaries, fileName);
        }
    }

    /**
     * Extracts the language keys which are defined by the "__i18n_" prefex
     * @private
     */
    function extractKeysFromMacroStrings(file, keys, missing, dictionaries, fileName) {
        var lines = file.split('\n');
        for (var i = 0, l = lines.length; i < l; i++) {
            var matches = lines[i].match(/['"]__i18n_\S+['"]/);
            if (matches && matches.length > 0) {
                for (var j = 0; j < matches.length; j++) {
                    var key = matches[j].replace(/__i18n_/, '').replace(/['"]/g, '');
                    if (!keys[key]) {
                        keys[key] = true;
                        if (!checkDictionaries(key, dictionaries, fileName, i)) {
                            missing[key] = '!!! UNKNOWN !!!';
                        }
                    }
                }
            }
        }
    }

    /**
     * Extracts the language keys from the arguments which are passed to CMS.i18n
     * @private
     */
    function extractKeysFromI18nCall(file, keys, missing, dictionaries, fileName) {
        var key, translation, args, line;
        var ast = parser.parse(file, {loc: true});
        var calls = selector.match(':has(:root > .callee > .property > .name:val("i18n")) > .arguments', ast);

        for (var i = 0, l = calls.length; i < l; i++) {
            line = selector.match('.loc .line', calls[i])[0];
            args = calls[i];
            translation = args[0].value;

            if (args.length === 1) {
                // (deprecated) call of CMS.i18n with one string that is key and fallback translation
                key = extractKeyFromCall(args[0], pretty(ERROR_INVALID_FIRST_ARG, {file: fileName, line: line}));
            } else if (args.length === 2) {
                // default call of CMS.i18n 2nd argument is key, first one is fallback translation
                key = extractKeyFromCall(args[1], pretty(ERROR_INVALID_SECOND_ARG, {file: fileName, line: line}));
            } else {
                // well... it seems that something is badly wrong...
                grunt.fail.warn(pretty(ERROR_INVALID_CALL, {file: fileName, line: line}));
            }

            if (key) {
                if (!keys[key]) {
                    keys[key] = true;
                    if (!checkDictionaries(key, dictionaries, fileName, line)) {
                        missing[key] = translation;
                    }
                }
            }
        }
    }

    /**
     * Extracts the used language keys from the html template files
     * @private
     */
    function extractKeysFromHtmlTemplate(file, keys, missing, dictionaries, fileName) {
        var lines = file.split('\n');
        for (var i = 0, l = lines.length; i < l; i++) {
            var matches = lines[i].match(/i18n\('[.\w]*'\)/);
            if (matches && matches.length > 0) {
                for (var j = 0; j < matches.length; j++) {
                    var key = matches[j].replace(/i18n\('*/g, '').replace(/'*\)/g, '');
                    if (!keys[key]) {
                        keys[key] = true;
                        if (!checkDictionaries(key, dictionaries, fileName, i)) {
                            missing[key] = '!!! UNKNOWN !!!';
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if there are unused language keys in the given dictionary
     * @private
     */
    function checkUnusedKeys(dic, codeKeys) {
        var unused = {};
        // console.log(Object.keys(dic.dictionary));
        Object.keys(dic.dictionary).forEach(function (dicKey) {
            if (!codeKeys[dicKey] && !unused[dicKey]) {
                unused[dicKey] = true;
            }
        });

        return Object.keys(unused).map(function (key) {
            return key.replace(/\n/g, '\\n');
        }).sort();
    }

    grunt.registerMultiTask('checkLang', 'Checks if the language files contains exactly the used languge keys', function () {
        var i, j;
        var langFiles = grunt.file.expand(this.options().languageFiles);
        var codeKeys = {};
        var dictionaries = [];
        var missing = {};
        var numChecked = 0;
        var dic;

        // read the used language files
        for (i = 0; i < langFiles.length; i++) {
            dic = grunt.file.readJSON(langFiles[i]);
            dic.file = langFiles[i];
            dictionaries.push(dic);
        }

        // check the source code files
        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;

            for (j = 0; j < sources.length; j++) {
                grunt.verbose.write('Check ' + sources[j] + '...');
                extractKeys(sources[j], codeKeys, missing, dictionaries);
                grunt.verbose.ok();
            }
            numChecked += sources.length;
        }

        var numMissing = Object.keys(missing).length;
        if (numMissing > 0) {
            grunt.log.ok(numChecked + ' file(s) checked, ' + numMissing + ' keys missing:');
            grunt.verbose.writeln(JSON.stringify(missing, null, '\t'));
        } else {
            grunt.log.ok(numChecked + ' file(s) checked without errors');
        }

        for (i = 0; i < dictionaries.length; i++) {
            var unused = checkUnusedKeys(dictionaries[i], codeKeys);
            if (unused && unused.length > 0) {

                if (false) { // TODO: find better way via options? separate task?
                    grunt.log.warn('Remove unused entries in"' + dictionaries[i].file + '":\n' + unused.join('\n'));
                    dic = dictionaries[i].dictionary;
                    for (j = 0; j < unused.length; j++) {
                        delete dic[unused[j]];
                    }
                    delete dictionaries[i].file;
                    grunt.file.write(dictionaries[i].file, JSON.stringify(dictionaries[i], null, 4));
                } else {
                    grunt.fail.warn('There are unused entries in "' + dictionaries[i].file + '":\n' + unused.join('\n'));
                }
            }
        }
    });
};
