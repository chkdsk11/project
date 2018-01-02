/**
 * @desc 添加促销活动的js验证
 * @author 吴俊华
 * @date 2016-09-06
 */
$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});
    var _flagForValidate = 0;
    //选择不同优惠类型
    $('#promotion_type').on('change',function(){
        var promotion_type = $("select[name='promotion_type'] option:checked").val();
        //初始化规则
        $('#rule-tbody').empty();
        $('#addNextRule').show();
        $('.real_pay').hide();
        $('.promotion_mutex label input').removeAttr('checked');
        $('.promotion_mutex label input').removeAttr('disabled');
        $('.promotion_mutex label input').attr('name','promotion_mutex[]');

        //满赠活动显示是否实付
        if(promotion_type == '15'){
            $('.real_pay').show();
        }

        //满减活动或满折活动
        /*if(promotion_type == '5' || promotion_type == '10'){
            $('.promotion_mutex label input').eq(1).removeAttr('name');
            $('.promotion_mutex label input').eq(2).removeAttr('name');
            $('.promotion_mutex label input').eq(1).prop('checked','checked');
            $('.promotion_mutex label input').eq(1).attr('disabled','disabled');
            $('.promotion_mutex label input').eq(2).prop('checked','checked');
            $('.promotion_mutex label input').eq(2).attr('disabled','disabled');
        }*/
        //包邮活动
        if(promotion_type == '20'){
            $('.promotion_mutex label input').eq(4).removeAttr('name');
            //$('.promotion_mutex label input').eq(4).prop('checked','checked');
            $('.promotion_mutex label input').eq(4).attr('disabled','disabled');
        }

        $('#shop_single_temp').hide();
        $('#except_id_box').empty();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        switch (promotion_scope) {
            case 'single':
                createComponents("/goods/getGoodsSearchComponents","#shop_single_temp",{
                    title:'<span  class="text-red">*</span>'+"添加商品",
                    description:"请输入商品名称或者商品ID(多个以逗号隔开)",
                    relate:{
                        id:"id",
                        name:"goods_name",
                        price:"price"
                    },
                    extend:{
                        columnStyle:"col-sm-3"
                    },
                    promotion_type : promotion_type
                });
                $("#NotJoinActivity").html("");
                $('#shop_single_temp').show();
                break;
        }
        //加价购不显示全场
        var all = $('#promotion_scope').find("option[value = 'all']");
        var all_html = "<option value='all' selected='selected'>全场</option>";
        if(promotion_type == '40'){
             all.remove();
        }else if(all.length < 1){
            $('#promotion_scope').prepend(all_html);
        }
    });

    //活动平台
    $("input[name='promotion_platform_pc']").on('click',function(){
        var promotion_platform_pc = $("input[name='promotion_platform_pc']:checked").length;
        $("input[name='promotion_platform_pc']").val(promotion_platform_pc);
    });
    $("input[name='promotion_platform_app']").on('click',function(){
        var promotion_platform_app = $("input[name='promotion_platform_app']:checked").length;
        $("input[name='promotion_platform_app']").val(promotion_platform_app);
    });
    $("input[name='promotion_platform_wap']").on('click',function(){
        var promotion_platform_wap = $("input[name='promotion_platform_wap']:checked").length;
        $("input[name='promotion_platform_wap']").val(promotion_platform_wap);
    });
    $("input[name='promotion_platform_wechat']").on('click',function(){
        var promotion_platform_wap = $("input[name='promotion_platform_wechat']:checked").length;
        $("input[name='promotion_platform_wechat']").val(promotion_platform_wap);
    });
    $('body').on('keyup','#join_times',function () {
        var nums = $(this).val();
        if( nums.length > 10){
            layer_required('位数不能大于10');
            $(this).val('');
        }
    });

    //适用人群
    $("#promotion_for_users").on('change',function(){
        var promotion_for_users = $(this).val();
        var join_times = $('#join_times').val();
        if(!$.trim(join_times) && promotion_for_users == 20){
            $('#join_times').val(1);
            $('#join_times').attr('readonly','readonly');
        }else {
            $('#join_times').removeAttr('readonly');
            $('#join_times').val('');
        }
    });

    //选择不同使用范围的特效
    $('#promotion_scope').on('change',function(){
        $('#shop_category').hide();
        $('#shop_brand_temp').hide();
        $('#shop_single_temp').hide();
        $('#except_id_box').empty();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var promotion_type = $("#promotion_type").val();

        switch (promotion_scope) {
            case 'all':
                var valicode=createValidateNotJoinActivity(promotion_scope);
                if(valicode==1){
                    _flagForValidate=1;
                }
                break;
            case 'category':
                $('#shop_category').show();
                var valicode=createValidateNotJoinActivity(promotion_scope);
                if(valicode==1){
                    _flagForValidate=1;
                }
                break;
            case 'brand':
                createBrandComponents("#shop_brand_temp");
                $('#shop_brand_temp').show();
                var valicode=createValidateNotJoinActivity(promotion_scope);
                if(valicode==1){
                    _flagForValidate=1;
                }
                break;
            case 'single':
                createComponents("/goods/getGoodsSearchComponents","#shop_single_temp",{
                    title:'<span  class="text-red">*</span>'+"添加商品",
                    description:"请输入商品名称或者商品ID(多个以逗号隔开)",
                    relate:{
                        id:"id",
                        name:"goods_name",
                        price:"price"
                    },
                    extend:{
                        columnStyle:"col-sm-3"
                    },
                    promotion_type : promotion_type
                });
                $("#NotJoinActivity").html("");
                $('#shop_single_temp').show();
                break;
        }

    });

    //添加规则行
    $('#addNextRule').on('click',function(){
        var _units = '<option value="yuan">元</option><option value="item">件</option>';
        var promotion_type = $("select[name='promotion_type'] option:checked").val();
        if($('#selector').val() == 1 && promotion_type == 15) {
            _units = '<option value="yuan">元</option>';
        }
        switch (promotion_type) {
            //满减
            case '5':
                $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td></td><td><label class="col-sm-3 control-label no-padding-right"> </label><input type="text" name="reduce_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 元 </label></td><td></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                break;
            //满折
            case '10':
                $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td></td><td><label class="col-sm-3 control-label no-padding-right"> </label><input type="text" name="discount_rate[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 折 </label></td><td></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                break;
            //满赠
            case '15':
                //选择叠加规则后，不能添加规则行
                if($("input[name='is_superimposed']:checked").val() == 1){
                    layer_required('选择叠加规则后，不能添加规则！');
                    return false;
                }
                //添加第二行时，不能是否叠加为空
                if($('.rule-line').length >= 1){
                    $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td><lable><a class="addPremiums" style="text-decoration: none;" href="javascript:void(0);">添加赠品</a></lable></td><td class="premium"></td><td></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                    $("input[name='is_superimposed']").eq(0).attr('disabled','disabled');
                }else{
                    $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td><lable><a class="addPremiums" style="text-decoration: none;" href="javascript:void(0);">添加赠品</a></lable></td><td class="premium"></td><td><div class="radio"><label><input name="is_superimposed" type="radio" class="ace" value="1"><span class="lbl">&nbsp;是</span></label><label><input name="is_superimposed" type="radio" class="ace" value="0" checked><span class="lbl">&nbsp;否</span></label></div></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                }
                break;
            //包邮
            case '20':
                $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td></td><td></td><td></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                if($('.rule-line').length == 1){
                    $('#addNextRule').hide();
                }
                break;
            //加价购
            case '40':
                //选择叠加规则后，不能添加规则行
                if($("input[name='is_superimposed']:checked").val() == 1){
                    layer_required('选择叠加规则后，不能添加规则！');
                    return false;
                }
                //添加第二行时，不能是否叠加为空
                if($('.rule-line').length >= 1){
                    $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td><lable><a class="addPremiums" style="text-decoration: none;" href="javascript:void(0);">添加换购品</a></lable></td><td class="premium"></td><td></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                    $("input[name='is_superimposed']").eq(0).attr('disabled','disabled');
                }else{
                    $('#rule-tbody').append('<tr class="rule-line"><td><label class="col-sm-3 control-label no-padding-right"> 满 </label><input type="text" name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" /><label class="control-label"> 单位 </label><select name="unit[]">'+_units+'</select></td><td><lable><a class="addPremiums" style="text-decoration: none;" href="javascript:void(0);">添加换购品</a></lable></td><td class="premium"></td><td></td><td><button type="button" class="btn btn-danger btn-sm del-rule-line">删除</button></td></tr>');
                }
                break;
        }

    });

    //实付对规则改变
    $('#selector').on("change", function () {
        if($(this).val() == 1){
            var _units='<option value="yuan">元</option>';
            layer_required("选择实付操作,规则只能为元")
        }else{
            var _units='<option value="yuan">元</option><option value="item">件</option>';
        }
        $(".rule-line").find("select[name='unit[]']").html(_units);
    })
    //删除规则行
    $('body').on('click','.del-rule-line',function(){
        var $this =$(this);
        layer.confirm('确认删除这条规则吗',{
            btn: ['是的','取消'],
            yes: function () {
                if($('.rule-line').length == 1){
                    layer_required('至少留有一条规则！');
                }else{
                    $this.parent().parent().remove();
                    if($('.rule-line').length <= 1){
                        $("input[name='is_superimposed']").eq(0).removeAttr('disabled');
                        layer.closeAll();
                        return false;
                    }
                    layer.closeAll();
                }
            }
        });
    });

    //添加促销活动
    $('#addPromotion').on('click',function(){
        var promotion_title = $('#promotion_title').val();
        var promotion_content = $('#promotion_content').val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var promotion_platform = $(".promotion_platform input:checked").length;
        var promotion_type = $("select[name='promotion_type'] option:checked").val();
        var join_times = $('#join_times').val();
        var nowTime = new Date().getTime();

        //验证活动名称
        if(!$.trim(promotion_title)){
            layer_required('活动名称不能为空！');return false;
        }
        //验证时间合法性
        if(!$.trim(start_time) || !$.trim(end_time)){
            layer_required('活动开始时间或结束时间不能为空！');return false;
        }
        if(startTime > endTime){
            layer_required('活动开始时间不能大于结束时间！');return false;
        }
        if(endTime < nowTime){
            layer_required('活动结束时间不能小于当前时间！');return false;
        }
        //验证活动平台
        if(promotion_platform == 0){
            layer_required('活动平台至少勾选一个！');return false;
        }
        //验证活动说明
        if(!$.trim(promotion_content)){
            layer_required('活动说明不能为空！');return false;
        }
        //单用户最多参与次数
        if($.trim(join_times)){
            if(join_times == 0){
                layer_required('单用户最多参与次数不能为0！');return false;
            }
            if(!regNumber.test(join_times)){
                layer_required('单用户最多参与次数只能是正整数');return false;
            }
        }

        /****** 验证使用范围start *****/
        switch (promotion_scope) {
            //分类验证
            case 'category':
                if(isCategorySelect() == false){
                    return false;
                }
                break;
            case 'brand':
                var brand_id = $('input[name="shop_brand"]').val();
                if(brand_id == '' || brand_id == undefined){
                    layer_required('请添加品牌！');return false;
                }
                break;
            case 'single':
                var goods_id = $('#shop_single').val();
                if(goods_id == '' || goods_id == undefined){
                    layer_required('请添加单品！');return false;
                }
        }
        /****** 验证使用范围end *****/


        /***** 验证规则start *****/
        //至少要有一条规则
        if($('.rule-line').length < 1){
            layer_required('请添加规则！');return false;
        }

        //公共验证门槛金额
        var _errorSign = false;
        $('input[name="full_price[]"]').each(function() {
            if($(this).val().length < 1){
                layer_required('门槛不能为空！');
                _errorSign = true;
                return false;
            }
            if ($.trim($(this).val()) == '') {
                layer_required('请填写门槛金额！');
                _errorSign = true;
                return false;
            } else if (!!isNaN($(this).val())) {
                layer_required('请正确填写门槛金额！');
                _errorSign = true;
                return false;
            } else if (parseFloat($(this).val()) <= 0) {
                layer_required('门槛金额必须大于0！');
                _errorSign = true;
                return false;
            }
        });

        //分别验证不同的规则条件
        switch (promotion_type) {
            //验证满减活动的规则
            case '5':
                //验证优惠金额
                $('input[name="reduce_price[]"]').each(function() {
                    if ($.trim($(this).val()) == '') {
                        layer_required('请填写优惠金额！');
                        _errorSign = true;
                        return false;
                    } else if (!!isNaN($(this).val())) {
                        layer_required('请正确填写优惠金额！');
                        _errorSign = true;
                        return false;
                    } else if (parseFloat($(this).val()) <= 0) {
                        layer_required('优惠金额必须大于0！');
                        _errorSign = true;
                        return false;
                    }else if(!regMoney.test($(this).val())){
                        layer_required('优惠金额最多是两位小数！');
                        _errorSign = true;
                        return false;
                    }
                    var enough_price = $(this).parent().parent().find('input[name="full_price[]"]').val();
                    var minus_price = $(this).val();
                    var unit=$(this).parent().parent().find('select[name="unit[]"]').val();
                    if(unit=="yuan"){
                        if ( parseFloat(enough_price) <= parseFloat(minus_price)) {
                        layer_required('门槛金额必须大于优惠金额！');
                        _errorSign = true;
                        return false;
                        }
                    }else{
                        if(enough_price<=0){
                            layer_required('件数不能低于0');
                            _errorSign = true;
                            return false;
                        }
                    }
                    

                });
            
                if (_errorSign) return;
                break;

            //验证满折活动的规则
            case '10':
                //验证折扣率
                $('input[name="discount_rate[]"]').each(function() {
                    if ($.trim($(this).val()) == '') {
                        layer_required('请填写折扣率！');
                        _errorSign = true;
                        return false;
                    } else if (!!isNaN($(this).val())) {
                        layer_required('请正确填写折扣率！');
                        _errorSign = true;
                        return false;
                    } else if (parseFloat($(this).val()) <= 0 || parseFloat($(this).val()) >= 10) {
                        layer_required('折扣率只能是0.1~9.9之间！');
                        _errorSign = true;
                        return false;
                    }
                    var enough_price = $(this).parent().parent().find('input[name="full_price[]"]').val();
                    var minus_price = $(this).val();
                    var unit=$(this).parent().parent().find('select[name="unit[]"]').val();
                    if(unit=="yuan"){
                        if ( parseFloat(enough_price) <= parseFloat(minus_price)) {
                            layer_required('门槛金额必须大于优惠金额！');
                            _errorSign = true;
                            return false;
                        }
                    }else{
                        if(enough_price<=0){
                            layer_required('件数不能低于0');
                            _errorSign = true;
                            return false;
                        }
                    }
                });
                if (_errorSign) return;
                break;

            //验证满赠活动的规则
            case '15':
                $(".rule-line").each(function () {
                   if($(this).find('td').eq(2).find('input[name="premiums_number[]"]').length < 1){
                       layer_required('赠品信息不能为空！');
                       _errorSign = true;
                       return false;
                   }
                });
                //验证赠品数量
                $('input[name="premiums_number[]"]').each(function() {
                    if ($.trim($(this).val()) == '') {
                        layer_required('请填写赠品数量！');
                        _errorSign = true;
                        return false;
                    } else if (!!isNaN($(this).val())) {
                        layer_required('请正确填写赠品数量！');
                        _errorSign = true;
                        return false;
                    }else if (parseInt($(this).val()) <= 0) {
                        layer_required('赠品数量必须大于0！');
                        _errorSign = true;
                        return false;
                    }
                    var enough_price = $(this).parent().parent().find('input[name="full_price[]"]').val();
                    var minus_price = $(this).val();
                    var unit=$(this).parent().parent().find('select[name="unit[]"]').val();
                    if(unit=="yuan"){
                        if ( parseFloat(enough_price) <= parseFloat(minus_price)) {
                            layer_required('门槛金额必须大于优惠金额！');
                            _errorSign = true;
                            return false;
                        }
                    }else{
                        if(enough_price<=0){
                            layer_required('件数不能低于0');
                            _errorSign = true;
                            return false;
                        }
                    }
                });
                if (_errorSign) return;
                break;

            //验证包邮活动的规则
            case '20':
                $('input[name="full_price[]"]').each(function () {
                    var unit=$(this).parent().parent().find('select[name="unit[]"]').val();
                    if($(this).val().length < 1){
                        layer_required('门槛不能设置空值');
                        _errorSign = true;
                        return false;
                    }
                    if($(this).val() <= 0){
                        layer_required('件数不能低于0');
                        _errorSign = true;
                        return false;
                    }
                });
                if (_errorSign) return;
                break;

            //验证加价购活动的规则
            case '40':
                $('.premium').each(function () {
                    if($(this).find('input[name="reduce_price[]"]').length < 1){
                        layer_required('选购商品不能为空！');
                        _errorSign = true;
                        return false;
                    }
                });
                $('input[name="reduce_price[]"]').each(function() {
                    if ($.trim($(this).val()) == '') {
                        layer_required('请填写加价购金额！');
                        _errorSign = true;
                        return false;
                    } else if (!!isNaN($(this).val())) {
                        layer_required('请正确填写加价购金额！');
                        _errorSign = true;
                        return false;
                    } else if (parseFloat($(this).val()) <= 0) {
                        layer_required('加价购金额必须大于0！');
                        _errorSign = true;
                        return false;
                    }else if(!regMoney.test($(this).val())){
                        layer_required('加价购金额最多是两位小数！');
                        _errorSign = true;
                        return false;
                    }
                    var enough_price = $(this).parent().parent().find('input[name="full_price[]"]').val();
                    var minus_price = $(this).val();
                    var unit=$(this).parent().parent().find('select[name="unit[]"]').val();
                    if(unit=="yuan"){
                        if ( parseFloat(enough_price) <= parseFloat(minus_price)) {
                            layer_required('门槛金额必须大于优惠金额！');
                            _errorSign = true;
                            return false;
                        }
                    }else{
                        if(enough_price<=0){
                            layer_required('件数不能低于0');
                            _errorSign = true;
                            return false;
                        }
                    }


                });

                if (_errorSign) return;
                break;
        }

        //公共验证门槛条件不能有相同
        var enoughPrices = $('input[name="full_price[]"]');
        var tempEnoughPrice = [];
        var priceType = $('select[name="unit[]"]');
        var tempPriceType = $(priceType[0]).val();
        var i = 0, len = 0, val = 0;
        for(i = 0, len = enoughPrices.length; i < len; i++){
            val = $(enoughPrices[i]).val();
            if($.inArray(val, tempEnoughPrice) !== -1){
                layer_required('门槛条件不能有相同！');return false;
            }
            tempEnoughPrice.push(val);
        }
        tempEnoughPrice = null;
        for(i = 1, len = priceType.length; i < len; i++){
            val = $(priceType[i]).val();
            if(val !== tempPriceType){
                layer_required('门槛单位要一致！');return false;
            }
        }

        /***** 验证规则end *****/
        $("#premiums_group").val(get_promotion_detail());

        //ajax提交
        ajaxSubmit('addPromotionForm');

    });
});
//验证标签

