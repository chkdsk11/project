
$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});

    $('#addAd').on('click',function(){
        var wap_path = $("input[name='wap_path']").val();
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

        if(!$.trim(wap_path)){
            layer_required('主题路径不能为空！');return false;
        }
        ajaxSubmit('addAdForm');
    });

});
