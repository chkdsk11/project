/**
 * @author 邓永军
 * @desc 生成添加搜索类组件
 * api 接口地址 String
 * ele dom元素地址 String
 * info {
 * title:value,
 * description:value
 * relate:{
 * id:value
 * name:value
 * }
 * } Object
 * */
  function createComponents(api,ele,info){

    //生成uuid附加到Class里 防止组件交叉Crash

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

    //组件基础样式HTML
    var style=info.extend.columnStyle;
    if(style=="")style="col-sm-2";
    var base_html='<div class="form-group"><label class="'+style+' control-label no-padding-right" >'+info.title+'</label><div class="col-sm-7"><div class="input-group"><input class="form-control search_goods_input_'+pre+'" type="text" placeholder="'+info.description+'"><span class="input-group-btn"><button class="btn btn-sm btn-default do_search_btn_'+pre+'" type="button"><i class="ace-icon fa fa-search bigger-110"></i></button></span></div></div></div><div class=form-group><label class="'+style+' control-label no-padding-right"></label><div class=col-sm-9><div class="lister lister_'+pre+'"><ul class="left left_'+pre+'"></ul></div><div class="mid_panel"><div class="add_all add_all_'+pre+'"><a href="javascript:void(0);">全部添加>></a></div><div class="add add_'+pre+'"><a href="javascript:void(0);">添&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;加>></a></div><div class="del_all del_all_'+pre+'"><a href="javascript:void(0);"><< 全部删除</a></div><div class="del del_'+pre+'"><a href="javascript:void(0);"><< 删&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;除</a></div></div><div class="lister lister_'+pre+'"><ul class="right right_'+pre+'"></ul></div></div></div>';
    $(ele).html(base_html);

    //组件人为每个操作后调用此方法进行对根元素附加id数据
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

    var return_result= function () {
        var _tempS=[];
        $(".lister_"+pre+" .right_"+pre+" li a").each(function () {
            var dt=$(this).data("mid");
            _tempS.push(dt);
        });
        $(ele).attr("data-ids",_tempS.join(","));
        $("#shop_single").val(_tempS.join(","));
    }

    //组件启动进行异步加载左栏数据

         $.post(api, function (data) {
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


    //添加操作
    $(".add_"+pre).on("click", function () {

        var mids_arr=[];
        $(".lister_"+pre+" .left_"+pre+" li").find($(".list_on a")).each(function () {
            var mid=$(this).data("mid");
            mids_arr.push(mid);
        });
        var mids=mids_arr.join(",");

        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var promotion_type = $("select[name='promotion_type'] option:checked").val();
        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
        var promotion_platform = $(".promotion_platform input:checked").length;
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        //验证活动平台
        if(promotion_platform == 0){
            layer_required('活动平台至少勾选一个！');return false;
        }
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
        var jsonData = {
            shop_single : mids,
            promotion_id : promotion_id,
            promotion_scope : promotion_scope,
            promotion_type : promotion_type,
            promotion_start_time: start_time,
            promotion_end_time: end_time,
            promotion_for_users : promotion_for_users,
            promotion_platform_pc : promotion_platform_pc,
            promotion_platform_app : promotion_platform_app,
            promotion_platform_wap : promotion_platform_wap,
            promotion_platform_wechat : promotion_platform_wechat
        }

        $.post('/promotion/verifyTimeRange',jsonData,function(data){
            if(!data){
                layer_error('操作失败');
                return false;
            }
            if(data.status == 'error'){
                layer_error(data.info);
                return false;
            }
            if(data.status == 'success'){
                $(".lister_"+pre+" .left_"+pre+" li").find($(".list_on a")).each(function () {
                    $(".lister_"+pre+" .right_"+pre+" li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })

                    $(".lister_"+pre+" .right_"+pre).append("<li>"+$(this).parent().html()+"</li>");
                    $(this).parent()[0].remove();
                    $(".lister_"+pre+" .right_"+pre+" li").on("click", function () {
                        $(this).toggleClass("list_on");
                    })
                    var mid=$(this).data("mid");

                    $.post('/goods/isOnShelf',{platform:platform,ids:mid},function (res) {
                        if(res.code == '400'){
                            layer_required(res.msg);
                            return false;
                        }
                    })
                })

                return_result();
            }
        },'json');

    });

    //删除操作
    $(".del_"+pre).on("click", function () {
        $(".lister_"+pre+" .right_"+pre+" li").find($(".list_on a")).each(function () {
            $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                $(this).toggleClass("list_on");
            })
            $(".lister_"+pre+" .left_"+pre).append("<li>"+$(this).parent().html()+"</li>");
            $(this).parent()[0].remove();
            $(".lister_"+pre+" .left_"+pre+" li").on("click", function () {
                $(this).toggleClass("list_on");
            })
        })
        return_result();
    });

    //添加全部
    $(".add_all_"+pre).on("click", function () {


        var mids_arr=[];
        $(".lister_"+pre+" .left_"+pre+" li").find($("a")).each(function () {
            var mid=$(this).data("mid");
            mids_arr.push(mid);
        });
        var mids=mids_arr.join(",");

        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var promotion_type = $("select[name='promotion_type'] option:checked").val();
        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
        var promotion_platform = $(".promotion_platform input:checked").length;
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        //验证活动平台
        if(promotion_platform == 0){
            layer_required('活动平台至少勾选一个！');return false;
        }
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
        var jsonData = {
            shop_single : mids,
            promotion_id : promotion_id,
            promotion_scope : promotion_scope,
            promotion_type : promotion_type,
            promotion_for_users : promotion_for_users,
            promotion_start_time: start_time,
            promotion_end_time: end_time,
            promotion_platform_pc : promotion_platform_pc,
            promotion_platform_app : promotion_platform_app,
            promotion_platform_wap : promotion_platform_wap,
            promotion_platform_wechat : promotion_platform_wechat,
        }

        $.post('/promotion/verifyTimeRange',jsonData,function(data) {
            if (!data) {
                layer_error('操作失败');
                return false;
            }
            if (data.status == 'error') {
                layer_error(data.info);
                return false;
            }
            if (data.status == 'success') {
                $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                $(".lister_"+pre+" .left_"+pre+" li").each(function () {
                    if($(this).hasClass("list_on"))$(this).removeClass("list_on");

                });
                $(".lister_" + pre + " .left_" + pre + " li a").each(function () {
                    var mid=$(this).data("mid");
                    $.post('/goods/isOnShelf',{platform:platform,ids:$(this).data('mid')},function (res) {
                        if(res.code == '400'){
                            layer_required(res.msg);
                            return false;
                        }
                    })
                });
                $(".lister_"+pre+" .right_"+pre).html($(".lister_"+pre+" .right_"+pre).html()+$(".lister_"+pre+" .left_"+pre).html());
                $(".lister_"+pre+" .left_"+pre).html("")
                $(".lister_" + pre + " .right_" + pre + " li").on("click", function () {
                    $(this).toggleClass("list_on");
                })
                return_result();
            }
        },'json');

    });

    //删除全部
    $(".del_all_"+pre).on("click", function () {
        $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
            $(this).toggleClass("list_on");
        })
        $(".lister_"+pre+" .right_"+pre+" li").each(function () {
            if($(this).hasClass("list_on"))$(this).removeClass("list_on");
        });
        $(".lister_"+pre+" .left_"+pre).html($(".lister_"+pre+" .left_"+pre).html()+$(".lister_"+pre+" .right_"+pre).html());
        $(".lister_"+pre+" .right_"+pre).html("")
        $(".lister_" + pre + " .left_" + pre + " li").on("click", function () {
            $(this).toggleClass("list_on");
        })
        return_result();
    });

    //点击搜索对输入框根据条件进行查询并列入左框

    $(".do_search_btn_"+pre).on("click", function () {
        var search_goods_input=$(".search_goods_input_"+pre).val();
        var s_input={};
        if(search_goods_input!=""){
            s_input={input:search_goods_input}
        }
        if(search_goods_input == ""){
            layer_required('商品搜索不能为空');
            return false;
        }
        $.post(api,s_input, function (data) {
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
                layer_required('搜索结果为空');
                //$(".lister_"+pre+" .left_"+pre+" li").remove();
            }
        });
    });
}
//动态添加品牌

