module.exports = function (grunt) {

    grunt.initConfig({
        image_resize: {
            options: {
                quality: 0.66,
                width: 1920,
                overwrite: true
            },
            media: {
                files: [
                    {
                        expand: true,
                        src: '*/media/*/*.jpg',
                    },
                    {
                        expand: true,
                        src: '*/media/*/*.jpeg',
                    },
                    {
                        expand: true,
                        src: '*/media/*/*.JPG',
                    },
                    {
                        expand: true,
                        src: '*/media/*/*.JPEG',
                    }
                ]
            },
        },
        analyzeModuleUsage: {
            src: '*/templates/*/template.json'
        }
    });

    grunt.registerMultiTask('analyzeModuleUsage', 'Analyzes how often modules are used in templates', function () {
        var moduleUsage = {};

        var countUnitAndIterateChildren = function (unit) {
            var moduleId = unit.moduleId;
            if (moduleId.match(/^MODUL-/)) {
                return;
            }
            moduleUsage[moduleId] = moduleUsage[moduleId] ? moduleUsage[moduleId] + 1 : 1;

            if (unit.children && unit.children.length) {
                unit.children.forEach(countUnitAndIterateChildren);
            }
        };

        this.files.forEach(function (file) {
            grunt.log.writeln('Analyzing ' + file.src.length + ' templates…');

            file.src.forEach(function (f) {
                var template = require('./' + f);
                var content = JSON.parse(template.content);
                countUnitAndIterateChildren(content[0]);
            });
        });

        // convert and sort
        var list = Object.keys(moduleUsage).map(function (key) {
            return {id: key, count: moduleUsage[key]};
        });

        list.sort(function (a, b) {
            return b.count - a.count;
        });

        // print results
        grunt.log.writeln('Styles:');
        list.forEach(function (item) {
            if (item.id.match(/^rz_style|rz_selector/)) {
                grunt.log.writeln(item.count + "\t" + item.id);
            }
        });
        grunt.log.writeln('');

        // print results
        grunt.log.writeln('Modules:');
        list.forEach(function (item) {
            if (!item.id.match(/^rz_style|rz_selector/)) {
                grunt.log.writeln(item.count + "\t" + item.id);
            }
        });
    });


    grunt.loadNpmTasks('grunt-image-resize');

    grunt.registerTask('default', ['image_resize:media']);

};
