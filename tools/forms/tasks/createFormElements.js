/* global module */

var buildParamIdx = function (params) {
    var paramIdx = {};
    for(var i = 0; i < params.length; i++) {
        if (params[i].name) {
            paramIdx[params[i].name] = i;
        }
    }
    return paramIdx;
};

module.exports = function (grunt) {

    grunt.registerMultiTask('createFormElements', 'Compiles a formElements file from the give JSON sources', function () {
        for (var i = 0; i < this.files.length; i++) {
            var elements = [];
            var elementsIdx = {};
            var target = this.files[i].dest;
            var sources = this.files[i].src;

            for (var j = 0; j < sources.length; j++) {
                var elem = grunt.file.readJSON(sources[j]);

                elementsIdx[elem.descr.id] = {
                    idx: j,
                    paramIdx: buildParamIdx(elem.params)
                };
                elements.push(elem);
            }

            var content = [
                '/* GENEREATED FILE - DO NOT EDIT */\n\n',
                'CMS.config.formElements = \n',
                JSON.stringify(elements, null, '    '), ';\n\n',
                'CMS.config.formElementsIndex = \n',
                JSON.stringify(elementsIdx,null, '    '), ';\n',
            ].join('');

            grunt.file.write(target, content);
        }
    });

};
