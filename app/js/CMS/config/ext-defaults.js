Ext.ns('CMS.config');

/**
* Default config options for Ext classes
* @requires CMS.config.urls
*/
Ext.apply(CMS.config, {
    'ext-defaults': {

        'Ext.Window': {
            constrainHeader: true,
            shadow: false
        },
        'Ext.grid.GridPanel': {
            loadMask: true
        },
        'Ext.ux.form.ColorPickerField': {
            imgPath: CMS.config.urls.imagePath
        }

    }
});

Ext.apply(Ext, {
    USE_NATIVE_JSON: true
});
