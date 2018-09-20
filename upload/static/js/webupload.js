var WEBUpload;


if (WEBUpload == undefined) {
    WEBUpload = function (settings) {
        var defaultStting = {
            auto: true,
            swf: STATICURL + 'js/webuploader/Uploader.swf',
            server: '',
            formData: {},
            compress: false,
            duplicate: true,
            fileSingleSizeLimit: 0,
            fileVal: 'Filedata',
            pick: undefined,
            accept: null,
            onFileQueued: function (file) {
                fileQueued.call(this, file);
            },
            onUploadBeforeSend: function (obj, data, headers) {
                // data.filetype = obj.file.type;
                if (this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
                    var inputObj = $(this.customSettings.progressTarget + '_aid');
                    if (inputObj && parseInt(inputObj.value)) {
                        data.aid = inputObj.value;
                    }
                } else if (this.customSettings.uploadSource == 'portal') {
                    var inputObj = $('catid');
                    if (inputObj && parseInt(inputObj.value)) {
                        data.catid = inputObj.value;
                    }
                }
                uploadStart.call(this, obj.file);
            },
            onUploadProgress: function (file, percentage) {
                uploadProgress.call(this, file, Math.ceil(percentage * file.size), file.size);
            },
            onUploadSuccess: function (file, data) {
                uploadSuccess.call(this, file, data);
            },
            onError: function (type, p1, p2) {
                var file, errorCode;
                switch (type) {
                    case 'F_EXCEED_SIZE':
                        file = p2;  // p1: size
                        errorCode = WEBUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT;
                        break;
                    case 'Q_TYPE_DENIED':
                        file = p1;
                        errorCode = WEBUpload.QUEUE_ERROR.INVALID_FILETYPE;
                        break;
                }
                if (errorCode) fileQueueError.call(this, file, errorCode);
            }
        };
        var consumerStting = window.jQuery.extend({}, defaultStting, settings);
        var uploader = WebUploader.create(consumerStting);

        uploader.debug = function (obj) {
            console.log('WEBUpload debug ：', obj);
        };
        uploader.customSettings = window.jQuery.extend({}, settings.customSettings);

        return uploader;
    };
    if(typeof forum_post_inited == 'undefined') {
        appendscript(JSPATH + 'forum_post.js?' + VERHASH);
    }
}

WEBUpload.QUEUE_ERROR = {
    QUEUE_LIMIT_EXCEEDED: -100,
    FILE_EXCEEDS_SIZE_LIMIT: -110,
    ZERO_BYTE_FILE: -120,
    INVALID_FILETYPE: -130
};
WEBUpload.UPLOAD_ERROR = {
    HTTP_ERROR: -200,
    MISSING_UPLOAD_URL: -210,
    IO_ERROR: -220,
    SECURITY_ERROR: -230,
    UPLOAD_LIMIT_EXCEEDED: -240,
    UPLOAD_FAILED: -250,
    SPECIFIED_FILE_ID_NOT_FOUND: -260,
    FILE_VALIDATION_FAILED: -270,
    FILE_CANCELLED: -280,
    UPLOAD_STOPPED: -290,
    RESIZE: -300
};
WEBUpload.FILE_STATUS = {
    QUEUED: -1,
    IN_PROGRESS: -2,
    ERROR: -3,
    COMPLETE: -4,
    CANCELLED: -5
};
WEBUpload.UPLOAD_TYPE = {
    NORMAL: -1,
    RESIZED: -2
};

WEBUpload.BUTTON_ACTION = {
    SELECT_FILE: -100,
    SELECT_FILES: -110,
    START_UPLOAD: -120,
    JAVASCRIPT: -130,	// DEPRECATED
    NONE: -130
};
WEBUpload.CURSOR = {
    ARROW: -1,
    HAND: -2
};
WEBUpload.WINDOW_MODE = {
    WINDOW: "window",
    TRANSPARENT: "transparent",
    OPAQUE: "opaque"
};

WEBUpload.RESIZE_ENCODING = {
    JPEG: -1,
    PNG: -2
};


var sdCloseTime = 2;


