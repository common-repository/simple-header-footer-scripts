jQuery(function ($) {

	"use strict";

	var shfDismissNotice = {

		init: function () {

			var welcomeNotice = $('.shf-notice-welcome');

			welcomeNotice.on('click', '.notice-dismiss', function (e) {
				e.preventDefault();

				var data = {
					action: 'shf_dismiss_welcome_notice',
					nonce: shfAdminConfig.nonce
				};

				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: data,
					success: function (response) {
						welcomeNotice.fadeOut();
					}
				});

			});
		},

	}

	// Init.
	shfDismissNotice.init();

});