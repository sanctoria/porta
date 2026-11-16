(function () {
	'use strict';
	var cfg = window.portaLoginLogin || {};

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	ready(function () {
		// WordPress already ships the eye toggle (.wp-hide-pw) on the password
		// field. Do not add a second "Show password" button.
		if (cfg.rememberMeDefault) {
			var rem = document.getElementById('rememberme');
			if (rem) {
				rem.checked = true;
			}
		}
	});
})();