//获取当前的选中平台
function getCurrentPlatforms() {
    var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
    var promotion_platform_app = $("input[name='promotion_platform_app']").val();
    var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
    var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
    var platform=[];
    if(promotion_platform_pc == 1)
    {
        platform.push('pc');
    }
    if(promotion_platform_app == 1)
    {
        platform.push('app');
    }
    if(promotion_platform_wap == 1)
    {
        platform.push('wap');
    }
    if(promotion_platform_wechat == 1)
    {
        platform.push('wechat');
    }
    return platform;
}

/**
 * @author 邓永军
 * [添加赠品]
 * @param  {[type]} )   {              var $this [description]
 * @param  {[type]} 1);                                          s[8] [description]
 * @return {[type]}     [description]
 * @modify 吴俊华  添加验证
 */
$('body').on('click','.addPremiums',function () {
    var $this=$(this);
    var permium_num_arr = [];
    if($this.parent().parent().parent().find('input[name="premiums_id[]"]').length > 0){
        $this.parent().parent().parent().find('input[name="premiums_id[]"]').each(function () {
            permium_num_arr.push($(this).val());
        });
    }
    var full_price = $this.parent().parent().siblings().find('input[name="full_price[]"]').val();
    if ($.trim(full_price) == '') {
        layer_required('请填写门槛金额！');return false;
    }
    if (!!isNaN(full_price)) {
        layer_required('请正确填写门槛金额！');return false;
    }
    if (parseFloat(full_price) <= 0) {
        layer_required('门槛金额必须大于0！');return false;
    }

    var pre=function() {
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
    var base_html='<div class="form-horizontal"></div><div class="form-group"><div class="col-sm-7"><div class="input-group"><input class="form-control search_goods_input_'+pre+'" type="text" placeholder="请输入商品名称（可以多个）或者输入商品ID（可以多个）"><span class="input-group-btn"><button class="btn btn-sm btn-default do_search_btn_'+pre+'" type="button"><i class="ace-icon fa fa-search bigger-110"></i></button></span></div></div></div><div class=form-group><label class="col-sm-2 control-label no-padding-right"></label><div class=col-sm-11><div class="lister lister_'+pre+'"><ul class="left left_'+pre+'"></ul></div><div class="mid_panel"><div class="add_all add_all_'+pre+'"><a href="javascript:void(0);">全部添加>></a></div><div class="add add_'+pre+'"><a href="javascript:void(0);">添&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;加>></a></div><div class="del_all del_all_'+pre+'"><a href="javascript:void(0);"><< 全部删除</a></div><div class="del del_'+pre+'"><a href="javascript:void(0);"><< 删&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;除</a></div></div><div class="lister lister_'+pre+'"><ul class="right right_'+pre+'"></ul></div></div></div><div class="clearfix"><div class="col-md-offset-3 col-md-9"><button id=addPremiumsBtn class="btn btn-info" type=button><i class="ace-icon fa fa-check bigger-110"></i>确认</button>&nbsp; &nbsp; &nbsp;<button id=cancelPremiumsBtn class=btn type=button><i class="ace-icon fa fa-undo bigger-110"></i>取消</button></div></div></div>';
    var index=layer.open({
        type: 1,
        area: ['845px', '550px'],
        title: $("#promotion_type").val() == '40' ? '添加换购品':'添加赠品',
        shade: 0.6,
        moveType: 1,
        shift: 1,
        closeBtn:1,
        content:base_html
    });
    var _p=$this.parent().parent().parent().find("td.premium");
    var __p = _p.html();
    $("#addPremiumsBtn").on("click", function () {
        var _tempS=[];
        if(permium_num_arr.length > 0){
            var _sf = 1;
            $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                var arr = permium_num_arr;
                var value = $(this).data("mid");
                $.each(arr,function (n,v) {
                    if(v == value){
                        _sf = 0;
                    }
                })
            });
            if(_sf == 0){
                if($('#promotion_type').val() == '40')
                {
                    layer_required('不能重复添加换购品');
                }else{
                    layer_required('不能重复添加赠品');
                }
                return false;
            }
        }
        $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
            var dt=$(this).data("mid");
            var nm=$.trim($(this).text());
            if($('#promotion_type').val() == '40')
            {
                _p.html(__p + "<div style='height: auto'><div style='padding:5px 0;height: auto;background-color:#4f99c6;color: #fff;'>"+nm+"</div><a class=close_tips style='font-size: 18px;line-height:32px;margin-left:5px;text-decoration: none;' href='javascript:void(0);'>×</a><input name='premiums_id[]' type='hidden' value='"+dt+"'/><input type=text name='reduce_price[]' class='col-xs-10 col-sm-4' placeholder='加价购金额不能为空'></div>");
                __p = _p.html();
            }else{
                _p.html(__p + "<div style='height: auto'><div style='padding:5px 0;height: auto;background-color:#4f99c6;color: #fff;'>"+nm+"</div><a class=close_tips style='font-size: 18px;line-height:32px;margin-left:5px;text-decoration: none;' href='javascript:void(0);'>×</a><input name='premiums_id[]' type='hidden' value='"+dt+"'/><input type=text name='premiums_number[]' class='col-xs-10 col-sm-3' placeholder='数量不可为空'></div>");
                __p = _p.html();
            }

        });
        layer.close(index);
    })
    $("#cancelPremiumsBtn").on("click", function () {
        layer.close(index);
    })

    var return_result= function () {
        var _tempS=[];
        $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
            var dt=$(this).data("mid");
            _tempS.push(dt);
        });
        $this.parent().parent().parent().find("td.premium").find('input[name="premiums_id[]"]').val(_tempS.join(","));
    }
    if($("#promotion_type").val() == '40'){
        $.post("/goods/getGoodsSearchComponents",{promotion_type : $("#promotion_type").val()}, function (data) {
            if(data!="0"){
                var obj= data;
                $.each(obj, function (n,v) {
                    if(v["is_unified_price"] == "0"){
                        $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                    }else{
                        $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                    }
                });
                $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                    $(this).toggleClass("list_on");
                });
            }
        });
    }else{
        $.post("/goods/getGoodsForGift",{platform:getCurrentPlatforms(),prmotion_type:$("#promotion_type").val()}, function (data) {
            if(data!="0"){
                var obj= data;
                $.each(obj, function (n,v) {
                    if(v["is_unified_price"] == "0"){
                        $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                        /*if(v['whether_is_gift'] == 1){
                            var zpfd = 1;
                            $.each(getCurrentPlatforms(),function (i,n) {
                                switch (n){
                                    case 'pc':
                                        if(v['gift_pc'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                    case 'app':
                                        if(v['gift_app'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                    case 'wap':
                                        if(v['gift_wap'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                    case 'wechat':
                                        if(v['gift_wechat'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                }
                            });
                            if(zpfd == 1){
                                $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                            }
                        }else{
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                        }*/

                    }else{
                        $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                        /*if(v['whether_is_gift'] == 1){
                            var zpfd = 1;
                            $.each(getCurrentPlatforms(),function (i,n) {
                                switch (n){
                                    case 'pc':
                                        if(v['gift_pc'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                    case 'app':
                                        if(v['gift_app'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                    case 'wap':
                                        if(v['gift_wap'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                    case 'wechat':
                                        if(v['gift_wechat'] == 0){
                                            zpfd = 0;
                                        }
                                        break;
                                }
                            });
                            if(zpfd == 1){
                                $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                            }
                        }else{
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                        }*/

                    }
                });
                $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                    $(this).toggleClass("list_on");
                });
            }
        });
    }

    //添加操作

    $(".add_"+pre).on("click", function () {
        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
        var platform_gift=[];
        if(promotion_platform_pc == 1)
        {
            platform_gift.push('pc');
        }
        if(promotion_platform_app == 1)
        {
            platform_gift.push('app');
        }
        if(promotion_platform_wap == 1)
        {
            platform_gift.push('wap');
        }
        if(promotion_platform_wechat == 1)
        {
            platform_gift.push('wechat');
        }
        $(".lister_"+pre+" .left_"+pre+" li").find($(".list_on a")).each(function () {
            var _mid = $(this).data('mid');
            var _s = 1;
            $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                if($(this).data('mid') == _mid && _s == 1){
                    _s = 0;
                }
            });
            if(_s == 0){
                layer_required('不能添加重复赠品');
                return false;
            }
            $(".lister_"+pre+" .right_"+pre+" li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_"+pre+" .right_"+pre).append("<li>"+$(this).parent().html()+"</li>");
            $(this).parent()[0].remove();
            $(".lister_"+pre+" .right_"+pre+" li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            var mid=$(this).data("mid");

            $.post('/goods/isOnShelf',{platform:platform_gift,ids:mid},function (res) {
                if(res.code == '400'){
                    layer_required(res.msg);
                    return false;
                }
            })
        })
        return_result();
    });

    //删除操作
    $(".del_"+pre).on("click", function () {
        $(".lister_"+pre+" .right_"+pre+" li").find($(".list_on a")).each(function () {
            $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
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
        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
        var platform_gift=[];
        if(promotion_platform_pc == 1)
        {
            platform_gift.push('pc');
        }
        if(promotion_platform_app == 1)
        {
            platform_gift.push('app');
        }
        if(promotion_platform_wap == 1)
        {
            platform_gift.push('wap');
        }
        if(promotion_platform_wechat == 1)
        {
            platform_gift.push('wechat');
        }
        $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
            $(this).toggleClass("list_on");
        })
        $(".lister_" + pre + " .left_" + pre + " li").each(function () {
            if ($(this).hasClass("list_on")) $(this).removeClass("list_on");
        });
        $(".lister_"+pre+" .right_"+pre).html($(".lister_"+pre+" .right_"+pre).html()+$(".lister_"+pre+" .left_"+pre).html());
        $(".lister_" + pre + " .left_" + pre).html("");
        $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
            $(this).toggleClass("list_on");
        })
        $(".lister_" + pre + " .left_" + pre + " li a").each(function () {
            var _mid = $(this).data('mid');
            var _s = 1;
            $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
                if($(this).data('mid') == _mid && _s == 1){
                    _s = 0;
                }
            });
            if(_s == 0){
                layer_required('不能添加重复赠品');
                return false;
            }
            var mid=$(this).data("mid");
            $.post('/goods/isOnShelf',{platform:platform_gift,ids:$(this).data('mid')},function (res) {
                if(res.code == '400'){
                    layer_required(res.msg);
                    return false;
                }
            })
        });
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
    $(".do_search_btn_"+pre).on("click", function () {
        var promotion_type = $('#promotion_type').val();
        var search_goods_input=$(".search_goods_input_"+pre).val();
        var s_input={};
        if(search_goods_input!=""){
            s_input={input:search_goods_input,promotion_type:promotion_type};
        }else{
            layer_required('商品数据不能为空');
            return false;
        }

        if($("#promotion_type").val() == '40'){
            $.post("/goods/getGoodsSearchComponents",s_input, function (data) {
                if(data!="0"){
                    var obj= data;
                    $(".lister_"+pre+" .left_"+pre).html("");
                    $.each(obj, function (n,v) {
                        v=v[0];
                        if(v["is_unified_price"] == "0"){
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");

                        }else{

                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                        }
                    })
                    $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                }else{
                    layer_required('没有查询到任何结果');
                    return false;
                }
            });
        }else{
            $.post("/goods/getGoodsForGift",s_input, function (data) {
                if(data!="0"){
                    var obj= data;
                    $(".lister_"+pre+" .left_"+pre).html("");
                    $.each(obj, function (n,v) {
                        v=v[0];
                        if(v["is_unified_price"] == "0"){
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                            /*if(v['whether_is_gift'] == 1){
                                var zpfd = 1;
                                $.each(getCurrentPlatforms(),function (i,n) {
                                    switch (n){
                                        case 'pc':
                                            if(v['gift_pc'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                        case 'app':
                                            if(v['gift_app'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                        case 'wap':
                                            if(v['gift_wap'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                        case 'wechat':
                                            if(v['gift_wechat'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                    }
                                });
                                if(zpfd == 1){
                                    $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                            "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                                }
                            }else{
                                $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                            }*/

                        }else{
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                            /*if(v['whether_is_gift'] == 1){
                                var zpfd = 1;
                                $.each(getCurrentPlatforms(),function (i,n) {
                                    switch (n){
                                        case 'pc':
                                            if(v['gift_pc'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                        case 'app':
                                            if(v['gift_app'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                        case 'wap':
                                            if(v['gift_wap'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                        case 'wechat':
                                            if(v['gift_wechat'] == 0){
                                                zpfd = 0;
                                            }
                                            break;
                                    }
                                });
                                if(zpfd == 1){
                                    $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                            "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                                }
                            }else{
                                $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                        "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                            }*/

                        }
                    })
                    $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                }else{
                    layer_required('没有查询到任何结果');
                    return false;
                }
            });
        }

    });
});
$('body').on("click",".close_tips", function () {
    $(this).parent().remove();
});
//$("body").on("change","input[name='full_price[]']",function () {
//    if($(this).val()<=0){
//         layer_required("不能少于0");
//        $(this).val("");
//        return false;
//    }
//    if($(this).parent().parent().find('td:eq(2) input[name="reduce_price[]"]').val() !=""){
//        if(( $(this).val() - $(this).parent().parent().find('td:eq(2) input[name="reduce_price[]"]').val()) < 0 && $("#promotion_type").val() == 5 && $(this).parent().find('select[name="unit[]"]').val() =="yuan"){
//            layer_required('优惠金额大于条件');
//            $(this).val("");
//            return ;
//        }
//
//    }
//    if($(this).parent().find('select[name="unit[]"]').val() !="yuan"){
//        if (!regNumber.test($(this).val())) {
//            layer_required('只能输入正整数');
//            $(this).val("");
//            return;
//        }
//
//    }
//    if(!regMoney.test($(this).val())){
//        layer_required("请输入小于两位小数的数字");
//        $(this).val("");
//    }
//});
$("body").on("change","input[name='reduce_price[]']",function () {
    if($(this).val()<=0){
         layer_required("不能少于0");
        $(this).val("");
        return false;
    }
    if(!regMoney.test($(this).val())){
        layer_required("请输入小于两位小数的数字");
        $(this).val("");
    }
    if($(this).parent().parent().find('td:eq(0) input[name="full_price[]"]').val() !=""){
        if(($(this).val()-$(this).parent().parent().find('td:eq(0) input[name="full_price[]"]').val()) > 0 && $("#promotion_type").val() == 5 && $(this).parent().parent().find('td:eq(0) select[name="unit[]"]').val()=="yuan"){
            layer_required('优惠金额大于条件');
            $(this).val("");
            return ;
        }

    }
});

