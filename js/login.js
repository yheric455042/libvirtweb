(function ($){
	var page = {
		modal : $('<div>').attr({class : 'login-page'}),
	};

	page.login = function (uid, password) {
		return $.ajax({
			type: 'POST',
			url: 'base.php',
            async: false,
			data: {
				'action': 'login',
				'params': {
					'uid': uid,
					'password': password
				}
			}
		});	

	};


	$(function () {

		$('.login-page #login-submit').click(function () {
			page.login($('.login-page #uid').val(), $('.login-page #passwd').val()).done(function (data) {
				if(data != 'error') {
					window.location.href = './index.php';
				}
			});	
		});

	});
	
})(jQuery);
