/**
 * Created by Administrator on 2016/8/26.
 */
//上传单张图片
$(document).on('change', '#move_logo,#move_share', function(){
    uploadFile('/groupact/upload', $(this).attr('id'), $(this).attr('data-img'));
});
//上传批量图片
$(document).on('change', '.move_image', function(){
    if(filesize(this)){
        uploadFileMore('/groupact/upload', $(this).attr('id'));
    }
});

//判断上传图片是否符合要求
function filesize(ele) {
    var type = 'jpeg,jpg,png,gif';
    // 返回 KB，保留小数点后两位
    var i= 0,len=ele.files.length;
    for( i ; i < len ; i++ ){
        if( type.indexOf(ele.files[i].type.replace('image/','')) < 0){
            layer_required('文件类型错误');
            return false;
        }
        if( (ele.files[i].size / 1024).toFixed(2) > 2048 ){
            layer_required('文件超出大小限制');
            return false;
        }
    }
    return true;
}

/**
 * 封装多图片ajax上传
 * @param url 上传地址
 * @param fileId 上传文本框的name
 * @author 梁伟
 */
function uploadFileMore(url, fileId)
{
    $.ajaxFileUpload({
        url: url,
        secureuri: false,
        fileElementId: fileId,
        dataType: 'json',
        success: function (data, status) {
            if(data.status == 'success') {
                for(var i=0;i<data['data'].length;i++){
                    $('#'+fileId).parent().siblings('ul').append('<li><img src="'+data['data'][i]['src']+'" ><a href="javascript:;" class="close-btn">&times;</a><input type="hidden" name="goods_slide_images[]" value="'+data['data'][i]['src']+'"></li>');
                }
            } else {
                alert(data.info);
            }
        },
        error: function (data, status, e) {
            alert(e);
        }
    });
}

