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

		$('.login-page #login-submit').on('click', function () {
            var btn = $(this);
            //btn.button('loading');
			page.login($('.login-page #uid').val(), $('.login-page #passwd').val()).done(function (data) {
				if(data != 'error') {
					window.location.href = './index.php';
				}
                //btn.button('reset');
			});	
		});

	});
	
})(jQuery);
