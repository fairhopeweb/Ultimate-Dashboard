(function ($) {
	var buttonsWrapper = document.querySelector(
		".onboarding-heatbox .heatbox-footer"
	);
	var skipButton = document.querySelector(".onboarding-heatbox .skip-button");
	var saveButton = document.querySelector(".onboarding-heatbox .save-button");
	var subscribeButton = document.querySelector(
		".onboarding-heatbox .subscribe-button"
	);
	var slideIndexes = ["modules", "subscription", "finished"];
	var currentSlide = "modules";
	var doingAjax = false;
	var slider;

	function init() {
		if (!buttonsWrapper || !skipButton || !saveButton || !subscribeButton) {
			return;
		}

		setupSlider();
	}

	function setupSlider() {
		slider = tns({
			container: ".udb-onboarding-slides",
			items: 1,
			loop: false,
			autoHeight: true,
			controls: false,
			navPosition: "bottom",
			onInit: onSliderInit,
		});
	}

	function onSliderInit(instance) {
		slider.events.on("indexChanged", onSliderIndexChanged);

		var dotsWrapper = document.querySelector(".onboarding-heatbox .udb-dots");

		if (dotsWrapper) {
			dotsWrapper.appendChild(instance.navContainer);
		}

		skipButton.addEventListener("click", onSkipButtonClick);
		saveButton.addEventListener("click", onSaveButtonClick);
		subscribeButton.addEventListener("click", onSubscribeButtonClick);
	}

	function onSliderIndexChanged(e) {
		currentSlide = slideIndexes[e.index];

		if (currentSlide === "modules") {
			onModulesSlideSelected();
		} else if (currentSlide === "subscription") {
			onSubscriptionSlideSelected();
		} else if (currentSlide === "finished") {
			onFinishedSlideSelected();
		}
	}

	function onModulesSlideSelected() {
		buttonsWrapper.classList.remove("is-hidden");
		skipButton.textContent = "Skip";
		saveButton.textContent = "Done";
		saveButton.classList.remove("is-invisible");
	}

	function onSubscriptionSlideSelected() {
		buttonsWrapper.classList.remove("is-hidden");
		skipButton.textContent = "Go to Dashboard";
		saveButton.classList.add("is-invisible");
	}

	function onFinishedSlideSelected() {
		buttonsWrapper.classList.add("is-hidden");
	}

	function onSkipButtonClick(e) {
		switch (currentSlide) {
			case "modules":
				// Go to next slide.
				slider.goTo("next");
				break;

			case "subscription":
				// Go to dashboard.
				window.location.href = udbPluginOnboarding.adminUrl;
				break;

			default:
				break;
		}
	}

	function onSaveButtonClick(e) {
		if (doingAjax) return;
		startLoading(saveButton);

		$.ajax({
			url: udbPluginOnboarding.ajaxUrl,
			type: "POST",
			data: {
				action: "udb_plugin_onboarding_save_modules",
				nonce: udbPluginOnboarding.nonces.saveModules,
				modules: getSelectedModules(),
			},
		})
			.done(function (r) {
				if (!r.success) return;
				slider.goTo("next");
			})
			.fail(onAjaxFail)
			.always(function () {
				stopLoading(saveButton);
			});
	}

	function onSubscribeButtonClick(e) {
		var nameField = document.querySelector("#udb-subscription-name");
		var emailField = document.querySelector("#udb-subscription-email");
		if (doingAjax || !nameField || !emailField) return;

		startLoading(subscribeButton);

		$.ajax({
			url: udbPluginOnboarding.ajaxUrl,
			type: "POST",
			data: {
				action: "udb_plugin_onboarding_subscribe",
				nonce: udbPluginOnboarding.nonces.subscribe,
				name: nameField.value,
				email: emailField.value,
			},
		})
			.done(function (r) {
				if (!r.success) return;
				slider.goTo("next");
			})
			.fail(onAjaxFail)
			.always(function () {
				stopLoading(subscribeButton);
			});
	}

	function onAjaxFail(jqXHR) {
		var errorMesssage = "Something went wrong";

		if (jqXHR.responseJSON && jqXHR.responseJSON.data) {
			errorMesssage = jqXHR.responseJSON.data;
		}

		alert(errorMesssage);
	}

	function startLoading(button) {
		doingAjax = true;
		button.classList.add("is-loading");
	}

	function stopLoading(button) {
		button.classList.remove("is-loading");
		doingAjax = false;
	}

	function getSelectedModules() {
		var checkboxes = document.querySelectorAll(
			'.udb-modules-slide .module-toggle input[type="checkbox"]'
		);
		if (!checkboxes.length) return [];

		var modules = [];

		[].slice.call(checkboxes).forEach(function (checkbox) {
			var module = checkbox.id.replace("udb_modules__", "");

			if (checkbox.checked) {
				modules.push(module);
			}
		});

		return modules;
	}

	init();
})(jQuery);
