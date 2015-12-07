define(['jquery'], function ($) {
	return {
		init: function () {
			$('input[type=submit][id^=fieldMUNIT]').click(function (e) {
				e.preventDefault();
				return false;
			});
		}
	};
});