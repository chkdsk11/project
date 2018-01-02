/**
 * @desc 优惠券添加编辑
 * @author 邓永军
 */
$(function () {
    $('.datetimepk').datetimepicker({
        step: 10
    });
});
checkAll($("#use_platform_switch"), $("input[name='use_platform[]']"));
$("input[name='use_platform[]']").on("click", function () {
    var $this = $(this);
    if ($this.prop("checked") == false) {
        if ($("#use_platform_switch").prop("checked")) {
            $("#use_platform_switch").prop("checked", false);
        }
    } else {
        var temp_flag = 0;
        $("input[name='use_platform[]']").each(function () {
            if ($(this).prop("checked") == false) {
                temp_flag = 1;
                return false;
            }
        });
        if (temp_flag == 0) {
            $("#use_platform_switch").prop("checked", true);
        }
    }
});
$("input[name=coupon_type]").each(function () {
    if ($(this).prop("checked")) {
        switch ($(this).val()) {
            case "1":
                $("#vt_area_a").html($("#fullcut_vt").html());
                break;
            case "2":
                $("#vt_area_a").html($("#discout_vt").html());
                break;
            case "3":
                $("#vt_area_a").html($("#freepacket_vt").html());
                break;
        }
    }
})
$("input[name=coupon_type]").on("click", function () {
    var $this = $(this);
    switch ($this.val()) {
        case "1":
            $("#vt_area_a").html($("#fullcut_vt").html());
            break;
        case "2":
            $("#vt_area_a").html($("#discout_vt").html());
            break;
        case "3":
            $("#vt_area_a").html($("#freepacket_vt").html());
            break;
    }
});


var add_tag = function (tel, obj) {
    if ($.trim($(obj).text()) == "") $(obj).text("");
    var o_flag = 0;
    $("#tags_area > .tag").each(function () {
        var tel_temp = $(this).data("tel");
        if (tel == tel_temp) {
            o_flag = 1;
            layer_error("手机号码已经重复,请重新输入");
            return false;
        }
    });
    if (o_flag == 0) {
        $(obj).append('<span class="tag" data-tel="' + tel + '">' + tel +
        '<button type="button" class="close close_tag" onclick="close_tag($(this))">×</button></span>');
    }
}

function y1(v){

    var re = /^[0-9]+.?[0-9]*$/;
    if(!re.test(v)){
        $("input[name=coupon_value]").val("");
        layer_required("必须输入数字");
    }else{
        if(v<=0){layer_required("优惠券金额不能为0");$("input[name=coupon_value]").val("");return false;}
        var rezzs=/^[1-9]+[0-9]*]*$/;
        if(!rezzs.test(v)){
            if(!regMoney.test(v)){
                $("input[name=coupon_value]").val("");
                layer_required("优惠券金额不能大于2位小数");
            }
        }
        if($("input[name=coupon_value]").val()!= "" && $("input[name=min_cost]").val()!= ""){
            if(parseFloat($("input[name=coupon_value]").val()) > parseFloat($("input[name=min_cost]").val())){
                $("input[name=coupon_value]").val("");
                layer_required("优惠券金额不能超过订单金额");
            }
        }
    }
}
function y2(v){
    var re = /^[0-9]+.?[0-9]*$/;
    if(!re.test(v)){
        $("input[name=min_cost]").val("");
        layer_required("必须输入数字");
    }else{
        if(v<=0){layer_required("订单金额不能为0");$("input[name=min_cost]").val("");return false;}
        var rezzs=/^[1-9]+[0-9]*]*$/;
        if(!rezzs.test(v)){
            if(!regMoney.test(v)){
                $("input[name=min_cost]").val("");
                layer_required("订单金额不能大于2位小数");
            }
        }
        if($("input[name=coupon_value]").val()!= "" && $("input[name=min_cost]").val()!= ""){
            if(parseFloat($("input[name=coupon_value]").val()) > parseFloat($("input[name=min_cost]").val())){
                layer_required("优惠券金额不能超过订单金额");
                $("input[name=min_cost]").val("");
            }
        }
    }
}
function y3(v){
    var re = /^[0-9]+.?[0-9]*$/;
    if(!re.test(v)){
        $("input[name=coupon_value]").val("");
        layer_required("必须输入数字");
    }else{
        if(v<=0){layer_required("优惠券折扣不能为0");$("input[name=coupon_value]").val("");return false;}
        var rezzs=/^[1-9]+[0-9]*]*$/;
        if(v<=0 || v>=10){
            $("input[name=coupon_value]").val("");
            layer_required("折扣必须在0~10之间");
        }
        if(!rezzs.test(v)){
            if(!regDiscount.test(v)){
                $("input[name=coupon_value]").val("");
                layer_required("折扣不能大于1位小数");
            }
        }
    }
}
function y4(v){
    var discount_unit=$("#discount_unit").val();
    switch (discount_unit){
        case "1":
            var re = /^[0-9]+.?[0-9]*$/;
            if(!re.test(v)){
                $("input[name=min_cost]").val("");
                layer_required("必须输入数字");
            }else{
                if(v<=0){layer_required("订单金额不能为0"); $("input[name=min_cost]").val("");return false;}
                var rezzs=/^[1-9]+[0-9]*]*$/;
                if(!rezzs.test(v)){
                    if(!regDiscount.test(v)){
                        $("input[name=min_cost]").val("");
                        layer_required("订单金额不能大于1位小数");
                    }
                }
            }
            break;
        case "2":
            var re = /^[0-9]+.?[0-9]*$/;
            if(!re.test(v)){
                $("input[name=min_cost]").val("");
                layer_required("必须输入数字");
            }else{
                if(v<=0){layer_required("商品件数不能为0"); $("input[name=min_cost]").val("");return false;}
                var rezzs=/^[1-9]+[0-9]*]*$/;
                if(!rezzs.test(v)){
                    if(!regNumber.test(v)){
                        $("input[name=min_cost]").val("");
                        layer_required("商品件数不能为小数");
                    }
                }
            }
            break;
    }

}
function y5(v){
    var re = /^[0-9]+.?[0-9]*$/;
    if(!re.test(v)){
        $("input[name=min_cost]").val("");
        layer_required("必须输入数字");
    }else{
        if(v<=0){layer_required("订单金额不能为0");$("input[name=min_cost]").val("");return false;}
        var rezzs=/^[1-9]+[0-9]*]*$/;
        if(!rezzs.test(v)){
            if(!regDiscount.test(v)){
                $("input[name=min_cost]").val("");
                layer_required("订单金额不能大于1位小数");
            }
        }
    }
}
function close_tag(e) {
    e.parent().remove();
    var tel_arr = [];
    $("#tags_area span.tag").each(function () {
        tel_arr.push($(this).data("tel"));
    });
    $("#tels").val(tel_arr.join(","));
    if ($.trim($("#tags_area").text()) == "") $("#tags_area").text("");
}
$("#add_tag_btn").on("click", function () {
    var tel = $("#add_tel").val();
    if (tel == "") {
        layer_required("请输入手机号码");
        return false;
    }
    if (!regMobliePhone.test(tel)) {
        layer_required("手机号码不符合规定");
        return false;
    }
    //判断系统是否有该用户
    $.post('/user/getUserIdByPhone',{phone:tel},function (res) {
        if(res == 'no'){
            layer_required('该用户不存在');
        }else{
            add_tag(tel, "#tags_area");
            var tel_arr = [];
            $("#tags_area span.tag").each(function () {
                tel_arr.push($(this).data("tel"));
            });
            $("#tels").val(tel_arr.join(","));
            $("#add_tel").val("");
        }
    });

});
$("#import_btn").on("click", function () {
    $("#import_tel_input").trigger("click");
});
$("#import_tel_input").change(function () {
    var _f = $(this).val();
    if (_f == "") {
        layer_error("还没选择文件");
        return false;
    } else {
        $('#import_tel').submit();
        $("#import_ifm").load(function () {
            var json_data = $(this).contents().find('body').html();
            var obj_data = $.parseJSON(json_data);
            var O_info = obj_data.data;
            if(obj_data.error == 0){
                $.each(O_info, function (i, v) {
                    add_tag(v, "#tags_area");
                })
                var tel_arr = [];
                $("#tags_area span.tag").each(function () {
                    tel_arr.push($(this).data("tel"));
                });
                $("#tels").val(tel_arr.join(","));
            }else{
                var O_info = obj_data.info;
                $.each(O_info, function (i, v) {
                    add_tag(v, "#tags_area");
                })
                var tel_arr = [];
                $("#tags_area span.tag").each(function () {
                    tel_arr.push($(this).data("tel"));
                });
                $("#tels").val(tel_arr.join(","));
                layer_required(obj_data.data);
            }
            $('#import_tel')[0].reset();
        });
    }
});


$('textarea[class*=autosize]').autosize({
    append: "\n"
});



$("#app_url").change(function () {
    var url_value = $(this).val();
    if(!appUrl.test(url_value)){
        layer_required('链接格式出错');
        $(this).val('');
    }
});
$("#wap_url").change(function () {
    var url_value = $(this).val();
    if(!regUrl.test(url_value)){
        layer_required('链接格式出错');
        $(this).val('');
    }
});
$("#pc_url").change(function () {
    var url_value = $(this).val();
    if(!regUrl.test(url_value)){
        layer_required('链接格式出错');
        $(this).val('');
    }
});
$("#wechat_url").change(function () {
    var url_value = $(this).val();
    if(!regUrl.test(url_value)){
        layer_required('链接格式出错');
        $(this).val('');
    }
});
$('body').on('keyup','#coupon_number',function () {
    var nums = $(this).val();
    if( nums.length > 10){
        layer_required('位数不能大于10');
        $(this).val('');
    }
});

$('body').on('keyup','#limit_number',function () {
    var nums = $(this).val();
    if( nums.length > 10){
        layer_required('位数不能大于10');
        $(this).val('');
    }
});
$('body').on('keyup','input[name="coupon_value"]',function () {
    var nums = $(this).val();
    if( nums.length > 10){
        layer_required('位数不能大于10');
        $(this).val('');
    }
});
$('body').on('keyup','input[name="min_cost"]',function () {
    var nums = $(this).val();
    if( nums.length > 10){
        layer_required('位数不能大于10');
        $(this).val('');
    }
});
$("#group").change(function () {
    var getData = $(this).val();
    if (getData == 3) {
        $("#special_add").show(0);
    } else {
        $("#special_add").hide(0);
    }
    if (getData != 3) {
        $("#is_register_bonus").show(0);
    } else {
        $("#is_register_bonus").hide(0);
    }
})

