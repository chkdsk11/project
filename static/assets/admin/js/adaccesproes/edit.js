//上传图片
$(document).on('change', '#first_image_upload', function(){
    uploadFile('/adaccesories/upload', $(this).attr('id'), $(this).attr('data-img'));
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
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});


    $('#channel').change(function () {

        var type = $("select[name='channel'] option:checked").val();
        switch (type){
            case '0':
                $('#for_app').show();
                $('#for_wap').show();

                break;
            case '1':
                $('#for_wap').hide();
                $('#for_app').show();
                break;
            case '2':
                $('#for_app').hide();
                $('#for_wap').show();
                break;
        }
    });

    $('#position').change(function () {
        $('#PD').hide();
        $('#TJ').hide();
        $('#TD').hide();
        var type = $("select[name='position'] option:checked").data('type');
        var html ='';
        switch (type){
            case 0:
                $('#TJ').show();
                html  +='<option value="1" selected = "selected" >商品推荐</option>';
                html  +='<option value="2">图片广告</option>';

                break;
            case 1:
                $('#TJ').show();
                html  ='<option value="1" selected = "selected" >商品推荐</option>'
                break;
            default:
                $('#PD').show();
                html  ='<option value="2" selected = "selected" >图片广告</option>'
                break;
        }
        $("#advertisement_type").html(html);
    });

    $('#advertisement_type').change(function () {
        $('#TJ').hide();
        $('#PD').hide();
        switch ($(this).val()){
            case '1':
                $('#TJ').show();
                break;
            case '2':
                $('#PD').show();
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
        var ad_position = $("#position").val();
        var advertisement_type = $("#advertisement_type").val();
        var channel = $("select[name='channel'] option:checked").val();
        var product_status = 0;
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
            case '2': //图片广告
                var first_image = $("input[name='first_image']").val();
                var app_location = $("input[name='app_location']").val();
                var wap_location = $("input[name='wap_location']").val();
                var sort = $("input[name='sort']").val();

                if(!first_image){
                    layer_required('请上传广告图片！');return false;
                }
                if(channel==0){
                    if(!app_location){
                        layer_required('请填写app目标地址！');return false;
                    }
                    if(!wap_location){
                        layer_required('请填写wap目标地址！');return false;
                    }
                }else if(channel==1){
                    if(!app_location){
                        layer_required('请填写app目标地址！');return false;
                    }
                }else if(channel==2){

                    if(!wap_location){
                        layer_required('请填写wap目标地址！');return false;
                    }
                }
                break;
            case '1'://商品推荐
                var sort_pro = $(".product_order");
                var tr_list = $("#product_list tbody tr");
                var len = tr_list.length;

                if (len < 2) {
                    layer_required('请先添加商品！');
                    product_status =1;
                }
                sort_pro.each(function(){
                    if (!$(this).val()) {
                        layer_required('请输入商品的排序！');
                        product_status =1;
                    }

                    if (isNaN($(this).val())) {
                        layer_required('排序请输入数字！');
                        product_status =1;
                    }
                });
                $('.product_image').each(function(){
                    if (!$(this).val()) {
                        layer_required('请添加商品图片！');
                        product_status =1;
                    }
                });
                break;
        }
        if(product_status==1){
            return false;
        }
        if(!action_description){
            layer_required('请输入活动说明！'); return false;
        }
        ajaxSubmit('addAdForm');
    });

});


function searchgoods(){
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
        "<tr>" +
        "<td class='pid'>"+product_id+"</td>" +
        "<input type='hidden' name='product_id[]' value='"+product_id+"'/>" +
        "<td>"+product_name+"</td>" +
        "<td><input type='file' class='upload_image' id='input_"+product_id+"' data-id='"+product_id+"' name='product_image1' value='上传' class='product_image'>" +
        "<img style='width: 120px; height: 120px;' id='img_"+product_id+"' scr=''/><input type='hidden'  class='product_image' id='p_"+product_id+"' value='' name='product_image[]'>" +
        "</td>" +
        "<td><input type='text' name='order_product[]' class='product_order' size='5'></td>" +
        "<td><input type='button' value='删除' onclick='deleteok(this)' class='btn'/></td>" +
        "</tr>");
});
// 删除商品
function deleteok(obj){
    $(obj).parent().parent().remove();
}
