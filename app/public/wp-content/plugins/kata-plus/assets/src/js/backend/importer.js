/**
 * Footer Builder.
 *
 * @author  Webnus
 * @package	Kata Plus
 * @since	1.0.0
 */
'use strict';
(function ($) {
	/**
	 * Global variables.
	 *
	 * @since	1.0.0
	 */
	var plugins = [];
	var PLUGIN_AJAX_TIMEOUT_MS = 600000;

	function kataExtractHrefFromPluginResponse(raw, prefix) {
		if (!raw || typeof raw !== 'string') {
			return '';
		}
		var idx = raw.lastIndexOf(prefix);
		if (idx === -1) {
			return '';
		}
		return $.trim(raw.substr(idx + prefix.length));
	}

	function kataParsePluginAjaxError(raw) {
		if (!raw || typeof raw !== 'string') {
			return '';
		}
		var key = 'plugin_action_error:';
		var idx = raw.lastIndexOf(key);
		if (idx === -1) {
			return '';
		}
		var msg = $.trim(raw.substr(idx + key.length));
		try {
			msg = decodeURIComponent(msg.replace(/\+/g, ' '));
		} catch (err) {}
		return msg;
	}

	function kataBuildPluginActionAjaxPayload($btn) {
		var href = $btn.attr('href');
		var plugin_action = $btn.attr('data-plugin-action');
		if (!href || href === '#' || !plugin_action) {
			return null;
		}
		var parsed;
		try {
			parsed = new URL(href, window.location.href);
		} catch (err) {
			return null;
		}
		var plugin = parsed.searchParams.get('plugin');
		var nonce = parsed.searchParams.get('tgmpa-nonce');
		if (!plugin || !nonce) {
			return null;
		}
		var data = {
			action: 'kata_plus_plugin_actions',
			'plugin-action': plugin_action,
			plugin: plugin,
			'tgmpa-nonce': nonce,
		};
		data['tgmpa-' + plugin_action] = plugin_action + '-plugin';
		if (
			plugin_action === 'install' ||
			plugin_action === 'update'
		) {
			data.page = 'install-required-plugins';
		}
		return {
			payload: data,
			plugin_action: plugin_action,
		};
	}

	function kataPluginT(key, fallback) {
		try {
			if (
				typeof kata_install_plugins !== 'undefined' &&
				kata_install_plugins.translation &&
				kata_install_plugins.translation[key]
			) {
				return kata_install_plugins.translation[key];
			}
		} catch (err) {}
		return fallback || '';
	}

	function kataParsePluginHtmlError(raw) {
		if (!raw || typeof raw !== 'string') {
			return '';
		}

		var phpErrorMatch = raw.match(
			/(?:Fatal error|Parse error|Warning|Error):\s*([^\n<]+)/i
		);
		if (phpErrorMatch && phpErrorMatch[1]) {
			return $.trim(phpErrorMatch[1]);
		}

		var $doc = $('<div>').html(raw);
		var dieMessage = $.trim($doc.find('.wp-die-message').text());
		if (dieMessage) {
			return dieMessage;
		}

		return '';
	}

	function kataResolvePluginAjaxError(responseText, textStatus, httpStatus) {
		responseText =
			typeof responseText === 'string' ? responseText : '';

		var msg = kataParsePluginAjaxError(responseText);
		if (!msg) {
			msg = kataParsePluginHtmlError(responseText);
		}
		if (!msg && textStatus === 'timeout') {
			msg = kataPluginT('fail-plugin-timeout');
		}
		if (!msg && httpStatus >= 400) {
			msg = kataPluginT('plugin-error-title', 'Plugin action failed');
		}
		return msg || kataPluginT('plugin-error-title', 'Plugin action failed');
	}

	function kataGetPluginRow($btn) {
		return $btn.closest('.kata-required-plugin');
	}

	function kataGetPluginInstallBox($btn) {
		var $box = $btn
			.closest('.kata-lightbox')
			.find('.kata-install-plugins')
			.first();
		if (!$box.length) {
			$box = $btn
				.closest('.kata-col-import-demo')
				.find('.kata-install-plugins')
				.first();
		}
		return $box;
	}

	function kataClearPluginNotice($btn) {
		kataGetPluginInstallBox($btn).find('.kata-plugin-install-notice').remove();
		kataGetPluginRow($btn).removeClass('kata-plugin-has-error kata-plugin-processing');
	}

	function kataShowPluginNotice($btn, message) {
		if (!message) {
			return;
		}

		var $box = kataGetPluginInstallBox($btn);
		var $row = kataGetPluginRow($btn);
		var pluginName = $.trim($row.find('h4').first().text());

		kataClearPluginNotice($btn);

		var $notice = $(
			'<div class="kata-plugin-install-notice notice notice-error" role="alert"></div>'
		);

		if (pluginName) {
			$notice.append(
				$('<p class="kata-plugin-install-notice-plugin"></p>').html(
					'<strong>' + pluginName + '</strong>'
				)
			);
		}

		$notice
			.append(
				$('<p class="kata-plugin-install-notice-message"></p>').text(
					message
				)
			)
			.appendTo($box);

		$row.addClass('kata-plugin-has-error');
	}

	function kataSetPluginRowProcessing($btn, isProcessing) {
		var $row = kataGetPluginRow($btn);
		var $siblings = $row.siblings('.kata-required-plugin');

		$row.toggleClass('kata-plugin-processing', isProcessing);

		if (isProcessing) {
			$siblings.not('.active').css('opacity', '0.55');
			$btn.addClass('installing').css('cursor', 'default');
		} else {
			$siblings.css('opacity', '');
			$btn.removeClass('installing').css('cursor', 'pointer');
		}
	}

	var timer;
	var wishlist = {};

	lozad('.lozad', {
		load: function (el) {
			el.src = el.dataset.src;
			el.onload = function () {
				el.classList.add('kata-loaded');
			};
		},
	}).observe();

	/**
	 * constructor
	 *
	 * @since	1.0.0
	 */
	(function () {
		/**
		 * Create Wishlist
		 */
		if (!localStorage.getItem('importer-wishlist')) {
			localStorage.setItem('importer-wishlist', JSON.stringify(wishlist));
		}
	})();

	/**
	 * Categories.
	 *
	 * @since	1.0.0
	 */
	$('.demo-categories').find('select[name="demo-categories"]').niceSelect();

	$('.demo-categories')
		.find('select[name="demo-categories"]')
		.on('change', function () {
			$('#kata-importer-search-styles').remove();
			$(
				'<style id="kata-importer-search-styles">.kata-importer[website-type] {display:none;}.kata-importer[website-type*="' +
					this.value +
					'"]{display:inline-block;}</style>'
			).appendTo('head');
		});

	/**
	 * Tags.
	 *
	 * @since	1.0.0
	 */
	$('.kata-importer').each(function (index, element) {
		var $this = $(this),
			$wrap = $this.closest('.kata-importer-wrapper'),
			$tag = $this.attr('website-type'),
			all = $wrap.find('.kata-importer[website-type*="all"]').length,
			fast = $wrap.find('.kata-importer[website-type*="fast"]').length,
			free = $wrap.find('.kata-importer[website-type*="free"]').length,
			pro = $wrap.find(
				'.kata-importer[website-type*="pro"]:not([website-type*="fast"])'
			).length;
		if ($tag.indexOf('all') != -1) {
			$wrap.find('.demotypeitem[value="all"]').find('span').text(all);
		}
		if ($tag.indexOf('fast') != -1) {
			$wrap.find('.demotypeitem[value="fast"]').find('span').text(fast);
		}
		if ($tag.indexOf('free') != -1) {
			$wrap.find('.demotypeitem[value="free"]').find('span').text(free);
		}
		if ($tag.indexOf('pro') != -1) {
			$wrap.find('.demotypeitem[value="pro"]').find('span').text(pro);
		}
	});
	$('.kata-demotypes')
		.find('.demotypeitem')
		.on('click', function () {
			var $this = $(this),
				value = $this.attr('value');
			$this.addClass('active').siblings().removeClass('active');
			$('#kata-importer-search-styles').remove();
			$(
				'<style id="kata-importer-search-styles">.kata-importer[website-type] {display:none;}.kata-importer[website-type*="' +
					value +
					'"]{display:inline-block;}</style>'
			).appendTo('head');
		});

	/**
	 * Search.
	 *
	 * @since	1.0.0
	 */
	$('.kata-demo-importer-search-box')
		.find('input[type="text"]')
		.on('input', function (el) {
			clearTimeout(timer); //clear any running timeout on key up
			timer = setTimeout(function () {
				var searchValue = jQuery(el.target).val().toLowerCase();
				$('#kata-importer-search-styles').remove();
				if (searchValue) {
					$(
						'<style id="kata-importer-search-styles">.kata-importer[demo-name] {display:none;}.kata-importer[demo-name*="' +
							searchValue +
							'"]{display:inline-block;}</style>'
					).appendTo('head');
				}
			}, 250);
		});

	/**
	 * Wishlist.
	 *
	 * @since	1.0.0
	 */
	$.each(
		JSON.parse(localStorage.getItem('importer-wishlist')),
		function (key, val) {
			if (val) {
				$('.kata-importer[demo-name="' + key + '"]').attr(
					'data-wishlist',
					true
				);
			}
		}
	);
	$('.kata-demo-importer-wish-list')
		.find('.kata-icon')
		.on('click', function (el) {
			var $this = $(this),
				$wrap = $this.closest('.kata-demo-importer-wish-list');
			if ($this.attr('data-show') == 'false') {
				$this.attr('data-show', true);
				$wrap.attr('data-show', true);
				$('#kata-importer-search-styles').remove();
				$(
					'<style id="kata-importer-search-styles">.kata-importer[data-wishlist="false"] {display:none;}.kata-importer[data-wishlist="true"]{display:inline-block;}</style>'
				).appendTo('head');
			} else {
				$this.attr('data-show', false);
				$wrap.attr('data-show', false);
				$('#kata-importer-search-styles').remove();
			}
		});

	$('.kata-demo-addto-wishlist')
		.find('.kata-icon')
		.on('click', function (el) {
			var $this = $(this),
				$wrap = $this.closest('.kata-importer'),
				item = $wrap.attr('demo-name');

			wishlist = JSON.parse(localStorage.getItem('importer-wishlist'));

			if (!wishlist[item]) {
				wishlist[item] = true;
				$wrap.attr('data-wishlist', true);
			} else {
				wishlist[item] = false;
				$wrap.attr('data-wishlist', false);
			}
			localStorage.setItem('importer-wishlist', JSON.stringify(wishlist));
		});

	/**
	 * Modal nicescroll.
	 *
	 * @since	1.0.0
	 */
	function ModalNice() {
		$('.kata-lightbox-content').niceScroll({
			cursorcolor: '#aaa', // change cursor color in hex
			cursoropacitymin: 0, // change opacity when cursor is inactive (scrollabar "hidden" state), range from 1 to 0
			cursoropacitymax: 1, // change opacity when cursor is active (scrollabar "visible" state), range from 1 to 0
			cursorwidth: '7px', // cursor width in pixel (you can also write "5px")
			cursorborder: 'none', // css definition for cursor border
			cursorborderradius: '5px', // border radius in pixel for cursor
			scrollspeed: 60, // scrolling speed
			mousescrollstep: 40, // scrolling speed with mouse wheel (pixel)
			hwacceleration: true, // use hardware accelerated scroll when supported
			gesturezoom: true, // (only when boxzoom=true and with touch devices) zoom activated when pinch out/in on box
			grabcursorenabled: true, // (only when touchbehavior=true) display "grab" icon
			autohidemode: true, // how hide the scrollbar works, possible values:
			spacebarenabled: true, // enable page down scrolling when space bar has pressed
			railpadding: {
				top: 0,
				right: 1,
				left: 0,
				bottom: 1,
			}, // set padding for rail bar
			disableoutline: true, // for chrome browser, disable outline (orange highlight) when selecting a div with nicescroll
			horizrailenabled: false, // nicescroll can manage horizontal scroll
			railalign: 'right', // alignment of vertical rail
			railvalign: 'bottom', // alignment of horizontal rail
			enablemousewheel: true, // nicescroll can manage mouse wheel events
			enablekeyboard: true, // nicescroll can manage keyboard events
			smoothscroll: true, // scroll with ease movement
			cursordragspeed: 0.3, // speed of selection when dragged with cursor
		});
	}

	/**
	 * Install plugins (TGMPA via admin-ajax). Long timeout supports heavy packages (e.g. Revolution Slider).
	 *
	 * @since	1.0.0
	 */
	function KataPlusInstallPlugin($install_plugin_btn, install_multiple) {
		if ($install_plugin_btn.hasClass('installing')) {
			return;
		}

		var parsed = kataBuildPluginActionAjaxPayload($install_plugin_btn);
		if (!parsed) {
			kataShowPluginNotice(
				$install_plugin_btn,
				kataPluginT('plugin-error-title', 'Plugin action failed')
			);
			return;
		}

		var plugin_action = parsed.plugin_action;
		var data = parsed.payload;
		var active_status = plugin_action;

		kataClearPluginNotice($install_plugin_btn);
		kataSetPluginRowProcessing($install_plugin_btn, true);

		function advanceMultiQueue() {
			if (!install_multiple) {
				return;
			}
			plugins.shift();
			if (plugins.length > 0) {
				KataPlusInstallPlugin($(plugins[0].item), true);
				$('.kata-btn-install-plugins').removeAttr('style');
			} else {
				StepManager();
			}
		}

		function kataHandlePluginResponse(responseText, httpStatus, textStatus) {
			responseText =
				typeof responseText === 'string' ? responseText : '';

			if (httpStatus >= 400 || textStatus === 'timeout') {
				kataShowPluginNotice(
					$install_plugin_btn,
					kataResolvePluginAjaxError(
						responseText,
						textStatus,
						httpStatus
					)
				);
				kataSetPluginRowProcessing($install_plugin_btn, false);
				return true;
			}

			var serverErr = kataParsePluginAjaxError(responseText);
			if (
				!serverErr &&
				responseText.indexOf('activate_href:') === -1 &&
				responseText.indexOf('deactivate_href:') === -1
			) {
				serverErr = kataParsePluginHtmlError(responseText);
			}
			if (serverErr) {
				kataShowPluginNotice($install_plugin_btn, serverErr);
				kataSetPluginRowProcessing($install_plugin_btn, false);
				return true;
			}

			return false;
		}

		$.ajax({
			type: 'GET',
			url: ajaxurl,
			data: data,
			dataType: 'text',
			timeout: PLUGIN_AJAX_TIMEOUT_MS,
			success: function (responseText, textStatus, jqXHR) {
				var httpStatus =
					jqXHR && jqXHR.status ? jqXHR.status : 200;

				if (
					kataHandlePluginResponse(
						responseText,
						httpStatus,
						textStatus
					)
				) {
					return;
				}

				var nextHref = '';

				if (
					active_status === 'install' ||
					active_status === 'update'
				) {
					nextHref = kataExtractHrefFromPluginResponse(
						responseText,
						'activate_href:'
					);
					if (!nextHref) {
						kataShowPluginNotice(
							$install_plugin_btn,
							kataResolvePluginAjaxError(
								responseText,
								textStatus,
								httpStatus
							)
						);
						kataSetPluginRowProcessing($install_plugin_btn, false);
						return;
					}

					$install_plugin_btn
						.attr('data-plugin-action', 'activate')
						.attr('href', nextHref)
						.text(kataPluginT('activate', 'Activate'));

					kataSetPluginRowProcessing($install_plugin_btn, false);
					KataPlusInstallPlugin($install_plugin_btn, install_multiple);
					return;
				}

				if (active_status === 'deactivate') {
					nextHref = kataExtractHrefFromPluginResponse(
						responseText,
						'activate_href:'
					);
					if (nextHref) {
						$install_plugin_btn
							.attr('data-plugin-action', 'activate')
							.attr('href', nextHref)
							.text(kataPluginT('activate', 'Activate'));
					}
					kataGetPluginRow($install_plugin_btn).removeClass('active');
					kataSetPluginRowProcessing($install_plugin_btn, false);
					StepManager();
					advanceMultiQueue();
					return;
				}

				if (active_status === 'activate') {
					nextHref = kataExtractHrefFromPluginResponse(
						responseText,
						'deactivate_href:'
					);
					if (!nextHref) {
						kataShowPluginNotice(
							$install_plugin_btn,
							kataResolvePluginAjaxError(
								responseText,
								textStatus,
								httpStatus
							)
						);
						kataSetPluginRowProcessing($install_plugin_btn, false);
						return;
					}

					$install_plugin_btn
						.attr('data-plugin-action', 'deactivate')
						.attr('href', nextHref)
						.text(kataPluginT('deactivate', 'Deactivate'));
					kataGetPluginRow($install_plugin_btn).addClass('active');
					kataClearPluginNotice($install_plugin_btn);
					kataSetPluginRowProcessing($install_plugin_btn, false);
					StepManager();
					advanceMultiQueue();
				}
			},
			error: function (jqXHR, textStatus) {
				kataHandlePluginResponse(
					jqXHR && jqXHR.responseText ? jqXHR.responseText : '',
					jqXHR && jqXHR.status ? jqXHR.status : 500,
					textStatus
				);
			},
		});
	}

	/**
	 * Install plugin bulk.
	 *
	 * @since	1.0.0
	 * @event	click
	 */
	function KataPlusInstallPluginBulk() {
		$('.kata-btn-install-plugins').on('click', function (e) {
			e.preventDefault();
			var $this = $(this),
				$required_plugins = $this
					.parent()
					.next('.kata-required-plugins');

			plugins.length = 0;

			$required_plugins
				.find('.kata-required-plugin:not(:hidden)')
				.each(function () {
					var $this = $(this);
					var $plugin_action_btn = $this.find(
						'.kata-btn-plugin-action'
					);
					var plugin_href = $plugin_action_btn.attr('href');
					var plugin_action =
						$plugin_action_btn.data('plugin-action');

					if (
						plugin_href != undefined &&
						plugin_href != '#' &&
						plugin_action != 'deactivate'
					) {
						plugins.push({
							item: $plugin_action_btn[0],
							href: plugin_href,
							plugin_action: plugin_action,
						});
					}
				});

			if (!plugins.length) {
				$this.css({
					background: '#e6e7e8',
					'border-color': '#e6e7e8',
					color: '#a1a2a3',
					'box-shadow': 'none',
					cursor: 'default',
				});
			} else {
				KataPlusInstallPlugin($(plugins[0]['item']), true);
			}
		});
	}

	/**
	 * Step Manager.
	 *
	 * @since	1.0.0
	 * @event	click
	 */
	function StepManager() {
		setTimeout(function () {
			var $plugins = $(
				'.kata-lightbox-wrapper.active-modal .kata-required-plugin'
			);
			if (
				$plugins.length &&
				!$plugins.filter(':not(.active)').length
			) {
				$('.kata-btn.kata-btn-install-plugins').css({
					'pointer-events': 'none',
					background: '#e6e7e8',
					'border-color': '#e6e7e8',
					color: '#a1a2a3',
					'box-shadow': 'none',
					cursor: 'default',
				});
				$('.kt-importer-step[data-step="1"]').trigger('click');
				var currnet_location =
					window.location.search.indexOf('kata-plus-fast-mode') >= 0
						? 'fastmode'
						: 'importer';
				if (currnet_location != 'fastmode') {
					$('.kata-lightbox-content').attr('tabindex', 1);
					$('.kata-lightbox-content').attr(
						'style',
						'transform: translateX(-740px)'
					);
				}
			} else {
				$('.kata-btn-install-plugins').removeAttr('style');
			}
		}, 500);
		$('.resume-import-progress').on('click', function (e) {
			e.preventDefault();
			var $wrap = $(this).closest('.initial-notice');
			var eContainer = $(this).hasClass('activation-e-con');
			if (
				$wrap
					.find('.importer-wraning')
					.find('input[type="checkbox"]')
					.is(':checked')
			) {
				$wrap.fadeOut();

				if (eContainer) {
					$.ajax({
						url: importer_localize.ajax.url,
						type: 'POST',
						data: {
							action: 'activate_elementor_container',
							nonce: importer_localize.ajax.nonce,
						},
						success: function (data) {},
						error: function (data) {},
					});
				}
			}
		});
		$(document).on(
			'click',
			'.importer-wraning [for="warning-1"]',
			function () {
				var $this = $(this),
					$wrap = $this.closest('.kata-checkbox-wrap');
				$wrap.find('input[type="checkbox"]').trigger('click');
			}
		);
	}

	var importedContents = {};
	/**
	 * Select Data.
	 *
	 * @since	1.0.0
	 * @event	click
	 */
	$(document).ajaxComplete(function (event, xhr, options) {
		if (options.name == 'ImporterBuildSteps') {
			$('.kata-checkbox-wrap input[type="checkbox"]').on(
				'click',
				function () {
					var $this = $(this),
						$wrap = $this.closest('.kata-col-import-demo'),
						checks = $wrap
							.find('.kata-checkbox-wrap')
							.siblings()
							.find('input[type="checkbox"]')
							.prop('checked');

					if ($this.prop('checked') || checks) {
						$wrap
							.find('.kata-import-demo-btn')
							.removeClass('disabled');
					} else {
						$wrap
							.find('.kata-import-demo-btn')
							.addClass('disabled');
					}
					if ($this.attr('id') == 'all') {
						if ($this.prop('checked') == true) {
							$(
								'.kata-checkbox-wrap input[type="checkbox"]:not(#all)'
							).prop('checked', true);
						} else if ($this.prop('checked') == false) {
							$(
								'.kata-checkbox-wrap input[type="checkbox"]:not(#all)'
							).prop('checked', false);
						}
					}
				}
			);
			// Import Request
			$('.kata-import-demo-btn').on('click', function (e) {
				var $this = $(this),
					$wrap = $this.closest('.kata-col-import-demo'),
					$parent = $wrap.closest('.kata-lightbox-content'),
					$tasks = $parent.find('.kata-importer-tasks'),
					$key = $this.data('key'),
					$name = $this.data('name'),
					$screenshot = $this.data('screenshot'),
					demo_data = [];

				e.preventDefault();
				$parent
					.find('.kata-import-content-wrap')
					.find('.kata-checkbox-input')
					.attr('disabled', 'disabled');
				$wrap.find('.kata-import-demo-btn').addClass('disabled');
				$wrap.find('.kata-import-demo-btn').text('Downloading Content');
				var current_i = $wrap
					.find('input[type="checkbox"][data-type]:checked')
					.first()
					.data('type');
				$.each(
					$wrap.find('input[type="checkbox"][data-type]:checked'),
					function () {
						demo_data.push($(this).data('type'));
					}
				);

				var currnet_location =
					window.location.search.indexOf('kata-plus-fast-mode') >= 0
						? 'fastmode'
						: 'importer';

				$.ajax({
					url: importer_localize.ajax.url,
					type: 'POST',
					data: {
						action: 'BuildImporter',
						demo_data: demo_data,
						key: $key,
						name: $name,
						screenshot: $screenshot,
						nonce: importer_localize.ajax.nonce,
						currnet_location: currnet_location,
					},
					success: function (data) {
						$('li.kt-importer-step.kt-last-step').trigger('click');
						$('li.kt-importer-step[data-step="1"]').addClass(
							'inactive'
						);
						$('li.kt-importer-step[data-step="0"]').addClass(
							'inactive'
						);

						$('.kata-checkbox-wrap').remove();
						$('.kata-required-plugin').remove();
						$(data).appendTo($tasks.find('.tasks'));
						importedContents['key'] = $key;
						start_import($key, demo_data, current_i);
					},
					error: function (data) {
						if (typeof data.responseText !== 'undefined') {
							jQuery('.steps').html(data.responseText);
						}
					},
				});
			});
		}
	});

	var mediaTry = 1;
	var before_action = '';

	function kataSetImporterWizardStep($context, step) {
		var $steps = $context.find(
			'.kata-import-wizard-header .kt-importer-step'
		);
		$steps.removeClass('kt-active-step inactive');
		$steps.filter('[data-step="' + step + '"]').addClass('kt-active-step');
		if (2 === step) {
			$steps.filter('[data-step="0"], [data-step="1"]').addClass('inactive');
		}
	}

	function start_import(key, import_items, current) {
		if (!import_items || typeof current == 'undefined') {
			ImportDone();
			return;
		}
		$('.kata-importer-task-menus').removeClass('kata-import-active');
		$('.kata-importer-task-menus[data-action="' + current + '"]').addClass(
			'kata-import-active'
		);
		var demo = $('.kata-import-demo-title').text();
		$.ajax({
			url: importer_localize.ajax.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'Importer',
				key: key,
				import_item: current,
				demo: demo.toLowerCase(),
				mediaTry: mediaTry,
				nonce: importer_localize.ajax.nonce,
			},
			success: function (response, data) {
				if (typeof response.status == 'undefined') {
					response.status = '';
				}

				if (response.status == 'newAJAX') {
					$('.kata-importer-tasks')
						.find('li[data-action="' + current + '"]')
						.html(
							'<span class="message info please-wait">Please Wait</span>' +
								$('.kata-importer-tasks')
									.find('li[data-action="' + current + '"]')
									.html()
						);
					mediaTry++;
					jQuery('.kata-col-import-demo .meter span')
						.css('width', 100 / (import_items.length + 1) + '%')
						.trigger('size-change');
					start_import(key, import_items, import_items[0]);
				} else {
					import_items.shift();
					jQuery('.kata-col-import-demo .meter span')
						.css('width', 100 / (import_items.length + 1) + '%')
						.trigger('size-change');
					if (response.status == 'success') {
						importedContents[current] = true;
						$('.kata-importer-tasks')
							.find('li[data-action="' + current + '"]')
							.addClass('kata-import-done');
					} else {
						importedContents[current] = response.message;
						$('.kata-importer-tasks')
							.find('li[data-action="' + current + '"]')
							.addClass('kata-import-error');
						if (typeof response.message != 'undefined') {
							$('.kata-importer-tasks')
								.find('li[data-action="' + current + '"]')
								.html(
									'<span class="message">' +
										response.message +
										'</span>' +
										$('.kata-importer-tasks')
											.find(
												'li[data-action="' +
													current +
													'"]'
											)
											.html()
								);
						}
					}
					if (import_items) {
						start_import(key, import_items, import_items[0]);
					}
				}
			},
			error: function (response) {
				if (response.status == 'newAJAX') {
					setTimeout(function () {
						start_import(key, import_items, current);
					}, 25);
				}
			},
		});
	}

	function ImportDone() {
		var demo = $('.kata-import-demo-title').text(),
			demo_url = $('.demo_url').val();
		$.ajax({
			url: importer_localize.ajax.url,
			type: 'POST',
			data: {
				action: 'ImportDone',
				key: importedContents['key'],
				demo_url: demo_url,
				reports: importedContents,
				demo: demo.toLowerCase(),
				nonce: importer_localize.ajax.nonce,
			},
			success: function (response) {
				$('#kata-importer-search-styles').remove();
				jQuery('.kata-lightbox-wrapper .ti-close')
					.first()
					.trigger('click');
				var $modal = jQuery(
					'.kata-importer .kata-lightbox-wrapper'
				).first();
				var screenshot = $('.kata-col-import-demo-image')
					.closest('.kata-importer')
					.attr('demo-screenshot');
				$('.kata-col-import-demo-image')
					.find('img')
					.attr('src', screenshot);
				$modal.fadeIn().addClass('active-modal').html(response);
				kataSetImporterWizardStep($modal, 2);
				if ($('.kata-lightbox-wrapper').hasClass('active-modal')) {
					$('.kata-lightbox-wrapper').addClass('done');
				}
				$('.kata-lightbox-wrapper')
					.find('.ti-close')
					.on('click', function () {
						var $this = $(this),
							$wrap = $this.closest('.kata-lightbox-wrapper');
						$wrap.fadeOut().removeClass('active-modal').html('');
						$('.kata-lightbox-wrapper').removeClass('done');
					});
				RedirectAfterImportDone();
			},
		});
	}

	/**
	 * Importer Steps.
	 *
	 * @since	1.0.0
	 * @event	click
	 */
	function ImporterSteps() {
		StepManager();
		$('.kata-lightbox')
			.find('.kt-importer-step')
			.on('click', function () {
				var $this = $(this),
					$wrap = $this.closest('.kata-lightbox'),
					$modal = $this.closest('.kata-lightbox-wrapper');

				if ($modal.hasClass('done')) {
					return;
				}

				var step = $this.data('step'),
					width = $wrap.innerWidth();
				$this
					.addClass('kt-active-step')
					.siblings()
					.removeClass('kt-active-step');

				var currnet_location =
					window.location.search.indexOf('kata-plus-fast-mode') >= 0
						? 'fastmode'
						: 'importer';
				if (currnet_location != 'fastmode') {
					$('.kata-lightbox-content').attr('tabindex', step);
					$wrap
						.find('.kata-lightbox-content')
						.attr(
							'style',
							'transform: translateX(-' + width * step + 'px)'
						);
				}
			});
	}

	/**
	 * Redirect After Import Done.
	 *
	 * @since	1.0.0
	 */
	function RedirectAfterImportDone() {
		$('.kata-lightbox-wrapper')
			.find('.ti-close')
			.on('click', function () {
				window.location.replace(window.location.href);
			});
	}

	/**
	 * Importer modal.
	 *
	 * @since	1.0.0
	 */
	$('.kata-importer')
		.find('.kata-btn-importer')
		.on('click', function () {
			var $this = $(this),
				$wrap = $this.closest('.kata-importer'),
				$demo_url = $wrap.find('.kata-importer-preview').attr('href'),
				$modal = $wrap.find('.kata-lightbox-wrapper'),
				$screenshot = $wrap.attr('demo-screenshot'),
				$name = $wrap.attr('demo-name'),
				$e_con = $wrap.attr('data-econ'),
				currnet_location =
					window.location.search.indexOf('kata-plus-fast-mode') >= 0
						? 'fastmode'
						: 'importer',
				$key = $wrap.attr('data-key');

			$this.addClass('requested');
			$.ajax({
				url: importer_localize.ajax.url,
				name: 'ImporterBuildSteps',
				type: 'POST',
				data: {
					action: 'ImporterBuildSteps',
					key: $key,
					demo_url: $demo_url,
					screenshot: $screenshot,
					name: $name,
					e_con: $e_con,
					nonce: importer_localize.ajax.nonce,
					currnet_location: currnet_location,
				},
				success: function (data) {
					$this.removeClass('requested');
					$modal.fadeIn().addClass('active-modal').html(data);
					ImporterSteps();
					KataPlusInstallPluginBulk();
					ModalNice();
					$('.kata-lightbox-wrapper')
						.find('.ti-close')
						.on('click', function () {
							var $this = $(this),
								$wrap = $this.closest('.kata-lightbox-wrapper');
							$wrap
								.fadeOut()
								.removeClass('active-modal')
								.html('');
						});
					$('.kata-btn-plugin-action').on('click', function (event) {
						event.preventDefault();
						KataPlusInstallPlugin($(this), false);
					});

					/**
					 * Reset Demo
					 */
					$('.kata-import-demo-reset').on('click', function () {
						if (confirm(importer_localize.ajax.reset_message)) {
							$('.kata-import-demo-reset')
								.find('.dashicons-update-alt')
								.css('opacity', '1');
							$.ajax({
								url: importer_localize.ajax.url,
								name: 'reset_site',
								type: 'POST',
								data: {
									action: 'reset_site',
									reset: 'yes',
									nonce: importer_localize.ajax.nonce,
								},
								success: function (data, response) {
									$('.kata-import-demo-reset')
										.find('.dashicons-update-alt')
										.css('opacity', '0');
									if (data.status == 'not_allowed') {
										alert(data.message);
									} else {
										alert(data.message);
									}
								},
							});
						}
					});
				},
				error: function () {},
			});
		});
})(jQuery);
