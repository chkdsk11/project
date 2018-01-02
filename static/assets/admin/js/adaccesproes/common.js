

//删除操作
$('.del').click( function(){
    layer_confirm('', '/adaccesories/del', {id: $(this).attr('data-id')});
});

//取消操作
$('.cancel').click( function(){
    layer_confirm('取消该活动？', '/adaccesories/cancel', {id: $(this).attr('data-id')});
});

//清除搜索
$('.clear_search').click(function(){
    $("input[name='name']").val('');
    $("input[name='id']").val('');
    $("select[name='ad_position']").val(-1);
    $("select[name='ad_type']").val(0);
    $("select[name='ad_status']").val(0);
    $("select[name='ad_channel']").val(0);

    return false;
});