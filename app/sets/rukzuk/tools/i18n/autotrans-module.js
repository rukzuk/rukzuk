#!/usr/bin/env node
/*!
 * Commandline tool to replace certain
 * values in a client lang file (json) with others (based on a JSON-Map file)
 *
 * Developed to replace text strings in generated module lang files
 */
var fs = require('fs');
var tr = require('yandex-translate');
var Sync = require('sync');

var inputFile = process.argv[2];
var glossaryFile = process.argv[3] || './glossary.json';
var staticStrFile = process.argv[4] || './static-module-str-map.json';

// TODO: add parameters and check if lang was already set?! Currently the target lang is overwritten!
var initialLang = 'de';
var targetLang = 'en';

tr.defaults({key: 'trnsl.1.1.20130812T115230Z.20bc8292c224b0c0.07ddf3cd7fa1e1c3b0be6b5553a4591a25c75eea'});

if (!inputFile || !glossaryFile || !staticStrFile) {
    console.error(process.argv[1], " <input-module-lang-file> [glossary-map] [static-str-map]");
    process.exit(1);
}

// based on http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript/3561711#3561711
RegExp.escape = function (s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

// fetch data
var glossaryReplaceMap = JSON.parse(fs.readFileSync(glossaryFile, {encoding: 'utf8'}));

// invert object key => value to __i18n_value => key
var staticStrReplaceMap = (function () {
    var staticStrObj = JSON.parse(fs.readFileSync(staticStrFile, {encoding: 'utf8'}));
    var res = {};
    Object.keys(staticStrObj).forEach(function (key) {
        res['__i18n_' + staticStrObj[key]] = key;
    });
    return res;
})();

// read Data File
var data = JSON.parse(fs.readFileSync(inputFile, {encoding: 'utf8'}));

// Run in a fiber
Sync(function () {

    var glossaryStats = {};
    var staticStrStats = {};

    // iterate over all modules
    Object.keys(data).forEach(function (moduleFolder) {

        console.warn('===== Module:', moduleFolder);

        // iterate over all selectors
        var moduleLang = data[moduleFolder];
        Object.keys(moduleLang).forEach(function (selector) {
            var langDict = moduleLang[selector];

            // replace static strings (__i18n_...)
            Object.keys(staticStrReplaceMap).forEach(function (needle) {
                if (langDict[initialLang] == needle) {
                    staticStrStats[needle] = staticStrStats[needle] ? staticStrStats[needle] + 1 : 1;
                    langDict[initialLang] = staticStrReplaceMap[needle];
                }
            });

            // use src as start point
            if (String(langDict).toString() !== '[object Object]') {
                console.error('ERROR: malformed input!', 'langDict', langDict, 'initialLang',initialLang);
                process.exit(132);
            }

            langDict[targetLang] = langDict[initialLang];

            // replace glossary entries
            // TODO: add more languages in glossary (atm its implicit de->en)
            Object.keys(glossaryReplaceMap).forEach(function (needle) {
                // yeah node.js does not understand String.replace(...,..., flags) see: https://github.com/joyent/node/issues/4438
                var re = new RegExp('\\b(' + RegExp.escape(needle) + ')\\b', 'g');

                var match = langDict[targetLang].match(re);
                if (match) {
                    glossaryStats[needle] = glossaryStats[needle] ? glossaryStats[needle] + match.length : match.length;
                    langDict[targetLang] = langDict[targetLang].replace(re, glossaryReplaceMap[needle]); // + '($1)'
                    // Capitalize the first Char
                    langDict[targetLang] = langDict[targetLang].charAt(0).toUpperCase() + langDict[targetLang].slice(1);
                }
            });

            // auto translate via web-api
            var origStr = String(langDict[targetLang]);
            var result = tr.translate.sync(tr, origStr, {from: initialLang, to: targetLang});
            if (result.text && result.text[0]) {
                console.warn(langDict[targetLang], '==>', result.text[0]);
                langDict[targetLang] = '§' + result.text[0];
            } else {
                console.warn('Miss: ', langDict[targetLang], result);
            }
        }, this);

        console.warn('------');
    }, this);

    console.warn('glossaryStats', glossaryStats);
    console.warn('staticStrStats', staticStrStats);

    var langObjJsonStr = JSON.stringify(data, null, 4);
    console.warn(langObjJsonStr);

    fs.writeFileSync(inputFile.replace('.json', '_autotrans.json'), langObjJsonStr, {encoding: 'utf8'});

});
