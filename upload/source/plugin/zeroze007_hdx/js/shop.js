function buy(id) {
    try {

        yulegameJQ("#item-id").val(id);
        yulegameJQ("#item-form").submit();
    } catch(e) {
        alert(hdxLang.jsError+ e.message);
    }

}

yulegameJQ(function() {

    yulegameJQ(".buy-link").click(function(){
        try {
            var itemId = yulegameJQ(this).attr('item');

            if (isNaN(parseInt(itemId))) {
                throw new Error("Invalid item value!");
            }

            showDialog(hdxLang.confirmToBuy,"confirm", hdxLang.buy,"buy("+ itemId +")");



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });

	
	
    yulegameJQ("#item-form").validate({
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