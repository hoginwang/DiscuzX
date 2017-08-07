yulegameJQ(function() {
	
	yulegameJQ(".rob-jail-link, .bail-link").click(function(){
		try {
			var crimUid = yulegameJQ(this).attr('item');
			if (isNaN(parseInt(crimUid))) {
				throw new Error("Invalid item value!");
			}
			
			yulegameJQ("#crim-uid").val(crimUid);
			
			yulegameJQ("#action").val(yulegameJQ(this).attr('action'));
			yulegameJQ("#jail-form").submit();
			
			
		} catch(e) {
			alert(hdxLang.jsError+ e.message);
		}
	});
	
	
	yulegameJQ(".escape-btn").click(function(event){
		try {
			event.preventDefault();
			yulegameJQ("#action").val(yulegameJQ(this).attr('action'));
			yulegameJQ("#jail-form").submit();
			
			
		} catch(e) {
			alert(hdxLang.jsError+ e.message);
		}		
	});
	
	
	yulegameJQ("#jail-form").validate({
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
					/*try {
						var pickType = parseInt(yulegameJQ('#gift-form :radio').fieldValue());
						if (pickType == 1 && form.memberName.value == '') {
							throw new Error('请填写会员名称。');
						} else if (pickType == 2 && form.memberUid.value == '') {
							throw new Error('请填写会员UID。');
						}
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