function do_select_click_x(pre) {
    var brandID = $('.select_brand_list_' + pre).find("option:selected").val();
    var brandName = $.trim($('.select_brand_list_' + pre).find("option:selected").text());

    var limit_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
    var limit_scope = $("select[name='promotion_scope'] option:checked").val();
    var limit_type = $("#promotion_type").val();
    var start_time = $('#start_time').val();
    var end_time = $('#end_time').val();
    var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
    var limit_platform_pc = $("input[name='promotion_platform_pc']").val();
    var limit_platform_app = $("input[name='promotion_platform_app']").val();
    var limit_platform_wap = $("input[name='promotion_platform_wap']").val();
    var limit_platform_wechat = $("input[name='promotion_platform_wechat']").val();
    var promotion_platform = $(".promotion_platform input:checked").length;
    //验证活动平台
    if(promotion_platform == 0){
        layer_required('活动平台至少勾选一个！');return false;
    }

    var jsonData = {
        shop_brand : brandID,
        promotion_id : limit_id,
        promotion_scope : limit_scope,
        promotion_type : limit_type,
        promotion_start_time: start_time,
        promotion_end_time: end_time,
        promotion_for_users : promotion_for_users,
        promotion_platform_pc : limit_platform_pc,
        promotion_platform_app : limit_platform_app,
        promotion_platform_wap : limit_platform_wap,
        promotion_platform_wechat : limit_platform_wechat
    }

    $.post('/limitbuy/verifyTimeRange',jsonData,function(data) {
        if (!data) {
            layer_error('操作失败');
            return false;
        }
        if (data.status == 'error') {
            layer_error(data.info);
            return false;
        }
        if (data.status == 'success') {

            if ($(".hiddenBrandids_" + pre).val() == "") {
                $(".brand_table_body_" + pre).append("<tr class='brand_insert'><td>" + brandID + "</td><td>" + brandName +
                "</td><td><input type='text' placeholder='数量'></td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
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
                    $(".brand_table_body_" + pre).append("<tr class='brand_insert'><td>" + brandID + "</td><td>" + brandName +
                    "</td><td><input type='text' placeholder='数量'></td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");

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
    },'json');
}

//动态添加品牌
function do_select_click(pre){
    var brandID=$('.select_brand_list_'+pre).find("option:selected").val();
    var brandName= $.trim($('.select_brand_list_'+pre).find("option:selected").text());
    var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
    var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
    var promotion_type = $("select[name='promotion_type'] option:checked").val();
    var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
    var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
    var promotion_platform_app = $("input[name='promotion_platform_app']").val();
    var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
    var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
    var promotion_platform = $(".promotion_platform input:checked").length;
    var start_time = $('#start_time').val();
    var end_time = $('#end_time').val();
    //验证活动平台
    if(promotion_platform == 0){
        layer_required('活动平台至少勾选一个！');return false;
    }

    var jsonData = {
        shop_brand : brandID,
        promotion_id : promotion_id,
        promotion_scope : promotion_scope,
        promotion_type : promotion_type,
        promotion_start_time: start_time,
        promotion_end_time: end_time,
        promotion_for_users : promotion_for_users,
        promotion_platform_pc : promotion_platform_pc,
        promotion_platform_app : promotion_platform_app,
        promotion_platform_wap : promotion_platform_wap,
        promotion_platform_wechat : promotion_platform_wechat
    }

    $.post('/promotion/verifyTimeRange',jsonData,function(data){
        if(!data){
            layer_error('操作失败');
            return false;
        }
        if(data.status == 'error'){
            layer_error(data.info);
            return false;
        }
        if(data.status == 'success'){
            if($(".hiddenBrandids_"+pre).val()==""){
                $(".brand_table_body_"+pre).append("<tr><td>"+brandID+"</td><td>"+brandName+"</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
                var _tempPool=[];
                $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                    var v=$(this).html();
                    _tempPool.push(v);
                })
                $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
            }else{
                //查询是否重复
                var ch_same=$(".hiddenBrandids_"+pre).val();
                var ch_arr=ch_same.split(",");
                var _s="1";
                $.each(ch_arr, function (n,v) {
                    if(v==brandID){
                        _s="0";
                        layer_required("请不要重复添加");
                        return false;
                    }
                });
                if(_s=="1"){
                    $(".brand_table_body_"+pre).append("<tr><td>"+brandID+"</td><td>"+brandName+"</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");

                    var _tempPool=[];
                    $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                        var v=$(this).html();
                        _tempPool.push(v);
                    })
                    $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
                }

            }

            $(".brand_table_body_"+pre+" tr td a.del").on("click", function () {
                $(this).parent().parent().remove();
                if($(".brand_table_body_"+pre+" tr")[0]){
                    var _tempPool=[];
                    $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                        var v=$(this).html();
                        _tempPool.push(v);
                    })
                    $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
                }else{
                    $(".hiddenBrandids_"+pre).val("");
                }
            })
        }
    },'json');

}

