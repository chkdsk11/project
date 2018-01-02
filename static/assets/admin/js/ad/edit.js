$(document).on('change', '#first_image_upload', function(){
    uploadFile('/ad/upload', $(this).attr('id'), $(this).attr('data-img'));
});
//上传图片
$(document).on('change', '.upload_image', function(){
    var fileId = $(this).data('id');
    $.ajaxFileUpload({
        url: '/adaccesories/upload',
        secureuri: false,
        fileElementId: 'input_'+fileId,
        dataType: 'json',
        success: function (data, status) {
            if(data.status == 'success') {
                $('#img_' + fileId).attr('src', data['data'][0]['src']).show();
                $('#p_' + fileId).val(data['data'][0]['src']);
            } else {
                alert(data.info);
            }
        },
        error: function (data, status, e) {
            alert(e);
        }
    });
});

$(function() {
    //颜色插件
    $('#picker').ColorPicker({
        onChange:function(hsb,hex){
            $('#picker').css('backgroundColor','#'+hex).val('#'+hex);
        }
    });
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});

    $('#advertisement_type').change(function () {
        $('#PD').hide();
        $('#TJ').hide();
        $('#TD').hide();
        switch ($(this).val()){
            case '1':
                $('#PD').show();
                break;
            case '2':
                $('#TJ').show();
                break;
            case '3':
                $('#TD').show();
                break;
        }
    });

    $('#addAd').on('click',function(){
        var action_name = $("input[name='action_name']").val();
        var start_time = $("input[name='start_time']").val();
        var end_time = $("input[name='end_time']").val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var nowTime = new Date().getTime();
        var action_description = $("#action_description").val();
        var is_default = $("input[name='is_default']").val();
        var ad_position = $(".sku_menu_row1");
        var adp_ids = new Array();
        var advertisement_type = $("select[name='advertisement_type'] option:checked").val();
        var ajax_status = true;


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

        /****** 验证使用范围start *****/
        switch (advertisement_type) {
            case '1': //图片广告
                var first_image = $("input[name='first_image']").val();
                var second_image = $("input[name='second_image']").val();
                var background = $("input[name='background']").val();
                var slogan_image = $("input[name='slogan_image']").val();
                var location_image = $("input[name='location_image']").val();
                var order_image = $("input[name='order_image']").val();
                if(!first_image && !second_image){
                    layer_required('上传或填写URL二选一！');return false;
                }
                if (second_image && second_image.length > 100) {
                    layer_required('图片URL请不要输入超过100个字符！');return false;
                }
                if(!background){
                    layer_required('请选择背景颜色！');return false;
                }
                if(!slogan_image){
                    layer_required('请填写广告语！');return false;
                }
                if (slogan_image.length > 30) {
                    layer_required('广告语请不要输入超过30个字符！');return false;
                }
                if(!background){
                    layer_required('请选择背景颜色！');return false;
                }
                if (background.length > 7) {
                    layer_required('背景颜色请不要输入超过7个字符！');return false;
                }
                if(!location_image){
                    layer_required('请填写目标地址！');return false;
                }

                if(!order_image){
                    layer_required('请填写目标排序！');return false;
                }
                if(isNaN(order_image)){
                    layer_required('排序请输入数字！');return false;
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

                // 验证提交的商品是否满足广告位的个数要求
                $.ajax({
                    type:"post",
                    url:"/ad/ajax_check_product_num",
                    data:{"ad_position":adp_ids,"product_num":len-1},
                    dataType:"json",
                    async:false,
                    success:function(msg) {
                        if (msg.code == 300) {
                            layer_required('该广告位需添加' + msg.num + '个商品！');
                            ajax_status = false;
                        }
                    }
                });// $.ajax({}) END
                break;
            case '3'://文字推荐
                if (!$('#slogan_text').val()) {
                    layer_required('请输入文字广告！');
                    return false;
                }
                if ($('#slogan_text').val().length > 10) {
                    layer_required('文字广告字符数量不超过10个');
                    return false;
                }
                if (!$('#location_text').val()) {
                    layer_required('请输入文字广告目标地址！');
                    return false;
                }
                if ($('#location_text').val().length > 100) {
                    layer_required('目标地址字符数量不超过100个');
                    return false;
                }
                if (!$('#order_text').val()) {
                    layer_required('请输入文字广告排序！');
                    return false;
                }
                if (isNaN($('#order_text').val())) {
                    layer_required('请输入数字的目标排序！');
                    return false;
                }
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
    var type =$('#is_global').val();
    if(!search){
        layer_required('请输入正确的商品id！');
        return false;
    }
    $.post('/ad/searchgoods',{search:search,is_global:type},function (data) {
        if(data.list){
            $('#search_result').html("<option value=''>-选择商品-</option>");
            $.each(data.list, function(i, item) {
                $('#search_result').append("<option value='" + item.id + "'>" + item.goods_name + "</option>");
            });
        }else{
            layer_required('没有搜索到该上架商品！');
            $('#search_result').empty();
            return false;
        }
    });
}

// 添加产品按钮
$('#add_product').bind('click', function() {
    var product_id = $('#search_result').val();
    var product_name = $('#search_result option:selected').html();
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
        "<tr>" +
        "<td class='pid'>"+product_id+"</td>" +
        "<input type='hidden' name='product_id[]' value='"+product_id+"'/>" +
        "<td>"+product_name+"</td>" +
        "<td><input type='file' class='upload_image' id='input_"+product_id+"' data-id='"+product_id+"' name='product_image1' value='上传' class='product_image'>" +
        "<img style='width: 120px; height: 120px;' id='img_"+product_id+"' scr=''/><input type='hidden'  class='product_image' id='p_"+product_id+"' value='' name='product_image[]'>" +
        "</td>" +
        "<td><input type='text' name='order_product["+product_id+"]' class='product_order' size='5'></td>" +
        "<td><input type='button' value='删除' onclick='deleteok(this)' class='btn'/></td>" +
        "</tr>");
});

// 删除商品
function deleteok(obj){
    $(obj).parent().parent().remove();
}
