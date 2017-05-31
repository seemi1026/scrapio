(function($) {
	// Toggle form type
	$('input[type=radio][name=options]').change(function() {
		if (this.value == 'review') {
			$('#group-review-url').hide();
			$('#input-review-url').removeClass('send').val('');
			$('#group-profile-url').show();
			$('#group-asin').show();
			$('#input-profile-url').addClass('send');
			$('#input-asin').addClass('send');
			$('#group-get-profile').hide();
			$('#group-get-profile input[type=checkbox]').prop('checked', false);
		}
		else if (this.value == 'profile') {
			$('#group-profile-url').hide();
			$('#input-profile-url').removeClass('send').val('');
			$('#group-asin').hide();
			$('#input-asin').removeClass('send').val('');
			$('#group-review-url').show();
			$('#group-get-profile').show();
			$('#input-review-url').addClass('send');
		}
	});

	// Handle submit
	$('#form-tester').submit(function(event) {
		event.preventDefault();
		var formData = $(this).find('input.send').serialize();

		$.ajax({
			type: 'GET',
			url: ajaxurl,
			data: formData,
			beforeSend: function() { 
				$("#response").html('Requesting...');
				$("#button-submit").prop('disabled', true);
			},
			success: function(response) {
				$('#response').html(JSON.stringify(response, undefined, 4));
				$("#button-submit").prop('disabled', false);
			}
		});
	})
})(jQuery);