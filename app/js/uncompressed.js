/**
* This should only be included in uncompressed ("Nightly") mode.
* Used to reflect the fact that this is an uncompressed version in the UI.
*/

Ext.onReady(function () {
    (function () {
        CMS.app.Application.setProductName(null, ['uncompressed'].concat(CMS.app.Application.productSubtitles));
    }).defer(1); // wait till application is loaded
});
