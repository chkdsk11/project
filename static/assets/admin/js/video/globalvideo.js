/**
 * Created by Administrator on 2016/8/26.
 */

$(function(){
    //提交form
    $("#form").submit(function(){
        ajaxSubmit('form');
        return false;
    });
    //更新
    $('#up_video').click(function(){
        _submitFn('/video/videoCrontab');
        return false;
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        layer_confirm('', '/video/del', {video_id: $(this).attr('data-id')});
    });
    //添加视频
    $("#fileSelecter").selectUpload({
        //绑定添加文件按钮
        selectFileId: "fileSelecter",
        //每添加一个视频文件的回调
        addFiles: function (file, fileType){
            $("#videoId").html(file.name);
            $("#videoProgress").html("0");
            $("#videoSpeed").html("0");
            $("#videoStatus").html("等待上传");
        }
    });

    //上传视频
    $("#uploadBtn").click(function () {
        var video_desc = $("#content").val();
        if (!window.userSelectedFiles) {
            layer_required('请选择视频');
            return false;
        };
        //要上传的文件
        window.uploadFunction({
            file: window.userSelectedFiles[window.userSelectedFiles.length-1],
            uploadUrl: "/video/add",
            video_desc: video_desc
        });
    });
});