$('body').on('keyup','#relative_validity',function () {
    var relative_validity = $(this).val();
    if(relative_validity.length > 0){
        if (!$.trim(relative_validity)) {
            $(this).val('');
            layer_required("相对有效期不能为空")
            return false;
        }
        if (parseInt(relative_validity) <= 0 ) {
            $(this).val('');
            layer_required("优惠券相对有效期不能设置为0天后过期");
            return false;
        }
    }
});

$("#do_ajax_sure_save_btn").on("click", function () {
    var thisBtn = $(this);
    var coupon_name = $("#coupon_name").val();
    if (coupon_name == "") {
        layer_required("优惠券名称不能为空");
        return false;
    }
    var start_provide_time = $("#start_provide_time").val();
    var start_provide_time_timestamp = new Date(start_provide_time).getTime();
    if (start_provide_time == "") {
        layer_required("开始发放时间不能为空");
        return false;
    }
    var end_provide_time = $("#end_provide_time").val();
    var end_provide_time_timestamp = new Date(end_provide_time).getTime();
    if (start_provide_time == "") {
        layer_required("结束发放时间不能为空");
        return false;
    }
    if(end_provide_time_timestamp<start_provide_time_timestamp){
        layer_required("结束发放时间不能早于开始发放时间");
        return false;
    }

    var validitytype = $("#validitytype").val();
    if (validitytype == "2") {
        var relative_validity = $("#relative_validity").val();
        if (!$.trim(relative_validity)) {
            layer_required("相对有效期不能为空")
            $("#relative_validity").val('')
            return false;
        }
        if (parseInt(relative_validity) <= 0 ) {
            layer_required("优惠券相对有效期不能设置为0天后过期");
            $("#relative_validity").val('')
            return false;
        }
    }else{
        var start_use_time = $("#start_use_time").val();
        var start_use_time_timestamp = new Date(start_use_time).getTime();

        if (start_use_time == "") {
            layer_required("优惠券开始时间不能为空");
            return false;
        }
        var end_use_time = $("#end_use_time").val();
        var end_use_time_timestamp = new Date(end_use_time).getTime();
        if (end_use_time == "") {
            layer_required("优惠券结束时间不能为空");
            return false;
        }
        if(end_use_time_timestamp<start_use_time_timestamp){
            layer_required("结束使用时间不能早于开始使用时间");
            return false;
        }
        if(start_use_time_timestamp < start_provide_time_timestamp){
            layer_required("开始使用时间不能早于开始发放时间");
            return false;
        }
        if(end_use_time_timestamp < start_provide_time_timestamp){
            layer_required("结束使用时间不能早于开始发放时间");
            return false;
        }
        if(end_use_time_timestamp < end_provide_time_timestamp){
            layer_required("结束使用时间不能早于结束发放时间");
            return false;
        }
    }

    var use_platform = $("input[name='use_platform[]']:checked").length;
    if (use_platform < 1) {
        layer_required("必须选中一个或者多个活动平台");
        return false;
    }
    var coupon_description = $("#coupon_description").val();
    if (coupon_description == "") {
        layer_required("活动说明不能为空");
        return false;
    }
    var coupon_type = $("input[name='coupon_type']:checked").val();
    switch (coupon_type) {
        case "1":
            var coupon_value = $("input[name='coupon_value']").val();
            var min_cost = $("input[name='min_cost']").val();
            if(coupon_value.length < 1){
                layer_required("请输入优惠券金额");
                return false;
            }
            if (!regMoney.test(coupon_value)) {
                layer_required("满减券只能输入数字");
                return false;
            }
            if(min_cost.length < 1){
                layer_required("请输入订单金额");
                return false;
            }
            if (!regMoney.test(min_cost)) {
                layer_required("订单金额只能输入数字");
                return false;
            }
            if (coupon_value == "" || min_cost == "") {
                layer_required("满减券必须填写优惠券金额和最低订单金额");
                return false;
            }
            break;
        case "2":
            var coupon_value = $("input[name='coupon_value']").val();
            var min_cost = $("input[name='min_cost']").val();
            var discount_unit = $('#discount_unit').val();
            if(coupon_value.length < 1){
                layer_required("请输入优惠券金额");
                return false;
            }
            if(!regDiscount.test(coupon_value)){
                layer_required("折扣券只能输入一位小数");
                return false;
            }
            if(min_cost.length < 1){
                layer_required("请输入订单金额");
                return false;
            }
            if(discount_unit == 1){
                //元
                if(!regMoney.test(min_cost)){
                    layer_required("订单金额只能输入数字");
                    return false;
                }
            }else{
                //件
                if(!regNumber.test(min_cost) && min_cost < 1){
                    layer_required("商品件数只能输入数字且必须等于等于1");
                    return false;
                }
            }
            if (coupon_value == "" || min_cost == "") {
                layer_required("折扣券必须填写折扣券金额和最低订单金额/商品件数");
                return false;
            }
            break;
        case "3":
            var min_cost = $("input[name='min_cost']").val();
            if(min_cost.length < 1){
                layer_required("订单金额不能为空");
                return false;
            }
            if (!regMoney.test(min_cost)) {
                layer_required("订单金额只能输入数字");
                return false;
            }
            if (min_cost == "") {
                layer_required("包邮券必须填写最低订单金额");
                return false;
            }
            break;
    }
    var channel_id = $("#channel_id").val();
    if (channel_id=="选择渠道") {
        layer_required("渠道必须选择");
        return false;
    }
    if ($("#coupon_number").val().length < 1) {
        $("#coupon_number").val(0);
        return false;
    }
    var coupon_number = $("#coupon_number").val();
    if (!regNumber.test(coupon_number)) {
        layer_required("发放张数只能输入数字");
        return false;
    }
    if (coupon_number < 1 && coupon_number != 0) {
        layer_required("发放张数必须填写,最低不少于1");
        return false;
    }
    var limit_number = $("#limit_number").val();
    if(limit_number.length < 1){
        layer_required("每人限领取张数不能为空");
        return false;
    }
    if (!regNumber.test(limit_number)) {
        layer_required("每人限领取领取的数量只能为正整数");
        return false;
    }
    if (parseInt(limit_number) < 1) {
        layer_required("每人限领取张数必须填写,最低为1");
        return false;
    }
    if(parseInt(limit_number) > 100){
        layer_required('每人限制领取100张');
        $("#limit_number").val('');
        return false;
    }
    if((limit_number-coupon_number)>0 && coupon_number != 0){
        layer_required("每人限领取张数不可大于发放张数");
        return false;
    }
    var group = $("#group").val();
    /*if (group == 1) {
        if ($("input[name='register_bonus']").is(":checked") == false) {
            layer_required("新用户必须选择是否注册发放");
            return false;
        }
    }*/
    if (group == 3) {
        var tels = $("#tels").val();
        if (tels == "") {
            layer_required("选择特定用户必须输入手机号码或者导入号码列表");
            return false;
        }
    }
    var use_range = $("#use_range").val();
    var result_flag = 1;
    switch (use_range) {
        case "all":
            var category_ids = $("#category_set").val();
            var brand_ids = $("#brand_set").val();
            var goods_ids = $("#goods_set").val();

            //检查品类
            if (category_ids != "") {
                //验证格式
                var ch = category_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        result_flag = 0;
                        layer_required("品类格式不对，请重新输入");
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            result_flag = 0;
                            layer_required("品类重复id，请重新输入");
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "category",
                    ids: category_ids
                }, function (data) {
                    if (data == "0") {
                        result_flag = 0;
                        layer_required("品类id列表输入有误,请检查重新输入");
                        return false;
                    }
                });
            }
            //检查品牌
            if (brand_ids != "") {
                //验证格式
                var ch = brand_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("品牌格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("品牌重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "brand",
                    ids: brand_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("品牌id列表输入有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            //检查商品
            if (goods_ids != "") {
                //验证格式
                var ch = goods_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("商品格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("商品重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "single",
                    ids: goods_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("商品id列表有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            break;
        case "category":
            var brand_ids = $("#brand_set").val();
            var goods_ids = $("#goods_set").val();
            var hiddenUseRange = $("#hiddenUseRange").val();
            if (hiddenUseRange == "") {
                layer_required("必须选择分类");
                result_flag = 0;
                return false;
            }
            //检查品牌
            if (brand_ids != "") {
                //验证格式
                var ch = brand_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("品牌格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("品牌重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "brand",
                    ids: brand_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("品牌id列表输入有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            //检查商品
            if (goods_ids != "") {
                //验证格式
                var ch = goods_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("商品格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("商品重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "single",
                    ids: goods_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("商品id列表有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            break;
        case "brand":
            var goods_ids = $("#goods_set").val();
            var shop_brand = $("input[name='shop_brand']").val();
            if (shop_brand == "") {
                layer_required("必须输入品牌相关信息");
                result_flag = 0;
                return false;
            }
            //检查商品
            if (goods_ids != "") {
                //验证格式
                var ch = goods_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("商品格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("商品重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "single",
                    ids: goods_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("商品id列表有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            break;
        case "single":
            var hiddenUseRange = $("#hiddenUseRange").val();
            if (hiddenUseRange == "") {
                layer_required("必须选择商品");
                result_flag = 0;
                return false;
            }

            break;
    }
    if(result_flag == 0)return false;
    var provide_type3=$("#provide_type").val();
    if(provide_type3 == 2 || provide_type3 ==3){

        if(coupon_number == 0){
            layer_required("激活码与统一码发放数量不能设置为0");
            return false;
        }
        var codeNum = $("#codeNum").val();
        var tmpNum = coupon_number-codeNum;
        if(provide_type3 == 3){
            if($("input[name=create_min_num]").val()>tmpNum){
                layer_required("激活码只剩下"+tmpNum+"个,数量不足,你可修改发放张数以增加激活码剩余量");
                return false;
            }
        }else{
            if($("input[name=create_min_num]").val() > 1){
                layer_required("统一码只能输入一张");
                return false;
            }
        }

    }
    if($("input[name=is_activecode]").attr("checked")=="checked"){
        var create_min_num = $("input[name=create_min_num]").val();
        if(create_min_num == ""){
            layer_required("生成数量不能为空");
            return false;
        }else{
            if(create_min_num > 30000){
                layer_required("一次生成数量不能超过30000");
                return false;
            }
        }
    }
    var v1 = $('input[name=coupon_value]').val();
    var v2 = $('input[name=min_cost]').val();
    if(checkO.test(v1) || checkO.test(v2)){
        layer_required('金额格式错误');
        return false;
    }
    var app_url = $("#app_url").val();
    var wap_url = $("#wap_url").val();
    var pc_url = $("#pc_url").val();
    var wehchat_url = $("#wechat_url").val();
    /*if(!appUrl.test(app_url) && app_url.length > 0){
        layer_required('链接格式出错');
        $("#app_url").val('');
        return false;
    }*/
    if(!regUrl.test(wap_url) && wap_url.length > 0){
        layer_required('链接格式出错');
        $("#wap_url").val('');
        return false;
    }
    /*if(!regUrl.test(pc_url) && pc_url.length > 0){
        layer_required('链接格式出错');
        $("#pc_url").val('');
        return false;
    }*/
    if(!regUrl.test(wehchat_url) && wehchat_url.length > 0){
        layer_required('链接格式出错');
        $("#wehchat_url").val('');
        return false;
    }

    layer.msg('正在发送数据中,请稍等');
    var form = $('#edit_coupon');
    var url = form.attr('action');
    var data = {};
    thisBtn.attr('disabled','disabled');
    data = form.serializeArray();
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        cache: false,
        dataType:'json',
        success: function (msg) {
            if(msg.status == 'error'){
                layer.load(20, {time: 1});
                layer.msg(msg.info, {shade: 0.3, time: 1000});
                setTimeout(function () {
                    $('#do_ajax_sure_save_btn').removeAttr('disabled');
                },2000);
                return false;
            }else if(msg.status == 'success'){
                parent.layer.msg(
                    msg.info,
                    {shade: 0.3, time: time},
                    function(){
                        layer.load(20, {
                            shade: [0.3,'#ffffff'] //0.1透明度的白色背景
                        });
                        parent.window.location.href = msg.url; return true;
                    }
                )
            }
        },
        error: function () {
            layer.load(20, {time: 1});
            thisBtn.removeAttr('disabled');
            layer.msg('操作失败!');return false;
        }
    });
});




//提交表单
$("#do_ajax_sure_btn").on("click", function () {
    var thisBtn = $(this);
    //thisBtn.attr('disabled','disabled');
    var coupon_name = $("#coupon_name").val();
    if (coupon_name == "") {
        layer_required("优惠券名称不能为空");
        return false;
    }
    var start_provide_time = $("#start_provide_time").val();
    var start_provide_time_timestamp = new Date(start_provide_time).getTime();
    if (start_provide_time == "") {
        layer_required("开始发放时间不能为空");
        return false;
    }
    var end_provide_time = $("#end_provide_time").val();
    var end_provide_time_timestamp = new Date(end_provide_time).getTime();
    if (start_provide_time == "") {
        layer_required("结束发放时间不能为空");
        return false;
    }
    if(end_provide_time_timestamp<start_provide_time_timestamp){
        layer_required("结束发放时间不能早于开始发放时间");
        return false;
    }
    var validitytype = $("#validitytype").val();
    if (validitytype == "2") {
        var relative_validity = $("#relative_validity").val();
        if (!$.trim(relative_validity)) {
            layer_required("相对有效期不能为空")
            $("#relative_validity").val('')
            return false;
        }
        if (parseInt(relative_validity) <= 0 ) {
            layer_required("优惠券相对有效期不能设置为0天后过期");
            $("#relative_validity").val('')
            return false;
        }
    }else{
        var start_use_time = $("#start_use_time").val();
        var start_use_time_timestamp = new Date(start_use_time).getTime();
        if (start_use_time == "") {
            layer_required("优惠券开始时间不能为空");
            return false;
        }
        var end_use_time = $("#end_use_time").val();
        var end_use_time_timestamp = new Date(end_use_time).getTime();
        if (end_use_time == "") {
            layer_required("优惠券结束时间不能为空");
            return false;
        }
        if(end_use_time_timestamp<start_use_time_timestamp){
            layer_required("结束使用时间不能早于开始使用时间");
            return false;
        }
        if(start_use_time_timestamp < start_provide_time_timestamp){
            layer_required("开始使用时间不能早于开始发放时间");
            return false;
        }
        if(end_use_time_timestamp < start_provide_time_timestamp){
            layer_required("结束使用时间不能早于开始发放时间");
            return false;
        }
        if(end_use_time_timestamp < end_provide_time_timestamp){
            layer_required("结束使用时间不能早于结束发放时间");
            return false;
        }
    }

    var use_platform = $("input[name='use_platform[]']:checked").length;
    if (use_platform < 1) {
        layer_required("必须选中一个或者多个活动平台");
        return false;
    }
    var coupon_description = $("#coupon_description").val();
    if (coupon_description == "") {
        layer_required("活动说明不能为空");
        return false;
    }
    var coupon_type = $("input[name='coupon_type']:checked").val();
    switch (coupon_type) {
        case "1":
            var coupon_value = $("input[name='coupon_value']").val();
            var min_cost = $("input[name='min_cost']").val();
            if(coupon_value.length < 1){
                layer_required("请输入优惠券金额");
                return false;
            }
            if (!regMoney.test(coupon_value)) {
                layer_required("满减券只能输入数字");
                return false;
            }
            if(min_cost.length < 1){
                layer_required("请输入订单金额");
                return false;
            }
            if (!regMoney.test(min_cost)) {
                layer_required("订单金额只能输入数字");
                return false;
            }
            if (coupon_value == "" || min_cost == "") {
                layer_required("满减券必须填写优惠券金额和最低订单金额");
                return false;
            }
            break;
        case "2":
            var coupon_value = $("input[name='coupon_value']").val();
            var min_cost = $("input[name='min_cost']").val();
            var discount_unit = $('#discount_unit').val();
            if(coupon_value.length < 1){
                layer_required("请输入优惠券金额");
                return false;
            }
            if(!regDiscount.test(coupon_value)){
                layer_required("折扣券只能输入一位小数");
                return false;
            }
            if(min_cost.length < 1){
                layer_required("请输入订单金额");
                return false;
            }
            if(discount_unit == 1){
                //元
                if(!regMoney.test(min_cost)){
                    layer_required("订单金额只能输入数字");
                    return false;
                }
            }else{
                //件
                if(!regNumber.test(min_cost) && min_cost < 1){
                    layer_required("商品件数只能输入数字且必须等于等于1");
                    return false;
                }
            }
            if (coupon_value == "" || min_cost == "") {
                layer_required("折扣券必须填写折扣券金额和最低订单金额/商品件数");
                return false;
            }
            break;
        case "3":
            var min_cost = $("input[name='min_cost']").val();
            if(min_cost.length < 1){
                layer_required("订单金额不能为空");
                return false;
            }
            if (!regMoney.test(min_cost)) {
                layer_required("订单金额只能输入数字");
                return false;
            }
            if (min_cost == "") {
                layer_required("包邮券必须填写最低订单金额");
                return false;
            }
            break;
    }
    var channel_id = $("#channel_id").val();

    if (channel_id=="选择渠道") {
        layer_required("渠道必须选择");
        return false;
    }

    if ($("#coupon_number").val().length < 1) {
        $("#coupon_number").val(0);
        return false;
    }
    var coupon_number = $("#coupon_number").val();
    if (!regNumber.test(coupon_number)) {
        layer_required("发放张数只能输入数字");
        return false;
    }
    if (coupon_number < 1 && coupon_number != 0) {
        layer_required("发放张数必须填写,最低不少于1");
        return false;
    }
    var limit_number = $("#limit_number").val();
    if(limit_number.length < 1){
        layer_required("每人限领取张数不能为空");
        return false;
    }
    if (!regNumber.test(limit_number)) {
        layer_required("每人限领取张数只能输入数字");
        return false;
    }
    if (parseInt(limit_number) < 1) {
        layer_required("每人限领取张数必须填写,最低为1");
        return false;
    }
    if(parseInt(limit_number) > 100){
        layer_required('每人限制领取100张');
        $("#limit_number").val('');
        return false;
    }
    if((limit_number-coupon_number)>0 && coupon_number != 0){
        layer_required("每人限领取张数不可大于发放张数");
        return false;
    }
    var group = $("#group").val();
    /*if (group == 1) {
        if ($("input[name='register_bonus']").is(":checked") == false) {
            layer_required("新用户必须选择是否注册发放");
            return false;
        }
    }*/
    if (group == 3) {
        var tels = $("#tels").val();
        if (tels == "") {
            layer_required("选择特定用户必须输入手机号码或者导入号码列表");
            return false;
        }
    }
    var use_range = $("#use_range").val();
    var result_flag = 1;
    switch (use_range) {
        case "all":
            var category_ids = $("#category_set").val();
            var brand_ids = $("#brand_set").val();
            var goods_ids = $("#goods_set").val();

            //检查品类
            if (category_ids != "") {
                //验证格式
                var ch = category_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        result_flag = 0;
                        layer_required("品类格式不对，请重新输入");
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            result_flag = 0;
                            layer_required("品类重复id，请重新输入");
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "category",
                    ids: category_ids
                }, function (data) {
                    if (data == "0") {
                        result_flag = 0;
                        layer_required("品类id列表输入有误,请检查重新输入");
                        return false;
                    }
                });
            }
            //检查品牌
            if (brand_ids != "") {
                //验证格式
                var ch = brand_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("品牌格式不对，请重新输入");
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("品牌重复id，请重新输入");
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "brand",
                    ids: brand_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("品牌id列表输入有误,请检查重新输入");
                        return false;
                    }
                });
            }
            //检查商品
            if (goods_ids != "") {
                //验证格式
                var ch = goods_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("商品格式不对，请重新输入");
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("商品重复id，请重新输入");
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "single",
                    ids: goods_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("商品id列表有误,请检查重新输入");
                        return false;
                    }
                });
            }
            break;
        case "category":
            var brand_ids = $("#brand_set").val();
            var goods_ids = $("#goods_set").val();
            var hiddenUseRange = $("#hiddenUseRange").val();
            if (hiddenUseRange == "") {
                layer_required("必须选择分类");
                return false;
            }
            //检查品牌
            if (brand_ids != "") {
                //验证格式
                var ch = brand_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("品牌格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("品牌重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "brand",
                    ids: brand_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("品牌id列表输入有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            //检查商品
            if (goods_ids != "") {
                //验证格式
                var ch = goods_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("商品格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("商品重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "single",
                    ids: goods_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("商品id列表有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            break;
        case "brand":
            var goods_ids = $("#goods_set").val();
            var shop_brand = $("input[name='shop_brand']").val();
            if (shop_brand == "") {
                layer_required("必须输入品牌相关信息");
                result_flag = 0;
                return false;
            }
            //检查商品
            if (goods_ids != "") {
                //验证格式
                var ch = goods_ids.split(",");
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("商品格式不对，请重新输入");
                        result_flag = 0;
                        break;
                    }
                }

                //验证重复
                for (var i = 0; i < ch.length; i++) {
                    var temp = ch[i];
                    for (var j = 0; j < ch.length; j++) {
                        if (temp == ch[j] && i != j) {
                            layer_required("商品重复id，请重新输入");
                            result_flag = 0;
                            break;
                        }
                    }
                }
                //验证id是否存在数据库
                $.post("/coupon/checkExistIds", {
                    type: "single",
                    ids: goods_ids
                }, function (data) {
                    if (data == "0") {
                        layer_required("商品id列表有误,请检查重新输入");
                        result_flag = 0;
                        return false;
                    }
                });
            }
            break;
        case "single":
            var hiddenUseRange = $("#hiddenUseRange").val();
            if (hiddenUseRange == "") {
                layer_required("必须选择商品");
                result_flag = 0;
                return false;
            }

            break;
    }
        if(result_flag == 0)return false;


    var provide_type3=$("#provide_type").val();
    if(provide_type3 == 2 || provide_type3 ==3) {
        if (coupon_number == 0) {
            layer_required("激活码与统一码发放数量不能设置为无限");
            return false;
        }
    }
    if($("input[name=is_activecode]").attr("checked")=="checked"){
            var create_min_num = $("input[name=create_min_num]").val();
            if(create_min_num == ""){
                layer_required("生成数量不能为空");
                return false;
            }else{
                if(create_min_num > 30000){
                    layer_required("一次生成数量不能超过30000");
                    return false;
                }
            }
        }
        var v1 = $('input[name=coupon_value]').val();
        var v2 = $('input[name=min_cost]').val();
        if(checkO.test(v1) || checkO.test(v2)){
            layer_required('金额格式错误');
            return false;
        }
        var app_url = $("#app_url").val();
        var wap_url = $("#wap_url").val();
        var pc_url = $("#pc_url").val();
        var wechat_url = $("#wechat_url").val();
        /*if(!appUrl.test(app_url) && app_url.length > 0){
            layer_required('链接格式出错');
            $("#app_url").val('');
            return false;
        }*/
        if(!regUrl.test(wap_url) && wap_url.length > 0){
            layer_required('链接格式出错');
            $("#wap_url").val('');
            return false;
        }
        /*if(!regUrl.test(pc_url) && pc_url.length > 0){
            layer_required('链接格式出错');
            $("#pc_url").val('');
            return false;
        }*/
        if(!regUrl.test(wechat_url) && wechat_url.length > 0){
            layer_required('链接格式出错');
            $("#wechat_url").val('');
            return false;
        }

    layer.msg('正在发送数据中,请稍等');
    var form = $('#insert_coupon');
    var url = form.attr('action');
    var data = {};
    thisBtn.attr('disabled','disabled');
    data = form.serializeArray();
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        cache: false,
        dataType:'json',
        success: function (msg) {
            if(msg.status == 'error'){
                layer.load(20, {time: 1});
                layer.msg(msg.info, {shade: 0.3, time: 1000});
                setTimeout(function () {
                    $('#do_ajax_sure_btn').removeAttr('disabled');
                },2000);
                return false;
            }else if(msg.status == 'success'){
                parent.layer.msg(
                    msg.info,
                    {shade: 0.3, time: time},
                    function(){
                        layer.load(20, {
                            shade: [0.3,'#ffffff'] //0.1透明度的白色背景
                        });
                        parent.window.location.href = msg.url; return true;
                    }
                )
            }
        },
        error: function () {
            layer.load(20, {time: 1});
            thisBtn.removeAttr('disabled');
            layer.msg('操作失败!');return false;
        }
    });
});

$("#validitytype").on("click", function () {
    var $this = $(this);
    if ($this.val() == 1) {
        $("#start_use_time_form").show(0);
        $("#end_use_time_form").show(0);
        $("#hd_relative_validity").hide(0);
    } else {
        $("#start_use_time_form").hide(0);
        $("#end_use_time_form").hide(0);
        $("#hd_relative_validity").show(0);
    }
});

//双向 不能选择 本站
$("#channel_id").change(function () {
    var vname = $.trim($("#channel_id").find("option:selected").text());
    var provide_type = $("#provide_type").val();
    /*if (provide_type == 3 && vname == "本站") {
        $("#channel_id").find("option:selected").prop("selected", false);
        layer_required("不能选择 本站");
        return false;
    }*/
});
var provide_type2=$("#provide_type").val();
if(provide_type2 == 2 || provide_type2 ==3){
    $("#active_code_area").html($("#select_active_code").html());
    if(provide_type2 == 2 ){
       $('#active_code_area').html('<div id="active_code_area"><div class="form-group" id="create_code"><label class="col-sm-2 control-label no-padding-right">生成激活码</label><div class="col-sm-5"><div class="checkbox"><label><input name="is_activecode" type="checkbox" class="ace" value="1" checked> <span class="lbl"></span></label></div></div></div><div class="form-group" id="create_code_num"><label class="col-sm-2 control-label no-padding-right" for="use_range">生成数量</label><div class="col-sm-5"><input type="number" class="col-xs-10 col-sm-5" name="create_min_num" placeholder="请输入少于发放的张数" min="0" value="1"></div></div></div>');
    }
    //$("#active_code_area").html($("#active_code_area").html()+$("#show_active_code_num").html());
    //$("input[name=is_activecode]").trigger("click");
}

$("#provide_type").change(function () {
    var vname = $.trim($("#channel_id").find("option:selected").text());
    var provide_type = $(this).val();
    if($("#active_code_area")[0]){
        if(provide_type == 2 || provide_type == 3){
            $("#active_code_area").html($("#select_active_code").html());
            if(provide_type == 2){
                $('input[name="is_activecode"]').trigger('click');
                //$('input[name="is_activecode"]').attr('disabled',true);
                $('input[name="create_min_num"]').val(1);
                //$('input[name="create_min_num"]').attr('disabled',true);

            }
            //$("input[name=is_activecode]").attr("checked",true);
        }else{
            $("#active_code_area").html("");
           // $("input[name=is_activecode]").attr("checked",false);
        }
    }
    /*if (provide_type == 3 && vname == "本站") {
        $("#channel_id").find("option:selected").prop("selected", false);
        layer_required("不能选择 本站");
        return false;
    }*/
})
var _ef=1;
$("body").on("click", "input[name=is_activecode]",function () {
    if(_ef == 1){
        $("#active_code_area").html($("#active_code_area").html()+$("#show_active_code_num").html());
        $("input[name=is_activecode]").attr("checked",true);
        _ef=2;
    }else{
        $("#create_code_num").remove();
        $("input[name=is_activecode]").attr("checked",false);
        _ef=1;
    }

})
/*if($("#active_code_area")[0]){
    var _ef=1;
    $("body").on("click", "input[name=is_activecode]",function () {
        if(_ef == 1){
            $("#active_code_area").html($("#active_code_area").html()+$("#show_active_code_num").html());
            $("input[name=is_activecode]").attr("checked",true);
            _ef=2;
        }else{
            $("#create_code_num").remove();
            $("input[name=is_activecode]").attr("checked",false);
            _ef=1;
        }

    })

}*/
//动态添加品牌

function do_select_click(pre) {
    var brandID = $('.select_brand_list_' + pre).find("option:selected").val();
    var brandName = $.trim($('.select_brand_list_' + pre).find("option:selected").text());
    if ($(".hiddenBrandids_" + pre).val() == "") {
        $(".brand_table_body_" + pre).append("<tr><td>" + brandID + "</td><td>" + brandName +
        "</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
        var _tempPool = [];
        $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
            var v = $(this).html();
            _tempPool.push(v);
        })
        $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
    } else {
        //查询是否重复
        var ch_same = $(".hiddenBrandids_" + pre).val();
        var ch_arr = ch_same.split(",");
        var _s = "1";
        $.each(ch_arr, function (n, v) {
            if (v == brandID) {
                _s = "0";
                layer_error("请不要重复添加");
                return false;
            }
        });
        if (_s == "1") {
            $(".brand_table_body_" + pre).append("<tr><td>" + brandID + "</td><td>" + brandName +
            "</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");

            var _tempPool = [];
            $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                var v = $(this).html();
                _tempPool.push(v);
            })
            $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
        }



    }
    $(".brand_table_body_" + pre + " tr td a.del").on("click", function () {
        $(this).parent().parent().remove();
        if ($(".brand_table_body_" + pre + " tr")[0]) {
            var _tempPool = [];
            $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                var v = $(this).html();
                _tempPool.push(v);
            })
            $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
        } else {
            $(".hiddenBrandids_" + pre).val("");
        }
    })

}


