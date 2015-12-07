// String utility functions by Seitenbau

/**
* @class SB.string
* @singleton
* Just a namespace containing string utility functions
*/
Ext.ns('SB.string');


/**
* @method toHTMLDocument
* Creates an HTMLDocument from an input string.
* For example: <tt>SB.string.toHTMLDocument('&lt;p id="foo"&gt;bar').getElementById('foo').innerHTML // "bar"</tt>
* @param {String} input A String containing markup
*/
SB.string.toHTMLDocument = (function () {
    var doc;
    var result = function (input) {
        //doc.open();
        //doc.write(input);
        //doc.close();
        if (!doc.documentElement) {
            doc.write('<html><head><title></title></head><body></body></html>');
        }
        try { // FIXME: does not respect head (SBCMS-86)
            // The thing is, we're injecting doctype, html, head and body.
            // Browsers do just fine with body, but head seems to be a bit of a problem.
            doc.documentElement.innerHTML = input;
        } catch (e) {
            doc.body.innerHTML = input;
        }
        return doc;
    };

    if (typeof document.implementation.createHTMLDocument == 'function') {
        doc = document.implementation.createHTMLDocument('');
        return result;
    } else
    // Firefox does not support document.implementation.createHTMLDocument()
    // cf. http://www.quirksmode.org/dom/w3c_html.html#t12
    // The following is taken from http://gist.github.com/49453
    /*global XSLTProcessor:false, DOMParser:false*/
    if (typeof XSLTProcessor != 'undefined') {
        var templ = '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">'
                + '<xsl:output method="html"/><xsl:template match="/">'
                + '<html><head><title/></head><body/></html>'
                + '</xsl:template></xsl:stylesheet>';
        var proc = new XSLTProcessor();
        proc.importStylesheet(new DOMParser().parseFromString(templ, 'text/xml'));
        doc = proc.transformToDocument(document.implementation.createDocument('', 'root', null));
        return result;
    } else { // fallback to iframe
        var fr = document.createElement('iframe');
        fr.style.cssText = 'display: none !important;';
        fr.src = Ext.SSL_SECURE_URL;
        return function (input) {
            document.body.appendChild(fr);
            doc = fr.contentDocument;
            result(input);
            document.body.removeChild(fr);
            return doc;
        };
    }
})();

/**
* @method trimTrailing
* Remove trailing whitespace from a multiline string
* @prarm {String} input The string to clean up
* @return String The cleaned up string
*/
SB.string.trimTrailing = function (input) {
    return input.replace(/[ \t]+([\r\n]+|$)/g, '$1');
};
