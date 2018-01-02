//删除操作
$('.del').click( function(){
    layer_confirm('', '/adwap/del', {id: $(this).attr('data-id')});
});

//清除搜索
$('.clear_search').click(function(){
    $("input[name='ad_name']").val('');
    $("select[name='ad_position']").val(0);
    $("select[name='ad_type']").val('');
    $("select[name='ad_status']").val('');
    return false;
});
//取消操作
$('.cancel').click( function(){
    layer_confirm('取消该活动？', '/adwap/cancel', {id: $(this).attr('data-id')});
});