createValidateNotJoinActivity($("#use_range").val());
$("body").on("keyup","input[name=create_min_num]", function () {
    var coupon_number=$("#coupon_number").val();
    if(coupon_number==""){
        layer_required("发放张数不能为空");
        $(this).val("");
        return false;
    }else{
        if(!regNumber.test($(this).val())){
            layer_required("必须填写正整数");
            $(this).val("");
            return false;
        }
        if($('#provide_type').val() == 3){
            if(coupon_number<parseInt($(this).val())){
                layer_required("数量不能超过发放张数");
                $(this).val("");
                return false;
            }
        }else{
            if(parseInt($(this).val())>1){
                layer_required("统一码发放数量不能大于1");
                $(this).val("");
                return false;
            }
        }

    }
});
$("#use_range").change(function () {
    var range_val = $(this).val();
    switch (range_val) {
        // 1 全场
        case "all":
            $("#ajax_use_range_area").html("");
            createValidateNotJoinActivity(range_val);
            break;
        // 2 品类
        case "category":
            $("#ajax_use_range_area").html($("#select_category").html());
            var one_category = $('#one_category'); //一级分类
            var two_category = $('#two_category'); //二级分类
            var three_category = $('#three_category'); //三级分类
            var categoryBox = $('#categoryBox');
            one_category.on('change', function () {
                //初始化城市选项名
                two_category.find("option").remove();
                $('#three_category').find("option").remove();
                two_category.append('<option value="0">请选择</option>');
                $('#three_category').hide();
                var one_category_id = one_category.val();
                if (one_category_id == 0) {
                    return;
                }
                $.post('/promotion/getCategory', {
                    pid: one_category_id
                }, function (data) {
                    if (!data) {
                        return;
                    }
                    if (data.status == 'success') {
                        if (data.data.length > 0) {
                            two_category.show();
                            for (var i = 0; i < data.data.length; i++) {
                                two_category.append('<option value="' + data.data[i]['id'] + '">' + data.data[i][
                                    'category_name'] + '</option>');
                            }
                        }
                        $("select[name='shop_category[]']").each(function () {
                            if ($(this).val() != "0" && $(this).val() != null) {
                                $("#hiddenUseRange").val($(this).val());
                            }
                        })
                    }
                }, "json");

            });

            //输出二级分类下面的子分类
            two_category.on('change', function () {
                //初始化区县选项名
                three_category.find("option").remove();
                three_category.append('<option value="0">请选择</option>');
                var two_category_id = two_category.val();
                if (two_category_id == 0) {
                    return;
                }
                $.post('/promotion/getCategory', {
                    pid: two_category_id
                }, function (data) {
                    if (!data) {
                        return;
                    }
                    if (data.status == 'success') {
                        if (data.data.length > 0) {
                            three_category.show();
                            for (var i = 0; i < data.data.length; i++) {
                                three_category.append('<option value="' + data.data[i]['id'] + '">' + data.data[i][
                                    'category_name'] + '</option>');
                            }
                        }
                        $("select[name='shop_category[]']").each(function () {
                            if ($(this).val() != "0" && $(this).val() != null) {
                                $("#hiddenUseRange").val($(this).val());
                            }
                        })
                    }
                }, "json");
            });
            three_category.on('change', function () {
                $("select[name='shop_category[]']").each(function () {
                    if ($(this).val() != "0" && $(this).val() != null) {
                        $("#hiddenUseRange").val($(this).val());
                    }
                })
            });
            createValidateNotJoinActivity(range_val);
            break;
        // 3 品牌
        case "brand":
            //生成uuid附加到Class里 防止组件交叉Crash
            var pre = function () {
                var s = [];
                var hexDigits = "0123456789abcdef";
                for (var i = 0; i < 36; i++) {
                    s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
                }
                s[14] = "4";
                s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);
                s[8] = s[13] = s[18] = s[23] = "-";
                var uuid = s.join("");
                return uuid;
            }();

            var base_html =
                '<div class=form-group><label class="col-sm-2 control-label no-padding-right">添加品牌</label><div class=col-sm-5><div class=" append_area_' +
                pre + '"><input class="col-sm-7 search_goods_input_' + pre +
                '" placeholder="品牌ID/名称" type=text><div ><button class="btn btn-sm btn-success do_search_btn_' + pre +
                '" type=button>搜索</button></div></div></div></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right">批量添加品牌</label><div class=col-sm-5><textarea rows="3" cols="5" class="form-control batch_add_brand_' +
                pre +
                '" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><div style="margin-bottom: 5px;"><button class="btn btn-sm do_validate_brand_batch_' +
                pre + '" type="button">验证</button></div><div><button class="btn btn-sm do_add_brand_batch_' + pre +
                '" type="button">添加</button></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>操作</th><tbody class="brand_table_body_' +
                pre + '"></table></div></div><input type="hidden" class="hiddenBrandids_' + pre +
                '" name="shop_brand"><div>';


            $("#ajax_use_range_area").html(base_html);

            //批量添加
            $(".do_validate_brand_batch_" + pre).on("click", function () {
                //验证格式
                var is_pass=1;
                var batch_add_brand = $(".batch_add_brand_" + pre).val();
                var ch = batch_add_brand.split(",");
                if(batch_add_brand.length < 1){
                    layer_required("品牌id列表不能为空,请重新输入");
                    return false;
                }
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        is_pass = 0;
                        layer_required("位置:" + ch[i] + ",格式不对，请重新输入");
                        break;
                    }
                }
                if(arrRepeat(ch) == true){
                    is_pass = 0;
                    layer_required("数据重复，请重新输入");
                }
                if(is_pass == 0)return false;
                //验证重复
                var hiddenBrandids = $(".hiddenBrandids_" + pre).val()
                var repeat_arr = hiddenBrandids.split(",");
                for (var i = 0; i < repeat_arr.length; i++) {
                    for (var t = 0; t < ch.length; t++) {
                        if (repeat_arr[i] == ch[t]) {
                            layer_required("位置:" + ch[t] + ",产生重复,请重新检查输入");
                            is_pass = 0;
                            return false;
                        }
                    }
                }
                if(is_pass == 0)return false;
                //验证ID是否存在
                $.post("/coupon/getBrandValidIssetId", {
                    ids: batch_add_brand
                }, function (data) {
                    if (data != "0") {
                        layer_required("验证通过");
                    } else {
                        layer_required("验证失败");
                    }
                })
            })
            //添加批量
            $(".do_add_brand_batch_" + pre).on("click", function () {
                var batch_add_brand = $(".batch_add_brand_" + pre).val();
                var ch = batch_add_brand.split(",");
                if(batch_add_brand.length < 1){
                    layer_required("品牌id列表不能为空,请重新输入");
                    return false;
                }
                for (var i = 0; i < ch.length; i++) {
                    if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                        layer_required("位置:" + ch[i] + ",格式不对，请重新输入");
                        break;
                    }
                }
                if (arrRepeat(ch) == true){
                    layer_required("品牌重复，请重新输入");
                    return false;
                }
                var hiddenBrandids = $(".hiddenBrandids_" + pre).val()
                var repeat_arr = hiddenBrandids.split(",");
                for (var i = 0; i < repeat_arr.length; i++) {
                    for (var t = 0; t < ch.length; t++) {
                        if (repeat_arr[i] == ch[t]) {
                            layer_required("位置:" + ch[t] + ",产生重复,请重新检查输入");
                            return false;
                        }
                    }
                }
                var batch_add_brand = $(".batch_add_brand_" + pre).val();
                var ch = batch_add_brand.split(",");
                $.post("/coupon/getBrandValidIssetId", {
                    ids: batch_add_brand
                }, function (data) {
                    if (data != "0") {
                        $.each(data, function (n, v) {
                            $(".brand_table_body_" + pre).append("<tr><td>" + v['id'] + "</td><td>" + v['brand_name'] +
                            "</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
                            if ($(".hiddenBrandids_" + pre).val() == "") {
                                $(".hiddenBrandids_" + pre).val(v['id']);
                            } else {
                                $(".hiddenBrandids_" + pre).val($(".hiddenBrandids_" + pre).val() + "," + v['id']);
                            }

                        })
                        var _tempPool = [];
                        $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                            var v = $(this).html();
                            _tempPool.push(v);
                        })
                        $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
                        $(".brand_table_body_" + pre + " tr td a.del").on("click", function () {
                            $(this).parent().parent().remove();
                            if ($(".brand_table_body_" + pre + " tr")[0]) {
                                var _tempPool = [];
                                $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                                    var v = $(this).html();
                                    _tempPool.push(v);
                                })
                                $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
                            } else {
                                $(".hiddenBrandids_" + pre).val("");
                            }
                        })
                    } else {
                        layer_required("添加失败");
                    }
                })



            })
            //搜索操作
            $(".do_search_btn_" + pre).on("click", function () {
                var search_goods_input = $(".search_goods_input_" + pre).val();
                if (search_goods_input.length > 0) {
                    $.post("/coupon/getBrandSearchComponents", {
                        input: search_goods_input
                    }, function (output) {
                        if (output != "0") {
                            var obj = output;
                            //如果已经有dom存在
                            if ($('.select_brand_list_' + pre)[0]) {
                                $('.select_brand_list_'+pre).html("");
                                $.each(obj, function (n,v) {
                                    $('.select_brand_list_'+pre).append('<option value="'+ v["id"]+'">'+ v["brand_name"]+'</option>');
                                });
                            } else {
                                //新建dom

                                $('.append_area_'+pre).append('<div class="clearfix"></div><select class="col-sm-7 select_brand_list_'+pre+'" ></select><button class="btn btn-sm btn-danger do_select_btn_'+pre+'" type="button" onclick=do_select_click("'+pre+'")>确定</button></div>');
                                $.each(obj, function (n,v) {
                                    $('.select_brand_list_'+pre).append('<option value="'+ v["id"]+'">'+ v["brand_name"]+'</option>');
                                });


                            }

                        } else {
                            $('.select_brand_list_'+pre).remove();
                            $('.do_select_btn_'+pre).remove();
                            layer_required("没有查询到任何结果");
                        }

                    })
                } else {

                    layer_required("输入不能为空");
                }

            })
            createValidateNotJoinActivity(range_val);
            break;
        // 4 单品
        case "single":

            var pre = function () {
                var s = [];
                var hexDigits = "0123456789abcdef";
                for (var i = 0; i < 36; i++) {
                    s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
                }
                s[14] = "4";
                s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);
                s[8] = s[13] = s[18] = s[23] = "-";
                var uuid = s.join("");
                return uuid;
            }();
            var base_html =
                '<div class="form-group"><label class="col-sm-2 control-label no-padding-right" >添加商品</label><div class="col-sm-7"><div class="input-group"><input class="form-control search_goods_input_' +
                pre +
                '" type="text" placeholder="请输入商品名称（可以多个）或者输入商品ID（可以多个）以英文逗号隔开"><span class="input-group-btn"><button class="btn btn-sm btn-default do_search_btn_' +
                pre +
                '" type="button"><i class="ace-icon fa fa-search bigger-110"></i></button></span></div></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right"></label><div class=col-sm-9><div class="lister lister_' +
                pre + '"><ul class="left left_' + pre + '"></ul></div><div class="mid_panel"><div class="add_all add_all_' +
                pre + '"><a href="javascript:void(0);">全部添加>></a></div><div class="add add_' + pre +
                '"><a href="javascript:void(0);">添        加>></a></div><div class="del_all del_all_' +
                pre + '"><a href="javascript:void(0);"><< 全部删除</a></div><div class="del del_' + pre +
                '"><a href="javascript:void(0);"><< 删        除</a></div></div><div class="lister lister_' +
                pre + '"><ul class="right right_' + pre + '"></ul></div></div></div>';
            $("#ajax_use_range_area").html(base_html);
            mt = 0;
            $('body').on('keyup','.search_goods_input_'+pre,function(){
                var $this = $(this);
                if(mt == 0){
                    if($this.val().length > 0 ){
                        $('.do_search_btn_'+pre).toggleClass('btn-info');
                        mt = 1;
                    }
                }else{
                    if($this.val().length == 0){
                        $('.do_search_btn_'+pre).toggleClass('btn-info');
                        mt = 0;
                    }
                }
            });
            var return_result = function () {
                var _tempS = [];
                $(".lister_" + pre + " .right_" + pre + " li a").each(function () {
                    var dt = $(this).data("mid");
                    _tempS.push(dt);
                });
                $("#hiddenUseRange").val(_tempS.join(","));
            }
            $.post("/goods/getGoodsForCoupon", function (data) {
                if (data != "0") {
                    var obj = data;
                    $.each(obj, function (n, v) {
                        if(v["is_unified_price"] == "0"){
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                        }else{
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元,wechat价格:" + v["goods_price_wechat"] + "元</a></li>");
                        }

                    });
                    $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    });
                }
            });
            //添加操作

            $(".add_" + pre).on("click", function () {
                var use_platform_arr=[];
                var ipass = 1;

                $('input[name="use_platform[]"]:checked').each(function () {
                    switch ($(this).val())
                    {
                        case "1":
                            use_platform_arr.push('pc');
                            break;
                        case "2":
                            use_platform_arr.push('app');
                            break;
                        case "3":
                            use_platform_arr.push('wap');
                            break;
                        case "4":
                            use_platform_arr.push('wechat');
                            break;
                    }
                });

                $(".lister_" + pre + " .left_" + pre + " li").find($(".list_on a")).each(function () {
                    var mid=$(this).data("mid");
                    $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                    if($(".lister_"+pre+" .right_"+pre+" li").length > 0){
                        $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                            if($(this).data("mid") == mid){
                                ipass = 0 ;
                            }
                        });
                    }
                    if(ipass == 1){
                        $(".lister_" + pre + " .right_" + pre).append("<li>" + $(this).parent().html() + "</li>");
                        $(this).parent()[0].remove();
                        $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                            $(this).toggleClass("list_on");
                        })

                        $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:$(this).data('mid')},function (res) {
                            if(res.code == '400'){
                                layer_required(res.msg);
                                return false;
                            }
                        })
                    }

                })
                if(ipass == 0){
                    layer_required('不能添加同一个商品');
                    return false;
                }
                return_result();
            });

            //删除操作

            $(".del_" + pre).on("click", function () {
                $(".lister_" + pre + " .right_" + pre + " li").find($(".list_on a")).each(function () {
                    $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                    $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                    var $that = $(this);
                    var mid = $that.data('mid');
                    if($(".lister_"+pre+" .left_"+pre+" li").length > 0){
                        $(".lister_"+pre+" .left_"+pre+" li a").each(function () {
                            if($(this).data('mid') != mid){
                                $(".lister_"+pre+" .left_"+pre).append("<li>"+$(this).parent().html()+"</li>");
                            }
                        });
                    }else{
                        $(".lister_"+pre+" .left_"+pre).append("<li>"+$(this).parent().html()+"</li>");
                    }
                    $(this).parent()[0].remove();

                })
                return_result();

            });

            //添加全部

            $(".add_all_" + pre).on("click", function () {
                var use_platform_arr=[];
                var ipass = 1;
                $('input[name="use_platform[]"]:checked').each(function () {
                    switch ($(this).val())
                    {
                        case "1":
                            use_platform_arr.push('pc');
                            break;
                        case "2":
                            use_platform_arr.push('app');
                            break;
                        case "3":
                            use_platform_arr.push('wap');
                            break;
                        case "4":
                            use_platform_arr.push('wechat');
                            break;
                    }
                });
                $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                $(".lister_" + pre + " .left_" + pre + " li").each(function () {
                    if ($(this).hasClass("list_on")) $(this).removeClass("list_on");
                });
                $(".lister_" + pre + " .left_" + pre + " li a").each(function () {
                    var mid=$(this).data("mid");
                    if($(".lister_"+pre+" .right_"+pre+" li").length > 0){
                        $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                            if($(this).data("mid") == mid){
                                ipass = 0 ;
                            }
                        });
                    }
                    if(ipass == 1){
                        $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:$(this).data('mid')},function (res) {
                            if(res.code == '400'){
                                layer_required(res.msg);
                                return false;
                            }
                        })
                    }

                });
                if(ipass == 0){
                    layer_required('不能添加同一个商品');
                    return false;
                }
                $(".lister_"+pre+" .right_"+pre).html($(".lister_"+pre+" .right_"+pre).html()+$(".lister_"+pre+" .left_"+pre).html());
                $(".lister_" + pre + " .left_" + pre).html("");
                $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                return_result();
            });

            //删除全部

            $(".del_all_" + pre).on("click", function () {
                $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                $(".lister_" + pre + " .right_" + pre + " li").each(function () {
                    if ($(this).hasClass("list_on")) $(this).removeClass("list_on");
                });
                $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                    var mid = $(this).data('mid');
                    var $that = $(this);
                    $(".lister_"+pre+" .left_"+pre+" li a").each(function () {
                        if($(this).data('mid') == mid){
                            $that.parent().remove();
                        }
                    });
                });
                $(".lister_"+pre+" .left_"+pre).html($(".lister_"+pre+" .left_"+pre).html()+$(".lister_"+pre+" .right_"+pre).html());
                $(".lister_" + pre + " .right_" + pre).html("");
                $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                return_result();

            });

            //点击搜索对输入框根据条件进行查询并列入左框

            $(".do_search_btn_" + pre).on("click", function () {
                var search_goods_input = $(".search_goods_input_" + pre).val();
                var s_input = {};
                if (search_goods_input != "") {
                    s_input = {
                        input: search_goods_input
                    }
                }
                if(search_goods_input.length > 0){
                    $.post("/goods/getGoodsForCoupon", s_input, function (data) {
                        if (data != "0") {
                            var obj = data;
                            $(".lister_" + pre + " .left_" + pre).html("");
                            $.each(obj, function (n, v) {
                                v=v[0];
                                if(v["is_unified_price"] == "0"){
                                    $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                            "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                                }else{
                                    $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                            "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元,wechat价格:" + v["goods_price_wechat"] + "元</a></li>");
                                }
                            })
                            $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                                $(this).toggleClass("list_on");
                            })
                        }else{
                            layer_required('搜索结果为空');
                            $(".lister_"+pre+" .left_"+pre+" li").remove();
                        }
                    });
                }else{
                    layer_required('不能输入空值');
                }

            });
            $("#NotJoinActivity").html("");
            break;
        case 'special':
            $("#ajax_use_range_area").html("");
            createValidateNotJoinActivity(range_val);
            break;
    }
});

