/* global DynCSS, PHP */
DynCSS.enableDebug(PHP.isEditMode);

DynCSS.setFormValueImpl(function (unitId) {
    return PHP.getFormValues(unitId);
});

DynCSS.setResolutionsImpl(function () {
    return PHP.resolutions || {};
});

// api methods
DynCSS.registerGetColorById( function (colorId) {
    return PHP.getColorById(colorId);
});
DynCSS.registerGetImageUrl(function (mediaId, size, quality) {
    // TODO: more param checks?
    return PHP.getImageUrl(mediaId, size, quality);
});
DynCSS.registerGetMediaUrl(function (mediaId, download) {
    // TODO: more param checks?
    return PHP.getMediaUrl(mediaId, download);
});

// add json tree
DynCSS.add(JSON.parse(JSON.stringify(PHP.jsonTree)));

// compile code
DynCSS.compile(function (err, css) {
    if (err) {
        PHP.error(err);
    } else {
        PHP.callback(css);
    }
});