$("body").on("change","input[name='discount_rate[]']",function () {
    if($(this).val()<=0){
         layer_required("不能少于0");
        $(this).val("");
        return false;
    }
    if(!regDiscount.test($(this).val())){
        layer_required("请输入小于一位小数的数字");
        $(this).val("");
    }
});
$("body").on("change","input[name='premiums_number[]']",function () {
    if($(this).val()<=0){
         layer_required("不能少于0");
        $(this).val("");
        return false;
    }
    if(!regNumber.test($(this).val())){
        layer_required("请输入正整数");
        $(this).val("");
    }
});
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);  //获取url中"?"符后的字符串并正则匹配
    var context = "";
    if (r != null)
        context = r[2];
    reg = null;
    r = null;
    return context == null || context == "" || context == "undefined" ? "" : context;
}
$("body").on("focus","input",function () {
    $("#addPromotion").attr('disabled', 'disabled');
}).on('blur','input',function () {
    if(getQueryString('sign') != '0'){
        setTimeout(function () {
            $("#addPromotion").removeAttr('disabled');
        },750);
    }
});


/**
 * [获取促销满赠详情]
 * @author 邓永军
 * @returns {Array}
 */
function get_promotion_detail(){

    var _json="[";
    $(".rule-line").each(function (i) {
        var _t=[];
        var this_tr=$(this);
        var monney=this_tr.find("input[name='full_price[]']").val();
        var units=this_tr.find("select[name='unit[]']").val();
        if(i>0){
            _json+=",{";
        }else{
            _json+="{";
        }
        _json+='"full_price":"'+monney+'",';
        _json+='"unit":"'+units+'",';
        _json+='"premiums_group":[';
        var __t=0;
        this_tr.find("input[name='premiums_id[]']").each(function (r) {
            if(typeof _t[r] != "object"){
                _t[r]=[];
            }
            __t=r+1;
            _t[r]['premiums_id']=$(this).val();
        });
        this_tr.find("input[name='premiums_number[]']").each(function (r) {
            if(typeof _t[r] != "object"){
                _t[r]=[];
            }
            _t[r]['premiums_number']=$(this).val();
        });
        this_tr.find("input[name='reduce_price[]']").each(function (r) {
            if(typeof _t[r] != "object"){
                _t[r]=[];
            }
            _t[r]['premiums_number']=$(this).val();
        });
        for(var i=0;i<__t;i++){
            if(i>0){
                _json+=',{';
            }else{
                _json+='{';
            }
            _json+='"premiums_id":"'+_t[i]["premiums_id"]+'",';
            _json+='"premiums_number":"'+_t[i]["premiums_number"]+'"';
            _json+='}';
        }
        _json+=']';

        _json+="}";
    });
    _json+="]";
    return _json;
}
createValidateNotJoinActivity($("#promotion_scope").val());


