(function ($) {
	/**
	 * Kata Plus counter: show ending number first, animate from start value on scroll.
	 *
	 * @param {jQuery} $scope Widget wrapper.
	 */
	var KataPlusCounterHandler = function ($scope) {
		var $counterNumber = $scope.find('.elementor-counter-number');

		if (!$counterNumber.length || $counterNumber.data('kataCounterInit')) {
			return;
		}

		if (!$.fn.numerator) {
			return;
		}

		$counterNumber.data('kataCounterInit', true);

		var runAnimation = function () {
			var data = $counterNumber.data(),
				fromValue = parseFloat(data.fromValue),
				toValue = parseFloat(data.toValue);

			if (isNaN(fromValue) || isNaN(toValue) || fromValue === toValue) {
				return;
			}

			var decimalMatch = toValue.toString().match(/\.(.*)/);

			if (decimalMatch) {
				data.rounding = decimalMatch[1].length;
			}

			data.duration = data.duration || 2000;
			data.fromValue = fromValue;
			data.toValue = toValue;

			$counterNumber.numerator(data);
		};

		if (
			typeof elementorModules !== 'undefined' &&
			elementorModules.utils &&
			elementorModules.utils.Scroll &&
			elementorModules.utils.Scroll.scrollObserver
		) {
			var observer = elementorModules.utils.Scroll.scrollObserver({
				callback: function (event) {
					if (event.isInViewport) {
						observer.unobserve($counterNumber[0]);
						runAnimation();
					}
				},
			});

			observer.observe($counterNumber[0]);
		} else if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							io.unobserve(entry.target);
							runAnimation();
						}
					});
				},
				{ threshold: 0.25 }
			);

			io.observe($counterNumber[0]);
		} else {
			runAnimation();
		}
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/kata-plus-counter.default',
			KataPlusCounterHandler
		);
	});
})(jQuery);
