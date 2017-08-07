function fire() {
    try {

        yulegameJQ("#guard-hired-form").submit();
    } catch(e) {
        alert(hdxLang.jsError+ e.message);
    }

}

function quit() {
    try {

        yulegameJQ("#guard-form").submit();
    } catch(e) {
        alert(hdxLang.jsError+ e.message);
    }

}

function hire(id) {
    try {

        yulegameJQ("#guard-uid").val(id);
        yulegameJQ("#guard-form").submit();
    } catch(e) {
        alert(hdxLang.jsError+ e.message);
    }

}

yulegameJQ(function() {
    yulegameJQ(".fire-link").click(function(){
        try {


            showDialog(hdxLang.confirmToContinue,"confirm", null,"fire()");



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });
    yulegameJQ(".quit-link").click(function(){
        try {


            showDialog(hdxLang.confirmToContinue,"confirm", null,"quit()");



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });
	
    yulegameJQ(".hire-link").click(function(){
        try {
            var uid = yulegameJQ(this).attr('uid');

            if (isNaN(parseInt(uid))) {
                throw new Error("UID invalid!");
            }

            showDialog(hdxLang.confirmToContinue,"confirm", hdxLang.hire,"hire("+ uid +")");



        } catch(e) {
            alert(hdxLang.jsError+ e.message);
        }
    });	
        
        
    yulegameJQ("#guard-join-form").validate({
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
	
    yulegameJQ("#guard-form").validate({
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