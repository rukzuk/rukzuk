DynCSS.defineModule('rz_form_field_select', function (api, v, ctx) {
    
    if (v.cssEnableStyling) {

        var cssLabel = {};
		var cssFieldSet = {};
		var cssFieldSetLabel = {};
        if (v.cssEnableLabelAlign == 'left') {
            cssLabel.display = 'table-cell';
			cssFieldSetLabel.display = 'inline-block';
			cssFieldSetLabel.marginTop = '0px';
			cssFieldSet.display = 'block';
        } else {
            cssLabel.display = 'block';
        }
        var result = {
            '&.rz_form_field_select label': cssLabel,
			'&.rz_form_field_select fieldset': cssFieldSet,
			'&.rz_form_field_select fieldset label': cssFieldSetLabel
        };

        return result;
    }

});