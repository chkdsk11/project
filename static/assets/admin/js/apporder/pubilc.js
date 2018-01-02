
$(function() {
    $('#start_time,#end_time').datetimepicker({step:10});
});
//清除搜索
$('.clear_search').click(function(){
    $("input[name='end_time']").val('');
    $("input[name='start_time']").val('');
    return false;
});