/**************编辑模块_Start**************/
var range_val = $("#use_range").val();

switch (range_val) {
    case 'special':
        $("#ajax_use_range_area").html("");
        createValidateNotJoinActivity(range_val);
        var ban_join_rule=$("#ban_join_rule").val();
        var obj= $.parseJSON(ban_join_rule);
        $("textarea[name='except_category_id']").val(obj['category']);
        $("textarea[name='except_brand_id']").val(obj['brand']);
        $("textarea[name='except_good_id']").val(obj['single']);
        break;
    // 1 全场
    case "all":
        $("#ajax_use_range_area").html("");
        createValidateNotJoinActivity(range_val);
        var ban_join_rule=$("#ban_join_rule").val();
        if(typeof ban_join_rule != 'undefined'){
            var obj= $.parseJSON(ban_join_rule);
            $("textarea[name='except_category_id']").val(obj['category']);
            $("textarea[name='except_brand_id']").val(obj['brand']);
            $("textarea[name='except_good_id']").val(obj['single']);
        }
        break;
    // 2 品类
    case "category":
        $("#ajax_use_range_area").html($("#select_category").html());
        var one_category = $('#one_category'); //一级分类
        var two_category = $('#two_category'); //二级分类
        var three_category = $('#three_category'); //三级分类
        var categoryBox = $('#categoryBox');
        one_category.on('change', function () {
            //初始化城市选项名
            two_category.find("option").remove();
            $('#three_category').find("option").remove();
            two_category.append('<option value="0">请选择</option>');
            $('#three_category').hide();
            var one_category_id = one_category.val();
            if (one_category_id == 0) {
                return;
            }
            $.post('/promotion/getCategory', {
                pid: one_category_id
            }, function (data) {
                if (!data) {
                    return;
                }
                if (data.status == 'success') {
                    if (data.data.length > 0) {
                        two_category.show();
                        for (var i = 0; i < data.data.length; i++) {
                            two_category.append('<option value="' + data.data[i]['id'] + '">' + data.data[i][
                                'category_name'] + '</option>');
                        }
                    }
                    $("select[name='shop_category[]']").each(function () {
                        if ($(this).val() != "0" && $(this).val() != null) {
                            $("#hiddenUseRange").val($(this).val());
                        }
                    })
                }
            }, "json");

        });

        //输出二级分类下面的子分类
        two_category.on('change', function () {
            //初始化区县选项名
            three_category.find("option").remove();
            three_category.append('<option value="0">请选择</option>');
            var two_category_id = two_category.val();
            if (two_category_id == 0) {
                return;
            }
            $.post('/promotion/getCategory', {
                pid: two_category_id
            }, function (data) {
                if (!data) {
                    return;
                }
                if (data.status == 'success') {
                    if (data.data.length > 0) {
                        three_category.show();
                        for (var i = 0; i < data.data.length; i++) {
                            three_category.append('<option value="' + data.data[i]['id'] + '">' + data.data[i][
                                'category_name'] + '</option>');
                        }
                    }
                    $("select[name='shop_category[]']").each(function () {
                        if ($(this).val() != "0" && $(this).val() != null) {
                            $("#hiddenUseRange").val($(this).val());
                        }
                    })
                }
            }, "json");
        });
        three_category.on('change', function () {
            $("select[name='shop_category[]']").each(function () {
                if ($(this).val() != "0" && $(this).val() != null) {
                    $("#hiddenUseRange").val($(this).val());
                }
            })
        });
        createValidateNotJoinActivity(range_val);
        var ban_join_rule=$("#ban_join_rule").val();
        var obj= JSON.parse(ban_join_rule);

        $("textarea[name='except_brand_id']").val(obj['brand']);
        $("textarea[name='except_good_id']").val(obj['single']);
        break;
    // 3 品牌
    case "brand":
        //生成uuid附加到Class里 防止组件交叉Crash
        var pre = function () {
            var s = [];
            var hexDigits = "0123456789abcdef";
            for (var i = 0; i < 36; i++) {
                s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
            }
            s[14] = "4";
            s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);
            s[8] = s[13] = s[18] = s[23] = "-";
            var uuid = s.join("");
            return uuid;
        }();
        var shop_brand_value="";
        if($("#brand_ids_tmp")[0] && $("#brand_ids_tmp").val()  !=  ""){
            shop_brand_value=$("#brand_ids_tmp").val();
        }
        var base_html =
            '<div class=form-group><label class="col-sm-2 control-label no-padding-right">添加品牌</label><div class=col-sm-5><div class=" append_area_' +
            pre + '"><input class="col-sm-7 search_goods_input_' + pre +
            '" placeholder="品牌ID/名称" type=text><div ><button class="btn btn-sm btn-success do_search_btn_' + pre +
            '" type=button>搜索</button></div></div></div></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right">批量添加品牌</label><div class=col-sm-5><textarea rows="3" cols="5" class="form-control batch_add_brand_' +
            pre +
            '" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><div style="margin-bottom: 5px;"><button class="btn btn-sm do_validate_brand_batch_' +
            pre + '" type="button">验证</button></div><div><button class="btn btn-sm do_add_brand_batch_' + pre +
            '" type="button">添加</button></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>操作</th><tbody class="brand_table_body_' +
            pre + '"></table></div></div><input type="hidden" class="hiddenBrandids_' + pre +
            '" name="shop_brand" value="'+shop_brand_value+'"><div>';


        $("#ajax_use_range_area").html(base_html);
        //编辑获取数据
        if($("#brand_ids_tmp")[0] && $("#brand_ids_tmp").val()  !=  ""){
            var _s=$("#brand_ids_tmp").val();
            $.post("/coupon/getBrandValidIssetId", {
                ids: _s
            }, function (data) {
                if (data != "0") {
                    $.each(data, function (n, v) {
                        $(".brand_table_body_" + pre).append("<tr><td>" + v['id'] + "</td><td>" + v['brand_name'] +
                        "</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
                        if ($(".hiddenBrandids_" + pre).val() == "") {
                            $(".hiddenBrandids_" + pre).val(v['id']);
                        } else {
                            $(".hiddenBrandids_" + pre).val($(".hiddenBrandids_" + pre).val() + "," + v['id']);
                        }

                    })
                    var _tempPool = [];
                    $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                        var v = $(this).html();
                        _tempPool.push(v);
                    })
                    $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
                    $(".brand_table_body_" + pre + " tr td a.del").on("click", function () {
                        $(this).parent().parent().remove();
                        if ($(".brand_table_body_" + pre + " tr")[0]) {
                            var _tempPool = [];
                            $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                                var v = $(this).html();
                                _tempPool.push(v);
                            })
                            $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
                        } else {
                            $(".hiddenBrandids_" + pre).val("");
                        }
                    })
                } else {
                    layer_required("添加失败");
                }
            })
        }

        //批量添加
        $(".do_validate_brand_batch_" + pre).on("click", function () {
            //验证格式
            var is_pass=1;
            var batch_add_brand = $(".batch_add_brand_" + pre).val();
            var ch = batch_add_brand.split(",");
            if(batch_add_brand.length < 1){
                layer_required("品牌id列表不能为空,请重新输入");
                return false;
            }
            for (var i = 0; i < ch.length; i++) {
                if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                    is_pass = 0;
                    layer_required("位置:" + ch[i] + ",格式不对，请重新输入");
                    break;
                }
            }
            if(arrRepeat(ch) == true){
                is_pass = 0;
                layer_required("数据重复，请重新输入");
            }
            if(is_pass == 0)return false;
            //验证重复
            var hiddenBrandids = $(".hiddenBrandids_" + pre).val()
            var repeat_arr = hiddenBrandids.split(",");
            for (var i = 0; i < repeat_arr.length; i++) {
                for (var t = 0; t < ch.length; t++) {
                    if (repeat_arr[i] == ch[t]) {
                        is_pass = 0;
                        layer_required("位置:" + ch[t] + ",产生重复,请重新检查输入");
                        return false;
                    }
                }
            }
            if(is_pass == 0)return false;
            //验证ID是否存在
            $.post("/coupon/getBrandValidIssetId", {
                ids: batch_add_brand
            }, function (data) {
                if (data != "0") {
                    layer_required("验证通过");
                } else {
                    layer_required("验证失败");
                }
            })
        })
        //添加批量
        $(".do_add_brand_batch_" + pre).on("click", function () {
            var batch_add_brand = $(".batch_add_brand_" + pre).val();
            var ch = batch_add_brand.split(",");
            if(batch_add_brand.length < 1){
                layer_required("品牌id列表不能为空,请重新输入");
                return false;
            }
            for (var i = 0; i < ch.length; i++) {
                if (isNaN(ch[i]) || !regNumber.test(ch[i])) {
                    layer_required("位置:" + ch[i] + ",格式不对，请重新输入");
                    break;
                }
            }
            if (arrRepeat(ch) == true){
                layer_required("品牌重复，请重新输入");
                return false;
            }
            var hiddenBrandids = $(".hiddenBrandids_" + pre).val()
            var repeat_arr = hiddenBrandids.split(",");
            for (var i = 0; i < repeat_arr.length; i++) {
                for (var t = 0; t < ch.length; t++) {
                    if (repeat_arr[i] == ch[t]) {
                        layer_required("位置:" + ch[t] + ",产生重复,请重新检查输入");
                        return false;
                    }
                }
            }
            var batch_add_brand = $(".batch_add_brand_" + pre).val();
            var ch = batch_add_brand.split(",");
            $.post("/coupon/getBrandValidIssetId", {
                ids: batch_add_brand
            }, function (data) {
                if (data != "0") {
                    $.each(data, function (n, v) {
                        $(".brand_table_body_" + pre).append("<tr><td>" + v['id'] + "</td><td>" + v['brand_name'] +
                        "</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
                        if ($(".hiddenBrandids_" + pre).val() == "") {
                            $(".hiddenBrandids_" + pre).val(v['id']);
                        } else {
                            $(".hiddenBrandids_" + pre).val($(".hiddenBrandids_" + pre).val() + "," + v['id']);
                        }

                    })
                    var _tempPool = [];
                    $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                        var v = $(this).html();
                        _tempPool.push(v);
                    })
                    $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
                    $(".brand_table_body_" + pre + " tr td a.del").on("click", function () {
                        $(this).parent().parent().remove();
                        if ($(".brand_table_body_" + pre + " tr")[0]) {
                            var _tempPool = [];
                            $(".brand_table_body_" + pre + " tr").find("td:eq(0)").each(function () {
                                var v = $(this).html();
                                _tempPool.push(v);
                            })
                            $(".hiddenBrandids_" + pre).val(_tempPool.join(","));
                        } else {
                            $(".hiddenBrandids_" + pre).val("");
                        }
                    })
                } else {
                    layer_required("添加失败");
                }
            })



        })
        //搜索操作
        $(".do_search_btn_" + pre).on("click", function () {
            var search_goods_input = $(".search_goods_input_" + pre).val();
            if (search_goods_input.length > 0) {
                $.post("/coupon/getBrandSearchComponents", {
                    input: search_goods_input
                }, function (output) {
                    if (output != "0") {
                        var obj = output;
                        //如果已经有dom存在
                        if ($('.select_brand_list_' + pre)[0]) {
                            $('.select_brand_list_' + pre).html("");
                            $.each(obj, function (n, v) {
                                $('.select_brand_list_' + pre).append('<option value="' + v["id"] + '">' + v[
                                    "brand_name"] + '</option>');
                            });
                        } else {
                            //新建dom

                            $('.append_area_' + pre).append(
                                '<div class="clearfix"></div><select class="col-sm-7 select_brand_list_' + pre +
                                '" ></select><button class="btn btn-sm btn-danger do_select_btn_' + pre +
                                '" type="button" onclick=do_select_click("' + pre + '")>确定</button></div>');
                            $.each(obj, function (n, v) {
                                $('.select_brand_list_' + pre).append('<option value="' + v["id"] + '">' + v[
                                    "brand_name"] + '</option>');
                            });


                        }

                    } else {
                        if ($('.select_brand_list_' + pre)[0]) {
                            $('.select_brand_list_' + pre).parent().remove();
                        }
                        layer_required("没有查询到任何结果");
                    }

                })
            } else {

                layer_required("输入不能为空");
            }

        })
        createValidateNotJoinActivity(range_val);
        var ban_join_rule=$("#ban_join_rule").val();
        var obj= JSON.parse(ban_join_rule);

        $("textarea[name='except_good_id']").val(obj['single']);
        break;
    // 4 单品
    case "single":

        var pre = function () {
            var s = [];
            var hexDigits = "0123456789abcdef";
            for (var i = 0; i < 36; i++) {
                s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
            }
            s[14] = "4";
            s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);
            s[8] = s[13] = s[18] = s[23] = "-";
            var uuid = s.join("");
            return uuid;
        }();
        var base_html =
            '<div class="form-group"><label class="col-sm-2 control-label no-padding-right" >添加商品</label><div class="col-sm-7"><div class="input-group"><input class="form-control search_goods_input_' +
            pre +
            '" type="text" placeholder="请输入商品名称（可以多个）或者输入商品ID（可以多个）以英文逗号隔开"><span class="input-group-btn"><button class="btn btn-sm btn-default do_search_btn_' +
            pre +
            '" type="button"><i class="ace-icon fa fa-search bigger-110"></i></button></span></div></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right"></label><div class=col-sm-9><div class="lister lister_' +
            pre + '"><ul class="left left_' + pre + '"></ul></div><div class="mid_panel"><div class="add_all add_all_' +
            pre + '"><a href="javascript:void(0);">全部添加>></a></div><div class="add add_' + pre +
            '"><a href="javascript:void(0);">添        加>></a></div><div class="del_all del_all_' +
            pre + '"><a href="javascript:void(0);"><< 全部删除</a></div><div class="del del_' + pre +
            '"><a href="javascript:void(0);"><< 删        除</a></div></div><div class="lister lister_' +
            pre + '"><ul class="right right_' + pre + '"></ul></div></div></div>';
        $("#ajax_use_range_area").html(base_html);
        var mt = 0;
        $('body').on('keyup','.search_goods_input_'+pre,function(){
            var $this = $(this);
            if(mt == 0){
                if($this.val().length > 0 ){
                    $('.do_search_btn_'+pre).toggleClass('btn-info');
                    mt = 1;
                }
            }else{
                if($this.val().length == 0){
                    $('.do_search_btn_'+pre).toggleClass('btn-info');
                    mt = 0;
                }
            }
        });


        var return_result = function () {
            var _tempS = [];
            $(".lister_" + pre + " .right_" + pre + " li a").each(function () {
                var dt = $(this).data("mid");
                _tempS.push(dt);
            });
            $("#hiddenUseRange").val(_tempS.join(","));
        }
        $.post("/goods/getGoodsForCoupon", function (data) {
            if (data != "0") {
                var obj = data;
                $.each(obj, function (n, v) {
                    if(v["is_unified_price"] == "0"){
                        $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                    }else{
                        $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元,wechat价格:" + v["goods_price_wechat"] + "元</a></li>");
                    }
                });
                $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                });

                /***************编辑操作*****************/
                if($("#single_flag")[0]){
                    var dt=$("input[name='hiddenUseRange']").val();
                    $.post("/goods/getGoodsForCoupon",{input:dt}, function (res) {
                        $.each(res, function (n, v) {
                            v=v[0];
                            if(v["is_unified_price"] == "0"){
                                $(".lister_" + pre + " .right_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                            }else{
                                $(".lister_" + pre + " .right_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元,wechat价格:" + v["goods_price_wechat"] + "元</a></li>");
                            }
                        });
                        $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                            $(this).toggleClass("list_on");
                        })
                        var sg_str_arr=dt.split(",");
                        /*$(".lister_" + pre + " .left_" + pre+"  li a").each(function () {
                            for(var i=0;i<sg_str_arr.length;i++){
                                if($(this).data("mid")==sg_str_arr[i]){
                                    $(this).parent().remove();
                                }
                            }
                        });*/
                    });



                }
                /*************编辑操作结束***************/
            }
        });






        //添加操作
        $(".add_" + pre).on("click", function () {
            var use_platform_arr=[];
            var ipass = 1;
            $('input[name="use_platform[]"]:checked').each(function () {
                switch ($(this).val())
                {
                    case "1":
                        use_platform_arr.push('pc');
                        break;
                    case "2":
                        use_platform_arr.push('app');
                        break;
                    case "3":
                        use_platform_arr.push('wap');
                        break;
                    case "4":
                        use_platform_arr.push('wechat');
                        break;
                }
            });
            $(".lister_" + pre + " .left_" + pre + " li").find($(".list_on a")).each(function () {
                var mid=$(this).data("mid");
                if($(".lister_"+pre+" .right_"+pre+" li").length > 0){
                    $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                        if($(this).data("mid") == mid){
                            ipass = 0 ;
                        }
                    });
                }
                if(ipass == 1){
                    $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                    $(".lister_" + pre + " .right_" + pre).append("<li>" + $(this).parent().html() + "</li>");
                    $(this).parent()[0].remove();
                    $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })

                    $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:$(this).data('mid')},function (res) {
                        if(res.code == '400'){
                            layer_required(res.msg);
                            return false;
                        }
                    })
                }

            })
            if(ipass == 0){
                layer_required('不能添加同一个商品');
                return false;
            }
            return_result();
        });

        //删除操作

        $(".del_" + pre).on("click", function () {
            $(".lister_" + pre + " .right_" + pre + " li").find($(".list_on a")).each(function () {
                $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                var $that = $(this);
                var mid = $that.data('mid');
                if($(".lister_"+pre+" .left_"+pre+" li").length > 0){
                    $(".lister_"+pre+" .left_"+pre+" li a").each(function () {
                        if($(this).data('mid') != mid){
                            $(".lister_"+pre+" .left_"+pre).append("<li>"+$(this).parent().html()+"</li>");
                        }
                    });
                }else{
                    $(".lister_"+pre+" .left_"+pre).append("<li>"+$(this).parent().html()+"</li>");
                }
                $(this).parent()[0].remove();

            })
            return_result();

        });

        //添加全部

        $(".add_all_" + pre).on("click", function () {
            var ipass = 1;
            var use_platform_arr=[];
            $('input[name="use_platform[]"]:checked').each(function () {
                switch ($(this).val())
                {
                    case "1":
                        use_platform_arr.push('pc');
                        break;
                    case "2":
                        use_platform_arr.push('app');
                        break;
                    case "3":
                        use_platform_arr.push('wap');
                        break;
                    case "4":
                        use_platform_arr.push('wechat');
                        break;
                }
            });
            $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_" + pre + " .left_" + pre + " li").each(function () {
                if ($(this).hasClass("list_on")) $(this).removeClass("list_on");

            });
            $(".lister_" + pre + " .left_" + pre + " li a").each(function () {
                var mid=$(this).data("mid");
                if($(".lister_"+pre+" .right_"+pre+" li").length > 0){
                    $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                        if($(this).data("mid") == mid){
                            ipass = 0 ;
                        }
                    });
                }
                if(ipass == 1){
                    $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:$(this).data('mid')},function (res) {
                        if(res.code == '400'){
                            layer_required(res.msg);
                            return false;
                        }
                    })
                }
            });
            if(ipass == 0){
                layer_required('不能添加同一个商品');
                return false;
            }
            $(".lister_"+pre+" .right_"+pre).html($(".lister_"+pre+" .right_"+pre).html()+$(".lister_"+pre+" .left_"+pre).html());
            $(".lister_" + pre + " .left_" + pre).html("")
            $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            return_result();
        });

        //删除全部

        $(".del_all_" + pre).on("click", function () {
            $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_" + pre + " .right_" + pre + " li").each(function () {
                if ($(this).hasClass("list_on")) $(this).removeClass("list_on");
            });
            $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                var mid = $(this).data('mid');
                var $that = $(this);
                $(".lister_"+pre+" .left_"+pre+" li a").each(function () {
                    if($(this).data('mid') == mid){
                        $that.parent().remove();
                    }
                });
            });
            $(".lister_"+pre+" .left_"+pre).html($(".lister_"+pre+" .left_"+pre).html()+$(".lister_"+pre+" .right_"+pre).html());
            $(".lister_" + pre + " .right_" + pre).html("")
            $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            return_result();

        });

        //点击搜索对输入框根据条件进行查询并列入左框

        $(".do_search_btn_" + pre).on("click", function () {
            var search_goods_input = $(".search_goods_input_" + pre).val();

            var s_input = {};
            if (search_goods_input != "") {
                s_input = {
                    input: search_goods_input
                }
            }
            if(search_goods_input.length > 0){
                $.post("/goods/getGoodsForCoupon", s_input, function (data) {
                    if (data != "0") {
                        var obj = data;
                        $(".lister_" + pre + " .left_" + pre).html("");
                        $.each(obj, function (n, v) {
                            v=v[0];
                            if(v["is_unified_price"] == "0"){
                                $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                            }else{
                                $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元,wechat价格:" + v["goods_price_wechat"] + "元</a></li>");
                            }
                        })
                        $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                            $(this).toggleClass("list_on");
                        })
                        if($("#single_flag")[0]){
                            var dt=$("input[name='hiddenUseRange']").val();
                            var sg_str_arr=dt.split(",");
                            /*$(".lister_" + pre + " .left_" + pre+"  li a").each(function () {
                                for(var i=0;i<sg_str_arr.length;i++){
                                    if($(this).data("mid")==sg_str_arr[i]){
                                        $(this).parent().remove();
                                    }
                                }
                            });*/
                        }
                    }else{
                        layer_required('搜索结果为空');
                        $(".lister_"+pre+" .left_"+pre+" li").remove();
                    }
                });
            }else{
                layer_required('不能输入空值');
            }

        });
        $("#NotJoinActivity").html("");
        break;
}

