/* global module, process */
(function () {
    'use strict';

    module.exports = function (grunt) {

        var name = 'create';
        var desc = 'Creates a new rukzuk module files from a set of template files';
        var readline = require('readline');
        var cli;

        function getCLI() {
            if (!cli) {
                cli = cli || readline.createInterface({
                    input: process.stdin,
                    output: process.stdout
                });
            }
            return cli;
        }


        function processFiles(moduleId, files) {
            var data = {
                data: {
                    moduleId: moduleId
                }
            };

            for (var i = 0; i < files.length; i++) {
                var source = files[i].src[0];
                var target = files[i].dest.replace(/\{moduleId\}/g, moduleId);

                processFile(source, target, data);
            }
        }

        function processFile(source, target, data) {
            if (grunt.file.exists(target)) {
                grunt.log.writeln('Skip processing "' + source + '" because target "' + target + '" already exists.');
            } else {
                var tpl = grunt.file.read(source);
                var out = grunt.template.process(tpl, data);

                grunt.file.write(target, out);
                grunt.log.ok('Write "' + source + '" to "' + target);
            }
        }

        function checkReleaseList(moduleId, cb) {
            // TODO: implement new channelmap.json handling
            cb();
        }

        grunt.registerMultiTask(name, desc, function () {
            var moduleId = grunt.option('moduleId');
            var self = this;
            var done = this.async();

            if (!moduleId) {
                // module id has not been provided
                // -> nag the user
                getCLI().question('Enter module ID: ', function (raw) {
                    var moduleId = raw.trim();
                    grunt.option('moduleId', moduleId);
                    processFiles(moduleId, self.files);
                    checkReleaseList(moduleId, done);
                });
            } else {
                processFiles(moduleId, this.files);
                checkReleaseList(moduleId, done);
            }
        });
    };
}());