function createBrandComponents(ele){
    //生成uuid附加到Class里 防止组件交叉Crash
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

    var base_html='<div class=form-group><label class="col-sm-3 control-label no-padding-right"><span  class="text-red">*</span>添加品牌</label><div class=col-sm-5><div class=" append_area_'+pre+'"><input class="col-sm-7 search_goods_input_'+pre+'" placeholder="品牌ID/名称" type=text><div ><button class="btn btn-sm btn-success do_search_btn_'+pre+'" type=button>搜索</button></div></div></div></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right">批量添加品牌</label><div class=col-sm-5><textarea rows="3" cols="5" class="form-control batch_add_brand_'+pre+'" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><div style="margin-bottom: 5px;"><button class="btn btn-sm do_validate_brand_batch_'+pre+'" type="button">验证</button></div><div><button class="btn btn-sm do_add_brand_batch_'+pre+'" type="button">添加</button></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>操作</th><tbody class="brand_table_body_'+pre+'"></table></div></div><input type="hidden" class="hiddenBrandids_'+pre+'" name="shop_brand"><div>';


    $(ele).html(base_html);
    var code=0;
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
    $(".do_add_brand_batch_"+pre).on("click", function () {
        var batch_add_brand=$(".batch_add_brand_"+pre).val();
        var ch = batch_add_brand.split(",");
        if(batch_add_brand.length < 1){
            layer_required("品牌id列表不能为空,请重新输入");
            return false;
        }
        for(var i=0;i<ch.length;i++){
            if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                layer_required("格式不对，请重新输入");
                return false;
            }
        }
        if (arrRepeat(ch) == true){
            layer_required("品牌重复，请重新输入");
            return false;
        }
        var hiddenBrandids= $(".hiddenBrandids_"+pre).val()
        var repeat_arr=hiddenBrandids.split(",");
        for(var i=0;i<repeat_arr.length;i++) {
            for(var t=0;t<ch.length;t++){
                if(repeat_arr[i]==ch[t]){
                    layer_required("有重复的品牌,请重新检查输入");
                    return false;
                }
            }
        }

        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var promotion_type = $("select[name='promotion_type'] option:checked").val();
        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
        var promotion_platform = $(".promotion_platform input:checked").length;
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        //验证活动平台
        if(promotion_platform == 0){
            layer_required('活动平台至少勾选一个！');return false;
        }

        var jsonData = {
            shop_brand : batch_add_brand,
            promotion_id : promotion_id,
            promotion_scope : promotion_scope,
            promotion_type : promotion_type,
            promotion_start_time: start_time,
            promotion_end_time: end_time,
            promotion_for_users : promotion_for_users,
            promotion_platform_pc : promotion_platform_pc,
            promotion_platform_app : promotion_platform_app,
            promotion_platform_wap : promotion_platform_wap,
            promotion_platform_wechat : promotion_platform_wechat
        }

        $.post('/promotion/verifyTimeRange',jsonData,function(data){
            if(!data){
                layer_error('操作失败');
                return false;
            }
            if(data.status == 'error'){
                layer_error(data.info);
                return false;
            }
            if(data.status == 'success'){

                $.post("/coupon/getBrandValidIssetId",{ids:batch_add_brand}, function (data) {
                    if(data!="0"){
                        $.each(data, function (n,v) {
                            $(".brand_table_body_"+pre).append("<tr><td>"+v['id']+"</td><td>"+v['brand_name']+"</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
                            if($(".hiddenBrandids_"+pre).val()==""){
                                $(".hiddenBrandids_"+pre).val(v['id']);
                            }else{
                                $(".hiddenBrandids_"+pre).val($(".hiddenBrandids_"+pre).val()+","+v['id']);
                            }

                        })
                        var _tempPool=[];
                        $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                            var v=$(this).html();
                            _tempPool.push(v);
                        })
                        $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
                        $(".brand_table_body_"+pre+" tr td a.del").on("click", function () {
                            $(this).parent().parent().remove();
                            if($(".brand_table_body_"+pre+" tr")[0]){
                                var _tempPool=[];
                                $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                                    var v=$(this).html();
                                    _tempPool.push(v);
                                })
                                $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
                            }else{
                                $(".hiddenBrandids_"+pre).val("");
                            }
                        })
                    }else{
                        layer_required("添加失败");
                    }
                })
            }
        },'json');



    })

    //搜索操作
    $(".do_search_btn_"+pre).on("click", function () {
        var search_goods_input=$(".search_goods_input_"+pre).val();
        if(search_goods_input.length>0){
            $.post("/coupon/getBrandSearchComponents",{input:search_goods_input}, function (output) {
                if(output!="0"){
                    var obj= output;
                    //如果已经有dom存在
                    if($('.select_brand_list_'+pre)[0]){
                        $('.select_brand_list_'+pre).html("");
                        $.each(obj, function (n,v) {
                            $('.select_brand_list_'+pre).append('<option value="'+ v["id"]+'">'+ v["brand_name"]+'</option>');
                        });
                    }else{
                        //新建dom

                        $('.append_area_'+pre).append('<div class="clearfix"></div><select class="col-sm-7 select_brand_list_'+pre+'" ></select><button class="btn btn-sm btn-danger do_select_btn_'+pre+'" type="button" onclick=do_select_click("'+pre+'")>确定</button></div>');
                        $.each(obj, function (n,v) {
                            $('.select_brand_list_'+pre).append('<option value="'+ v["id"]+'">'+ v["brand_name"]+'</option>');
                        });


                    }
                    code == 2;
                }else{
                    $('.select_brand_list_'+pre).remove();
                    $('.do_select_btn_'+pre).remove();
                    layer_required("没有查询到任何结果");
                }

            })
        }else{

            layer_required("输入不能为空");
        }

    })
    return code;
}

