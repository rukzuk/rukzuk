#!/usr/bin/env node
/*!
 * Auto translate moduleManifest.json files
 */
var fs = require('fs');
var tr = require('yandex-translate');
var Sync = require('sync');

var inputFile = process.argv[2];

// TODO: add parameters and check if lang was already set?! Currently the target lang is overwritten!
var initialLang = 'de';
var targetLang = 'en';

tr.defaults({key: 'trnsl.1.1.20130812T115230Z.20bc8292c224b0c0.07ddf3cd7fa1e1c3b0be6b5553a4591a25c75eea'});

if (!inputFile) {
    console.error('Translates a single moduelManifest.json file from', initialLang, 'to', targetLang);
    console.error(process.argv[1], ' <input-module-mainfest-file> ');
    process.exit(1);
}

// read Data File
var data = JSON.parse(fs.readFileSync(inputFile, {encoding: 'utf8'}));

/**
 * Helper to parse JSON if string is valid json and has an object as root
 * @param str
 * @returns {*} - Object or null
 */
var getJson = function (str) {
    try {
        var res = JSON.parse(str);
        // typeof null is also an object but this is ok in our case, otherwise use String(JSON.parse(str)) === '[object Object]'
        if(typeof res === 'object') {
            return res;
        }
        return null;
    } catch (e) {
        return null;
    }
};

// Run in a fiber
Sync(function () {

    // iterate over all modules
    Object.keys(data).forEach(function (manifestKey) {

        if (['name','description', 'category'].indexOf(manifestKey) < 0) {
            return;
        }

        // build 'text' => {"de": "text, "en":"text"}
        var langObj = getJson(data[manifestKey]);
        if (!langObj) {
            langObj = {};
            langObj[initialLang] = data[manifestKey];
            langObj[targetLang] = data[manifestKey];
        }

        // auto translate via web-api
        var origStr = String(langObj[initialLang]);
        if(origStr.length !== 0) {
            var result = tr.translate.sync(tr, origStr, {from: initialLang, to: targetLang});
            if (result.text && result.text[0]) {
                console.warn(origStr, '==>', result.text[0]);
                langObj[targetLang] = '§' + result.text[0];
            } else {
                console.warn('Miss: ', origStr, result);
            }
        }
        data[manifestKey] = JSON.stringify(langObj);

    }, this);

    var langObjJsonStr = JSON.stringify(data, null, 4);
    console.warn(langObjJsonStr);
    fs.writeFileSync(inputFile, langObjJsonStr, {encoding: 'utf8'});

});
