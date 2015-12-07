/* global module */

module.exports = function (grunt) {

    grunt.registerMultiTask('createLang', 'Compiles a language file from the give JSON sources', function () {
        for (var i = 0; i < this.files.length; i++) {
            var libs = {};
            var availableLangs = [];
            var target = this.files[i].dest;
            var sources = this.files[i].src;

            for (var j = 0; j < sources.length; j++) {
                var lang = grunt.file.readJSON(sources[j]);

                availableLangs.push([lang.key, lang.name]);
                libs[lang.key] = lang;
            }

            var content = [
                'window.CMS = window.CMS || {};\n',
                'window.CMS.language = {\n',
                '  available: ', JSON.stringify(availableLangs), ',\n',
                '  libs: ', JSON.stringify(libs, null, '\t'),
                '};'
            ].join('');

            grunt.file.write(target, content);
        }
    });
};
