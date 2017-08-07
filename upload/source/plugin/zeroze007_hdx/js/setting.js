yulegameJQ(function() {
	yulegameJQ("#quit-form").validate({
		rules : {

		},
		messages : {

		},
		submitHandler: function(form) {
			yulegameJQ(form).ajaxSubmit({
				dataType : 'json',
				beforeSubmit : function(formData, jqForm, options) {
					loading();
					
					var error = '';
					var form = jqForm[0];
/*					try {
						//yulegameJQ("input.rob-btn").attr("disabled", true);
					} catch (error) {
						unloading();
						yulegameJQ("div.response-msg").text(error.message);
						yulegameJQ("div.response-msg").addClass('error-msg').show();
						return false;
					}*/
				},
				success : ajaxSuccessHandler,
				error: ajaxErrorHandler
			});		
		},
		errorPlacement : function(error, element) {
			yulegameJQ("div.response-msg").html(error);
			yulegameJQ("div.response-msg").addClass('error-msg').show();
		}

	});
	
});