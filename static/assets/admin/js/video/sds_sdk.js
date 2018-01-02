//start upload
//document.write("<script language='javascript' src='Gandalf.js'></script>");

(function () {
    window.uploadFunction = function (obj) {
    	window.video_desc = obj.video_desc;//全局
        var param = new window.uploadParams();
        param.uploadUrl = obj.uploadUrl;
        param.file = obj.file;
        param.fileKey = window.fileOperation.getFileKey(obj.file);

        window.LCUploader(param);
    }

    window.abortFunction = function (fileKey) {
        window.SDSAbort(fileKey);
    }

})();

//回调函数
(function () {
    window.uploadParams = function () {
        this.uploadUrl = "";
        this.file = {};
        this.uc1 = "0";
        this.uc2 = "0";
        this.uploadType = 1;

        this.init = function (data) { //一个文件上传完成后的回调
          switch (data.code) {
              case 101: //登录超时;
                  alert("登录超时！")
                  window.location.reload();
                  break;
              case 112:
                  alert("续传服务发生异常,请重新上传！");  
                  break;
              case 305:
                  alert("您的上传空间不足，如需更多空间，请联系客服人员。");
                  break;
              case 999:
                  alert("初始化请求错误，请联系后台。");
                  break;
              default:
                  alert(data.message);
          }
        };

        this.load = function (code, e) { //一个文件上传完成后的回调
            if (code == CallbackState.giveup) {
                $("#videoSpeed").html("0");
                $("#videoStatus").html("暂时放弃此文件上传");
            } else {            
            	var video_id = window.video_id;
            	var video_unique = window.video_unique;
            	var video_name = window.video_name;
            	var video_desc = window.video_desc;
            	if(video_id)
            	{
                    _submitFn('/video/add', {
                        video_id:video_id,
                        video_unique:video_unique,
                        video_name:video_name,
                        video_desc:video_desc
                    });
            	}
            	$("#videoProgress").html("100%");
                $("#videoSpeed").html("0");
                $("#videoStatus").html("上传完成");
            }
        };

        this.progress = function (code, e) {//上传进度回调
            if (code == CallbackState.lost) {
                $("#videoSpeed").html(0);
                $("#videoStatus").html("视频丢失");
            } else {
                $("#videoProgress").html(e.progress);
                $("#videoSpeed").html(e.speed);
                $("#videoStatus").html("正在上传");
            }
        };

        this.error = function (code, e) {//发上错误回调
            if (code == CallbackState.failed) {
                $("#videoSpeed").html("0");
                $("#videoStatus").html("上传失败");
            } else {
                $("#videoSpeed").html("0");
                $("#videoStatus").html("网络连接断开");
            }
        };

        this.abort = function (e) { //终止上传

        };
    };
})();

(function () {
    window.fileOperation = {
        selectFile: [],
        maxFileLength: 100,
        fileKeyList: {},
        fileTypes: "wmv|avi|dat|asf|rm|rmvb|ram|mpg|mpeg|3gp|mov|mp4|m4v|dvix|dv|dat|mkv|flv|f4v|vob|ram|qt|divx|cpk|fli|flc|mod",
        getFileType: function (f) {
            return f.name.split('.').pop();
        },
        getFileKey: function (f) {
            return [fileOperation.getFileType(f), f.size, + f.lastModifiedDate || f.name].join('_');
        },
        fileSelect: function (e) {
            var inpfile = document.getElementById("fileUploadId");
            if (inpfile) {
                inpfile.click && e.target != inpfile && inpfile.click();
            } else {
                inpfile = document.createElement('input');
                $("body").append(inpfile);
                inpfile.setAttribute('id', "fileUploadId");
                inpfile.setAttribute('type', "file");
                inpfile.setAttribute('autocomplete', "off");
                inpfile.setAttribute('multiple', "true");
                inpfile.style.display = "none";
                inpfile.addEventListener('change', fileOperation.showFileList, !1);
                inpfile.click && e.target != inpfile && inpfile.click();
            }
        },
        showFileList: function (e) {
            var files = e.target.files || e.dataTransfer.files;
            var m = fileOperation.maxFileLength - fileOperation.selectFile.length;
            for (var i = 0; i < Math.min(files.length, m) ; i++) {
                var f = files[i],
                    fType = fileOperation.getFileType(f),
                    fKey = fileOperation.getFileKey(f);

                if (eval("/" + window.fileOperation.fileTypes + "$/i").test(fType) == false) {
                    if (files.length == 1) {
                        alert("不支持该视频格式！");
                    } else {
                        alert("包含不支持的视频格式");
                    }
                }
                if (eval("/" + fileOperation.fileTypes + "$/i").test(fType) && fileOperation.selectFile.length < window.fileOperation.maxFileLength) {
                    if (!fileOperation.fileKeyList[fKey]) {
                        fileOperation.selectFile.push(f);
                        window.userSelectedFiles = [];//针对浙江在线的需求
                        window.userSelectedFiles.push(f);//供访问的文件列表
                        fileOperation.fileKeyList[fKey] = 1;


                        //添加文件回调函数
                        window.selectOptions.addFiles(f, fType);
                    }
                }
            }
        }
    };
})();


//绑定选择文件按钮
(function ($) {
    var selectOptions = {};
    var defaults = {
        selectFileId: "selectFile",
        addFiles: function (file, fileType) {
        	
        },
    };
    //alert(file);
    $.fn.selectUpload = function (options) {
        selectOptions = $.extend(defaults, options || {});
        return this.each(function () {
            $("#" + selectOptions.selectFileId).click(window.fileOperation.fileSelect);
            window.selectOptions = selectOptions;
        });
    };
})(jQuery);