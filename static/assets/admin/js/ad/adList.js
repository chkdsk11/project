//删除操作
$('.del').click( function(){
    layer_confirm('', '/ad/del', {id: $(this).attr('data-id')});
});
//清除搜索
$('.clear_search').click(function(){
    $("input[name='ad_name']").val('');

    $("#frist_select").val(0);
    $("select[name='ad_type']").val(0);
    $("select[name='ad_status']").val(0);
    $("select[name='ad_channel']").val(0);
    $("input[name='ad_position']").val(0);
    $("#second_select").hide();
    $("#third_select").hide();
    $("#fourth_select").hide();
    return false;
});
//取消操作
$('.cancel').click( function(){
    layer_confirm('取消该活动？', '/ad/cancel', {id: $(this).attr('data-id')});
});