/**
 * @author 邓永军
 * [promotion_scope_val description 编辑模块]
 * @type {[type]}
 */
var promotion_scope_val = $("#promotion_scope").val();
switch (promotion_scope_val){
    case "all":

        //不参加活动
        var expextCategoryIds=$("#expextCategoryIds").val();
        var expextBrandIds=$("#expextBrandIds").val();
        var expextSingleIds=$("#expextSingleIds").val();
        $("textarea[name='except_category_id']").val(expextCategoryIds);
        $("textarea[name='except_brand_id']").val(expextBrandIds);
        $("textarea[name='except_good_id']").val(expextSingleIds);
        break;
    case "category":
        $("#shop_category").html($("#select_category").html());
        $("#shop_category").show(0);
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
        //不参加活动
        var expextBrandIds=$("#expextBrandIds").val();
        var expextSingleIds=$("#expextSingleIds").val();
        $("textarea[name='except_brand_id']").val(expextBrandIds);
        $("textarea[name='except_good_id']").val(expextSingleIds);
        break;
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
            '<div class=form-group><label class="col-sm-3 control-label no-padding-right"><span  class="text-red">*</span>添加品牌</label><div class=col-sm-5><div class=" append_area_' +
            pre + '"><input class="col-sm-7 search_goods_input_' + pre +
            '" placeholder="品牌ID/名称" type=text><div ><button class="btn btn-sm btn-success do_search_btn_' + pre +
            '" type=button>搜索</button></div></div></div></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right">批量添加品牌</label><div class=col-sm-5><textarea rows="3" cols="5" class="form-control batch_add_brand_' +
            pre +
            '" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><div style="margin-bottom: 5px;"><button class="btn btn-sm do_validate_brand_batch_' +
            pre + '" type="button">验证</button></div><div><button class="btn btn-sm do_add_brand_batch_' + pre +
            '" type="button">添加</button></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>操作</th><tbody class="brand_table_body_' +
            pre + '"></table></div></div><input type="hidden" class="hiddenBrandids_' + pre +
            '" name="shop_brand" value="'+shop_brand_value+'"><div>';
        $("#shop_brand_temp").html(base_html);
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
                        $('.select_brand_list_'+pre).remove();
                        $('.do_select_btn_'+pre).remove();
                        layer_required("没有查询到任何结果");
                    }

                })
            } else {

                layer_required("输入不能为空");
            }

        })
        //不参加活动
        var expextSingleIds=$("#expextSingleIds").val();
        $("textarea[name='except_good_id']").val(expextSingleIds);
        break;
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
            '<div class="form-group"><label class="col-sm-3 control-label no-padding-right" ><span  class="text-red">*</span>添加商品</label><div class="col-sm-7"><div class="input-group"><input class="form-control search_goods_input_' +
            pre +
            '" type="text" placeholder="请输入商品名称（可以多个）或者输入商品ID（可以多个）"><span class="input-group-btn"><button class="btn btn-sm btn-default do_search_btn_' +
            pre +
            '" type="button"><i class="ace-icon fa fa-search bigger-110"></i></button></span></div></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-9><div class="lister lister_' +
            pre + '"><ul class="left left_' + pre + '"></ul></div><div class="mid_panel"><div class="add_all add_all_' +
            pre + '"><a href="javascript:void(0);">全部添加>></a></div><div class="add add_' + pre +
            '"><a href="javascript:void(0);">添        加>></a></div><div class="del_all del_all_' +
            pre + '"><a href="javascript:void(0);"><< 全部删除</a></div><div class="del del_' + pre +
            '"><a href="javascript:void(0);"><< 删        除</a></div></div><div class="lister lister_' +
            pre + '"><ul class="right right_' + pre + '"></ul></div></div></div>';
        $("#shop_single_temp").html(base_html);

        var return_result = function () {
            var _tempS = [];
            $(".lister_" + pre + " .right_" + pre + " li a").each(function () {
                var dt = $(this).data("mid");
                _tempS.push(dt);
            });
            $("#shop_single").val(_tempS.join(","));
        }

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


            $.post("/goods/getGoodsSearchComponents",{promotion_type : $("#promotion_type").val()}, function (data) {
                if (data != "0") {
                    var obj = data;

                    $.each(obj, function (n, v) {
                        if(v["is_unified_price"] == "0"){
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                        }else{
                            $(".lister_" + pre + " .left_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                    "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                        }
                    });
                    $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    });
                    /***************编辑操作*****************/
                    if($("#single_flag")[0]){
                        var dt=$("input[name='shop_single']").val();
                        $.post("/promotion/getGoodListByIds",{ids:dt,promotion_type : $("#promotion_type").val()}, function (data) {
                            $.each(data, function (n, v) {
                                if(v != null){
                                    if(v["is_unified_price"] == "0"){
                                        $(".lister_" + pre + " .right_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                                "id"] + "'>" + v["goods_name"] + "  原价:" + v["price"] + "</a></li>");
                                    }else{
                                        $(".lister_" + pre + " .right_" + pre).append("<li><a href=\"javascript:void(0);\" data-mid='" + v[
                                                "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                                    }
                                }

                            });
                            $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                                $(this).toggleClass("list_on");
                            })
                            var sg_str_arr=dt.split(",");
                            $(".lister_" + pre + " .left_" + pre+"  li a").each(function () {
                                for(var i=0;i<sg_str_arr.length;i++){
                                    if($(this).data("mid")==sg_str_arr[i]){
                                        $(this).parent().remove();
                                    }
                                }
                            });
                        })

                    }
                    /*************编辑操作结束***************/
                }
            });



        //添加操作
        $(".add_" + pre).on("click", function () {
            var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
            var promotion_platform_app = $("input[name='promotion_platform_app']").val();
            var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
            var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
            var platform_promotion=[];
            if(promotion_platform_pc == 1)
            {
                platform_promotion.push('pc');
            }
            if(promotion_platform_app == 1)
            {
                platform_promotion.push('app');
            }
            if(promotion_platform_wap == 1)
            {
                platform_promotion.push('wap');
            }
            if(promotion_platform_wechat == 1)
            {
                platform_promotion.push('wechat');
            }
            $(".lister_" + pre + " .left_" + pre + " li").find($(".list_on a")).each(function () {
                $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                $(".lister_" + pre + " .right_" + pre).append("<li>" + $(this).parent().html() + "</li>");
                $(this).parent()[0].remove();
                $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                var mid=$(this).data("mid");

                $.post('/goods/isOnShelf',{platform:platform_promotion,ids:mid},function (res) {
                    if(res.code == '400'){
                        layer_required(res.msg);
                        return false;
                    }
                })
            })
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
            var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
            var promotion_platform_app = $("input[name='promotion_platform_app']").val();
            var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
            var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
            var platform_promotion=[];
            if(promotion_platform_pc == 1)
            {
                platform_promotion.push('pc');
            }
            if(promotion_platform_app == 1)
            {
                platform_promotion.push('app');
            }
            if(promotion_platform_wap == 1)
            {
                platform_promotion.push('wap');
            }
            if(promotion_platform_wechat == 1)
            {
                platform_promotion.push('wechat');
            }
            $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_" + pre + " .left_" + pre + " li").each(function () {
                if ($(this).hasClass("list_on")) $(this).removeClass("list_on");
            });
            $(".lister_"+pre+" .right_"+pre).html($(".lister_"+pre+" .right_"+pre).html()+$(".lister_"+pre+" .left_"+pre).html());
            $(".lister_" + pre + " .left_" + pre).html("");
            $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_" + pre + " .left_" + pre + " li a").each(function () {
                var mid=$(this).data("mid");
                $.post('/goods/isOnShelf',{platform:platform_promotion,ids:$(this).data('mid')},function (res) {
                    if(res.code == '400'){
                        layer_required(res.msg);
                        return false;
                    }
                })
            });
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
                    input: search_goods_input,
                    promotion_type : $("#promotion_type").val()
                }
            }
            if(search_goods_input == ""){
            layer_required('商品搜索不能为空');
            return false;
        }
            $.post("/goods/getGoodsSearchComponents", s_input, function (data) {
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
                                    "id"] + "'>" + v["goods_name"] + "  pc价格:" + v["goods_price_pc"] + "元,app价格:" + v["goods_price_app"] + "元,wap价格:" + v["goods_price_wap"] + "元</a></li>");
                        }
                    })
                    $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                    if($("#single_flag")[0]){
                        var dt=$("input[name='shop_single']").val();
                        var sg_str_arr=dt.split(",");
                        $(".lister_" + pre + " .left_" + pre+"  li a").each(function () {
                            for(var i=0;i<sg_str_arr.length;i++){
                                if($(this).data("mid")==sg_str_arr[i]){
                                    $(this).parent().remove();
                                }
                            }
                        });
                    }


                }else{
                    layer_required('搜索结果为空');
                    //$(".lister_"+pre+" .left_"+pre+" li").remove();
                }
            });
        });



        break;
}
if($("#ruleValue")[0]){
    var ruleValue = $("#premiums_group_detail").val();
    var _obj= $.parseJSON(ruleValue);
    var _units='<option value="yuan">元</option><option value="item">件</option>';
    var _units_item='<option value="yuan">元</option><option value="item" selected>件</option>';
    if($('input[name="promotion_is_real_pay"]').is(':checked')) {
        _units='<option value="yuan">元</option>';
    }
    var promotion_type = $("select[name='promotion_type'] option:checked").val();
    var rule_tbody=$("#rule-tbody");
    switch (promotion_type){
        case "5":
            //满减
            $.each(_obj, function (n,v) {
                var _uu="";
                if(v['unit']=="item"){
                    _uu = _units_item
                }else{
                    _uu=_units;
                }

                rule_tbody.append('<tr class=rule-line><td><label class="col-sm-3 control-label no-padding-right">满</label><input type=text name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['full_price']+'"><label class=control-label>单位</label><select name="unit[]">'+_uu+'</select><td><td><label class="col-sm-3 control-label no-padding-right"></label><input type=text name="reduce_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['reduce_price']+'"><label class=control-label>元</label><td><td><button type=button class="btn btn-danger btn-sm del-rule-line">删除</button>');
            });
            break;
        case "10":
            //满折
            $.each(_obj, function (n,v) {
                var _uu="";
                if(v['unit']=="item"){
                    _uu = _units_item
                }else{
                    _uu=_units;
                }
                rule_tbody.append('<tr class=rule-line><td><label class="col-sm-3 control-label no-padding-right">满</label><input type=text name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['full_price']+'"><label class=control-label>单位</label><select name="unit[]">'+_uu+'</select><td><td><label class="col-sm-3 control-label no-padding-right"></label><input type=text name="discount_rate[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['discount_rate']+'"><label class=control-label>折</label><td><td><button type=button class="btn btn-danger btn-sm del-rule-line">删除</button>');
            });
            break;
        case "15":
            $.each(_obj, function (n,v) {
                var _uu="";
                if(v['unit']=="item"){
                    _uu = _units_item
                }else{
                    _uu=_units;
                }
                var is_superimposed=$("#is_superimposed").val();
                var _superimposed="";
                if(n<1){
                    var _hh="";
                    if(is_superimposed=="1"){
                        _hh="checked";
                    }
                    var _hhr="";
                    if(is_superimposed=="0"){
                        _hhr="checked";
                    }
                    var _hhb="";
                    if(_hhr=="checked"){
                        if($("#promotion_id").val()=="")_hhb="disabled";
                    }
                    _superimposed='<label><input name=is_superimposed type=radio class=ace value=1 '+_hh+' '+_hhb+'><span class=lbl>&nbsp;是</span></label><label><input name=is_superimposed type=radio class=ace value=0 '+_hhr+'><span class=lbl>&nbsp;否</span></label>';
                }
                var _ss="";
                $.each(v["premiums_group"], function (p,m) {
                    _ss+='<div style="height: auto"><div style="padding:5px 0;height: auto;background-color:#4f99c6;color: #fff;">'+m["premiums_title"]+'&nbsp;&nbsp;原价:'+m["premiums_price"]+'</div><a class="close_tips" style="font-size: 18px;line-height:32px;margin-left:5px;text-decoration: none;" href="javascript:void(0);">×</a><input name="premiums_id[]" type="hidden" value="'+m["premiums_id"]+'"><input type="text" name="premiums_number[]" class="col-xs-10 col-sm-3" placeholder="数量不可为空" value="'+m["premiums_number"]+'"></div>';
                })
                rule_tbody.append('<tr class=rule-line><td><label class="col-sm-3 control-label no-padding-right">满</label><input type=text name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['full_price']+'"><label class=control-label>单位</label><select name="unit[]">'+_uu+'</select><td><lable><a class=addPremiums style="text-decoration: none;" href="javascript:void(0);">添加赠品</a></lable><td class=premium>'+_ss+'<td><div class=radio>'+_superimposed+'</div><td><button type=button class="btn btn-danger btn-sm del-rule-line">删除</button>');
            });
            //满赠
            break;
        case "20":
            $.each(_obj, function (n,v) {
                var _uu="";
                if(v['unit']=="item"){
                    _uu = _units_item
                }else{
                    _uu=_units;
                }

                rule_tbody.append('<tr class=rule-line><td><label class="col-sm-3 control-label no-padding-right">满</label><input type=text name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['full_price']+'"><label class=control-label>单位</label><select name="unit[]">'+_uu+'</select><td><td><label class="col-sm-3 control-label no-padding-right"></label><td><td><button type=button class="btn btn-danger btn-sm del-rule-line">删除</button>');
                $("#addNextRule").hide(0);
            });
            //包邮
            break;
        case '40':
            $.each(_obj, function (n,v) {
                var _uu="";
                if(v['unit']=="item"){
                    _uu = _units_item
                }else{
                    _uu=_units;
                }
                var is_superimposed=$("#is_superimposed").val();
                var _superimposed="";
                if(n<1){
                    var _hh="";
                    if(is_superimposed=="1"){
                        _hh="checked";
                    }
                    var _hhr="";
                    if(is_superimposed=="0"){
                        _hhr="checked";
                    }
                    var _hhb="";
                    if(_hhr=="checked"){
                        if($("#promotion_id").val()=="")_hhb="disabled";
                    }
                    _superimposed='<label><input name=is_superimposed type=radio class=ace value=1 '+_hh+' '+_hhb+'><span class=lbl>&nbsp;是</span></label><label><input name=is_superimposed type=radio class=ace value=0 '+_hhr+'><span class=lbl>&nbsp;否</span></label>';
                }
                var _ss="";
                $.each(v["reduce_group"], function (p,m) {
                    _ss+='<div style="height: auto"><div style="padding:5px 0;height: auto;background-color:#4f99c6;color: #fff;">'+m["reduce_title"]+'&nbsp;&nbsp;原价:'+m["price"]+'</div><a class="close_tips" style="font-size: 18px;line-height:32px;margin-left:5px;text-decoration: none;" href="javascript:void(0);">×</a><input name="premiums_id[]" type="hidden" value="'+m["product_id"]+'"><input type="text" name="reduce_price[]" class="col-xs-10 col-sm-3" placeholder="加价购金额不能为空" value="'+m["reduce_price"]+'"></div>';
                })
                rule_tbody.append('<tr class=rule-line><td><label class="col-sm-3 control-label no-padding-right">满</label><input type=text name="full_price[]" class="col-xs-10 col-sm-5" placeholder="不可为空" value="'+v['full_price']+'"><label class=control-label>单位</label><select name="unit[]">'+_uu+'</select><td><lable><a class=addPremiums style="text-decoration: none;" href="javascript:void(0);">添加换购品</a></lable><td class=premium>'+_ss+'<td><td><button type=button class="btn btn-danger btn-sm del-rule-line">删除</button>');
            });
            //加价购
            break;
    }

}


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
        if($('#addPromotion')[0]){
            $('#addPromotion').attr("disabled","disabled");
        }

    }else{
        if($('#addPromotion')[0]){
          //  $('#addPromotion').removeAttr("disabled");
        }
    
    }
});

$("body").on('blur','input[name="full_price[]"],input[name="reduce_price[]"]',function () {
    var num = $(this).val();
    var type = $(this).next().next().val();
    if(type == 'item'){
        if(num.length > 0) $(this).val(parseInt(num));
    }else{
        if(num.length > 0) $(this).val(parseFloat(num).toFixed(2));
    }
    if(type != undefined){
        if($(this).val()<=0){
            layer_required("不能少于0");
            $(this).val("");
            return false;
        }
        if($(this).parent().parent().find('td:eq(2) input[name="reduce_price[]"]').val() !=""){
            if(( $(this).val() - $(this).parent().parent().find('td:eq(2) input[name="reduce_price[]"]').val()) < 0 && $("#promotion_type").val() == 5 && $(this).parent().find('select[name="unit[]"]').val() =="yuan"){
                layer_required('优惠金额大于条件');
                $(this).val("");
                return ;
            }

        }
        if($(this).parent().find('select[name="unit[]"]').val() !="yuan"){
            if (!regNumber.test($(this).val())) {
                layer_required('只能输入正整数');
                $(this).val("");
                return;
            }

        }
        if(!regMoney.test($(this).val())){
            layer_required("请输入小于两位小数的数字");
            $(this).val("");
        }
    }
});

