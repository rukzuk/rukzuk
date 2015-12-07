DynCSS.defineModule('rz_style_webfont', function (api, v, context) {
    //fix for IE9: length of a name of a font-family must not exceed 31 chars! (see: rz_style_font/notlive/css.js)
    var fontFamily = v.webfontId.replace(/'|"/g, '').substr(0, 31);

    var numberOfStyles = 5;
    for (var i = 0; i < numberOfStyles; i++) {
        // urls of font files
        var woffUrl = api.getMediaUrl(v['woff' + i]);
        var ttfUrl = api.getMediaUrl(v['ttf' + i]);

        if (woffUrl || ttfUrl) {
            var fontFace = [];

            fontFace.push('@font-face {');
            fontFace.push('  font-family: "' + fontFamily + '";');

            var fontFaceSrc = [];

            if (woffUrl) {
                fontFaceSrc.push('url("' + woffUrl + '") format("woff")');
            }

            if (ttfUrl) {
                fontFaceSrc.push('url("' + ttfUrl + '") format("truetype")');
            }

            // main src rule
            fontFace.push('  src: ' + fontFaceSrc.join(', ') + ';');

            fontFace.push('  font-weight: ' + v['fontWeight' + i] + ';');
            fontFace.push('  font-style: ' + v['fontStyle' + i] + ';');
            fontFace.push('}');

            // add this rule RAW (no selector, no other modification)
            api.raw(fontFace.join('\n'));
        }
    }

    // we don't add something to the tree
    return {};
});
