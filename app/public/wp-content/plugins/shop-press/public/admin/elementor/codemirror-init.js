/**
 * Initialize WordPress CodeMirror editor for ShopPress Custom CSS control.
 *
 * @package ShopPress
 * @since 1.2.0
 */
(function ($) {
	'use strict';

	var initializedEditors = {};

	// Wait for Elementor and WordPress CodeMirror to be ready.
	function initShopPressCodeEditor() {
		// Check if wp.codeEditor is available.
		if (typeof wp === 'undefined' || typeof wp.codeEditor === 'undefined') {
			return;
		}

		// Find all textareas with our control class that haven't been initialized.
		$('.sp-custom-css-field textarea').each(function () {
			var $textarea = $(this);
			var textareaId = $textarea.attr('id');

			// Skip if no ID or already initialized.
			if (!textareaId || initializedEditors[textareaId]) {
				return;
			}

			// Get editor settings from WordPress.
			var editorSettings = wp.codeEditor.defaultSettings ? wp.codeEditor.defaultSettings : {};
			var settings = $.extend({}, editorSettings, {
				codemirror: {
					type: 'text/css',
					indentUnit: 4,
					tabSize: 4,
					lineNumbers: true,
					lineWrapping: true,
					matchBrackets: true,
					autoCloseTags: true,
					autoCloseBrackets: true,
				},
			});

			// Initialize CodeMirror.
			try {
				var editor = wp.codeEditor.initialize(textareaId, settings);

				if (editor && editor.codemirror) {
					// Mark as initialized.
					initializedEditors[textareaId] = editor;

					// Sync changes back to textarea for Elementor.
					editor.codemirror.on('change', function () {
						var value = editor.codemirror.getValue();
						$textarea.val(value).trigger('input');
					});

					// Update CodeMirror when Elementor changes the value.
					$textarea.on('input change', function () {
						var currentValue = editor.codemirror.getValue();
						var textareaValue = $textarea.val();
						if (currentValue !== textareaValue) {
							editor.codemirror.setValue(textareaValue || '');
						}
					});
				}
			} catch (e) {
				console.warn('ShopPress: Failed to initialize CodeMirror for ' + textareaId, e);
			}
		});
	}

	// Initialize when DOM is ready.
	$(document).ready(function () {
		// Initial initialization.
		setTimeout(initShopPressCodeEditor, 500);

		// Initialize on Elementor panel ready.
		if (typeof elementor !== 'undefined') {
			elementor.hooks.addAction('panel/open_editor/widget', function () {
				setTimeout(initShopPressCodeEditor, 200);
			});

			// Also initialize when controls are rendered.
			elementor.hooks.addAction('panel/open_editor/control', function () {
				setTimeout(initShopPressCodeEditor, 200);
			});

			// Initialize when panel is opened.
			elementor.hooks.addAction('panel/open_editor', function () {
				setTimeout(initShopPressCodeEditor, 300);
			});
		}
	});
})(jQuery);
