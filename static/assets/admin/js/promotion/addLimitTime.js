/**
 * @desc 添加和编辑限时优惠的js验证
 * @author 吴俊华
 * @date 2016-09-09
 */
$(function() {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});
    var _flagForValidate=0;

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

    //添加限时优惠
    $('#addLimitPromotion').on('click',function(){
        //获取document的值
        var promotion_title = $('#promotion_title').val();
        var promotion_content = $('#promotion_content').val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var promotion_platform = $(".promotion_platform input:checked").length;
        var promotion_number = $("input[name='promotion_number']").val();
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

        var shop_single=$("#shop_single").val();
        var shop_single_arr=shop_single.split(",");
        if(shop_single_arr.length<1 || shop_single==""){
            layer_required('请添加单品！');return false;
        }else{
            var _fg=1;
            var offer_type=$("#offer_type").val();
            switch (offer_type){
                case "1":
                    $(".discount_num").each(function () {
                        if($(this).val()==""){
                            _fg=0;
                            layer_required("折扣不能为空");
                            return false;
                        }
                    });
                    var _strJson='';
                    $("#JgoodsTabTbody tr").each(function (i) {
                        var $this=$(this);
                        var id=$this.find("td:eq(0)").html();
                        if($this.data('is_unified_price') == '0'){
                            var discount=$this.find("td:eq(3) .discount_num").val();
                            if(i>0){
                                _strJson+=',';
                            }
                            _strJson+='{"id":"'+id+'","type":"1","discount":"'+discount+'"}';
                        }
                        if($this.data('is_unified_price') == '1'){
                            var pc = '';
                            var app = '';
                            var wap = '';
                            var wechat = '';
                            var platform_value = '';
                            if($this.find("td:eq(3) .discount_num")[0]){
                                if($this.find("td:eq(3) .discount_num").data('sid') == id){
                                    platform_value = $this.find("td:eq(3) .discount_num").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'app':
                                            app = $this.find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wap':
                                            wap = $this.find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.find("td:eq(3) .discount_num").val();
                                            break;
                                    }
                                }
                            }
                            if($this.next().find("td:eq(3) .discount_num")[0]){
                                if($this.next().find("td:eq(3) .discount_num").data('sid') == id){
                                    platform_value = $this.next().find("td:eq(3) .discount_num").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'app':
                                            app = $this.next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wap':
                                            wap = $this.next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.next().find("td:eq(3) .discount_num").val();
                                            break;
                                    }
                                }
                            }
                            if($this.next().next().find("td:eq(3) .discount_num")[0]){
                                if($this.next().next().find("td:eq(3) .discount_num").data('sid') == id){
                                    platform_value = $this.next().next().find("td:eq(3) .discount_num").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'app':
                                            app = $this.next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wap':
                                            wap = $this.next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                    }
                                }
                            }
                            if($this.next().next().next().find("td:eq(3) .discount_num")[0]){
                                if($this.next().next().next().find("td:eq(3) .discount_num").data('sid') == id){
                                    platform_value = $this.next().next().next().find("td:eq(3) .discount_num").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.next().next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'app':
                                            app = $this.next().next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wap':
                                            wap = $this.next().next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.next().next().next().find("td:eq(3) .discount_num").val();
                                            break;
                                    }
                                }
                            }
                            if(i>0){
                                _strJson+=',';
                            }
                            _strJson+='{"id":"'+id+'","type":"2","discount":{"pc":"'+pc+'","app":"'+app+'","wap":"'+wap+'","wechat":"'+wechat+'"}}';
                        }
                    })
                    $("#offer_goods").val('['+_strJson+']');
                    break;
                case "2":
                    $(".discount_price").each(function () {
                        if($(this).val()==""){
                            layer_required("优惠价不能为空");
                            _fg=0;
                            return false;
                        }
                    })
                    var _strJson='';
                    $("#JgoodsTabTbody tr").each(function (i) {
                        var $this=$(this);
                        var id=$this.find("td:eq(0)").html();
                        if($this.data('is_unified_price') == '0'){
                            var discount_price=$this.find("td:eq(4) .discount_price").val();
                            if(i>0){
                                _strJson+=',';
                            }
                            _strJson+='{"id":"'+id+'","type":"1","offers":"'+discount_price+'"}';
                        }
                        if($this.data('is_unified_price') == '1'){
                            var pc = '';
                            var app = '';
                            var wap = '';
                            var wechat = '';
                            var platform_value = '';
                            if($this.find("td:eq(4) .discount_price")[0]){
                                if($this.find("td:eq(4) .discount_price").data('sid') == id){
                                    platform_value = $this.find("td:eq(4) .discount_price").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'app':
                                            app = $this.find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wap':
                                            wap = $this.find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.find("td:eq(4) .discount_price").val();
                                            break;
                                    }
                                }
                            }
                            if($this.next().find("td:eq(4) .discount_price")[0]){
                                if($this.next().find("td:eq(4) .discount_price").data('sid') == id){
                                    platform_value = $this.next().find("td:eq(4) .discount_price").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'app':
                                            app = $this.next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wap':
                                            wap = $this.next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.next().find("td:eq(4) .discount_price").val();
                                            break;
                                    }
                                }
                            }
                            if($this.next().next().find("td:eq(4) .discount_price")[0]){
                                if($this.next().next().find("td:eq(4) .discount_price").data('sid') == id){
                                    platform_value = $this.next().next().find("td:eq(4) .discount_price").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'app':
                                            app = $this.next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wap':
                                            wap = $this.next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                    }
                                }
                            }
                            if($this.next().next().next().find("td:eq(4) .discount_price")[0]){
                                if($this.next().next().next().find("td:eq(4) .discount_price").data('sid') == id){
                                    platform_value = $this.next().next().next().find("td:eq(4) .discount_price").data('platform');
                                    switch (platform_value)
                                    {
                                        case 'pc':
                                            pc = $this.next().next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'app':
                                            app = $this.next().next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wap':
                                            wap = $this.next().next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                        case 'wechat':
                                            wechat = $this.next().next().next().find("td:eq(4) .discount_price").val();
                                            break;
                                    }
                                }

                            }
                            if(i>0){
                                _strJson+=',';
                            }
                            _strJson+='{"id":"'+id+'","type":"2","offers":{"pc":"'+pc+'","app":"'+app+'","wap":"'+wap+'","wechat":"'+wechat+'"}}';
                        }
                    })
                    $("#offer_goods").val('['+_strJson+']');
                    break;
            }
        }
        if(_fg==1){
            //ajax提交
           ajaxSubmit('addLimitPromotionForm');
        }
    });
});

//createValidateNotJoinActivity($("#promotion_scope").val());