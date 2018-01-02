$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});

    //取消限购活动
    $('body').on('click','.cancelPromotion',function(){
        var promotion_id = $(this).attr('promotion_id');
        var promotion_type = $('#promotion_type').val();
        var request = location.search;
        if(!promotion_id || !promotion_type){
            layer_required('操作失败！');return false;
        }
        layer_confirm('确定要取消这个限购活动吗?','/limitbuy/del',{promotion_id :promotion_id,promotion_type :promotion_type,request :request});
    });
});