
//上传图片
$(document).on('change', '#file_upload', function(){
    uploadFile('/handyentry/upload', $(this).attr('id'), $(this).attr('data-img'));
});

$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});

    //提交表单
    $('#addAd').on('click',function(){
        var action_name = $("input[name='name']").val();
        var start_time = $("input[name='start_time']").val();
        var end_time = $("input[name='end_time']").val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var nowTime = new Date().getTime();
        var link = $("input[name='link']").val();
        var start_version = $("select[name='start_version'] option:checked").val();
        var end_version = $("select[name='end_version'] option:checked").val();
        var icon_img = $("input[name='icon_img']").val();
        var is_wap = $("input[name='is_wap']").val();
        action_name = $.trim(action_name);
        //验证广告活动名称
        var check = /^[\u4e00-\u9fa5]+$/;
        if(!action_name){
            layer_required('广告名称不能为空！');return false;
        }else if(!check.test(action_name)){
            layer_required('名称只能输入汉字，请重新输入！');return false;
        }else if( action_name.length > 4 ){
            layer_required('名称只能输入4个汉字，请重新输入！');return false;
        }

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

        var start_num = $("#start_version").find("option:selected").attr("data-num");
        var end_num = $("#end_version").find("option:selected").attr("data-num");

        if(is_wap !=1 ){
            //验证版本
            if(!start_version || start_version == '0'){
                layer_required('请选择开始版本！');return false;
            }
            if(end_version){

                if((end_num < start_num)){
                    layer_required('开始版本不能大于结束版本！');return false;
                }
                // layer_required('请选择结束版本！');return false;
            }


        }


        if(!link){
            layer_required('请填写目标地址！');return false;
        }
        if(!icon_img){
            layer_required('请上传图片！');return false;
        }
        ajaxSubmit('addAdForm');
    });

});
