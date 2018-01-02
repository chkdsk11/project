//上传图片
$(document).on('change', '#first_image_upload,.upload_image', function(){
    uploadFile('/Adaccesories/upload', $(this).attr('id'), $(this).attr('data-img'));
});
$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});

    $('#position').change(function () {
        $('#PD').hide();
        $('#TJ').hide();
        $('#TD').hide();
        var type = $("select[name='position'] option:checked").data('type');
        var html ='';
        switch (type){
            case 0:
                $('#TJ').show();
                html  +='<option value="2" selected = "selected" >商品推荐</option>';
                html  +='<option value="1" selected = "selected" >图片广告</option>';
                break;
            case 1:
                $('#TJ').show();
                html  ='<option value="2" selected = "selected" >商品推荐</option>'
                break;
            case 2:
                $('#PD').show();
                html  ='<option value="1" selected = "selected" >图片广告</option>'
                break;
        }
        $("#advertisement_type").html(html);
    });

    $('#addAd').on('click',function(){
        var action_name = $("input[name='action_name']").val();
        var start_time = $("input[name='start_time']").val();
        var end_time = $("input[name='end_time']").val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var nowTime = new Date().getTime();
        var action_description = $("#action_description").val();
        var ad_position = $("select[name='position'] option:checked").val();
        var advertisement_type = $("select[name='advertisement_type'] option:checked").val();
        var channel = $("select[name='channel'] option:checked").val();

        //验证广告活动名称
        if(!$.trim(action_name)){
            layer_required('广告名称不能为空！');return false;
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

        if (ad_position<1) {
            layer_required('请选择广告位置！');
            return false;
        }
        if(!channel){
            layer_required('请选择一个使用终端！');
            return false;
        }
        /****** 验证使用范围start *****/
        switch (advertisement_type) {
            case '1': //图片广告
                var first_image = $("input[name='first_image']").val();
                var app_location = $("input[name='app_location']").val();
                var wap_location = $("input[name='wap_location']").val();
                var sort = $("input[name='sort']").val();

                if(!first_image){
                    layer_required('请上传广告图片！');return false;
                }

                if(!app_location){
                    layer_required('请填写app目标地址！');return false;
                }
                if(!wap_location){
                    layer_required('请填写wap目标地址！');return false;
                }
                break;
            case '2'://商品推荐
                var product_status = 0;
                var sort_pro = $(".product_order");
                var tr_list = $("#product_list tbody tr");
                var len = tr_list.length;
                if (len < 2) {
                    layer_required('请先添加商品！');
                    product_status==1
                    return false;
                }
                sort_pro.each(function(){
                    if (!$(this).val()) {
                        layer_required('请输入商品的排序！');
                        product_status==1
                        return false;
                    }
                    if (isNaN($(this).val())) {
                        layer_required('排序请输入数字！');
                        product_status==1
                        return false;
                    }
                });
                break;
        }

        //验证活动说明
        if(!$.trim(action_description)){
            layer_required('活动说明不能为空！');return false;
        }
        if (action_description.length > 200) {
            layer_required("活动说明字数需在规定200个字符之内！");return false;
        }

        ajaxSubmit('addAdForm');
    });

});


function searchGoods(){
    var search = $('#product_word').val();
    if(!search){
        layer_required('请输入正确的商品id！');
        return false;
    }
    $.post('/adaccesories/searchgoods',{search:search},function (data) {
        if(data.list){
            $('#search_result').html("<option value=''>-选择商品-</option>");
            $.each(data.list, function(i, item) {
                $('#search_result').append("<option value='" + item.id + "' data-img=" + item.goods_image + "''>" + item.goods_name + "</option>");
            });
        }else{
            layer_required('没有搜索到海外商品！');
            $('#search_result').empty();
            return false;
        }
    });
}

// 添加产品按钮
$('#add_product').bind('click', function() {
    var product_id = $('#search_result').val();
    var product_name = $('#search_result option:selected').html();
    var product_img =$('#search_result option:selected').attr('data-img');
    var state = 0;
    if(!product_id){
        layer_required('请选中商品后添加！');
        return false;
    }
    $('.pid').each(function(){
        if($(this).html()==product_id){
            state = 1;
        }
    });
    if(state==1){
        layer_required('不能重复添加商品！');
        return false;
    }
    $('#product_list').append(
        "<tr><td class='pid'>"+product_id+"</td>" +
            "<input type='hidden' name='product_id["+product_id+"]' value='"+product_id+"'/>" +
            "<td><input type='hidden' name='product_name["+product_id+"]' value='"+product_name+"'/>"+product_name+"</td>" +
            "<td>" +
            "<input type='hidden' name='image["+product_id+"]' value='"+product_img+"'>" +
            "<img class='thumb' src='"+product_img+"' class='img-rounded'>"+
            "</td>" +
            "<td><input type='text' name='product_srot["+product_id+"]' class='product_order' size='5'></td>" +
            "<td><input type='button' value='删除' onclick='deleteok(this)' class='btn'/></td>" +
        "</tr>");
});
// 删除商品
function deleteok(obj){
    $(obj).parent().parent().remove();
}
