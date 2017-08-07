yulegameJQ(function() {
	yulegameJQ("#rob-member-form").validate({
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
						var pickType = parseInt(yulegameJQ('#rob-member-form :radio').fieldValue());
						if (pickType == 1 && form.memberName.value == '') {
							throw new Error(hdxLang.pleaseTypeUsername);
						} else if (pickType == 2 && form.memberUid.value == '') {
							throw new Error(hdxLang.pleaseTypeUid);
						}
						
						//yulegameJQ("input.rob-btn").attr("disabled", true);
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
	
	
	yulegameJQ(".suggest-member-uid").click(function(){
		yulegameJQ(".pick-type").each(function(){
			if (yulegameJQ(this).val() == '3') {
				yulegameJQ(this).attr('checked', 'checked');
			}
		})
	});
	
	yulegameJQ("ul.suggest-member-list li img").mouseover(function(){
		yulegameJQ(this).addClass('selected');
	}).mouseout(function(){
		yulegameJQ(this).removeClass('selected');
	});
	
	yulegameJQ("ul.suggest-member-list li").click(function() {
		try {
			var uid = yulegameJQ(this).attr('uid');
			
			if (uid == undefined || uid == '') {
				throw new Error(hdxLang.couldNotGetUid);
			}
			yulegameJQ(".pick-type").each(function(){
				if (yulegameJQ(this).val() == '2') {
					yulegameJQ(this).attr('checked', 'checked');
					yulegameJQ("input.member-uid").val(uid);
					yulegameJQ("#rob-member-form").submit();
				}
			});
		} catch (e) {
			errorCatch(e.message);
		}
		
	});	
});