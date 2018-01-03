jQuery(document).ready(function($){
	$('#submit-mpr').on('click', function(event){
		event.preventDefault();

		$('.success-message-wrapper').empty();

		// Calling Ajax function mpr_reset_all_pass in multipass-reset-admin.php file
		$.ajax({
			url: ajaxurl,
			beforeSend: function(){
				$('#mprpreloader').show();
			},
			type: 'POST',
			data: {action: 'mpr_reset_all_pass'},
		})

		.done(function(msg) {
			setTimeout(function(){
				$('#mprpreloader').hide();
				$('.success-message-wrapper').html(msg);
			}, 600);
		})

		.fail(function(a,b,c) {
			console.log(a+b+c);
		})

		.always(function() {
		
		});
	});
});
