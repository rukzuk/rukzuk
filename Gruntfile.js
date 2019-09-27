/* global module */
module.exports = function (grunt) {
    var sourcemap = grunt.file.readJSON('app/js/sourcemap.json');
    var build = grunt.option('build') || 'SNAPSHOT';
    var channel = grunt.option('channel') || 'dev';
    var branch = grunt.option('branch') || 'unknown';
    var langFiles = 'app/lang/*.json';

    // the --files= command line parameter
    var testFiles = grunt.option('files');
    if (testFiles) {
        var pattern = /\.js$/;
        testFiles =  grunt.file.expand(testFiles.replace(/"/g, '').split(' ')).filter(function (f) {
            return pattern.test(f);
        });
    }

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        outputFolder: grunt.option('out') || 'build',
        packageFolder: (grunt.option('packageFolder')||'packaging'),
        artifactsFolder: grunt.option('artifactsFolder') || 'artifacts',
        reportsFolder: grunt.option('reportsFolder') || 'reports',
        channel: channel,
        build: build,
        branch: branch,

        copyright: {
            ext: grunt.file.read('app/copyright/ext.txt'),
            qrcodejs: grunt.file.read('app/copyright/qrcodejs.txt'),
            sb: grunt.file.read('app/copyright/rukzuk.txt'),
            tinymce: grunt.file.read('app/copyright/tinymce.txt'),
            uxSpinner: grunt.file.read('app/copyright/ux-spinner.txt'),
            uxTinymce: grunt.file.read('app/copyright/ux-tinymce.txt'),
            zipjs: grunt.file.read('app/copyright/zipjs.txt')
        },

        // ////////////////////////////////////////////////////////////////////
        // configure JSHint (documented at http://www.jshint.com/docs/)

        jsonlint: {
            all: {
                files: [{
                    src: 'app/lang/*.json'
                }, {
                    src: 'app/js/sourcemap.json'
                }, {
                    src: 'app/default/**/*.json'
                }]
            }
        },

        jshint: {
            files: testFiles || ['Gruntfile.js', 'app/js/CMS/**/*.js', 'app/js/SB/**/*.js', 'app/default/pageType/**/*.js', 'app/js/ext-overrides.js', '!app/js/SB/lib/URI.js'],
            options: {
                jshintrc: '.jshintrc',
                reporterOutput: ""
            }
        },

        checkLang: {
            files: ['app/js/SB/**/*.js', 'app/js/CMS/**/*.js', 'app/html/*.tpl', 'app/js/CMS/config/formElements/*.json'],
            options: {
                languageFiles: langFiles
            }
        },


        // ////////////////////////////////////////////////////////////////////
        // configure Sass
        'dart-sass': {
            production: { // options of the production version
                cwd: 'app/',
                options: {
                    outputStyle: 'compressed',
                    sourceMap: false,
                },
                files: {
                    'app/css/cms-all.css': 'app/sass/cms-all.scss',
                    'app/css/login.css': 'app/sass/login.scss',
                }
            },
            dev: { // options for the dev version
                cwd: 'app/',
                options: {
                    outputStyle: 'expanded',
                    sourceMap: true,
                    sourceMapEmbed: true,
                },
                files: {
                    'app/css/cms-all.css': 'app/sass/cms-all.scss',
                    'app/css/login.css': 'app/sass/login.scss',
                }

            }
        },

        // ////////////////////////////////////////////////////////////////////
        // configure copying of static files
        copy: {
            main: {
                files: [{
                    src: ['app/js/CMS/api/ApiAdapter.js'],
                    dest: '<%= outputFolder %>/app/js/CMS/api/',
                    flatten: true,
                    expand: true
                }, {
                    src: ['**'],
                    cwd: 'app/js/tiny_mce/',
                    dest: '<%= outputFolder %>/app/js/tiny_mce/',
                    expand: true
                }, {
                    src: ['**'],
                    cwd: 'app/js/bowser/',
                    dest: '<%= outputFolder %>/app/js/bowser/',
                    expand: true
                }, {
                    src: ['**'],
                    cwd: 'app/images/',
                    dest: '<%= outputFolder %>/app/images/',
                    expand: true
                }, {
                    src: ['**'],
                    cwd: 'app/login/images/',
                    dest: '<%= outputFolder %>/app/login/images/',
                    expand: true
                }, {
                    src: ['app/login/jquery-2.1.4.min.js'],
                    dest: '<%= outputFolder %>/app/login/',
                    flatten: true,
                    expand: true
                }, {
                    src: ['**'],
                    cwd: 'app/js/Ext/ext-3.2.1/resources/images/gray/',
                    dest: '<%= outputFolder %>/app/images/gray',
                    expand: true
                }, {
                    src: ['**'],
                    cwd: 'app/js/Ext/ext-3.2.1/resources/images/default/',
                    dest: '<%= outputFolder %>/app/images/default/',
                    expand: true
                }, {
                    src: ['app/js/CMS/i18n.js'],
                    dest: '<%= outputFolder %>/app/js/CMS/i18n.js'
                }, {
                    src: ['robots.txt', 'app/images/favicon.ico'],
                    dest: '<%= outputFolder %>/',
                    flatten: true,
                    expand: true
                }, {
                    src: ['app/blank.htm'],
                    dest: '<%= outputFolder %>/'
                }, {
                    src: ['app/backend_tpl.php'],
                    dest: '<%= outputFolder %>/'
                }, {
                    src: ['app/css/fonts/**', 'app/css/empty.css'],
                    dest: '<%= outputFolder %>/'
                }, {
                    src: ['app/sass/_mixins.scss', 'app/sass/_theme.scss', 'app/sass/cms-theme.scss', 'app/sass/login-theme.scss'],
                    dest: '<%= outputFolder %>/'
                }, {
                    src: ['**'],
                    cwd: 'app/default/pageType/',
                    dest: '<%= outputFolder %>/app/default/pageType/',
                    expand: true
                }]
            },
            packageClient: {
                files: [{
                    cwd: '<%= outputFolder %>/app/',
                    src: ['**'],
                    dest: '<%= packageFolder %>/<%= build %>/htdocs/app/',
                    expand: true
                }]
            },
            packageCMS: {
                options: {
                    mode: true
                },
                files: [{
                    cwd: 'app/server/',
                    src: ['application/**', 'images/**', 'library/**', 'bin/run.php', '!**/application/configs/local-application.ini', '!**/tests/**'],
                    dest: '<%= packageFolder %>/<%= build %>/htdocs/app/server/',
                    expand: true
                },
                {
                    cwd: 'app/server/',
                    src: ['environment/**'],
                    dest: '<%= packageFolder %>/<%= build %>/htdocs/app/server/',
                    expand: true
                },
                {
                    cwd: 'app/server',
                    src: ['environment/**'],
                    dest: '<%= packageFolder %>/<%= build %>/',
                    expand: true
                },
                {
                    cwd: 'app/service',
                    src: ['**'],
                    dest: '<%= packageFolder %>/<%= build %>/htdocs/app/service/',
                    expand: true
                },
                {
                    cwd: 'app/var',
                    src: ['*'],
                    dest: '<%= packageFolder %>/<%= build %>/htdocs/cms/var/',
                    expand: true
                }
                ]
            }
        },

        replace: {
            cmsVersion: {
                src: ['<%= packageFolder %>/<%= build %>/htdocs/app/server/library/Cms/Version.php'],
                overwrite: true,
                replacements: [
                    {
                        from: /@@BUILD.HASH@@/g,
                        to: '<%= buildHash %>'
                    },
                    {
                        from: /@@BUILD.VERSION@@/g,
                        to: '<%= build %>'
                    },
                    {
                        from: /@@BUILD.CHANNEL@@/g,
                        to: '<%= channel %>'
                    }
                ]
            }
        },

        compress: {
            rukzuk: {
                options: {
                    archive: '<%= artifactsFolder %>/<%= build %>.tgz',
                    mode: 'tgz'
                },
                files: [
                    {expand: true, cwd: '<%= packageFolder %>/', src: ['**'], dest: '.'}
                ]
            }
        },
        // ////////////////////////////////////////////////////////////////////
        // configure CSS minifier
        cssmin: {
            production: {
                options: {
                    banner: '<%= copyright.sb %>'
                },
                files: [{
                    src: 'app/css/cms-all.css',
                    dest: '<%= outputFolder %>/app/css/cms-all.css'
                }, {
                    src: ['app/css/login.css'],
                    dest: '<%= outputFolder %>/app/css/login.css'
                }, {
                    src: 'app/css/fileuploadfield.css',
                    dest: '<%= outputFolder %>/app/css/fileuploadfield.css'
                }, {
                    src: 'app/css/tricheckbox.css',
                    dest: '<%= outputFolder %>/app/css/tricheckbox.css'
                }]
            }
        },

        // ////////////////////////////////////////////////////////////////////
        // configure JS minifier
        // (one target per copyright header)
        uglify: {
            options: {
                report: 'min'
            },

            ext: {
                options: {banner: '<%= copyright.ext %>'},
                files: [{
                    src: sourcemap.extSources.src,
                    dest: '<%= outputFolder %>/' + sourcemap.extSources.dest
                }]
            },

            plupload: {
                files: [{
                    src: sourcemap.pluploadSources.src,
                    dest: '<%= outputFolder %>/' + sourcemap.pluploadSources.dest
                }]
            },

            qrcodejs: {
                options: {banner: '<%= copyright.qrcodejs %>'},
                files: [{
                    src: 'app/js/qrcodejs/qrcode.js',
                    dest: '<%= outputFolder %>/app/js/qrcodejs/qrcode.js'
                }]
            },

            sb: {
                options: {banner: '<%= copyright.sb %>'},
                files: [{
                    src: 'app/lang/lang.js',
                    dest: '<%= outputFolder %>/app/lang/lang.js'
                }, {
                    src: sourcemap.extFixes.src,
                    dest: '<%= outputFolder %>/' + sourcemap.extFixes.dest
                }, {
                    src: sourcemap.sbSources.src,
                    dest: '<%= outputFolder %>/' + sourcemap.sbSources.dest
                }, {
                    src: sourcemap.cmsConfigs.src,
                    dest: '<%= outputFolder %>/' + sourcemap.cmsConfigs.dest
                }, {
                    src: sourcemap.cmsSources.src,
                    dest: '<%= outputFolder %>/' + sourcemap.cmsSources.dest
                }, {
                    src: sourcemap.loginSources.src,
                    dest: '<%= outputFolder %>/' + sourcemap.loginSources.dest
                }, {
                    src: sourcemap.cmsFormEditSources.src,
                    dest: '<%= outputFolder %>/' + sourcemap.cmsFormEditSources.dest
                }]
            },

            tinymce: {
                options: {banner: '<%= copyright.tinymce %>'},
                files: [{
                    src: 'app/js/tiny_mce/tiny_mce.js',
                    dest: '<%= outputFolder %>/app/js/tiny_mce/tiny_mce.js'
                }]
            },

            uxSpinner: {
                options: {banner: '<%= copyright.uxSpinner %>'},
                files: [{
                    src: 'app/js/Ext/ux/Spinner.js',
                    dest: '<%= outputFolder %>/app/js/Ext/ux/Spinner.js'
                }]
            },

            uxTinymce: {
                options: {banner: '<%= copyright.uxTinymce %>'},
                files: [{
                    src: 'app/js/Ext/ux/TinyMCE.js',
                    dest: '<%= outputFolder %>/app/js/Ext/ux/TinyMCE.js'
                }]
            },

            zipjs: {
                options: {banner: '<%= copyright.zipjs %>'},
                files: [{
                    src: 'app/js/zipjs/zip.js',
                    dest: '<%= outputFolder %>/app/js/zipjs/zip.js'
                }, {
                    src: 'app/js/zipjs/deflate.js',
                    dest: '<%= outputFolder %>/app/js/zipjs/deflate.js'
                }]
            },

            defaultPageType: {
                files: [{
                    src: 'app/default/pageType/assets/pageType.js',
                    dest: '<%= outputFolder %>/app/default/pageType/assets/pageType.js'
                }]
            }
        },

        // ////////////////////////////////////////////////////////////////////
        // configure compiling the html files
        compileHtml: {
            production: {
                options: {
                    data: {
                        mode: 'production',
                        extSources: [sourcemap.extSources.dest],
                        extFixes: [sourcemap.extFixes.dest],
                        pluploadSources: [sourcemap.pluploadSources.dest],
                        sbSources: [sourcemap.sbSources.dest],
                        cmsConfigs: [sourcemap.cmsConfigs.dest],
                        cmsSources: [sourcemap.cmsSources.dest],
                        cmsFormEditSources: [sourcemap.cmsFormEditSources.dest],
                        loginSources: [sourcemap.loginSources.dest]
                    },
                    languageFiles: langFiles
                },
                files: [{
                    src: 'app/html/index.html.tpl',
                    dest: '<%= outputFolder %>/app/index.html'
                }, {
                    src: 'app/html/error500.html.tpl',
                    dest: '<%= outputFolder %>/app/error500.html'
                }, {
                    src: 'app/html/error404.html.tpl',
                    dest: '<%= outputFolder %>/app/error404.html'
                }, {
                    src: 'app/html/error403.html.tpl',
                    dest: '<%= outputFolder %>/app/error403.html'
                }, {
                    src: 'app/html/nojs.html.tpl',
                    dest: '<%= outputFolder %>/app/nojs.html'
                }, {
                    src: 'app/html/login.html.tpl',
                    dest: '<%= outputFolder %>/app/login.html'
                }, {
                    src: 'app/html/formeditor.html.tpl',
                    dest: '<%= outputFolder %>/app/formeditor.html'
                }, {
                    src: 'app/html/unsupported_browser.html.tpl',
                    dest: '<%= outputFolder %>/app/unsupported_browser.html'
                }]
            },

            dev: {
                options: {
                    data: {
                        mode: 'dev',
                        extSources: sourcemap.extSources.src,
                        extFixes: sourcemap.extFixes.src,
                        pluploadSources: sourcemap.pluploadSources.src,
                        sbSources: sourcemap.sbSources.src,
                        cmsConfigs: sourcemap.cmsConfigs.src,
                        cmsSources: sourcemap.cmsSources.src,
                        cmsFormEditSources: sourcemap.cmsFormEditSources.src,
                        loginSources: sourcemap.loginSources.src
                    },
                    languageFiles: langFiles
                },
                files: [{
                    src: 'app/html/index.html.tpl',
                    dest: 'app/index.html'
                }, {
                    src: 'app/html/error403.html.tpl',
                    dest: 'app/error403.html'
                }, {
                    src: 'app/html/error404.html.tpl',
                    dest: 'app/error404.html'
                }, {
                    src: 'app/html/error500.html.tpl',
                    dest: 'app/error500.html'
                }, {
                    src: 'app/html/login.html.tpl',
                    dest: 'app/login.html'
                }, {
                    src: 'app/html/nojs.html.tpl',
                    dest: 'app/nojs.html'
                }, {
                    src: 'app/html/formeditor.html.tpl',
                    dest: 'app/formeditor.html'
                }, {
                    src: 'app/html/unsupported_browser.html.tpl',
                    dest: 'app/unsupported_browser.html'
                }]
            }
        },

        // ////////////////////////////////////////////////////////////////////
        // configure compiling the html files
        createLang: {
            all: {
                files: [{
                    src: 'app/lang/*.json',
                    dest: 'app/lang/lang.js'
                }]
            }
        },

        createFormElements: {
            all: {
                files: [{
                    src: 'app/js/CMS/config/formElements/*.json',
                    dest: 'app/js/CMS/config/formElements.js'
                }]
            }
        },

        convertForm: {
            all: {
                files: [{
                    //src: 'app/**/modules-v4/**/form.json',
                }, {
                    src: 'app/**/modules-v4/**/websiteSettings.json'
                }, {
                    src: 'app/**/modules-v4/**/pageType.json'
                }]
            }
        },

        // ////////////////////////////////////////////////////////////////////
        // configure image minifier
        imagemin: {
            dist: {
                options: {
                    optimizationLevel: 3
                },
                files: [{
                    expand: true,
                    src: '**/*.png',
                    cwd: 'app/images/',
                    dest: '<%= outputFolder %>/app/images/'
                }, {
                    expand: true,
                    src: '**/*.png',
                    cwd: 'app/login/images/',
                    dest: '<%= outputFolder %>/app/login/images/'
                }, {
                    expand: true,
                    src: '**/*.png',
                    cwd: 'app/js/Ext/ext-3.2.1/resources/images/gray/',
                    dest: '<%= outputFolder %>/app/images/gray/'
                }, {
                    expand: true,
                    src: '**/*.png',
                    cwd: 'app/js/Ext/ext-3.2.1/resources/images/default/',
                    dest: '<%= outputFolder %>/app/images/default/'
                }]
            }
        },

        // ////////////////////////////////////////////////////////////////////
        // configure image minifier
        watch: {
            sass: {
                files: ['app/sass/*.scss'],
                tasks: ['sass:dev']
            },
            html: {
                files: ['app/**/*.html.tpl', 'app/js/sourcemap.json'],
                tasks: ['compileHtml:dev']
            },
            lang: {
                files: ['app/lang/*.json'],
                tasks: ['createLang']
            },
            formElements: {
                files: ['js/CMS/config/formElements/*.json'],
                tasks: ['createFormElements']
            },
            livereload: {
                // Send HTML, CSS and JavaScript files to the liveReload-server
                // if they are changed
                files: ['app/**/*.html', 'app/css/*.css', 'app/js/**/*.js'],
                options: {
                    livereload: true
                }
            }
        },

        setDefaultLang: {
            options: {
                sourceFiles: 'app/js/CMS/**/*.js',
                langFiles: 'app/lang/*.json',
                defaultLanguage: 'app/lang/en-US.json'
            },
            all: {}
        },

        phpunit: {
            all: {
                dir: 'app/server/tests/'
            },
            lib: {
                dir: 'app/server/tests/library'
            },
            app: {
                dir: 'app/server/tests/application'
            },
            small: {
                group: 'small'
            },
            coverage: {
                dir: 'app/server/tests/',
                options: {
                    logJunit: '<%= reportsFolder %>/cms/unitreport.xml',
                    coverageHtml: '<%= reportsFolder %>/cms/coverage/',
                    coverageClover: '<%= reportsFolder %>/cms/coverage.xml'
                }
            },
            options: {
                bin: 'app/server/vendor/bin/phpunit',
                configuration: 'app/server/tests/phpunit.xml',
                colors: true,
                followOutput: true
            }
        },
        availabletasks: {
            tasks: {
                options: {
                    filter: 'exclude',
                    tasks: ['default', 'availabletasks', 'copy', 'cssmin'],
                    groups: {
                        'Build tasks': ['build'],
                        'Development tasks': ['dev', 'test', 'watch', 'jshint', 'jsonlint', 'checkLang', 'phpunit', 'composer']
                    }
                }
            }
        },
        composer: {
            production: {
                options: {
                    usePhp: true,
                    phpArgs: {},
                    flags: ['no-dev'],
                    cwd: './app/server/library',
                    composerLocation: '../composer.phar'
                }
            },
            dev: {
                options: {
                    usePhp: true,
                    phpArgs: {},
                    flags: [],
                    cwd: './app/server',
                    composerLocation: './composer.phar'
                }
            }
        },
        phpcs: {
            application: {
                // src: ['./app/server/library/', './app/server/application/']
                src: ['./app/server/library/']
            },
            options: {
                bin: './app/server/vendor/bin/phpcs',
                standard: './app/server/rz_coding_standard',
                report: 'summary'
            }
        }
    });


    // load grunt plugins
    grunt.loadNpmTasks('grunt-available-tasks');
    grunt.loadNpmTasks('grunt-jsonlint');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-dart-sass');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-git');
    grunt.loadNpmTasks('grunt-composer');
    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-text-replace');
    grunt.loadTasks('tools/i18n/tasks');
    grunt.loadTasks('tools/forms/tasks');

    // list tasks
    grunt.registerTask('default', ['availabletasks']);
    grunt.registerTask('sass', ['dart-sass']); // alias sass for dart-sass

    grunt.registerMultiTask('compileHtml', 'Compile HTML files from Lo-Dash templates with i18n support', function () {
        var options = this.options();
        var timestamp = Date.now();

        options.data.build = build;
        options.data.branch = grunt.config.get("branch");
        options.data.channel = channel;
        options.data.timestamp = timestamp;
        options.data.date = grunt.template.today("isoDateTime");
        options.data.cacheBuster = function (src) {
            return options.data.mode !== 'dev' ? 'v-' + timestamp + '/' + src : src;
        };

        var langFiles = grunt.file.expand(this.options().languageFiles);

        var dic;
        options.data.i18n = function (key) {
            if (!dic.dictionary[key]) {
                grunt.fail.warn('i18n: key "' + key + '" in language "' + dic.key + '" not found!');
            }
            return dic.dictionary[key] || key;
        };

        langFiles.forEach(function (langFile) {
            dic = grunt.file.readJSON(langFile);
            options.data.i18nLangCode = dic.key;
            this.files.forEach(function (file) {
                var tpl = grunt.file.read(file.src[0]);
                var compiled = grunt.template.process(tpl, options);
                grunt.file.write(file.dest.replace(/\.html/, '_' + dic.key + '.html'), compiled);
            }, this);
        }, this);
    });

    grunt.registerTask('test', ['jsonlint', 'jshint', 'checkLang']);

    grunt.registerTask('dev', [
        'sass:dev',
        'createLang',
        'createFormElements',
        'resolveGitBranch',
        'compileHtml:dev',
        'composer:dev:install',
        'composer:production:install',
    ]);

    grunt.registerTask('build', [
        'sass:production',
        'createLang',
        'createFormElements',
        'copy:main',
        'cssmin',
        'uglify',
        'compileHtml:production',
        'imagemin',
        'copyWebsiteExports'
    ]);

    grunt.registerTask('packageClient', [
        'build',
        'copy:packageClient'
    ]);

    grunt.registerTask('packageCms', [
        'copy:packageCMS',
        'updateCmsVersionFile'
    ]);

    grunt.registerTask('package', [
        'packageClient',
        'packageCms',
        'compress:rukzuk'
    ]);

    grunt.registerTask('updateCmsVersionFile', function() {
        var exec = require('child_process').exec;
        var done = this.async();
        exec("git log -1 --format='%H'", {
            cwd: '.'
        }, function (error, stout) {
            if (!error) {
                grunt.config.set("buildHash", stout.trim());
                grunt.log.ok();
            } else {
                console.log(error.message);
                grunt.fail.fatal('Errors in test');
            }
            done();
        });
        grunt.task.run(['replace:cmsVersion']);

    });

    grunt.registerTask('resolveGitBranch', function() {
        var exec = require('child_process').exec;
        var done = this.async();
        exec("git rev-parse --abbrev-ref HEAD", {
            cwd: '.'
        }, function (error, stout) {
            if (!error) {
                grunt.config.set("branch", stout.trim());
                grunt.log.ok();
            } else {
                console.log(error.message);
                grunt.fail.warn('Errors while getting git branch');
            }
            done();
        });
    });

    grunt.registerTask('copyWebsiteExports', function() {
        var websitesConfig = grunt.file.readJSON('app/exports/websites.json');
        var outputFolder = grunt.config.get('outputFolder');
        grunt.file.copy('app/exports/websites.json',  outputFolder + '/app/exports/websites.json');
        websitesConfig.categories.forEach(function(category){
            grunt.log.ok('copy websites of category: ' + category.id);
            category.websites.forEach(function(website) {
                grunt.log.ok('copy website: ' + website.id);
                grunt.file.expandMapping('app/exports/' + website.id + '/**', outputFolder, {filter: 'isFile'}).forEach(function (obj) {
                    grunt.file.copy(obj.src, obj.dest);
                });
            });
        });
    });

    grunt.registerTask('db', function (action, subaction) {
        /* global require:false */
        var exec = require('child_process').exec;
        var done = this.async();
        var conf = '-n ';
        var doctrineCmd = './bin/doctrine';

        if (!action) {
            action = 'migrations:status';
        }

        if (action === 'migrate') {
            action = 'migrations:migrate';
        }

        if (action === 'generate') {
            action = 'migrations:generate';
        }

        if (action === 'help' || action === 'list') {
            action = 'list';
            conf = '';
        }

        if (action === 'migrate-testing') {
            action = 'migrations:migrate';
            doctrineCmd = './bin/doctrine-testing';
        }

        if (subaction && !action.match(/\:/)) {
            action = action + ':' + subaction;
        }

        exec(doctrineCmd + ' ' + action + ' ' + conf, {
            cwd: 'app/server/'
        }, function (error, stdout) {
            grunt.log.write(stdout);

            if (!error) {
                grunt.log.ok();
            } else {
                console.log(error.message);
                grunt.fail.fatal('Errors in test');
            }
            done();
        });
    });

    grunt.registerTask('orm', function (action, subaction) {
        /* global require:false */
        var exec = require('child_process').exec;
        var done = this.async();
        var conf = '';
        var doctrineCmd = './bin/doctrine';

        if (!action) {
            action = 'orm:info';
        }

        if (action === 'generate-repositories') {
            action = 'orm:generate-repositories';
            conf = './library';
        }

        if (action === 'generate-proxies') {
            action = 'orm:generate-proxies';
            conf = './library/Orm/Proxies';
        }

        if (action === 'help' || action === 'list') {
            action = 'list';
            conf = '';
        }

        if (subaction && !action.match(/\:/)) {
            action = action + ':' + subaction;
        }

        exec(doctrineCmd + ' ' + action + ' ' + conf, {
            cwd: 'app/server/'
        }, function (error, stout) {
            console.log(stout);

            if (!error) {
                grunt.log.ok();
            } else {
                console.log(error.message);
                grunt.fail.fatal('Errors in test');
            }
            done();
        });
    });


};
