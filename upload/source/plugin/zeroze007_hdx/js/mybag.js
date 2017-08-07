function discard(id) {
    try {

        yulegameJQ("#item-id").val(id);
                    yulegameJQ("#player-action").val('discard');
        yulegameJQ("#item-form").submit();
    } catch(e) {
        alert(hdxLang.jsError+ e.message);
    }

}

yulegameJQ(function() {

    yulegameJQ(".equip-link").click(function(){
        try {
            var itemId = yulegameJQ(this).attr('item');

            if (isNaN(parseInt(itemId))) {
                throw new Error(hdxLang.invalid +" item "+ hdxLang.value);
            }

            yulegameJQ("#item-id").val(itemId);
            yulegameJQ("#player-action").val('equip');
            yulegameJQ("#item-form").submit();



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });

    yulegameJQ(".unload-link").click(function(){
        try {
            var itemId = yulegameJQ(this).attr('item');

            if (isNaN(parseInt(itemId))) {
                throw new Error(hdxLang.invalid +" item "+ hdxLang.value);
            }

            yulegameJQ("#item-id").val(itemId);
            yulegameJQ("#player-action").val('unload');
            yulegameJQ("#item-form").submit();



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });	
    
    yulegameJQ(".use-link").click(function(){
        try {
            var itemId = yulegameJQ(this).attr('item');

            if (isNaN(parseInt(itemId))) {
                throw new Error(hdxLang.invalid +" item "+ hdxLang.value);
            }

            yulegameJQ("#item-id").val(itemId);
            yulegameJQ("#player-action").val('use');
            yulegameJQ("#item-form").submit();



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });	
    
    yulegameJQ(".discard-link").click(function(){
        try {
            var itemId = yulegameJQ(this).attr('item');

            if (isNaN(parseInt(itemId))) {
                throw new Error(hdxLang.invalid +" item "+ hdxLang.value);
            }

             showDialog(hdxLang.confirmToDiscard,"confirm", hdxLang.discard,"discard("+ itemId +")");



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
	

	
	
});