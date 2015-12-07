/* global module */
module.exports = function (grunt) {
    var name = 'renameModuleFiles';
    var description = [
        'Converts old file ("moduleData.json", "moduleManifest.json") to the new',
        'file structure ("form.json", "manifest.json" and "custom.json")'
    ].join('\n');


    function getModuleIdsFromSource(files) {
        var moduleIds = {};
        var i, j;

        for (i = 0; i < files.length; i++) {
            var sources = files[i].src;

            for (j = 0; j < sources.length; j++) {
                var moduleId = sources[j].replace(/\/.*$/, '');
                moduleIds[moduleId] = true;
            }
        }

        return Object.keys(moduleIds);
    }

    function convertModuleData(moduleId) {
        var legacyFile = moduleId + '/module/moduleData.json';
        var newFile =  moduleId + '/module/form.json';

        if (grunt.file.exists(legacyFile) && !grunt.file.exists(newFile))  {
            grunt.file.copy(legacyFile, newFile);
            grunt.file.delete(legacyFile);

            grunt.log.ok('Successfully converted "' + legacyFile + '" to "' + newFile + '"');
        } else {
            grunt.fail.warn('Cannot convert "moduleData.json" of "' + moduleId + '"');
        }
    }

    function convertModuleManifest(moduleId) {
        var legacyFile = moduleId + '/module/moduleManifest.json';
        var newManifest = moduleId + '/module/manifest.json';
        var newCustom = moduleId + '/module/custom.json';

        if (grunt.file.exists(legacyFile) && !grunt.file.exists(newManifest) && !grunt.file.exists(newCustom))  {
            var manifestJson = grunt.file.readJSON(legacyFile);
            var customJson = manifestJson.xm || {};

            delete manifestJson.xm;

            grunt.file.write(newManifest, JSON.stringify(manifestJson, null, 4));
            grunt.file.write(newCustom, JSON.stringify(customJson, null, 4));
            grunt.file.delete(legacyFile);

            grunt.log.ok('Successfully converted "' + legacyFile + '" to "' + newManifest + '" and "' +  newCustom + '"');
        } else {
            grunt.fail.warn('Cannot convert "moduleManifest.json" of "' + moduleId + '"');
        }
    }

    function convertModuleClassFile(moduleId) {
        var legacyFile = moduleId + '/module/' + moduleId + '.module.php';
        var newFile =  moduleId + '/module/' + moduleId + '.php';

        if (grunt.file.exists(legacyFile) && !grunt.file.exists(newFile))  {
            grunt.file.copy(legacyFile, newFile);
            grunt.file.delete(legacyFile);

            grunt.log.ok('Successfully converted "' + legacyFile + '" to "' + newFile + '"');
        } else {
            grunt.fail.warn('Cannot convert "' + legacyFile + '"');
        }
    }

    grunt.registerMultiTask(name, description, function () {
        var moduleIds = getModuleIdsFromSource(this.files);

        moduleIds.forEach(function (moduleId) {
            convertModuleData(moduleId);
            convertModuleManifest(moduleId);
            convertModuleClassFile(moduleId);
        });
    });
};
