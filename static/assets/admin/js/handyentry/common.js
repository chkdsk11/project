
$('.chagehide').click( function(){
    layer_confirm('确定要隐藏吗？', '/handyentry/hide', {id: $(this).attr('data-id')});
});
$('.chageshow').click( function(){
    layer_confirm('确定要显示吗？', '/handyentry/show', {id: $(this).attr('data-id')});
});
$('.del').click( function(){
    layer_confirm('确定要显示吗？', '/handyentry/del', {id: $(this).attr('data-id')});
});
$('.editsort').change(function(){
    var sort = $(this).val();
    var id = $(this).data('id');
    layer_request('post','/handyentry/editsort', {id:id,sort:sort});
})

//清除搜索
$('.clear_search').click(function(){
    $("input[name='name']").val('');
    $("input[name='id']").val('');
    $("select[name='status']").val('');
    return false;
});