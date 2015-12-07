DynCSS.defineModule('rz_form_field_text', function (api, v, ctx) {
    
    if (v.cssEnableStyling) {

        var cssLabel = {};

        if (v.cssEnableLabelAlign == 'left') {
            cssLabel.display = 'table-cell';
        } else {
            cssLabel.display = 'block';
        }

        var result = {
            '&.rz_form_field_text label': cssLabel
        };

        return result;
    }

});