/**************编辑模块_End**************/
var category_num = 0;
var brand_num = 0;
var goods_num = 0;
$('body').on('keyup','#category_set,#brand_set,#goods_set',function () {
    var $this = $(this);
    //$('#validateBtn').removeClass('btn-info');
    if($this[0]['id'] == 'category_set'){
        category_num = $this.val().length;
    }
    if($this[0]['id'] == 'brand_set'){
        brand_num = $this.val().length;
    }
    if($this[0]['id'] == 'goods_set'){
        goods_num = $this.val().length;
    }
    if(category_num > 0 || brand_num > 0 || goods_num > 0){
        $('#validateBtn').addClass('btn-info');
        if($('#do_ajax_sure_btn')[0]){
            $('#do_ajax_sure_btn').attr("disabled","disabled");
        }
        if($('#do_ajax_sure_save_btn')[0]){
            $('#do_ajax_sure_save_btn').attr("disabled","disabled");
        }
    }else{
        if($('#do_ajax_sure_btn')[0]){
           // $('#do_ajax_sure_btn').removeAttr("disabled");
        }
        if($('#do_ajax_sure_save_btn')[0] && $('#coupon_name').val() != ''){
           // $('#do_ajax_sure_save_btn').removeAttr("disabled");
        }
    }
});
$('input[name=coupon_value]').on('change',function () {
    var val = $(this).val();
    if(checkO.test(val) ){
        layer_required('金额格式错误');
        $(this).val('');
        return false;
    }
})
$('input[name=min_cost]').on('change',function () {
    var val = $(this).val();
    if(checkO.test(val) ){
        layer_required('金额格式错误');
        $(this).val('');
        return false;
    }
})

