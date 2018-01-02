$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});
    //取消促销活动
    $('body').on('click','.cancelPromotion',function(){
        var request = location.search;
        var promotion_id = $(this).attr('promotion_id');
        var promotion_type = $(this).attr('en_promotion_type');
        if(!promotion_id){
            layer_required('操作失败！');return false;
        }
        layer_confirm('确定要取消这个促销活动吗?','/promotion/del',{promotion_id :promotion_id,promotion_type :promotion_type,request :request});
    });
});