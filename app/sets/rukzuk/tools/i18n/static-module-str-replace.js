#!/usr/bin/env node
/*!
 * Commandline tool to replace certain
 * strings in a JSON file with others (based on a JSON-Map file)
 *
 * Developed to replace static text strings in moduleData.json
 *
 */
var fs = require('fs');

var someFile = process.argv[2];
var jsonMapFile = process.argv[3] || './static-module-str-map.json';

if (!someFile || !jsonMapFile) {
    console.error(process.argv[1], " <input-json-file> <json-map>");
    process.exit(1);
}

// based on http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript/3561711#3561711
RegExp.escape = function(s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

var replaceMap = JSON.parse(fs.readFileSync(jsonMapFile, 'utf8'));

fs.readFile(someFile, 'utf8', function (err, data) {
    if (err) {
        return console.error(err);
    }

    var result = JSON.stringify(JSON.parse(data), null, 4);
    var stats = {};

    Object.keys(replaceMap).forEach(function (key) {
        // yeah node.js does not understand String.replace(...,..., flags) see: https://github.com/joyent/node/issues/4438
        var re = new RegExp('"' + RegExp.escape(key) + '"', 'g');

        var match = result.match(re);
        if (match) {
            stats[key] = match.length;
            result = result.replace(re, '"__i18n_' + replaceMap[key] + '"');
        }


    });

    //console.warn(result);

    fs.writeFile(someFile, result, 'utf8', function (err) {
        if (err) return console.error(err);
    });

    console.log('Process', someFile);
    console.log(stats);
    console.log('Replace Done', '\n');

});
