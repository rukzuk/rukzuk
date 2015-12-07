/* global module, require */
/* jshint es5: true */
module.exports = function (grunt) {

    grunt.registerMultiTask('formatModuleData', 'Converts all module data to normalized format.', function () {
        var i, j;
        var count = 0;

        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;

            for (j = 0; j < sources.length; j++) {
                var source = sources[j];
                var json = grunt.file.readJSON(source);
                grunt.file.write(source, JSON.stringify(json, null, 4));
                count++;
            }
        }
        grunt.log.ok(count + ' file' + (count === 1 ? '' : 's') + ' json files formatted.');
    });


    grunt.registerMultiTask('checkFormatModuleData', 'Checks if all module data is proper formatted.', function () {
        var i, j;
        var count = 0;

        for (i = 0; i < this.files.length; i++) {
            var sources = this.files[i].src;

            for (j = 0; j < sources.length; j++) {
                var source = sources[j];
                var content = grunt.file.read(source);
                var formattedContent = JSON.stringify(JSON.parse(content), null, 4);
                if (content !== formattedContent) {
                    grunt.log.error(source, 'is not proper formatted! Fix with grunt formatModuleData');
                    return false;
                }
                count++;
            }
        }
        grunt.log.ok(count + ' file' + (count === 1 ? '' : 's') + ' have proper json format.');
    });

};
