DynCSS.defineModule('rz_form', function (api, v, ctx) {
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
        '.rz_form_field_text label, .rz_form_field_select label': cssLabel,
		'fieldset': cssFieldSet,
		'fieldset label': cssFieldSetLabel
    };
    return result;

});