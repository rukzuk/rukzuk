DynCSS.defineModule('rz_style_filter', function (api, v, res) {
    var result = {};
	var filters = [];
	
    if (v.cssEnableBlur) {
        filters.push('blur(' + v.cssBlur + ')');
    }
	
    if (v.cssEnableGrayscale) {
		filters.push('grayscale(' + v.cssGrayscale + ')');
	}

	if (v.cssEnableSaturate) {
		filters.push('saturate(' + v.cssSaturate + ')');
	}

	if (v.cssEnableBrightness) {
		filters.push('brightness(' + v.cssBrightness + ')');
	}

	if (v.cssEnableContrast) {
		filters.push('contrast(' + v.cssContrast + ')');
	}

	if (v.cssEnableSepia) {
		filters.push('sepia(' + v.cssSepia + ')');
	}

	if (v.cssEnableInvert) {
		filters.push('invert(' + v.cssInvert + ')');
	}

	if (v.cssEnableHueRotate) {
		filters.push('hue-rotate(' + v.cssHueRotate + ')');
	}
	
	if (filters.length) {
		filters = filters.join(' ');
		result = {
			'-webkit-filter': filters,
			filter: filters
		};
	}

    return result;
});
