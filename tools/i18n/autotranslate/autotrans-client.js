#!/usr/bin/env node
/*!
 * Commandline tool to replace certain
 * values in a client lang file (json) with others (based on a JSON-Map file)
 *
 * Developed to replace text strings in lang/en-EN.json
 *
 */
/* global process,require,console */
var fs = require('fs');
var tr = require('yandex-translate');
var sync = require('sync');

var someFile = process.argv[2];
var jsonMapFile = process.argv[3] || './glossary.json';
tr.defaults({key: 'trnsl.1.1.20130812T115230Z.20bc8292c224b0c0.07ddf3cd7fa1e1c3b0be6b5553a4591a25c75eea'});

if (!someFile || !jsonMapFile) {
    console.error(process.argv[1], " <input-json-file> <json-map>");
    process.exit(1);
}

// based on http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript/3561711#3561711
RegExp.escape = function (s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

var replaceMap = JSON.parse(fs.readFileSync(jsonMapFile, 'utf8'));
var data = fs.readFileSync(someFile, 'utf8');

// Run in a fiber
sync(function () {

    var langObj = JSON.parse(data);

    var stats = {};
    var langDict = langObj.dictionary;

    //var langArrStrings = [];
    //var langArrKeys = [];

    // replace of custom strings
    Object.keys(langDict).forEach(function (langKey) {
        Object.keys(replaceMap).forEach(function (needle) {
            // yeah node.js does not understand String.replace(...,..., flags) see: https://github.com/joyent/node/issues/4438
            var re = new RegExp('\\b(' + RegExp.escape(needle) + ')\\b', 'g');

            var match = langDict[langKey].match(re);
            if (match) {
                stats[needle] = stats[needle] ? stats[needle] + match.length : match.length;
                langDict[langKey] = langDict[langKey].replace(re, replaceMap[needle]); // + '($1)'
                // Capitalize the first Char
                langDict[langKey] = langDict[langKey].charAt(0).toUpperCase() + langDict[langKey].slice(1);
            }
        });

        // use translation API
        var origStr = String(langDict[langKey]);
        var result = tr.translate.sync(tr, origStr, {from: 'de', to: 'en'});
        if (result.text && result.text[0]) {
            console.warn(langDict[langKey], '==>', result.text[0]);
            langDict[langKey] = result.text[0];
        } else {
            console.warn('Miss: ', result);
        }
    });

    var langObjJsonStr = JSON.stringify(langObj, null, 4);
    console.warn(langObjJsonStr);

    fs.writeFile(someFile + '_autotrans', langObjJsonStr, 'utf8', function (err) {
        if (err) {
            return console.error(err);
        }
    });

});