$("body").on('keyup','#limit_number',function () {
    var num = $(this).val();
    if(num.length > 0) $(this).val(parseInt(num));
    if(num > 100) {
        layer_required('每人限制领取100张');
        $(this).val('');
    }
});

$("body").on('keyup','#coupon_number',function () {
    var num = $(this).val();
    if(regNumber.test(num)){
        if(num.length > 0) $(this).val(parseInt(num));
    }else{
        $(this).val('');
        layer_required('优惠券发放张数只能为数字');
    }

});
$('#is_pl').hide(0);
$('body').on('change','#channel_id',function () {
    var channel_id = $('#channel_id').val();
    var group = $('#group').val();
    var goods_tag = $('#goods_tag').val();
    if(channel_id == 0 && group == 2 && goods_tag == 0){
        $('#is_pl').show(0);
        $('#is_present').html('<option value="0" selected>否</option><option value="1" >是</option>');
    }else{
        $('#is_pl').hide(0);
        $('#is_present').html('<option value="0" selected>否</option><option value="1" >是</option>');
    }
});
$('body').on('change','#group',function () {
    var channel_id = $('#channel_id').val();
    var group = $('#group').val();
    var goods_tag = $('#goods_tag').val();
    if(channel_id == 0 && group == 2 && goods_tag == 0){
        $('#is_pl').show(0);
        $('#is_present').html('<option value="0" selected>否</option><option value="1" >是</option>');
    }else{
        $('#is_pl').hide(0);
        $('#is_present').html('<option value="0" selected>否</option><option value="1" >是</option>');
    }
});
$('body').on('change','#goods_tag',function () {
    var channel_id = $('#channel_id').val();
    var group = $('#group').val();
    var goods_tag = $('#goods_tag').val();
    if(channel_id == 0 && group == 2 && goods_tag == 0){
        $('#is_pl').show(0);
        $('#is_present').html('<option value="0" selected>否</option><option value="1" >是</option>');
    }else{
        $('#is_pl').hide(0);
        $('#is_present').html('<option value="0" selected>否</option><option value="1">是</option>');
    }
});
var _channel_id = $('#channel_id').val();
var _group = $('#group').val();
var _goods_tag = $('#goods_tag').val();
if(_channel_id == 0 && _group == 2 && _goods_tag == 0){
    $('#is_pl').show(0);
}else{
    $('#is_pl').hide(0);
}