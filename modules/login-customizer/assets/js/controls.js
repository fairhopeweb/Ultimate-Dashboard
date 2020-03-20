/**
 * String.prototype.includes polyfill.
 * 
 * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/includes
 */
if (!String.prototype.includes) {
	String.prototype.includes = function (search, start) {
		'use strict';

		if (search instanceof RegExp) {
			throw TypeError('first argument must not be a RegExp');
		}
		if (start === undefined) { start = 0; }
		return this.indexOf(search, start) !== -1;
	};
}

/**
 * Scripts within customizer control panel.
 *
 * Used global objects:
 * - jQuery
 * - wp
 * - udbLoginCustomizer
 */
(function ($) {
	var events = {};
	var state = {};

	wp.customize.bind('ready', function () {
		setupControls();
		listen();
	});

	function setupControls() {
		rangeControl();
		colorControl();
		loginTemplateControl();
	}

	function listen() {
		events.switchLoginPreview();
		events.bgFieldsChange();
		events.templateFieldsChange();
		events.layoutFieldsChange();
	}

	function rangeControl() {
		controls = document.querySelectorAll('.udb-customize-control-range');
		if (!controls.length) return;

		[].slice.call(controls).forEach(function (control) {
			var controlName = control.dataset.controlName;
			var slider = control.querySelector('[data-slider-for="' + controlName + '"]');
			var value = {};

			value.raw = wp.customize(controlName).get() + '';

			value.unit = value.raw.replace(/\d+/g, '');
			value.unit = value.unit ? value.unit : '%';
			value.number = value.raw.replace(value.unit, '');
			value.number = parseInt(value.number.trim(), 10);

			wp.customize(controlName, function (setting) {
				setting.bind(function (val) {
					value.raw = val + '';

					value.unit = value.raw.replace(/\d+/g, '');
					value.unit = value.unit ? value.unit : '%';
					value.number = value.raw.replace(value.unit, '');
					value.number = parseInt(value.number.trim(), 10);

					slider.value = value.number;
				});
			});

			slider.addEventListener('input', function (e) {
				value.number = this.value;

				wp.customize(controlName).set(value.number + value.unit);
			});

			control.querySelector('.udb-customize-control-reset').addEventListener('click', function (e) {
				wp.customize(controlName).set(this.dataset.resetValue);
			});
		});
	}

	function colorControl() {
		controls = document.querySelectorAll('.udb-customize-control-color');
		if (!controls.length) return;

		[].slice.call(controls).forEach(function (control) {
			var clearColor = control.querySelector('.wp-picker-clear');
			if (!clearColor) return;
			clearColor.classList.remove('button-small');
		});
	}

	function loginTemplateControl() {
		controls = document.querySelectorAll('.udb-customize-control-login-template');
		if (!controls.length) return;

		[].slice.call(controls).forEach(function (control) {
			var controlName = control.dataset.controlName;
			var images = control.querySelectorAll('.udb-customize-control-template img');

			if (!images.length) return;

			[].slice.call(images).forEach(function (image) {
				image.addEventListener('click', function (e) {
					var selected = this;

					[].slice.call(images).forEach(function (img) {
						if (img == selected) {
							img.parentNode.classList.add('is-selected');
						} else {
							img.parentNode.classList.remove('is-selected');
						}
					});

					wp.customize(controlName).set(this.dataset.templateName);
				});
			});
		});
	}

	/**
	 * Change the page when the "Login Customizer" panel is expanded (or collapsed).
	 */
	events.switchLoginPreview = function () {
		wp.customize.panel('udb_login_customizer_panel', function (section) {
			section.expanded.bind(function (isExpanded) {

				var currentUrl = wp.customize.previewer.previewUrl();

				if (isExpanded) {
					if (!currentUrl.includes(udbLoginCustomizer.loginPageUrl)) {
						wp.customize.previewer.send('udb-login-customizer-goto-login-page', { expanded: isExpanded });
					}
				} else {
					// Head back to the home page, if we leave the "Login Customizer" panel.
					wp.customize.previewer.send('udb-login-customizer-goto-home-page', { url: wp.customize.settings.url.home });
				}

			});
		});
	}

	events.bgFieldsChange = function () {
		wp.customize.section('udb_login_customizer_bg_section', function (section) {
			section.expanded.bind(function (isExpanded) {
				if (isExpanded) {

					if (wp.customize('udb_login[bg_image]').get()) {
						wp.customize.control('udb_login[bg_position]').activate();
						wp.customize.control('udb_login[bg_size]').activate();
						wp.customize.control('udb_login[bg_repeat]').activate();
					} else {
						wp.customize.control('udb_login[bg_position]').deactivate();
						wp.customize.control('udb_login[bg_size]').deactivate();
						wp.customize.control('udb_login[bg_repeat]').deactivate();
					}

				}
			})
		});

		wp.customize('udb_login[bg_image]', function (setting) {
			setting.bind(function (val) {

				if (val) {
					document.querySelector('[data-control-name="udb_login[bg_image]"]').classList.remove('is-empty');

					wp.customize.control('udb_login[bg_position]').activate();
					wp.customize.control('udb_login[bg_size]').activate();
					wp.customize.control('udb_login[bg_repeat]').activate();
				} else {
					document.querySelector('[data-control-name="udb_login[bg_image]"]').classList.add('is-empty');

					wp.customize.control('udb_login[bg_position]').deactivate();
					wp.customize.control('udb_login[bg_size]').deactivate();
					wp.customize.control('udb_login[bg_repeat]').deactivate();
				}

			});
		});
	};

	events.templateFieldsChange = function () {
		wp.customize('udb_login[template]', function (setting) {
			setting.bind(function (val) {

				var selected = document.querySelector('[data-control-name="udb_login[template]"] .is-selected img');
				var bgImage = selected ? selected.dataset.bgImage : '';

				if (bgImage) wp.customize('udb_login[bg_image]').set(bgImage);

				switch (val) {
					case 'left':
						wp.customize('udb_login[form_position]').set('left');
						break;

					case 'right':
						wp.customize('udb_login[form_position]').set('right');
						break;

					default:
						wp.customize('udb_login[form_position]').set('default');
				}

			});
		});
	}

	events.layoutFieldsChange = function () {
		wp.customize.section('udb_login_customizer_layout_section', function (section) {
			section.expanded.bind(function (isExpanded) {
				if (isExpanded) {

					if (wp.customize('udb_login[form_position]').get() === 'default') {
						wp.customize.control('udb_login[box_width]').deactivate();
						wp.customize.control('udb_login[form_border_width]').activate();
						wp.customize.control('udb_login[form_horizontal_padding]').activate();
						wp.customize.control('udb_login[form_border_color]').activate();
						wp.customize.control('udb_login[form_border_radius]').activate();
					} else {
						wp.customize.control('udb_login[box_width]').activate();
						wp.customize.control('udb_login[form_border_width]').deactivate();
						wp.customize.control('udb_login[form_horizontal_padding]').deactivate();
						wp.customize.control('udb_login[form_border_color]').deactivate();
						wp.customize.control('udb_login[form_border_radius]').deactivate();
					}

				}
			})
		});

		wp.customize('udb_login[form_position]', function (setting) {
			setting.bind(function (val) {
				
				if (val === 'default') {
					wp.customize.control('udb_login[box_width]').deactivate();
					wp.customize.control('udb_login[form_horizontal_padding]').activate();
					wp.customize.control('udb_login[form_border_width]').activate();
					wp.customize.control('udb_login[form_border_color]').activate();
					wp.customize.control('udb_login[form_border_radius]').activate();
				} else {
					wp.customize.control('udb_login[box_width]').activate();
					wp.customize.control('udb_login[form_horizontal_padding]').deactivate();
					wp.customize.control('udb_login[form_border_width]').deactivate();
					wp.customize.control('udb_login[form_border_color]').deactivate();
					wp.customize.control('udb_login[form_border_radius]').deactivate();
				}

			});
		});
	}
})(jQuery, wp.customize);