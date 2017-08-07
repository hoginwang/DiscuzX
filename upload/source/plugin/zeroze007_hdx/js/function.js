function ajaxSuccessHandler(response, statusText, xhr, $form) {
	try {
		unloading();

		var msg = response.msg;
		var title = hdxLang.promptInfo;
		if (response.result == false) {
			var mode = 'alert';
		} else {
			var mode = 'right';
		}
		if (response.url != undefined || response.url != null) {
			var func = 'location.href = "'+ response.url +'"';
		} else {
			var func = 'void(0)';

		}
		showDialog(msg, mode, title, func, 0);
	} catch (e) {
		alert(hdxLang.jsError+ e.message);
	}
}

function ajaxErrorHandler(XMLHttpRequest, textStatus, errorThrown) {

}


//loading mask
function loading() {
	yulegameJQ("#processing-modal").dialog({
		height : 90,
		width : 143,
		modal : true,
		buttons : {},
		close : function(event, ui) {
		},
		resizable : false,
		draggable : false

	});
	yulegameJQ(".ui-dialog-titlebar").hide();
	yulegameJQ("#processing-modal").parent().css('border', 'none');
	yulegameJQ(".ui-widget-content").css('background', 'none');
}


//unloading mask
function unloading() {
	yulegameJQ(".ui-dialog-titlebar").show();
	yulegameJQ("#processing-modal").dialog("destroy");
}



function copyToClipboard(e)
{

	if (document.all){                                              //判断IE

		window.clipboardData.setData('text', e);
		alert("成功复制到剪贴板！");}
	else{
		alert("您的浏览器不支持剪贴板操作，请自行复制。");
	};
}


//检测日期有效性
//必须使用格式  日/月/年
function checkDate(val) {
	var validformat = /^\d{2}\/\d{2}\/\d{4}jq/; // Basic check for format
	// validity
	var returnval = false;
	if (!validformat.test(val)) {
		// alert("Invalid Date Format. Please correct and submit again.")
		return false;
	} else { // Detailed check for valid date ranges
		var dayfield = val.split("/")[0];
		var monthfield = val.split("/")[1];
		var yearfield = val.split("/")[2];
		var dayobj = new Date(yearfield, monthfield - 1, dayfield);
		if ((dayobj.getMonth() + 1 != monthfield)
				|| (dayobj.getDate() != dayfield)
				|| (dayobj.getFullYear() != yearfield))
			// alert("Invalid Day, Month, or Year range detected. Please correct
			// and submit again.")
			returnal = false;
		else
			returnval = true;
	}
	/*
	 * if (returnval == false) input.select()
	 */
	return returnval;

}

//检测日期有效性
function isValidDate(day, month, year) {
	/*
	 * Purpose: return true if the date is valid, false otherwise
	 * 
	 * Arguments: day integer representing day of month month integer
	 * representing month of year year integer representing year
	 * 
	 * Variables: dteDate - date object
	 * 
	 */
	var dteDate;

	// set up a Date object based on the day, month and year arguments
	// javascript months start at 0 (0-11 instead of 1-12)
	dteDate = new Date(year, month, day);

	/*
	 * Javascript Dates are a little too forgiving and will change the date to a
	 * reasonable guess if it's invalid. We'll use this to our advantage by
	 * creating the date object and then comparing it to the details we put it.
	 * If the Date object is different, then it must have been an invalid date
	 * to start with...
	 */

	return ((day == dteDate.getDate()) && (month == dteDate.getMonth()) && (year == dteDate
			.getFullYear()));
}



function confirmDialog(dialog) {
	if (dialog.title == '' || dialog.title == undefined) {
		dialog.title = '提示';
	}
	if (dialog.width == undefined || null == dialog.width) {
		width = 300;
	}
	if (dialog.height == undefined || null == dialog.height) {
		height = 200;
	}
	yulegameJQ("#dialog-confirm").attr('title', dialog.title);
	yulegameJQ("#dialog-confirm p span.msg").html(dialog.msg);
	yulegameJQ("#dialog-confirm").dialog({
		height : height,
		width : width,
		modal : true,
		buttons : {
			"确定" : function() {
				yulegameJQ(this).dialog("close");
				var func = dialog.func;
				eval(func);
			},
			"取消" : function() {
				yulegameJQ(this).dialog("close");
			}

		},

		close : function(event, ui) {
			if (dialog.url != undefined && null != dialog.url) {
				location.href = dialog.url;
			}
		},
		resizable : false,
		draggable : false

	});
}

function showUIDialog(dialog) {
	yulegameJQ( "#dialog-modal" ).dialog( "destroy" );
	if (dialog.title == '' || dialog.title == undefined) {
		dialog.title = '提示';
	}
	if (dialog.width == undefined || null == dialog.width) {
		dialog.width = 300;
	}
	if (dialog.height == undefined || null == dialog.height) {
		dialog.height = 200;
	}
	yulegameJQ("#dialog-modal").attr('title', dialog.title);
	yulegameJQ("#dialog-modal p").html(dialog.msg);
	yulegameJQ("#dialog-modal").dialog({
		height : dialog.height,
		width : dialog.width,
		modal : true,
		buttons : {
			Ok : function() {
				if (dialog.url == undefined || null == dialog.url || '' == dialog.url) {
					yulegameJQ(this).dialog("close");
				} else {
					location.href = dialog.url.replace(/\&amp;/g, '&');
				}
			}
		},
		close : function(event, ui) {
			if (dialog.url != undefined && null != dialog.url) {
				location.href = dialog.url;
			}
		},
		resizable : false,
		draggable : false

	});
}

//email validation
function isValidEmail(str) {
	return (str.indexOf(".") > 2) && (str.indexOf("@") > 0);
}

//ajax 共同函数
function doAjax(obj) {

	var response = jq.ajax({
		type : 'post',
		dataType : 'json',
		data : obj,
		url : obj.url,
		async : false,
		beforeSend : function(XMLHttpRequest) {
			if (obj.loading) {
				loading();
			}
		},
		complete : function(XMLHttpRequest, textStatus) {
			if (obj.loading) {
				unloading();
			}

		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			var dialog = {
					'title' : '错误',
					'msg' : "程序出现未知错误，请联系我们取得技术支持。"
			};
			showDialog(dialog);


		} 		
	}).responseText;

	eval("var data=" + response);
	return data;
}



function showInfo(showObj, msg) {

	var options = {};
	yulegameJQ(".ui-widget div").addClass('ui-state-highlight')
	.addClass('ui-corner-all');
	yulegameJQ(".ui-widget div .msg").text(msg);

	yulegameJQ(showObj).show("slide", options, 500);

}


/**
 * Prompt a warning dialog
 * @param msg
 * @returns
 */
function showErrorMsg(msg, url) {
	try {
		msg = msg.replace('\n', '<p>&nbsp;</p>');
		var title = 'Message';
		var mode = 'alert';
		if (url != undefined && url != null) {
			var func = 'location.href = "'+ url +'"';
		} else {
			var func = 'void(0)';

		}
		showDialog(msg, mode, title, func, 0);

	} catch (e) {
		alert(msg);
	}
}

function errorCatch(msg) {
	showErrorMsg("JAVASCRIPT执行错误，请联系插件作者并告知你发生的错误。对你造成的不便，敬请见谅。" + "\n\n错误: "+ msg);
}

