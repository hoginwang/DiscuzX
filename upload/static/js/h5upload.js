function H5Uploader(upObj){
	this.upObj = upObj;
	var that = this;
	upObj.on('beforeFileQueued',function(file){
		var createQueue = true;
		if(this.customSettings.uploadSource == 'forum') {
			this.customSettings.alertType = 0;
			if(this.customSettings.uploadFrom == 'fastpost') {
				if(typeof forum_post_inited == 'undefined') {
					appendscript(JSPATH + 'forum_post.js?' + VERHASH);
				}
			}
		}
		try {
			if(this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
				var inputObj = $(this.customSettings.progressTarget+'_aid');
				if(inputObj && parseInt(inputObj.value)) {
					that.addPostParam('aid', inputObj.value);
				}
			} else if(this.customSettings.uploadSource == 'portal') {
				var inputObj = $('catid');
				if(inputObj && parseInt(inputObj.value)) {
					that.addPostParam(file,'catid', inputObj.value);
				}
			}
			
			if(this.customSettings.uploadSource == 'forum') {
				if(this.customSettings.maxAttachNum != undefined) {
					if(this.customSettings.maxAttachNum > 0) {
						this.customSettings.maxAttachNum--;
					} else {
						this.customSettings.alertType = 6;
						createQueue = false;
					}
				}
	
				if(createQueue && this.customSettings.maxSizePerDay != undefined) {
					if(this.customSettings.maxSizePerDay - file.size > 0) {
						this.customSettings.maxSizePerDay = this.customSettings.maxSizePerDay - file.size
					} else {
						this.customSettings.alertType = 11;
						createQueue = false;
					}
				}
				if(createQueue && this.customSettings.filterType != undefined) {
					var fileSize = this.customSettings.filterType[file.type.substr(1).toLowerCase()];
					if(fileSize != undefined && fileSize && file.size > fileSize) {
						this.customSettings.alertType = 5;
						createQueue = false;
					}
				}
	
			}
			if(!createQueue){
				alert(STATUSMSG[this.customSettings.alertType]);
			}
			return createQueue;
		} catch (ex) {
			console.log(ex);
		}
	});
	
	upObj.on('error', function(err_num){
		var MSG = {
			F_EXCEED_SIZE : '文件太大',
			Q_TYPE_DENIED : '禁止上传此类型的文件',
			Q_EXCEED_NUM_LIMIT : '选择的文件数量超过限制',
			F_DUPLICATE	  : '此文件已经上传'
			
		};
		alert(MSG[err_num] ? MSG[err_num] : err_num);
	});
	
	upObj.on('fileQueued',function(file){
		if(this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
			var preObj = $(this.customSettings.progressTarget);
			preObj.style.display = 'none';
			preObj.innerHTML = '';
			return;
		}
			
		var progress = new H5FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("等待上传...");
		progress.toggleCancel(true,this);
		try {
			if(this.customSettings.uploadSource == 'forum') {
				if(this.customSettings.uploadType == 'attach') {
					if(typeof switchAttachbutton == "function") {
						switchAttachbutton('attachlist');
					}
				} else if(this.customSettings.uploadType == 'image') {
					if(typeof switchImagebutton == "function") {
						switchImagebutton('imgattachlist');
					}
					try {
						$('imgattach_notice').style.display = '';
					} catch (ex) {}
				}
				var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
				var listObj = $(objId);
				var tableObj = listObj.getElementsByTagName("table");
				if(!tableObj.length) {
					listObj.innerHTML = "";
				}
			} else if(this.customSettings.uploadType == 'blog') {
				if(typeof switchImagebutton == "function") {
					switchImagebutton('imgattachlist');
				}
			}
		} catch (ex)  {
			this.debug(ex);
		}
	});
	
	upObj.on('uploadBeforeSend', function( object,data,header ) {
		   // 修改data可以控制发送哪些携带数据。
		   (this.customSettings.uploadType == 'image') && (data.type = 'image');
		   delete data.lastModifiedDate;
		   delete data.name;
		   if(object.file.post){
			 for(var i = 0 ; i < object.file.post.length;i++){
				 var v = object.file.post[i].split('|');
				 eval('data.' + v[0] + '="' + v[1] + '"');
			 }
		   }
		  
	});
	
	upObj.on('uploadStart',function(file){
			try {
				var progress = new H5FileProgress(file,this.customSettings.progressTarget);
				that.addPostParam(file,'filetype',file.ext);
				progress.setStatus("上传中...");
				if(upload.customSettings.uploadSource == 'forum') {
					var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
					var attachlistObj = $(objId).parentNode;
					attachlistObj.scrollTop = $(file.id).offsetTop - attachlistObj.clientHeight;
				}
			} catch (ex) {
			}
	});
	
	upObj.on('uploadProgress',function(file,percentage){
		try {
			var progress = new H5FileProgress(file, this.customSettings.progressTarget);
			progress.setStatus("正在上传("+ parseInt(percentage*100)+"%)...");

		} catch (ex) {
			
		}
	});
	
	upObj.on( 'uploadSuccess', function( file,response) {
		try {
			var progress = new H5FileProgress(file, this.customSettings.progressTarget);
			if(this.customSettings.uploadSource == 'forum') {
				if(this.customSettings.uploadType == 'poll') {
					var data = response;
					if(parseInt(data.aid)) {
						var preObj = $(this.customSettings.progressTarget);
						preObj.innerHTML = "";
						preObj.style.display = '';
						var img = new Image();
						img.src = IMGDIR + '/attachimg_2.png';//data.smallimg;
						var imgObj = document.createElement("img");
						imgObj.src = img.src;
						imgObj.className = "cur1";
						imgObj.onmouseout = function(){hideMenu('poll_img_preview_'+data.aid+'_menu');};//"hideMenu('poll_img_preview_"+data.aid+"_menu');";
						imgObj.onmouseover = function(){showMenu({'menuid':'poll_img_preview_'+data.aid+'_menu','ctrlclass':'a','duration':2,'timeout':0,'pos':'34'});};//"showMenu({'menuid':'poll_img_preview_"+data.aid+"_menu','ctrlclass':'a','duration':2,'timeout':0,'pos':'34'});";
						preObj.appendChild(imgObj);
						var inputObj = document.createElement("input");
						inputObj.type = 'hidden';
						inputObj.name = 'pollimage[]';
						inputObj.id = this.customSettings.progressTarget+'_aid';
						inputObj.value= data.aid;
						preObj.appendChild(inputObj);
						var preImgObj = document.createElement("span");
						preImgObj.style.display = 'none';
						preImgObj.id = 'poll_img_preview_'+data.aid+'_menu';
						img = new Image();
						img.src = data.smallimg;
						imgObj = document.createElement("img");
						imgObj.src = img.src;
						preImgObj.appendChild(imgObj);
						preObj.appendChild(preImgObj);
					}
				} else {
					aid = parseInt(response);
					if(aid > 0) {
						if(this.customSettings.uploadType == 'attach') {
							ajaxget('forum.php?mod=ajax&action=attachlist&aids=' + aid + (!fid ? '' : '&fid=' + fid)+(typeof resulttype == 'undefined' ? '' : '&result=simple'), file.id);
						} else if(this.customSettings.uploadType == 'image') {
							var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_'+aid);
							ajaxget('forum.php?mod=ajax&action=imagelist&type=single&pid=' + pid + '&aids=' + aid + (!fid ? '' : '&fid=' + fid), tdObj.id);
							$(file.id).style.display = 'none';
						}
					} else {
						aid = aid < -1 ? Math.abs(aid) : aid;
						if(typeof STATUSMSG[aid] == "string") {
							progress.setStatus(STATUSMSG[aid]);
							showDialog(STATUSMSG[aid], 'notice', null, null, 0, null, null, null, null, sdCloseTime);
						} else {
							progress.setStatus("取消上传");
						}
						this.removeFile(file.id);
						progress.setCancelled();
						progress.toggleCancel(true, this);
					}
				}
			} else if(this.customSettings.uploadType == 'album') {
				var data = response;
				if(parseInt(data.picid)) {
					var newTr = document.createElement("TR");
					var newTd = document.createElement("TD");
					var img = new Image();
					img.src = data.url;
					var imgObj = document.createElement("img");
					imgObj.src = img.src;
					newTd.className = 'c';
					newTd.appendChild(imgObj);
					newTr.appendChild(newTd);
					newTd = document.createElement("TD");
					newTd.innerHTML = '<strong>'+file.name+'</strong>';
					newTr.appendChild(newTd);
					newTd = document.createElement("TD");
					newTd.className = 'd';
					newTd.innerHTML = '图片描述<br/><textarea name="title['+data.picid+']" cols="40" rows="2" class="pt"></textarea>';
					newTr.appendChild(newTd);
					this.customSettings.imgBoxObj.appendChild(newTr);
				} else {
					showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
				}
				$(file.id).style.display = 'none';
			} else if(this.customSettings.uploadType == 'blog') {
				var data = response;
				if(parseInt(data.picid)) {
					var tdObj = that.getInsertTdId(this.customSettings.imgBoxObj, 'image_td_'+data.picid);
					var img = new Image();
					img.src = data.url;
					var imgObj = document.createElement("img");
					imgObj.src = img.src;
					imgObj.className = "cur1";
					imgObj.onclick = function() {insertImage(data.bigimg);};
					tdObj.appendChild(imgObj);
					var inputObj = document.createElement("input");
					inputObj.type = 'hidden';
					inputObj.name = 'picids['+data.picid+']';
					inputObj.value= data.picid;
					tdObj.appendChild(inputObj);
				} else {
					showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
				}
				$(file.id).style.display = 'none';
			} else if(this.customSettings.uploadSource == 'portal') {
				var data = response;
				if(data.aid) {
					if(this.customSettings.uploadType == 'attach') {
						ajaxget('portal.php?mod=attachment&op=getattach&type=attach&id=' + data.aid, file.id);
						if($('attach_tblheader')) {
							$('attach_tblheader').style.display = '';
						}
					} else {
						var tdObj = that.getInsertTdId(this.customSettings.imgBoxObj, 'attach_list_'+data.aid);
						ajaxget('portal.php?mod=attachment&op=getattach&id=' + data.aid, tdObj.id);
						$(file.id).style.display = 'none';
					}
				} else {
					showDialog('上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
					progress.setStatus("Cancelled");
					this.removeFile(file.id);
					progress.setCancelled();
					progress.toggleCancel(true, this);
				}
			} else {
				progress.setComplete();
				progress.setStatus("上传完成.");
				progress.toggleCancel(false);
			}
		} catch (ex) {
			console.log(ex);
		}
	});
	
	upObj.on( 'uploadFinished', function( file ) {
		var status = this.getStats();
		
	});
		
	upObj.on( 'uploadError', function( file ) {
		alert('上传出错');
	});		
	
}

H5Uploader.prototype.addPostParam = function (file,name, value){
	file.post || (file.post = new Array());
	file.post.push(name+ '|' + value);
}


H5Uploader.prototype.getInsertTdId = function(boxObj, tdId) {
	var tableObj = boxObj.getElementsByTagName("table");
	var tbodyObj, trObj, tdObj;
	if(!tableObj.length) {
		tableObj = document.createElement("table");
		tableObj.className = "imgl";
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
		boxObj.appendChild(tableObj);

	} else if(!tableObj[0].getElementsByTagName("tbody").length) {
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
	} else {
		tableObj = tableObj[0];
		tbodyObj = tableObj.getElementsByTagName("tbody")[0];
	}

	var createTr = true;
	var inserID = 0;
	if(tbodyObj.childNodes.length) {
		trObj = tbodyObj.childNodes[tbodyObj.childNodes.length -1];
		var findObj = trObj.getElementsByTagName("TD");
		for(var j=0; j < findObj.length; j++) {
			if(findObj[j].id == "") {
				inserID = j;
				tdObj = findObj[j];
				break;
			}
		}
		if(inserID) {
			createTr = false;
		}
	}
	if(createTr) {
		trObj = document.createElement("TR");
		for(var i=0; i < 4; i++) {
			var newTd = document.createElement("TD");
			newTd.width = "25%";
			newTd.vAlign = "bottom";
			newTd.appendChild(document.createTextNode(" "));
			trObj.appendChild(newTd);
		}
		tdObj = trObj.childNodes[0];
		tbodyObj.appendChild(trObj);
	}
	tdObj.id = tdId;
	return tdObj;
}


function H5FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;


	this.fileProgressWrapper = document.getElementById(this.fileProgressID);
	if (!this.fileProgressWrapper) {
		this.fileProgressWrapper = document.createElement("div");
		this.fileProgressWrapper.className = "progressWrapper";
		this.fileProgressWrapper.id = this.fileProgressID;

		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressContainer";

		var progressCancel = document.createElement("a");
		progressCancel.className = "progressCancel";
		progressCancel.href = "#";
		progressCancel.style.visibility = "hidden";
		progressCancel.appendChild(document.createTextNode(" "));

		var progressText = document.createElement("div");
		progressText.className = "progressName";
		progressText.appendChild(document.createTextNode(file.name));

		var progressBar = document.createElement("div");
		progressBar.className = "progressBarInProgress";

		var progressStatus = document.createElement("div");
		progressStatus.className = "progressBarStatus";
		progressStatus.innerHTML = "&nbsp;";

		this.fileProgressElement.appendChild(progressCancel);
		this.fileProgressElement.appendChild(progressText);
		this.fileProgressElement.appendChild(progressStatus);
		this.fileProgressElement.appendChild(progressBar);

		this.fileProgressWrapper.appendChild(this.fileProgressElement);

		document.getElementById(targetID).appendChild(this.fileProgressWrapper);
	} else {
		this.fileProgressElement = this.fileProgressWrapper.firstChild;
		this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
	this.setTimer(null);


}

H5FileProgress.prototype.setTimer = function (timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};
H5FileProgress.prototype.getTimer = function (timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

H5FileProgress.prototype.reset = function () {
	try {
		this.fileProgressElement.className = "progressContainer";

		this.fileProgressElement.childNodes[2].innerHTML = "&nbsp;";
		this.fileProgressElement.childNodes[2].className = "progressBarStatus";

		this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
		this.fileProgressElement.childNodes[3].style.width = "0%";

		this.appear();
	} catch (ex) {}
};

H5FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressElement.className = "progressContainer green";
	this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
	this.fileProgressElement.childNodes[3].style.width = percentage + "%";

	this.appear();
};
H5FileProgress.prototype.setComplete = function () {
	this.fileProgressElement.className = "progressContainer blue";
	this.fileProgressElement.childNodes[3].className = "progressBarComplete";
	this.fileProgressElement.childNodes[3].style.width = "";

};
H5FileProgress.prototype.setError = function () {
	this.fileProgressElement.className = "progressContainer red";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 5000));
};
H5FileProgress.prototype.setCancelled = function () {
	this.fileProgressElement.className = "progressContainer";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 2000));
};
H5FileProgress.prototype.setStatus = function (status) {
	this.fileProgressElement.childNodes[2].innerHTML = status;
};

H5FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
			var fileID = this.fileProgressID;
			that = this;
			this.fileProgressElement.childNodes[0].onclick = function () {
				swfUploadInstance.removeFile(fileID);
				that.setCancelled();
				return false;
			};
		}
};

H5FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}

	if (this.fileProgressWrapper.filters) {
		try {
			this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch (e) {
			this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressWrapper.style.opacity = 1;
	}

	this.fileProgressWrapper.style.height = "";

	this.height = this.fileProgressWrapper.offsetHeight;
	this.opacity = 100;
	this.fileProgressWrapper.style.display = "";

};

H5FileProgress.prototype.disappear = function () {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function () {
			oSelf.disappear();
		}, rate));
	} else {
		this.fileProgressWrapper.style.display = "none";
		this.setTimer(null);
	}
};