/**
 * @desc 根据情况创建不参加活动的各类id列表
 * @author 邓永军
 * @param type
 * all 全部
 * category 品类
 * brand 品牌
 * @require 新建id为NotJoinActivity的input:hidden
 */
function createValidateNotJoinActivity(type){
    switch (type){
        case "all":
            var base_html='<div class=form-group><label class="col-sm-3 control-label no-padding-right">设置不参加活动的ID</label><div class=col-sm-5><textarea rows="3" cols="5" id="category_set" name="except_category_id" class="form-control" placeholder="添加品类ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><button class="btn btn-sm " id=validateBtn type=button>验证</button></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><textarea rows="3" cols="5" id="brand_set" name="except_brand_id" class="form-control" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><textarea rows="3" cols="5" id="goods_set" name="except_good_id" class="form-control" placeholder="添加商品ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div></div>';
            $("#NotJoinActivity").html(base_html);
            $("#validateBtn").on("click", function () {
                var category_ids=$("#category_set").val().replace(/\s/g,"");
                var brand_ids=$("#brand_set").val().replace(/\s/g,"");
                var goods_ids=$("#goods_set").val().replace(/\s/g,"");
                $("#category_set").val(category_ids);
                $("#brand_set").val(brand_ids);
                $("#goods_set").val(goods_ids);
                if(category_ids == '' && brand_ids == '' && goods_ids == ''){
                    $(this).addClass('btn-info');
                    layer_required("不需要验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
                var result_flag=1;
                //检查品类
                if(category_ids!=""){
                    //验证格式
                    var ch = category_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"category",ids:category_ids}, function (data) {
                        if(data=="0"){
                            result_flag=0;
                            layer_required("品类id列表输入有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                //检查品牌
                if(brand_ids!=""){
                    //验证格式
                    var ch = brand_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"brand",ids:brand_ids}, function (data) {
                        if(data=="0"){
                            result_flag=0;
                            layer_required("品牌id列表输入有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                //检查商品
                if(goods_ids!=""){
                    //验证格式
                    var ch = goods_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"single",ids:goods_ids}, function (data) {
                        if(data=="0"){
                            result_flag=0;
                            layer_required("商品id列表有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                if(result_flag==1){
                    layer_required("通过验证");
                    $(this).addClass('btn-info');
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
            });
            break;
        case "category":
            var base_html='<div class=form-group><label class="col-sm-3 control-label no-padding-right">设置不参加活动的ID</label><div class=col-sm-5><textarea rows="3" cols="5" id="brand_set" name="except_brand_id" class="form-control" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><button class="btn btn-sm " id=validateBtn type=button>验证</button></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><textarea rows="3" cols="5" id="goods_set" name="except_good_id" class="form-control" placeholder="添加商品ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div></div>';
            $("#NotJoinActivity").html(base_html);
            $("#validateBtn").on("click", function () {

                var brand_ids=$("#brand_set").val().replace(/\s/g,"");
                var goods_ids=$("#goods_set").val().replace(/\s/g,"");
                $("#brand_set").val(brand_ids);
                $("#goods_set").val(goods_ids);
                if(brand_ids == '' && goods_ids == ''){
                    $(this).addClass('btn-info');
                    layer_required("不需要验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
                var result_flag=1;

                //检查品牌
                if(brand_ids!=""){
                    //验证格式
                    var ch = brand_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"brand",ids:brand_ids}, function (data) {
                        if(data=="0" || data=='null'){
                            result_flag=0;
                            layer_required("品牌id列表输入有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                //检查商品
                if(goods_ids!=""){
                    //验证格式
                    var ch = goods_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"single",ids:goods_ids}, function (data) {
                        if(data=="0" || data=='null'){
                            result_flag=0;
                            layer_required("商品id列表有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                if(result_flag==1){
                    layer_required("通过验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
            });
            break;
        case "brand":
            var base_html='<div class=form-group><label class="col-sm-3 control-label no-padding-right">设置不参加活动的ID</label><div class=col-sm-5><textarea rows="3" cols="5" id="goods_set" name="except_good_id" class="form-control" placeholder="添加商品ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><button class="btn btn-sm " id=validateBtn type=button>验证</button></div>';
            $("#NotJoinActivity").html(base_html);
            $("#validateBtn").on("click", function () {

                var goods_ids=$("#goods_set").val().replace(/\s/g,"");
                $("#goods_set").val(goods_ids);
                if(goods_ids == ''){
                    layer_required("不需要验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
                var result_flag=1;

                //检查商品
                if(goods_ids!=""){
                    //验证格式
                    var ch = goods_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"single",ids:goods_ids}, function (data) {
                        if(data=="0" || data[0]=='null'){
                            result_flag=0;
                            layer_required("商品id列表有误,请检查重新输入");
                            return false;
                        }
                    });
                }else{

                }

                if(result_flag==1){
                    layer_required("通过验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
            });
            break;
        case 'special':
            var base_html='<div class=form-group><label class="col-sm-3 control-label no-padding-right">设置不参加活动的ID</label><div class=col-sm-5><textarea rows="3" cols="5" id="category_set" name="except_category_id" class="form-control" placeholder="添加品类ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><button class="btn btn-sm " id=validateBtn type=button>验证</button></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><textarea rows="3" cols="5" id="brand_set" name="except_brand_id" class="form-control" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><textarea rows="3" cols="5" id="goods_set" name="except_good_id" class="form-control" placeholder="添加商品ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div></div>';
            $("#NotJoinActivity").html(base_html);
            $("#validateBtn").on("click", function () {
                var category_ids=$("#category_set").val().replace(/\s/g,"");
                var brand_ids=$("#brand_set").val().replace(/\s/g,"");
                var goods_ids=$("#goods_set").val().replace(/\s/g,"");
                $("#category_set").val(category_ids);
                $("#brand_set").val(brand_ids);
                $("#goods_set").val(goods_ids);
                if(category_ids == '' && brand_ids == '' && goods_ids == ''){
                    layer_required("不需要验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
                var result_flag=1;
                //检查品类
                if(category_ids!=""){
                    //验证格式
                    var ch = category_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"category",ids:category_ids}, function (data) {
                        if(data=="0"){
                            result_flag=0;
                            layer_required("品类id列表输入有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                //检查品牌
                if(brand_ids!=""){
                    //验证格式
                    var ch = brand_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"brand",ids:brand_ids}, function (data) {
                        if(data=="0"){
                            result_flag=0;
                            layer_required("品牌id列表输入有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                //检查商品
                if(goods_ids!=""){
                    //验证格式
                    var ch = goods_ids.split(",");
                    for(var i=0;i<ch.length;i++){
                        if(isNaN(ch[i]) || !regNumber.test(ch[i])){
                            result_flag=0;
                            layer_required("格式不对，请重新输入");
                            break;
                        }
                    }

                    //验证重复
                    for(var i=0;i<ch.length;i++){
                        var temp=ch[i];
                        for(var j=0;j<ch.length;j++){
                            if(temp==ch[j]&&i!=j){
                                result_flag=0;
                                layer_required("重复id，请重新输入");
                                break;
                            }
                        }
                    }
                    //验证id是否存在数据库
                    $.post("/coupon/checkExistIds",{type:"single",ids:goods_ids}, function (data) {
                        if(data=="0"){
                            result_flag=0;
                            layer_required("商品id列表有误,请检查重新输入");
                            return false;
                        }
                    });
                }
                if(result_flag==1){
                    layer_required("通过验证");
                    if($('#do_ajax_sure_btn')[0]){
                        $('#do_ajax_sure_btn').removeAttr('disabled');
                    }
                    if($('#do_ajax_sure_save_btn')[0]){
                        $('#do_ajax_sure_save_btn').removeAttr('disabled');
                    }
                    if($('#addLimitPromotion')[0]){
                        $('#addLimitPromotion').removeAttr('disabled');
                    }
                    if($('#addPromotion')[0]){
                        $('#addPromotion').removeAttr('disabled');
                    }
                    return 1;
                }
            });
            break;
    }
}