function fileQueued(file) {
    try {
        var createQueue = true;
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        if (this.customSettings.uploadSource == 'forum') {
            if (this.customSettings.maxAttachNum != undefined) {
                if (this.customSettings.maxAttachNum > 0) {
                    this.customSettings.maxAttachNum--;
                } else {
                    this.customSettings.alertType = 6;
                    createQueue = false;
                }
            }

            if (createQueue && this.customSettings.maxSizePerDay != undefined) {
                if (this.customSettings.maxSizePerDay - file.size > 0) {
                    this.customSettings.maxSizePerDay = this.customSettings.maxSizePerDay - file.size
                } else {
                    this.customSettings.alertType = 11;
                    createQueue = false;
                }
            }
            if (createQueue && this.customSettings.filterType != undefined) {
                var fileSize = this.customSettings.filterType[file.type.substr(1).toLowerCase()];
                if (fileSize != undefined && fileSize && file.size > fileSize) {
                    this.customSettings.alertType = 5;
                    createQueue = false;
                }
            }

        }
        if (createQueue) {
            progress.setStatus("等待上传...");
        } else {
            progress.setCancelled();
        }
        progress.toggleCancel(true, this);


    } catch (ex) {
        this.debug(ex);
    }

}

function fileQueueError(file, errorCode, message) {
    try {
        if (errorCode === WEBUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
            message = parseInt(message);
            showDialog("您选择的文件个数超过限制。\n" + (message === 0 ? "您已达到上传文件的上限了。" : "您还可以选择 " + message + " 个文件"), 'notice', null, null, 0, null, null, null, null, sdCloseTime);
            return;
        }

        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setError();
        progress.toggleCancel(false);

        switch (errorCode) {
            case WEBUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                progress.setStatus("文件太大.");
                this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                progress.setStatus("不能上传零字节文件.");
                this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.QUEUE_ERROR.INVALID_FILETYPE:
                progress.setStatus("禁止上传该类型的文件.");
                this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                alert("You have selected too many files.  " + (message > 1 ? "You may only add " + message + " more files" : "You cannot add any more files."));
                break;
            default:
                if (file !== null) {
                    progress.setStatus("Unhandled Error");
                }
                this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
    try {
        if (this.customSettings.uploadSource == 'forum') {
            if (this.customSettings.uploadType == 'attach') {
                if (typeof switchAttachbutton == "function") {
                    switchAttachbutton('attachlist');
                }
                try {
                    if (this.getStats().files_queued) {
                        $('attach_tblheader').style.display = '';
                        $('attach_notice').style.display = '';
                    }
                } catch (ex) {
                }
            } else if (this.customSettings.uploadType == 'image') {
                if (typeof switchImagebutton == "function") {
                    switchImagebutton('imgattachlist');
                }
                try {
                    $('imgattach_notice').style.display = '';
                } catch (ex) {
                }
            }
            var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
            var listObj = $(objId);
            var tableObj = listObj.getElementsByTagName("table");
            if (!tableObj.length) {
                listObj.innerHTML = "";
            }
        } else if (this.customSettings.uploadType == 'blog') {
            if (typeof switchImagebutton == "function") {
                switchImagebutton('imgattachlist');
            }
        }
        this.startUpload();
    } catch (ex) {
        this.debug(ex);
    }
}

function uploadStart(file, data) {
    try {
        if (this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
            var preObj = $(this.customSettings.progressTarget);
            preObj.style.display = 'none';
            preObj.innerHTML = '';
        }
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("上传中...");
        progress.toggleCancel(true, this);
        if (this.customSettings.uploadSource == 'forum') {
            var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
            var attachlistObj = $(objId).parentNode;
            attachlistObj.scrollTop = $(file.id).offsetTop - attachlistObj.clientHeight;
        }
    } catch (ex) {
    }

    return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {

    try {
        var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("正在上传(" + percent + "%)...");

    } catch (ex) {
        this.debug(ex);
    }
}

function uploadSuccess(file, serverData) {
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        if (this.customSettings.uploadSource == 'forum') {
            if (this.customSettings.uploadType == 'poll') {
                var data = eval('(' + serverData + ')');
                if (parseInt(data.aid)) {
                    var preObj = $(this.customSettings.progressTarget);
                    preObj.innerHTML = "";
                    preObj.style.display = '';
                    var img = new Image();
                    img.src = IMGDIR + '/attachimg_2.png';//data.smallimg;
                    var imgObj = document.createElement("img");
                    imgObj.src = img.src;
                    imgObj.className = "cur1";
                    imgObj.onmouseout = function () {
                        hideMenu('poll_img_preview_' + data.aid + '_menu');
                    };//"hideMenu('poll_img_preview_"+data.aid+"_menu');";
                    imgObj.onmouseover = function () {
                        showMenu({'menuid': 'poll_img_preview_' + data.aid + '_menu', 'ctrlclass': 'a', 'duration': 2, 'timeout': 0, 'pos': '34'});
                    };//"showMenu({'menuid':'poll_img_preview_"+data.aid+"_menu','ctrlclass':'a','duration':2,'timeout':0,'pos':'34'});";
                    preObj.appendChild(imgObj);
                    var inputObj = document.createElement("input");
                    inputObj.type = 'hidden';
                    inputObj.name = 'pollimage[]';
                    inputObj.id = this.customSettings.progressTarget + '_aid';
                    inputObj.value = data.aid;
                    preObj.appendChild(inputObj);
                    var preImgObj = document.createElement("span");
                    preImgObj.style.display = 'none';
                    preImgObj.id = 'poll_img_preview_' + data.aid + '_menu';
                    img = new Image();
                    img.src = data.smallimg;
                    imgObj = document.createElement("img");
                    imgObj.src = img.src;
                    preImgObj.appendChild(imgObj);
                    preObj.appendChild(preImgObj);
                }
            } else {
                aid = parseInt(serverData);
                if (aid > 0) {
                    if (this.customSettings.uploadType == 'attach') {
                        ajaxget('forum.php?mod=ajax&action=attachlist&aids=' + aid + (!fid ? '' : '&fid=' + fid) + (typeof resulttype == 'undefined' ? '' : '&result=simple'), file.id);
                    } else if (this.customSettings.uploadType == 'image') {
                        var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_' + aid);
                        ajaxget('forum.php?mod=ajax&action=imagelist&type=single&pid=' + pid + '&aids=' + aid + (!fid ? '' : '&fid=' + fid), tdObj.id);
                        $(file.id).style.display = 'none';
                    }
                } else {
                    aid = aid < -1 ? Math.abs(aid) : aid;
                    if (typeof STATUSMSG[aid] == "string") {
                        progress.setStatus(STATUSMSG[aid]);
                        showDialog(STATUSMSG[aid], 'notice', null, null, 0, null, null, null, null, sdCloseTime);
                    } else {
                        progress.setStatus("取消上传");
                    }
                    progress.setCancelled();
                    progress.toggleCancel(true, this);
                }
            }
        } else if (this.customSettings.uploadType == 'album') {
            var data = eval('(' + serverData + ')');
            if (parseInt(data.picid)) {
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
                newTd.innerHTML = '<strong>' + file.name + '</strong>';
                newTr.appendChild(newTd);
                newTd = document.createElement("TD");
                newTd.className = 'd';
                newTd.innerHTML = '图片描述<br/><textarea name="title[' + data.picid + ']" cols="40" rows="2" class="pt"></textarea>';
                newTr.appendChild(newTd);
                this.customSettings.imgBoxObj.appendChild(newTr);
            } else {
                showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
            }
            $(file.id).style.display = 'none';
        } else if (this.customSettings.uploadType == 'blog') {
            var data = eval('(' + serverData + ')');
            if (parseInt(data.picid)) {
                var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_' + data.picid);
                var img = new Image();
                img.src = data.url;
                var imgObj = document.createElement("img");
                imgObj.src = img.src;
                imgObj.className = "cur1";
                imgObj.onclick = function () {
                    insertImage(data.bigimg);
                };
                tdObj.appendChild(imgObj);
                var inputObj = document.createElement("input");
                inputObj.type = 'hidden';
                inputObj.name = 'picids[' + data.picid + ']';
                inputObj.value = data.picid;
                tdObj.appendChild(inputObj);
            } else {
                showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
            }
            $(file.id).style.display = 'none';
        } else if (this.customSettings.uploadSource == 'portal') {
            var data = eval('(' + serverData + ')');
            if (data.aid) {
                if (this.customSettings.uploadType == 'attach') {
                    ajaxget('portal.php?mod=attachment&op=getattach&type=attach&id=' + data.aid, file.id);
                    if ($('attach_tblheader')) {
                        $('attach_tblheader').style.display = '';
                    }
                } else {
                    var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'attach_list_' + data.aid);
                    ajaxget('portal.php?mod=attachment&op=getattach&id=' + data.aid, tdObj.id);
                    $(file.id).style.display = 'none';
                }
            } else {
                showDialog('上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
                progress.setStatus("Cancelled");
                progress.setCancelled();
                progress.toggleCancel(true, this);
            }
        } else {
            progress.setComplete();
            progress.setStatus("上传完成.");
            progress.toggleCancel(false);
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function getInsertTdId(boxObj, tdId) {
    var tableObj = boxObj.getElementsByTagName("table");
    var tbodyObj, trObj, tdObj;
    if (!tableObj.length) {
        tableObj = document.createElement("table");
        tableObj.className = "imgl";
        tbodyObj = document.createElement("TBODY");
        tableObj.appendChild(tbodyObj);
        boxObj.appendChild(tableObj);

    } else if (!tableObj[0].getElementsByTagName("tbody").length) {
        tbodyObj = document.createElement("TBODY");
        tableObj.appendChild(tbodyObj);
    } else {
        tableObj = tableObj[0];
        tbodyObj = tableObj.getElementsByTagName("tbody")[0];
    }

    var createTr = true;
    var inserID = 0;
    if (tbodyObj.childNodes.length) {
        trObj = tbodyObj.childNodes[tbodyObj.childNodes.length - 1];
        var findObj = trObj.getElementsByTagName("TD");
        for (var j = 0; j < findObj.length; j++) {
            if (findObj[j].id == "") {
                inserID = j;
                tdObj = findObj[j];
                break;
            }
        }
        if (inserID) {
            createTr = false;
        }
    }
    if (createTr) {
        trObj = document.createElement("TR");
        for (var i = 0; i < 4; i++) {
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

function uploadComplete(file) {
    try {
        if (this.getStats().files_queued === 0) {
        } else {
            this.startUpload();
        }
    } catch (ex) {
        this.debug(ex);
    }

}

function uploadError(file, errorCode, message) {
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setError();
        progress.toggleCancel(false);

        switch (errorCode) {
            case WEBUpload.UPLOAD_ERROR.HTTP_ERROR:
                progress.setStatus("Upload Error: " + message);
                this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
                progress.setStatus("Configuration Error");
                this.debug("Error Code: No backend file, File name: " + file.name + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.UPLOAD_FAILED:
                progress.setStatus("Upload Failed.");
                this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.IO_ERROR:
                progress.setStatus("Server (IO) Error");
                this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.SECURITY_ERROR:
                progress.setStatus("Security Error");
                this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                progress.setStatus("Upload limit exceeded.");
                this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
                progress.setStatus("File not found.");
                this.debug("Error Code: The file was not found, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
                progress.setStatus("Failed Validation.  Upload skipped.");
                this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
            case WEBUpload.UPLOAD_ERROR.FILE_CANCELLED:
                if (this.getStats().files_queued === 0) {
                }
                progress.setStatus(this.customSettings.alertType ? STATUSMSG[this.customSettings.alertType] : "Cancelled");
                progress.setCancelled();
                break;
            case WEBUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                progress.setStatus("Stopped");
                break;
            default:
                progress.setStatus("Unhandled Error: " + error_code);
                this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                break;
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function FileProgress(file, targetID) {
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

FileProgress.prototype.setTimer = function (timer) {
    this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function (timer) {
    return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function () {
    try {
        this.fileProgressElement.className = "progressContainer";

        this.fileProgressElement.childNodes[2].innerHTML = "&nbsp;";
        this.fileProgressElement.childNodes[2].className = "progressBarStatus";

        this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
        this.fileProgressElement.childNodes[3].style.width = "0%";

        this.appear();
    } catch (ex) {
    }
};

FileProgress.prototype.setProgress = function (percentage) {
    this.fileProgressElement.className = "progressContainer green";
    this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
    this.fileProgressElement.childNodes[3].style.width = percentage + "%";

    this.appear();
};
FileProgress.prototype.setComplete = function () {
    this.fileProgressElement.className = "progressContainer blue";
    this.fileProgressElement.childNodes[3].className = "progressBarComplete";
    this.fileProgressElement.childNodes[3].style.width = "";

};
FileProgress.prototype.setError = function () {
    this.fileProgressElement.className = "progressContainer red";
    this.fileProgressElement.childNodes[3].className = "progressBarError";
    this.fileProgressElement.childNodes[3].style.width = "";

    var oSelf = this;
    this.setTimer(setTimeout(function () {
        oSelf.disappear();
    }, 5000));
};
FileProgress.prototype.setCancelled = function () {
    this.fileProgressElement.className = "progressContainer";
    this.fileProgressElement.childNodes[3].className = "progressBarError";
    this.fileProgressElement.childNodes[3].style.width = "";

    var oSelf = this;
    this.setTimer(setTimeout(function () {
        oSelf.disappear();
    }, 2000));
};
FileProgress.prototype.setStatus = function (status) {
    this.fileProgressElement.childNodes[2].innerHTML = status;
};

FileProgress.prototype.toggleCancel = function (show, WEBUploadInstance) {
    this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
    if (WEBUploadInstance) {
        var fileID = this.fileProgressID;
        this.fileProgressElement.childNodes[0].onclick = function () {
            return false;
        };
    }
};

FileProgress.prototype.appear = function () {
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

FileProgress.prototype.disappear = function () {

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