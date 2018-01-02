
$(document).on('change', '#first_image_upload', function(){
    var fileId = $(this).attr('id');
    var channel = $("#channel").val();
    $.ajaxFileUpload({
        url: '/apptheme/upload',
        secureuri: false,
        fileElementId: fileId,
        data:{channel:channel},
        dataType: 'json',
        success: function (data, status) {
            if(data.status == 'success') {
                $(':input[name=theme_zip]').val(data['data'][0]['src']);
                layer_required(data.info);
            } else {
                layer_required(data.info);
            }
        },
        error: function (data, status, e) {
            alert(e);
        }
    });
});
$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});

    $('#addAd').on('click',function(){
        var channel = $("#channel").val();
        var theme_zip = $("input[name='theme_zip']").val();
        var local_url = $("input[name='local_url']").val();

        var start_time = $("input[name='start_time']").val();
        var end_time = $("input[name='end_time']").val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var nowTime = new Date().getTime();


        //验证时间合法性
        if(!$.trim(start_time) || !$.trim(end_time)){
            layer_required('开始时间或结束时间不能为空！');return false;
        }
        if(startTime > endTime){
            layer_required('开始时间不能大于结束时间！');return false;
        }
        if(endTime < nowTime){
            layer_required('结束时间不能小于当前时间！');return false;
        }

        if(!channel){
            layer_required('请选择应用平台！');return false;
        }
        if(!theme_zip){
            layer_required('请上传主题包！');return false;
        }
        if(!local_url){
            layer_required('请输入主会场地址！');return false;
        }

        ajaxSubmit('addAdForm');
    });

});
