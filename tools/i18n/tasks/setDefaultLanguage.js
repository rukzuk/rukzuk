/* global module */

module.exports = function (grunt) {

    var regexes = {};

    function getRegEx(key) {
        if (!regexes[key]) {
            key = key.replace(/\(/g, '\\(').replace(/\)/g, '\\)').replace(/\{/g, '\\{').replace(/\}/g, '\\}');
            regexes[key] = new RegExp('CMS\\.i18n\\(\'[^\']+\', \'' + key + '\'\\)', 'g');
        }
        return regexes[key];
    }

    function processSourceFile(fileName, dictionary) {
        var key;
        var newContent;
        var content = grunt.file.read(fileName).replace(/\\'/g, '{{u0027}}');

        for (key in dictionary) {
            if (dictionary.hasOwnProperty(key)) {
                newContent = 'CMS.i18n(\'' + dictionary[key] + '\', \'' + key + '\')';
                content = content.replace(getRegEx(key), newContent);
            }
        }

        content = content.replace(/\{\{u0027\}\}/g, '\\\'');
        grunt.file.write(fileName, content);
    }

    grunt.registerMultiTask('setDefaultLang', 'Replaces the default translations in the given source code files with the values of the language file', function () {
        var sourceFiles = grunt.file.expand(this.options().sourceFiles);
        var defLangFile = this.options().defaultLanguage;
        var dictionary = grunt.file.readJSON(defLangFile).dictionary;


        sourceFiles.forEach(function (fn) {
            grunt.verbose.writeln('Process ' + fn + '...');
            processSourceFile(fn, dictionary);
        });
    });
};
