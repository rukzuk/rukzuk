/* global module */
/*jslint node: true */
"use strict";

module.exports = function (grunt) {

    var _ = require('lodash');

    var buildDir = '../../../build/app/';
    var buildModulesDir = buildDir + 'sets/rukzuk/';

    var translatableModuleFiles = [{
        src: '**/form.json'
    }, {
        src: '**/manifest.json'
    }, {
        src: '**/custom.json'
    }];

    // default values
    grunt.option("gitRevision", {});
    grunt.option('gitVersion', 'UNKOWN');

    var copySrc = ['rz_*/**'];

    var uglifySrcExcludes = ['!./*/modules/*/assets/**/*.min.js', '!*/modules/*/assets/**/*-min.js'];
    var uglifySrc = ['*/modules/*/assets/**/*.js'].concat(uglifySrcExcludes);
    var cssMinSrcExcludes = ['!*/modules/*/assets/**/*.min.css', '!*/modules/*/assets/**/*-min.css'];
    var cssMinSrc = ['*/modules/*/assets/**/*.css'].concat(cssMinSrcExcludes);

    var packageIds = grunt.option('packages');
    var buildChannel = grunt.option('channel');
    var build = grunt.option('build') || 'SNAPSHOT';

    // fetch package ids from channelmap.json
    if (!packageIds && buildChannel) {
        var channelMap = grunt.file.readJSON('channelmap.json');
        var packageIdsInChannel = _.map(_.filter(channelMap.packages, function (obj) {
            return _.includes(obj.channels, buildChannel);
        }), 'id');
        packageIds = packageIdsInChannel.join(',');
    }

    if (packageIds) {
        var moduleIdArray = packageIds.split(',');
        grunt.log.writeln('copy the following packages (channel = ' + buildChannel + '): ' + packageIds);
        copySrc = moduleIdArray.map(function (entry) {
            return entry.trim() + '/**';
        });

        uglifySrc = copySrc.map(function (src) {
            return src + '/modules/*/assets/**/*.js';
        }).concat(uglifySrcExcludes);

        cssMinSrc = copySrc.map(function (src) {
            return src + '/modules/*/assets/**/*.css';
        }).concat(cssMinSrcExcludes);
    }

    grunt.initConfig({
        buildChannel: buildChannel,
        version: build,

        copy: {
            main: {
                files: [
                    {
                        expand: true,
                        /* prefixes of all modules */
                        src: copySrc,
                        dest: buildModulesDir
                    }
                ]
            }
        },

        uglify: {
            options: {
                /* prevent renaming of variable and function names*/
                mangle: {
                    except: ['$', 'jQuery', 'CMS']
                },
                preserveComments: 'some'
            },
            assets: {
                files: [
                    {
                        expand: true,
                        src: uglifySrc,
                        dest: buildModulesDir
                    }
                ]
            }
        },

        cssmin: {
            assets: {
                files: [
                    {
                        expand: true,
                        src: cssMinSrc,
                        dest: buildModulesDir
                    }
                ]
            }
        },

        phplint: {
            application: {
                files: [{
                    expand: true,
                    src: ['*/**/*.php', '!node_modules/**/*.php', '!tools/create/**/*.php', '!*/module/vendor/**']
                }]
            }
        },

        jshint: {
            files: [
                'Gruntfile.js',
                '*/modules/*/assets/**/*.js',
                // excludes
                '!rz_core/modules/*/assets/**/require.js',
                '!rz_core/modules/*/assets/**/absurd.js',
                '!rz_core/modules/*/assets/**/jquery.*.js',
                '!rz_core/modules/*/assets/**/jquery-*.js',
                '!rz_core/modules/*/assets/**/*.min.js',
                '!rz_core/modules/*/assets/**/*-min.js',
                '!rz_core/modules/rz_svg/assets/svg.js',
                '!rz_core/modules/rz_root/assets/notlive/dyncss/absurdhat.js',
                '!rz_core/modules/rz_root/assets/js/modernizer.js',
                '!rz_core/modules/rz_root/assets/js/prefixfree.*.js',
                '!rz_core/modules/rz_root/assets/js/vminpoly/**/*.js',
                '!rz_core/modules/rz_anchor/assets/waypoints.js',
                '!rz_core/modules/rz_form/assets/validform/validform.js',
                '!rz_core/modules/rz_anchor/assets/notlive/lib/urlify.js',
                '!rz_core/modules/rz_image/assets/notlive/jquery.mousewheel.js',
                '!rz_core/modules/rz_image/assets/notlive/jquery.panzoom.js',
                '!rz_core/modules/rz_root/assets/js/lazysizes.js',
                '!rz_core/modules/rz_root/assets/js/respimage.js',
            ],
            options: {
                jshintrc: '.jshintrc',
                reporterOutput: ""
            }
        },

        jsonlint: {
            application: {
                files: [
                    {
                        expand: true,
                        src: ['**/**.json', '!build/**/**.json', '!**/node_modules/**']
                    }
                ]
            }
        },
        jasmine: {
            test: {
                //src: 'src/**/*.js',
                src: 'rz_core/modules/rz_root/assets/notlive/cssHelper.js',
                options: {
                    keepRunner: true,
                    specs: 'tests/js/specs/**/*.js',
                    helpers: [
                        'tests/js/helper/*.js',
                        'tests/js/mocks/*.js',
                    ],
                    // host: 'http://127.0.0.1:8000/',
                    template: require('grunt-template-jasmine-requirejs'),
                    templateOptions: {
                        requireConfigFile: 'tests/js/require-main.js'
                    }
                }
            }
        },

        /* replace version string in the manifest.json/package.json*/
        'json-replace': {
            options:  {
                space: '    ',
                replace: {
                    version: '<%= version %>'
                },
            },
            manifest: {
                files: [{
                    expand: true,
                    cwd: buildModulesDir,
                    dest: buildModulesDir,
                    src: ['*/modules/**/manifest.json']
                }]
            }
        },

        watch: {
            json: {
                files: ['**/**.json'],
                tasks: ['jsonlint']
            },

            test: {
                files: ['**/**.php', '**/css.js'],
                tasks: ['phpunit']
            }
        },

        exportLanguageFileToTarget: {
            options: {
                target: (grunt.option('out') || '.') + '/export_<%= version %>.json'
            },
            all: {
                files: translatableModuleFiles
            }
        },

        importLanguageFile: {
            options: {
                lang: grunt.option('lang')
            },
            all: {
                files: translatableModuleFiles
            }
        },

        formatModuleData: {
            all: {
                files: [{
                    src: '*/modules/**/form.json'
                }, {
                    src: '*/modules/**/manifest.json'
                }, {
                    src: '*/modules/**/templatesnippet.json'
                }]
            }
        },

        checkFormatModuleData: {
            all: {
                files: [{
                    src: ['**/form.json', '!_server/**']
                }, {
                    src: ['**/manifest.json', '!_server/**']
                }, {
                    src: '**/templateSnippet.json'
                }]
            }
        },

        convertLegacyResponsiveModuleData: {
            all: {
                files: [{
                    src: '**/form.json'
                }]
            }
        },

        convertManifestLang:  {
            all: {
                files: [{
                    src: ['**/manifest.json']
                }]
            }
        },

        create: {
            options: {
                moduleId: grunt.option('moduleId')
            },

            module: {
                files: [{
                    src: 'tools/create/templates/rz_module/module/module.php',
                    dest: '{moduleId}/module/{moduleId}.php'
                }, {
                    src: 'tools/create/templates/rz_module/module/custom.json',
                    dest: '{moduleId}/module/custom.json'
                }, {
                    src: 'tools/create/templates/rz_module/module/manifest.json',
                    dest: '{moduleId}/module/manifest.json'
                }, {
                    src: 'tools/create/templates/rz_module/module/form.json',
                    dest: '{moduleId}/module/form.json'
                }]
            },

            cssJs: {
                files: [{
                    src: 'tools/create/templates/rz_module/assets/notlive/css.js',
                    dest: '{moduleId}/assets/notlive/css.js'
                }]
            },

            cssTest: {
                files: [{
                    src: 'tools/create/templates/rz_module/tests/CssTest.php',
                    dest: 'tests/php/modules/{moduleId}/CssTest.php'
                }]
            },

            moduleTest: {
                files: [{
                    src: 'tools/create/templates/rz_module/tests/ModuleTest.php',
                    dest: 'tests/php/modules/{moduleId}/ModuleTest.php'
                }]
            }

        },
        phpunit: {
            all: {
                dir: 'tests/php/'
            },
            options: {
                bin: 'tests/php/phpunit.phar',
                configuration: 'tests/php/phpunit.xml',
                colors: true,
                followOutput: true
            }
        },
        availabletasks: {
            tasks: {
                options: {
                    filter: 'exclude',
                    tasks: ['min', 'cssmin', 'exportLanguageFileToTarget', 'copy', 'default', 'availabletasks', 'saveRevision', 'uglify', 'version-release', 'version-snapshot', 'json-replace'],
                    groups: {
                        'Build tasks': ['build'],
                        'Development tasks': ['test', 'watch', 'jshint', 'jasmine', 'phpunit', 'jsonlint', 'phpcs', 'phplint', 'formatModuleData', 'checkFormatModuleData'],
                        'i18n import/export': ['exportLanguageFile', 'importLanguageFile', 'convertManifestLang'],
                        'Legacy converters': ['convertLegacyResponsiveModuleData', 'renameModuleFiles'],
                        'Scaffolding': ['create']
                    }
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-phplint');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-jsonlint');
    grunt.loadNpmTasks('grunt-json-replace');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-jasmine');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-available-tasks');

    // load local i18n tasks
    grunt.loadTasks('tools/i18n/tasks');

    // load local converter tasks
    grunt.loadTasks('tools/legacyConverter/tasks');

    // load local creator/template tasks
    grunt.loadTasks('tools/create/tasks');

    // list tasks
    grunt.registerTask('default', ['availabletasks']);

    // TASKS
    grunt.registerTask('min', ['uglify', 'cssmin']);
    grunt.registerTask('exportLanguageFile', ['exportLanguageFileToTarget']);
    grunt.registerTask('test', ['jsonlint', 'checkFormatModuleData', 'jshint', 'phplint', 'phpunit', 'jasmine']);

    // build
    grunt.registerTask('build', 'Create minified version of all modules in "' + buildDir + '", add version to manifest.json', ['copy', 'min', 'json-replace:manifest']);

};