$(function () {
    //---------------初始化是否抽奖等状态start-------------------
    if ($("input[name='gfa_type']:checked").val() == 1) { //判断抽奖
        $("#draw").show();
    }
    if ($("input[name='gfa_way']:checked").val() == 1) {
        $("#drawway-check").show();
        $("#drawscale").show();
        $("#drawnum").hide();
        $("input[name='draw_num']").val('');
    } else if ($("input[name='gfa_way']:checked").val() == 2) {
        $("#drawway-check").show();
        $("#drawscale").hide();
        $("#drawnum").show();
        $("input[name='draw_scale']").val('');
    }
    //根据抽奖改变状态
    $("#checkdraw input").click(function () {
        var draw = $(this).val();
        if (draw == 1) {
            $("#draw").show();
        } else {
            $("#draw").hide();
        }
    });
    $("#drawway input").click(function () {
        var drawway = $(this).val();
        $("#drawway-check").show();
        if (drawway == 1) {
            $("#drawscale").show();
            $("#drawnum").hide();
        } else {
            $("#drawscale").hide();
            $("#drawnum").show();
        }
    });
    //---------------初始化是否抽奖等状态end-------------------

    //---------------表单提交start-----------------------------
    $("#actSubmit").click(function(){
        var url = $(this).attr('data-url');
        if(!url){
            alert('缺少表单提交url');
            return false;
        }
        if ($.trim($("input[name='gfa_name']").val()) == '') {
            alert('活动名称不能为空');
            return false;
        }
        if ($.trim($("input[name='gfa_starttime']").val()) == '') {
            alert('开始时间不能为空');
            return false;
        }
        if ($.trim($("input[name='gfa_endtime']").val()) == '') {
            alert('结束时间不能为空');
            return false;
        }
        if ($.trim($("input[name='gfa_starttime']").val()) >= $.trim($("input[name='gfa_endtime']").val())) {
            alert('开始时间不能大于等于结束时间');
            return false;
        }
        if (!validate($("input[name='gfa_user_count']").val())) {
            alert('请输入正确格式的成团人数');
            return false;
        }
        if ($("input[name='gfa_user_count']").val() <= 1 || $("input[name='gfa_user_count']").val() > 10) {
            alert("成团人数必须在2~10之间");
            return false;
        }
        if (!validate($("input[name='gfa_cycle']").val())) {
            alert("请输入正确格式的组团周期");
            return false;
        }
        if (!validate($("input[name='gfa_allow_num']").val())) {
            alert("请输入正确格式的用户参团次数");
            return false;
        }
        if ($("input[name='gfa_type']:checked").val() == 1) { //判断抽奖
            if ($("input[name='gfa_way']:checked").val() == 1) {
                if (!validate($("input[name='draw_scale']").val())) {
                    alert("请输入不大于100的正整数中奖率");
                    return false;
                }
                if($("input[name='draw_scale']").val() > 100){
                    alert("请输入不大于100的正整数中奖率");
                    return false;
                }
            } else if ($("input[name='gfa_way']:checked").val() == 2) {
                if (!validate($("input[name='draw_num']").val())) {
                    alert("请输入正整数中奖个数");
                    return false;
                }
            } else {
                alert('请选择中奖方式');
                return false;
            }
        }
        if ($.trim($("select[name='goods_id'] option:selected").val()) <= 0) {
            alert('请选择商品');
            return false;
        }
        if ($.trim($("input[name='goods_name']").val()) == '' || $("input[name='goods_name']").val().length > 40) {
            alert('商品标题不能为空且在40字以内');
            return false;
        }
        if ($.trim($("input[name='goods_introduction']").val()) == '' || $("input[name='goods_introduction']").val().length > 56) {
            alert('商品卖点不能为空且在56字以内');
            return false;
        }
        if (!validate($("input[name='gfa_sort']").val())) {
            alert('请输入正确格式的权重');
            return false;
        }
        if ($.trim($("input[name='gfa_price']").val()) <= 0 || !validatefloat($("input[name='gfa_price']").val())) {
            alert('请输入正确格式的大于0的拼团价格');
            return false;
        }
        if (!validate($("input[name='gfa_num_init']").val())) {
            alert('请输入正确格式的已开团数');
            return false;
        } else if ($.trim($("input[name='share_title']").val()) == '') {
            alert('分享标题不能为空');
            return false;
        }
        if ($.trim($("input[name='share_content']").val()) == '') {
            alert('分享内容不能为空');
            return false;
        }
        $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: $("#myform").serialize()
            })
            .done(function (data) {
                if (data.status == 'success') {
                    location.href = '/groupact/list';
                } else {
                    alert(data.info);
                }
            });
        });
    //数字验证
    function validate(obj) {
        var reg = new RegExp("^[0-9]*$");
        if (obj && reg.test(obj)) {  //非数字
            return true;
        } else {
            return false;
        }
    }
    function validatefloat(obj) {
        var reg = new RegExp("\\d+\\.\\d+");
        if (validate(obj)) {
            return true;
        }
        if (obj && reg.test(obj)) {
            return true;
        } else {
            return false;
        }
    }
    //---------------表单提交end-------------------------------
    //搜索商品
    $("#searchGoods").click(function(){
        var data = {};
        data.goods = $('#goods').val();
        if ($.trim(data.goods) == '')
        {
            return false;
        }
        $.ajax({
            url: "/groupact/search",
            type: 'POST',
            dataType: 'json',
            data: data
        }).done(function (gdata) {
            $('#goods_id').empty();
            if(gdata.status == 'success'){
                var string = [];
                var goods = gdata.data;
                for (var i in goods)
                {
                    string.push('<option value="' + goods[i].id + '">' + goods[i].goods_name + '</option>');
                }
                $('#goods_id').append(string.join(''));
            }
        });
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        layer_confirm('确定要删除活动吗？', '/groupact/del', {id: $(this).attr('data-id')});
    });
    //取消操作
    $('.row').on('click', '.cancel', function(){
        layer_confirm('确定要取消活动吗？取消拼团抽奖会按原设置好的时间和抽奖规则进行抽奖。', '/groupact/cancel', {id: $(this).attr('data-id')});
    });
    //删除图片
    $("ul.list").on("click",".close-btn",function(){
        $(this).parent('li').remove();
    })
});
