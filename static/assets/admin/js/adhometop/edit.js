//上传图片
$(document).on('change', '.new_file', function(){
    var fileId = $(this).attr('id')
    var id = $(this).attr('data-id');
    $.ajaxFileUpload({
        url: '/ad/upload',
        secureuri: false,
        fileElementId: fileId,
        dataType: 'json',
        success: function (data, status) {
            if(data.status == 'success') {
                $('#image_url'+id).attr('src',data['data'][0]['src']);
                $('#file_url'+id).val(data['data'][0]['src']);
            } else {
                alert(data.info);
            }
        },
        error: function (data, status, e) {
            alert(e);
        }
    });
});

$(document).on('change', '.old_file', function(){
    var fileId = $(this).attr('id')
    var id = $(this).attr('data-id');
    $.ajaxFileUpload({
        url: '/ad/upload',
        secureuri: false,
        fileElementId:fileId,
        dataType: 'json',
        success: function (data, status) {
            if(data.status == 'success') {
                $('#oldimage_'+id).attr('src',data['data'][0]['src']);
                $('#oldimage_url_'+id).val(data['data'][0]['src']);
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
    var t =1;
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step: 10});
    $('#add_img').bind('click', function() {
        var label = '<tr>';
        label += '<td class="text-center"><input type="file" id="upload_file'+t+'" data-id="'+t+'"  name="upload_file[]" class="new_file" value="上传" /><input type="hidden" id="file_url'+t+'" name="image_url[]" value=""/></td>';
        label += '<td class="text-center"><input name="location[]" class="location" style="width:350px;" /></td>';
        label += '<td class="text-center"><img src="" style="width: 120px; height:120px;" id="image_url'+t+'" /></td>';
        label += '<td class="text-center"><input name="sort[]" style="width:50px;" class="sort" /></td>';
        label += '<td class="text-center"><input type="button" value="删除" onclick="deleteok(this)" class="btn"/></td>';
        label += '</tr>';
        $('#ad_imgs').append(label);
        t = t+1;
    });

    $('#addAd').click(function(){
        var name = $("input[name='name']").val();
        if (!name || name == "") {
            layer_required('活动名称不能为空！');return false;
        }

        var start_time = $("input[name='start_time']").val();
        var end_time = $("input[name='end_time']").val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var nowTime = new Date().getTime();

        //验证时间合法性
        if(!$.trim(start_time) || !$.trim(end_time)){
            layer_required('开始时间或结束时间不能为空！');return false;
        }
        if(startTime > endTime){
            layer_required('开始时间不能大于结束时间！');return false;
        }
        // if(endTime < nowTime){
        //     layer_required('结束时间不能小于当前时间！');return false;
        // }

        //验证是否有广告图
        var tr_list = $("#ad_imgs tr");
        var len = tr_list.length;
        if (len < 2) {
            layer_required('最少要添加一张广告图！');return false;
        }
        //验证是否有选择图片
        var num = 0;
        var new_file = $(".new_file");
        new_file.each(function(){
            if($(this).val() == "") {
                num++;
            }
        });
        if (num > 0) {
            layer_required('有'+num+'个广告图没有选择图片，请选择');return false;
        }
        //验证广告图目标地址
        var not_location = 0;
        var location_ad = $(".location");
        location_ad.each(function(){
            if (!$(this).val()) {
                not_location = 1;
                layer_required('请输入目标地址');return false;
            }
        });
        if (not_location == 1) {
            return false;
        }
        //验证广告图排序
        var not_ort = 0;
        var sort_ad = $(".sort");
        sort_ad.each(function(){
            if (!$(this).val()) {
                not_ort = 1;
                layer_required('请输入排序');return false;
                return false;
            }
            if (isNaN($(this).val())) {
                not_ort = 1;
                layer_required('排序请输入数字');return false;
                return false;
            }
        });
        if (not_ort == 1) {
            return false;
        }
        ajaxSubmit('addAdForm');
	});
});

// 删除广告图片
function deleteok(obj){
    $(obj).parent().parent().remove();
}

function deletad(id){
    layer_confirm('', '/adhometop/delad', {id: id});
}
