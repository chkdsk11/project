
$(function() {

    $('#channel').change(function () {
        switch ($(this).val()){
            case '90':
                $('#Android').show();
                break;
            case '89':
                $('#Android').hide();
                break;
        }
    });

    $('#addAd').on('click',function(){
        var versions = $("input[name='versions']").val();
        var channel = $("select[name='channel'] option:checked").val();

        if(!versions){
            layer_required('请输入版本号！');return false;
        }
        //检查安卓是否有下载渠道
        if(channel==90){
            if($(".pid").length<1){
                layer_required('该平台没有下载渠道！');return false;
            }
        }
        ajaxSubmit('addAdForm');
    });

});

// 添加产品按钮
$('#add_clannel').bind('click', function() {
    var clannel_id  = $('#clannel_data').val();
    var clannel_url  = $('#clannel_url').val();
    var clannel_name = $('#clannel_data option:selected').html();
    var state = 0;
    if(!clannel_id){
        layer_required('请选择下载渠道！');
        return false;
    }
    if(!clannel_url){
        layer_required('未填写下载地址！');
        return false;
    }
    $('.pid').each(function(){
        if($(this).val()==clannel_id){
            state = 1;
        }
    });
    if(state==1){
        layer_required('不能重复添加渠道！');
        return false;
    }
    $('#product_list').append(
        "<tr>" +
        "<td><input class='pid' type='hidden' name='clannel_id[]' value='"+clannel_id+"'/>"+clannel_name+"</td>" +
        "<td><input class='pid' type='hidden' name='clannel_url[]' value='"+clannel_url+"'/> "+clannel_url+"</td>" +
        "<td><input type='button' value='删除' onclick='deleteok(this)' class='btn'/></td>" +
        "</tr>"
    );
});

// 删除
function deleteok(obj){
    $(obj).parent().parent().remove();
}
// 删除
function delclannel(obj,id){
    $.post('/appversion/deldwonurl',{id:id},function (data) {
        if(data.status=='success'){
            $(obj).parent().parent().remove();
        }else{
            layer_required(data.info);
        }
    });
}
