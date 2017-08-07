function buy(id) {
	try {

		yulegameJQ("#yule-id").val(id);
		yulegameJQ("#yule-form").submit();
	} catch(e) {
		alert(hdxLang.jsError+ e.message);
	}

}

yulegameJQ(function() {

	yulegameJQ(".buy-link").click(function(){
		try {
			var yuleId = yulegameJQ(this).attr('item');

			if (isNaN(parseInt(yuleId))) {
				throw new Error("Invalid item value!");
			}

			showDialog(hdxLang.confirmToBuy,"confirm", hdxLang.buy,"buy("+ yuleId +")");



		} catch(e) {
			alert(hdxLang.jsError+ e.message);
		}
	});
	
	
	
	yulegameJQ("#yule-form").validate({
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	yulegameJQ("#gift-form").validate({
		rules : {
			pickType : {
				required : true
			}
		},
		messages : {
			pickType : {
				required: hdxLang.whichTypeToChoose
			}
		},
		submitHandler: function(form) {
			yulegameJQ(form).ajaxSubmit({
				dataType : 'json',
				beforeSubmit : function(formData, jqForm, options) {
					loading();
					
					var error = '';
					var form = jqForm[0];
					try {
						var pickType = parseInt(yulegameJQ('#gift-form :radio').fieldValue());
						if (pickType == 1 && form.memberName.value == '') {
							throw new Error(hdxLang.pleaseTypeUsername);
						} else if (pickType == 2 && form.memberUid.value == '') {
							throw new Error(hdxLang.pleaseTypeUid);
						}
						
						//yulegameJQ("input.gift-btn").attr("disabled", true);
					} catch (error) {
						unloading();
						yulegameJQ("div.response-msg").text(error.message);
						yulegameJQ("div.response-msg").addClass('error-msg').show();
						return false;
					}
					
					
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