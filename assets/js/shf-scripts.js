jQuery(function ($) {

	"use strict";

	var shfScripts = {

		init: function () {
			var $this = this;

			$this.submit_form();

			if (wp && wp.codeEditor) {
				$this.code_editor();
			}

		},

		code_editor: function () {

			// CodeMirror Config.
			var settings = {
				'codemirror': {
					lineNumbers: true,
					matchBrackets: true,
					mode: "htmlmixed",
					indentUnit: 4,
					indentWithTabs: true,
					lineWrapping: true,
					autoRefresh: true,
					autoCloseBrackets: true,
					autoCloseTags: true,
					value: document.documentElement.innerHTML,
				}
			};

			// Initialize code mirror.
			if (shfActiveHooks.length > 0) {
				for (var hook = 0; hook < shfActiveHooks.length; hook++) {
					var hookId = shfActiveHooks[hook],
						editorId = document.getElementById('shf-' + hookId);

					// Init wp code editor.
					wp.codeEditor.initialize(editorId, settings);

				}
			}

		},

		submit_form: function () {
			var saveSettings = $('#shf-save-settings'),
				formSaveSettings = $('#shf-form-submit');

			saveSettings.on('click', function (e) {
				e.preventDefault();
				formSaveSettings.submit();
			});
		},

	}

	// Init.
	shfScripts.init();

});