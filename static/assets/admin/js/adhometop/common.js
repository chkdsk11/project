//删除操作
$('.del').click( function(){
    layer_confirm('', '/adhometop/del', {id: $(this).attr('data-id')});
});
//清除搜索
$('.clear_search').click(function(){
    $("input[name='name']").val('');
    $("input[name='id']").val('');
    $("select[name='status']").val('');
    return false;
});
