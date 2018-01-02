/**
 * @desc 添加和编辑限购活动的js验证
 * @author 吴俊华
 * @date 2016-09-09
 */
$(function () {
    // 时间插件
    $('#start_time,#end_time').datetimepicker({step: 10});
    var _flagForValidate = 0;

    //适用人群
    $("#promotion_for_users").on('change',function(){
        var promotion_for_users = $(this).val();
        var limit_unit = $("select[name='limit_unit'] option:checked").val();
        var limit_number = $("input[name='limit_number']");
        if(promotion_for_users == 20 && limit_unit == 3){
            limit_number.val(1);
            limit_number.attr('readonly','readonly');
        }else {
            limit_number.removeAttr('readonly');
        }
    });

    //活动平台
    $("input[name='promotion_platform_pc']").on('click', function () {
        var promotion_platform_pc = $("input[name='promotion_platform_pc']:checked").length;
        $("input[name='promotion_platform_pc']").val(promotion_platform_pc);
    });
    $("input[name='promotion_platform_app']").on('click', function () {
        var promotion_platform_app = $("input[name='promotion_platform_app']:checked").length;
        $("input[name='promotion_platform_app']").val(promotion_platform_app);
    });
    $("input[name='promotion_platform_wap']").on('click', function () {
        var promotion_platform_wap = $("input[name='promotion_platform_wap']:checked").length;
        $("input[name='promotion_platform_wap']").val(promotion_platform_wap);
    });
    $("input[name='promotion_platform_wechat']").on('click', function () {
        var promotion_platform_wap = $("input[name='promotion_platform_wechat']:checked").length;
        $("input[name='promotion_platform_wechat']").val(promotion_platform_wap);
    });
    $('body').on('keyup', 'input[name="limit_number"]', function () {
        var nums = $(this).val();
        if (nums.length > 11) {
            layer_required('位数不能大于11');
            $(this).val('');
        }
    });
    var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
    switch (promotion_scope) {
        case 0:

            break;
        case 'all':
            $("#limit_number").html($("#xg").html());
            if ($("#expextCategoryIds").val() != "") {
                $("#category_set").val($("#expextCategoryIds").val());
            }
            if ($("#expextBrandIds").val() != "") {
                $("#brand_set").val($("#expextBrandIds").val());
            }
            if ($("#expextSingleIds").val() != "") {
                $("#goods_set").val($("#expextSingleIds").val());
            }
            break;
        case 'category':
            $("#limit_number").html($("#xg").html());
            $('#shop_category').show();
            if ($("#expextBrandIds").val() != "") {
                $("#brand_set").val($("#expextBrandIds").val());
            }
            if ($("#expextSingleIds").val() != "") {
                $("#goods_set").val($("#expextSingleIds").val());
            }
            break;
        case 'brand':

            $("#limit_number").html($("#xg").html());
            if ($("#expextSingleIds").val() != "") {
                $("#goods_set").val($("#expextSingleIds").val());
            }
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
                '<div class=form-group><label class="col-sm-3 control-label no-padding-right"><span  class="text-red">*</span>添加品牌</label><div class=col-sm-5><div class=" append_area_' +
                pre + '"><input class="col-sm-7 search_goods_input_' + pre +
                '" placeholder="品牌ID/名称" type=text><div ><button class="btn btn-sm btn-success do_search_btn_' + pre +
                '" type=button>搜索</button></div></div></div></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right">批量添加品牌</label><div class=col-sm-5><textarea rows="3" cols="5" class="form-control batch_add_brand_' +
                pre +
                '" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><div style="margin-bottom: 5px;"><button class="btn btn-sm do_validate_brand_batch_' +
                pre + '" type="button">验证</button></div><div><button class="btn btn-sm do_add_brand_batch_' + pre +
                '" type="button">添加</button></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>限购数量</th><th>操作</th><tbody class="brand_table_body_' +
                pre + '"></table></div></div><input type="hidden" class="hiddenBrandids_' + pre +
                '" name="shop_brand"><div>';

            $("#shop_brand_temp").html(base_html);
            //批量添加
            $(".do_validate_brand_batch_" + pre).on("click", function () {
                //验证格式
                var is_pass = 1;
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
                if (is_pass == 0)return false;
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
                if (is_pass == 0)return false;
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

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var jsonData = {
                            shop_brand: batch_add_brand,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (res) {
                            if (!res) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (res.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (res.status == 'success') {

                                $.each(data, function (n, v) {
                                    $(".brand_table_body_" + pre).append("<tr class='brand_insert'><td>" + v['id'] + "</td><td>" + v['brand_name'] +
                                        "</td><td><input type='text' placeholder='数量'></td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
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
                            }
                        }, 'json');
                    } else {
                        layer_required("添加失败");
                        return false;
                    }
                })
            });


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
                                    $('.select_brand_list_' + pre).append('<option value="' + v["id"] + '">' + v["brand_name"] + '</option>');
                                });
                            } else {
                                //新建dom

                                $('.append_area_' + pre).append('<div class="clearfix"></div><select class="col-sm-7 select_brand_list_' + pre + '" ></select><button class="btn btn-sm btn-danger do_select_btn_' + pre + '" type="button" onclick=do_select_click_x("' + pre + '")>确定</button></div>');
                                $.each(obj, function (n, v) {
                                    $('.select_brand_list_' + pre).append('<option value="' + v["id"] + '">' + v["brand_name"] + '</option>');
                                });


                            }

                        } else {
                            $('.select_brand_list_' + pre).remove();
                            $('.do_select_btn_' + pre).remove();
                            layer_required("没有查询到任何结果");
                        }

                    })
                } else {

                    layer_required("输入不能为空");
                }

            })

            $('#shop_brand_temp').show();
            var valicode = createValidateNotJoinActivity(promotion_scope);
            if (valicode == 1) {
                _flagForValidate = 1;
            }
            var limitBuyBrands = $("#limitBuyBrands").val();
            var limitBuyBrands_obj = $.parseJSON(limitBuyBrands);
            $.each(limitBuyBrands_obj, function (n, v) {
                $(".brand_table_body_" + pre).append("<tr class='brand_insert'><td>" + v['id'] + "</td><td>" + v['brand_name'] +
                    "</td><td><input type='text' placeholder='数量' value='" + v['promotion_num'] + "'></td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
            });
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
            break;
        case 'single':
            $("#limit_number").html("");
            $("#shop_single_temp").html($("#dp").html());
            $("#NotJoinActivity").html("");
            $('#shop_single_temp').show();
            if ($("#limitBuyGoods").val() != "") {
                var limitBuyGoods = $("#limitBuyGoods").val();
                var limitBuyGoods_obj = $.parseJSON(limitBuyGoods);
                var sid_arr = [];
                $.each(limitBuyGoods_obj, function (n, v) {
                    $("#discountRight").append('<li><a href="javascript:;" data-id="' + v["id"] + '" data-pname="' + v["goods_name"] + '" data-price="' + v['price'] + '" data-xg="' + v["promotion_num"] + '"><span class=pro-name>' + v["goods_name"] + '</span><span class=pro-price>&nbsp;---限购：' + v["promotion_num"] + '件</span></a></li>');
                    sid_arr.push(v["id"]);
                })
                $("#shop_single").val('');
                var sids = sid_arr.join(",");
                $("#shop_single").val(sids);
            }
            $.post("/limittime/getGoodsList", function (data) {
                if (data != "0") {
                    $.each(data, function (n, v) {
                        if (v["is_unified_price"] == "0") {
                            $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                        } else {
                            $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                        }
                    });
                    if ($("#limitBuyGoods").val() != "") {
                        var sg_str = $('#shop_single').val();
                        var sg_str_arr = sg_str.split(",");
                        /*$("#discountLeft li a").each(function () {
                            for (var i = 0; i < sg_str_arr.length; i++) {
                                if ($(this).data("id") == sg_str_arr[i]) {
                                    $(this).parent().remove();
                                }
                            }
                        });*/
                    }
                }
            });
            //点击搜索 加载商品
            $("#searchGoods").on("click", function () {
                var search_value = $("#search_value").val();
                if (search_value == "") {
                    layer_required("搜索内容不能为空");
                } else {
                    $.post("/limittime/getGoodsList", {input: search_value}, function (data) {
                        if (data != "0") {
                            $("#discountLeft").html("");
                            $.each(data, function (n, v) {
                                v = v[0];
                                if (v["is_unified_price"] == "0") {
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                                } else {
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                                }
                            });
                            if ($("#limitBuyGoods").val() != "") {
                                var sg_str = $('#shop_single').val();
                                var sg_str_arr = sg_str.split(",");
                                /*$("#discountLeft li a").each(function () {
                                    for (var i = 0; i < sg_str_arr.length; i++) {
                                        if ($(this).data("id") == sg_str_arr[i]) {
                                            $(this).parent().remove();
                                        }
                                    }
                                });*/
                            }
                        } else {
                            layer_required('搜索结果为空');
                            $("#discountLeft").html("");
                        }
                    });
                }
            });


            //添加一个单品
            $("#addOption").on("click", function () {
                var ipass = 1;
                var xg_num = $("#xg_num").val();
                if (xg_num <= 0) {
                    layer_required("请输入限购数量！");
                } else {
                    if (!regNumber.test(xg_num)) {
                        layer_required("限购数量必须为正整数！");
                        return false;
                    } else {
                        var ids_arr = [];
                        $("#discountLeft li.list_on a").each(function () {
                            ids_arr.push($(this).data("id"));
                        });
                        var ids = ids_arr.join(",");

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var platform = [];
                        if (promotion_platform_pc == 1) {
                            platform.push('pc');
                        }
                        if (promotion_platform_app == 1) {
                            platform.push('app');
                        }
                        if (promotion_platform_wap == 1) {
                            platform.push('wap');
                        }
                        if (promotion_platform_wechat == 1) {
                            platform.push('wechat');
                        }
                        var jsonData = {
                            shop_single: ids,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                            if (!data) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (data.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (data.status == 'success') {
                                $("#discountLeft li.list_on a").each(function () {
                                    var mid = $(this).data("id");
                                    if($("#discountRight li").length > 0){
                                        $("#discountRight li a").each(function () {
                                            if($(this).data('id') == mid){
                                                ipass = 0;
                                            }
                                        });
                                    }
                                    if(ipass == 1){
                                        $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name style="max-width: 100;">' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                        $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                            if (res.code == '400') {
                                                layer_required(res.msg);
                                                return false;
                                            }
                                        });
                                        $(this).parent().remove();
                                    }

                                });
                                if(ipass == 0){
                                    layer_required('不能添加同一个商品');
                                    return false;
                                }
                                $("#shop_single").val('');
                                var sid_arr = [];
                                $("#discountRight li a").each(function () {
                                    sid_arr.push($(this).data('id'));
                                });
                                var sids = sid_arr.join(",");
                                $("#shop_single").val(sids);
                            }
                        }, 'json');
                    }


                }
            });

            //删除
            $("#delOption").on("click", function () {
                $("#discountRight li.list_on a").each(function () {
                    var mid = $(this).data('id');
                    if($("#discountLeft li").length > 0){
                        $("#discountLeft li a").each(function () {
                            if(mid != $(this).data('id')){
                                $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                            }
                        });
                    }else{
                        $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                    }


                    $(this).parent().remove();
                });
                $("#shop_single").val('');
                var sid_arr = [];
                $("#discountRight li a").each(function () {
                    sid_arr.push($(this).data('id'));
                });
                var sids = sid_arr.join(",");
                $("#shop_single").val(sids);
            });

            //单品全部添加
            $("#addOptionAll").on("click", function () {
                var ipass = 1;
                var xg_num = $("#xg_num").val();
                if (xg_num <= 0 || !regNumber.test(xg_num)) {
                    layer_required("请输入限购数量！");
                } else {
                    var ids_arr = [];
                    $("#discountLeft li a").each(function () {
                        ids_arr.push($(this).data("id"));
                    });
                    var ids = ids_arr.join(",");

                    var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                    var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                    var promotion_type = $("#promotion_type").val();
                    var start_time = $('#start_time').val();
                    var end_time = $('#end_time').val();
                    var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                    var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                    var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                    var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                    var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                    var promotion_platform = $(".promotion_platform input:checked").length;
                    //验证活动平台
                    if (promotion_platform == 0) {
                        layer_required('活动平台至少勾选一个！');
                        return false;
                    }

                    var platform = [];
                    if (promotion_platform_pc == 1) {
                        platform.push('pc');
                    }
                    if (promotion_platform_app == 1) {
                        platform.push('app');
                    }
                    if (promotion_platform_wap == 1) {
                        platform.push('wap');
                    }
                    if (promotion_platform_wechat == 1) {
                        platform.push('wechat');
                    }
                    var jsonData = {
                        shop_single: ids,
                        promotion_id: promotion_id,
                        promotion_scope: promotion_scope,
                        promotion_type: promotion_type,
                        promotion_start_time: start_time,
                        promotion_end_time: end_time,
                        promotion_for_users : promotion_for_users,
                        promotion_platform_pc: promotion_platform_pc,
                        promotion_platform_app: promotion_platform_app,
                        promotion_platform_wap: promotion_platform_wap,
                        promotion_platform_wechat: promotion_platform_wechat
                    }

                    $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                        if (!data) {
                            layer_error('操作失败');
                            return false;
                        }
                        if (data.status == 'error') {
                            layer_error(data.info);
                            return false;
                        }
                        if (data.status == 'success') {
                            $("#discountLeft li a").each(function () {
                                var mid = $(this).data("id");
                                if($("#discountRight li").length > 0){
                                    $("#discountRight li a").each(function () {
                                        if($(this).data('id') == mid){
                                            ipass = 0;
                                        }
                                    });
                                }
                                if(ipass == 1){
                                    $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name style="max-width: 100;">' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                    $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                        if (res.code == '400') {
                                            layer_required(res.msg);
                                            return false;
                                        }
                                    });
                                    $(this).parent().remove();
                                }

                            });
                            if(ipass == 0){
                                layer_required('不能添加同一个商品');
                                return false;
                            }
                            $("#shop_single").val('');
                            var sid_arr = [];
                            $("#discountRight li a").each(function () {
                                sid_arr.push($(this).data('id'));
                            });
                            var sids = sid_arr.join(",");
                            $("#shop_single").val(sids);
                        }
                    }, 'json');

                }
            });

            //全部删除
            $("#delOptionAll").on("click", function () {
                $("#discountRight li a").each(function () {
                    var mid = $(this).data('id');
                    if($("#discountLeft li").length > 0){
                        $("#discountLeft li a").each(function () {
                            if(mid != $(this).data('id')){
                                $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                            }
                        });
                    }else{
                        $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                    }
                    $(this).parent().remove();
                });
                $("#shop_single").val('');
                var sid_arr = [];
                $("#discountRight li a").each(function () {
                    sid_arr.push($(this).data('id'));
                });
                var sids = sid_arr.join(",");
                $("#shop_single").val(sids);
            });
            break;
        case "more":
            $("#limit_number").html($("#xg").html());
            $("#shop_single_temp").html("");
            var dp_more = $("#dp").html();
            $('#shop_more_temp').show(0);
            $("#shop_more_temp").html(dp_more);
            $("#NotJoinActivity").html("");
            var limitBuyMore_obj = $.parseJSON($("#limitBuyMore").val());
            var promotion_buy_more_arr = [];
            $.each(limitBuyMore_obj, function (n, v) {
                promotion_buy_more_arr.push(v["id"]);
                $("#discountRight").append('<li><a href="javascript:;" data-id="' + v["id"] + '" data-pname="' + v["goods_name"] + '" data-price="' + v['price'] + '" data-xg="' + v["promotion_num"] + '"><span class=pro-name>' + v["goods_name"] + '</span><span class=pro-price>&nbsp;---限购：' + v["promotion_num"] + '件</span></a></li>');
            });

            $("#shop_more").val(promotion_buy_more_arr.join(","));
            $.post("/limittime/getGoodsList", function (data) {
                if (data != "0") {
                    $.each(data, function (n, v) {
                        if (v["is_unified_price"] == "0") {
                            $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                        } else {
                            $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                        }

                    });
                    if ($("#limitBuyMore").val() != "") {
                        var sg_str = $('#shop_more').val();
                        var sg_str_arr = sg_str.split(",");
                        /*$("#discountLeft li a").each(function () {
                            for (var i = 0; i < sg_str_arr.length; i++) {
                                if ($(this).data("id") == sg_str_arr[i]) {
                                    $(this).parent().remove();
                                }
                            }
                        });*/
                    }
                }
            });

            //点击搜索 加载商品
            $("#searchGoods").on("click", function () {
                var search_value = $("#search_value").val();
                if (search_value == "") {
                    layer_required("搜索内容不能为空");
                } else {
                    $.post("/limittime/getGoodsList", {input: search_value}, function (data) {
                        if (data != "0") {
                            $("#discountLeft").html("");
                            $.each(data, function (n, v) {
                                v = v[0];
                                if (v["is_unified_price"] == "0") {
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                                } else {
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                                }
                            });
                            if ($("#limitBuyMore").val() != "") {
                                var sg_str = $('#shop_more').val();
                                var sg_str_arr = sg_str.split(",");
                                /*$("#discountLeft li a").each(function () {
                                    for (var i = 0; i < sg_str_arr.length; i++) {
                                        if ($(this).data("id") == sg_str_arr[i]) {
                                            $(this).parent().remove();
                                        }
                                    }
                                });*/
                            }
                        } else {
                            layer_required('搜索结果为空');
                            $("#discountLeft").html("");
                        }
                    });
                }
            });


            //多单品添加
            $("#addOption").on("click", function () {
                var ipass = 1;
                var xg_num = $("#xg_num").val();
                if (xg_num <= 0) {
                    layer_required("请输入限购数量！");
                } else {
                    if (!regNumber.test(xg_num)) {
                        layer_required("限购数量必须为正整数！");
                        return false;
                    } else {
                        var sid_arr = [];
                        $("#discountLeft li.list_on a").each(function () {
                            sid_arr.push($(this).data('id'));
                        });
                        var sids = sid_arr.join(",");

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var platform = [];
                        if (promotion_platform_pc == 1) {
                            platform.push('pc');
                        }
                        if (promotion_platform_app == 1) {
                            platform.push('app');
                        }
                        if (promotion_platform_wap == 1) {
                            platform.push('wap');
                        }
                        var jsonData = {
                            shop_more: sids,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                            if (!data) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (data.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (data.status == 'success') {
                                $("#discountLeft li.list_on a").each(function () {
                                    var mid = $(this).data("id");

                                    if($("#discountRight li").length > 0){
                                        $("#discountRight li a").each(function () {
                                            if($(this).data('id') == mid){
                                                ipass = 0;
                                            }
                                        });
                                    }
                                    if(ipass == 1){
                                        $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name style="max-width: 100;">' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                        $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                            if (res.code == '400') {
                                                layer_required(res.msg);
                                                return false;
                                            }
                                        });
                                        $(this).parent().remove();
                                    }

                                });
                                if(ipass == 0){
                                    layer_required('不能添加同一个商品');
                                    return false;
                                }
                                var sid_arr2 = [];
                                $("#discountRight li a").each(function () {
                                    sid_arr2.push($(this).data('id'));
                                });
                                var sids = sid_arr2.join(",");
                                $("#shop_more").val(sids);
                            }
                        }, 'json');
                    }
                }


            });

            //删除
            $("#delOption").on("click", function () {
                $("#discountRight li.list_on a").each(function () {
                    var mid = $(this).data('id');
                    if($("#discountLeft li").length > 0){
                        $("#discountLeft li a").each(function () {
                            if(mid != $(this).data('id')){
                                $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                            }
                        });
                    }else{
                        $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                    }
                    $(this).parent().remove();
                });
                var sid_arr = [];
                $("#discountRight li a").each(function () {
                    sid_arr.push($(this).data('id'));
                });
                var sids = sid_arr.join(",");
                $("#shop_more").val(sids);
            });

            //多单品添加全部
            $("#addOptionAll").on("click", function () {
                var ipass = 1;
                var xg_num = $("#xg_num").val();
                if (xg_num <= 0 || !regNumber.test(xg_num)) {
                    layer_required("请输入限购数量！");
                } else {
                    var sid_arr = [];
                    $("#discountLeft li a").each(function () {
                        sid_arr.push($(this).data('id'));
                    });
                    var sids = sid_arr.join(",");

                    var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                    var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                    var promotion_type = $("#promotion_type").val();
                    var start_time = $('#start_time').val();
                    var end_time = $('#end_time').val();
                    var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                    var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                    var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                    var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                    var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                    var promotion_platform = $(".promotion_platform input:checked").length;
                    //验证活动平台
                    if (promotion_platform == 0) {
                        layer_required('活动平台至少勾选一个！');
                        return false;
                    }

                    var platform = [];
                    if (promotion_platform_pc == 1) {
                        platform.push('pc');
                    }
                    if (promotion_platform_app == 1) {
                        platform.push('app');
                    }
                    if (promotion_platform_wap == 1) {
                        platform.push('wap');
                    }
                    var jsonData = {
                        shop_more: sids,
                        promotion_id: promotion_id,
                        promotion_scope: promotion_scope,
                        promotion_type: promotion_type,
                        promotion_start_time: start_time,
                        promotion_end_time: end_time,
                        promotion_for_users : promotion_for_users,
                        promotion_platform_pc: promotion_platform_pc,
                        promotion_platform_app: promotion_platform_app,
                        promotion_platform_wap: promotion_platform_wap,
                        promotion_platform_wechat: promotion_platform_wechat
                    }

                    $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                        if (!data) {
                            layer_error('操作失败');
                            return false;
                        }
                        if (data.status == 'error') {
                            layer_error(data.info);
                            return false;
                        }
                        if (data.status == 'success') {
                            $("#discountLeft li a").each(function () {
                                var mid = $(this).data("id");
                                if($("#discountRight li").length > 0){
                                    $("#discountRight li a").each(function () {
                                        if($(this).data('id') == mid){
                                            ipass = 0;
                                        }
                                    });
                                }
                                if(ipass == 1){
                                    $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name style="max-width: 100;">' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                    $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                        if (res.code == '400') {
                                            layer_required(res.msg);
                                            return false;
                                        }
                                    });
                                    $(this).parent().remove();
                                }

                            });
                            var sid_arr = [];
                            $("#discountRight li a").each(function () {
                                sid_arr.push($(this).data('id'));
                            });
                            var sids = sid_arr.join(",");
                            $("#shop_more").val(sids);
                        }
                    }, 'json');
                }


            });

            //全部删除
            $("#delOptionAll").on("click", function () {
                $("#discountRight li a").each(function () {
                    var mid = $(this).data('id');
                    if($("#discountLeft li").length > 0){
                        $("#discountLeft li a").each(function () {
                            if(mid != $(this).data('id')){
                                $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                            }
                        });
                    }else{
                        $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                    }
                    $(this).parent().remove();
                });
                var sid_arr = [];
                $("#discountRight li a").each(function () {
                    sid_arr.push($(this).data('id'));
                });
                var sids = sid_arr.join(",");
                $("#shop_more").val(sids);
            });
            break;
    }
    //选择不同使用范围的特效

    $('#promotion_scope').on('change', function () {
        $('#shop_category').hide();
        $('#shop_brand_temp').hide();
        $('#shop_single_temp').hide();
        $("#shop_more_temp").hide();
        $('#except_id_box').empty();
        $("#shop_single").val('');
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();

        switch (promotion_scope) {
            case '0':
                $('#shop_category').hide();
                $('#shop_brand_temp').hide();
                $('#shop_single_temp').hide();
                $("#shop_more_temp").hide();
                $('#except_id_box').empty();
                $("#shop_single").val('');
                $("#limit_number").html('');
                $('#NotJoinActivity').html('');
                break;
            case 'all':
                $("#limit_number").html($("#xg").html());
                var valicode = createValidateNotJoinActivity(promotion_scope);
                var expextCategoryIds = $("#expextCategoryIds").val();
                var expextBrandIds = $("#expextBrandIds").val();
                var expextSingleIds = $("#expextSingleIds").val();
                $("textarea[name='except_category_id']").val(expextCategoryIds);
                $("textarea[name='except_brand_id']").val(expextBrandIds);
                $("textarea[name='except_good_id']").val(expextSingleIds);
                if (valicode == 1) {
                    _flagForValidate = 1;
                }
                break;
            case 'category':
                $("#limit_number").html($("#xg").html());
                $('#shop_category').show();
                var valicode = createValidateNotJoinActivity(promotion_scope);

                var expextBrandIds = $("#expextBrandIds").val();
                var expextSingleIds = $("#expextSingleIds").val();

                $("textarea[name='except_brand_id']").val(expextBrandIds);
                $("textarea[name='except_good_id']").val(expextSingleIds);
                if (valicode == 1) {
                    _flagForValidate = 1;
                }
                break;
            case 'brand':

                $("#limit_number").html($("#xg").html());
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
                    '<div class=form-group><label class="col-sm-3 control-label no-padding-right"><span  class="text-red">*</span>添加品牌</label><div class=col-sm-5><div class=" append_area_' +
                    pre + '"><input class="col-sm-7 search_goods_input_' + pre +
                    '" placeholder="品牌ID/名称" type=text><div ><button class="btn btn-sm btn-success do_search_btn_' + pre +
                    '" type=button>搜索</button></div></div></div></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right">批量添加品牌</label><div class=col-sm-5><textarea rows="3" cols="5" class="form-control batch_add_brand_' +
                    pre +
                    '" placeholder="添加品牌ID，多个ID以英文逗号隔开，请勿输入重复ID"></textarea></div><div style="margin-bottom: 5px;"><button class="btn btn-sm do_validate_brand_batch_' +
                    pre + '" type="button">验证</button></div><div><button class="btn btn-sm do_add_brand_batch_' + pre +
                    '" type="button">添加</button></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>限购数量</th><th>操作</th><tbody class="brand_table_body_' +
                    pre + '"></table></div></div><input type="hidden" class="hiddenBrandids_' + pre +
                    '" name="shop_brand"><div>';

                $("#shop_brand_temp").html(base_html);
                //批量添加
                $(".do_validate_brand_batch_" + pre).on("click", function () {
                    //验证格式
                    var is_pass = 1;
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
                    if (is_pass == 0)return false;
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
                    if (is_pass == 0)return false;
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

                            var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                            var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                            var promotion_type = $("#promotion_type").val();
                            var start_time = $('#start_time').val();
                            var end_time = $('#end_time').val();
                            var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                            var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                            var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                            var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                            var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                            var promotion_platform = $(".promotion_platform input:checked").length;
                            //验证活动平台
                            if (promotion_platform == 0) {
                                layer_required('活动平台至少勾选一个！');
                                return false;
                            }

                            var jsonData = {
                                shop_brand: batch_add_brand,
                                promotion_id: promotion_id,
                                promotion_scope: promotion_scope,
                                promotion_type: promotion_type,
                                promotion_start_time: start_time,
                                promotion_end_time: end_time,
                                promotion_for_users : promotion_for_users,
                                promotion_platform_pc: promotion_platform_pc,
                                promotion_platform_app: promotion_platform_app,
                                promotion_platform_wap: promotion_platform_wap,
                                promotion_platform_wechat: promotion_platform_wechat
                            }

                            $.post('/promotion/verifyTimeRange', jsonData, function (res) {
                                if (!res) {
                                    layer_error('操作失败');
                                    return false;
                                }
                                if (res.status == 'error') {
                                    layer_error(data.info);
                                    return false;
                                }
                                if (res.status == 'success') {

                                    $.each(data, function (n, v) {
                                        $(".brand_table_body_" + pre).append("<tr class='brand_insert'><td>" + v['id'] + "</td><td>" + v['brand_name'] +
                                            "</td><td><input type='text' placeholder='数量'></td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
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
                                }
                            }, 'json');
                        } else {
                            layer_required("添加失败");
                            return false;
                        }
                    })
                });


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
                                        $('.select_brand_list_' + pre).append('<option value="' + v["id"] + '">' + v["brand_name"] + '</option>');
                                    });
                                } else {
                                    //新建dom

                                    $('.append_area_' + pre).append('<div class="clearfix"></div><select class="col-sm-7 select_brand_list_' + pre + '" ></select><button class="btn btn-sm btn-danger do_select_btn_' + pre + '" type="button" onclick=do_select_click_x("' + pre + '")>确定</button></div>');
                                    $.each(obj, function (n, v) {
                                        $('.select_brand_list_' + pre).append('<option value="' + v["id"] + '">' + v["brand_name"] + '</option>');
                                    });


                                }

                            } else {
                                $('.select_brand_list_' + pre).remove();
                                $('.do_select_btn_' + pre).remove();
                                layer_required("没有查询到任何结果");
                            }

                        })
                    } else {

                        layer_required("输入不能为空");
                    }

                })

                $('#shop_brand_temp').show();
                var valicode = createValidateNotJoinActivity(promotion_scope);

                var expextSingleIds = $("#expextSingleIds").val();

                $("textarea[name='except_good_id']").val(expextSingleIds);
                if (valicode == 1) {
                    _flagForValidate = 1;
                }
                break;
            case 'single':
                $("#limit_number").html("");
                var dp = $("#dp").html();
                $('#shop_single_temp').show(0);
                $("#shop_single_temp").html(dp);
                $('#shop_more_temp').html('');
                $("#NotJoinActivity").html("");
                $.post("/limittime/getGoodsList", function (data) {
                    if (data != "0") {
                        $.each(data, function (n, v) {
                            if (v["is_unified_price"] == "0") {
                                $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                            } else {
                                $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                            }
                        });
                    }
                });
                //点击搜索 加载商品
                $("#searchGoods").on("click", function () {
                    var search_value = $("#search_value").val();
                    var pattern = /[，]+/;
                    if(pattern.test(search_value)) {
                        layer_required("搜索内容格式错误，不能包含中文逗号");
                        return false;
                    }
                    if (search_value == "") {
                        layer_required("搜索内容不能为空");
                    } else {
                        $.post("/limittime/getGoodsList", {input: search_value}, function (data) {
                            if (data != "0") {
                                $("#discountLeft").html("");
                                $.each(data, function (n, v) {
                                    v = v[0];
                                    if (v["is_unified_price"] == "0") {
                                        $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                                    } else {
                                        $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                                    }
                                });
                            } else {
                                layer_required('搜索结果为空');
                                $("#discountLeft").html("");
                            }
                        });
                    }
                });


                //添加一个单品
                $("#addOption").on("click", function () {
                    var ipass = 1;
                    var xg_num = $("#xg_num").val();
                    if (xg_num <= 0 || !regNumber.test(xg_num)) {
                        layer_required("请输入限购数量！");
                    } else {
                        var ids_arr = [];
                        $("#discountLeft li.list_on a").each(function () {
                            ids_arr.push($(this).data("id"));
                        });
                        var ids = ids_arr.join(",");

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var platform = [];
                        if (promotion_platform_pc == 1) {
                            platform.push('pc');
                        }
                        if (promotion_platform_app == 1) {
                            platform.push('app');
                        }
                        if (promotion_platform_wap == 1) {
                            platform.push('wap');
                        }
                        var jsonData = {
                            shop_single: ids,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                            if (!data) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (data.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (data.status == 'success') {
                                $("#discountLeft li.list_on a").each(function () {
                                    var mid = $(this).data("id");
                                    if($("#discountRight li").length > 0){
                                        $("#discountRight li a").each(function () {
                                            if($(this).data('id') == mid){
                                                ipass = 0;
                                            }
                                        });
                                    }
                                    if(ipass == 1){
                                        $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                        $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                            if (res.code == '400') {
                                                layer_required(res.msg);
                                                return false;
                                            }
                                        });
                                        $(this).parent().remove();
                                    }

                                });
                                if(ipass == 0){
                                    layer_required('不能添加同一个商品');
                                    return false;
                                }
                                $("#shop_single").val('');
                                var sid_arr = [];
                                $("#discountRight li a").each(function () {
                                    sid_arr.push($(this).data('id'));
                                });
                                var sids = sid_arr.join(",");
                                $("#shop_single").val(sids);
                            }
                        }, 'json');

                    }
                });

                //删除
                $("#delOption").on("click", function () {
                    $("#discountRight li.list_on a").each(function () {
                        var mid = $(this).data('id');
                        if($("#discountLeft li").length > 0){
                            $("#discountLeft li a").each(function () {
                                if(mid != $(this).data('id')){
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                                }
                            });
                        }else{
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                        }
                        $(this).parent().remove();
                    });
                    $("#shop_single").val('');
                    var sid_arr = [];
                    $("#discountRight li a").each(function () {
                        sid_arr.push($(this).data('id'));
                    });
                    var sids = sid_arr.join(",");
                    $("#shop_single").val(sids);
                });

                //单品全部添加
                $("#addOptionAll").on("click", function () {
                    var ipass = 1;
                    var xg_num = $("#xg_num").val();
                    if (xg_num <= 0 || !regNumber.test(xg_num)) {
                        layer_required("请输入限购数量！");
                    } else {
                        var ids_arr = [];
                        $("#discountLeft li a").each(function () {
                            ids_arr.push($(this).data("id"));
                        });
                        var ids = ids_arr.join(",");

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var platform = [];
                        if (promotion_platform_pc == 1) {
                            platform.push('pc');
                        }
                        if (promotion_platform_app == 1) {
                            platform.push('app');
                        }
                        if (promotion_platform_wap == 1) {
                            platform.push('wap');
                        }
                        var jsonData = {
                            shop_single: ids,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                            if (!data) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (data.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (data.status == 'success') {
                                $("#discountLeft li a").each(function () {
                                    var mid = $(this).data("id");
                                    if($("#discountRight li").length > 0){
                                        $("#discountRight li a").each(function () {
                                            if($(this).data('id') == mid){
                                                ipass = 0;
                                            }
                                        });
                                    }
                                    if(ipass == 1){
                                        $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                        $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                            if (res.code == '400') {
                                                layer_required(res.msg);
                                                return false;
                                            }
                                        });
                                        $(this).parent().remove();
                                    }

                                });
                                if(ipass == 0){
                                    layer_required('不能添加同一个商品');
                                    return false;
                                }
                                $("#shop_single").val('');
                                var sid_arr = [];
                                $("#discountRight li a").each(function () {
                                    sid_arr.push($(this).data('id'));
                                });
                                var sids = sid_arr.join(",");
                                $("#shop_single").val(sids);
                            }
                        }, 'json');

                    }
                });

                //全部删除
                $("#delOptionAll").on("click", function () {
                    $("#discountRight li a").each(function () {
                        var mid = $(this).data('id');
                        if($("#discountLeft li").length > 0){
                            $("#discountLeft li a").each(function () {
                                if(mid != $(this).data('id')){
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                                }
                            });
                        }else{
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                        }
                        $(this).parent().remove();
                    });
                    $("#shop_single").val('');
                    var sid_arr = [];
                    $("#discountRight li a").each(function () {
                        sid_arr.push($(this).data('id'));
                    });
                    var sids = sid_arr.join(",");
                    $("#shop_single").val(sids);
                });
                break;
            case "more":
                $("#limit_number").html($("#xg").html());
                $("#shop_single_temp").html("");

                var dp_more = $("#dp").html();
                $('#shop_more_temp').show(0);
                $("#shop_more_temp").html(dp_more);
                $("#NotJoinActivity").html("");
                $.post("/limittime/getGoodsList", function (data) {
                    if (data != "0") {
                        $.each(data, function (n, v) {
                            if (v["is_unified_price"] == "0") {
                                $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                            } else {
                                $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                            }
                        });
                    }
                });
                //点击搜索 加载商品
                $("#searchGoods").on("click", function () {
                    var search_value = $("#search_value").val();
                    var pattern = /[，]+/;
                    if(pattern.test(search_value)) {
                        layer_required("搜索内容格式错误，不能包含中文逗号");
                        return false;
                    }
                    if (search_value == "") {
                        layer_required("搜索内容不能为空");
                    } else {
                        $.post("/limittime/getGoodsList", {input: search_value}, function (data) {
                            if (data != "0") {
                                $("#discountLeft").html("");
                                $.each(data, function (n, v) {
                                    v = v[0];
                                    if (v["is_unified_price"] == "0") {
                                        $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>原价：' + v['price'] + '</span></a></li>');
                                    } else {
                                        $("#discountLeft").append('<li><a href="javascript:;" data-id=' + v['id'] + ' data-pname=' + v['goods_name'] + ' data-price=' + v['price'] + '><span class=pro-name>' + v['goods_name'] + '</span><span class=pro-price>pc价格：' + v['goods_price_pc'] + '元,app价格:' + v["goods_price_app"] + '元,wap价格:' + v["goods_price_wap"] + '元,微商城价格:' + v["goods_price_wechat"] + '元</span></a></li>');
                                    }
                                });
                            } else {
                                layer_required('搜索结果为空');
                                $("#discountLeft").html("");
                            }
                        });
                    }
                });


                //添加一个单品
                $("#addOption").on("click", function () {
                    var ipass = 1;
                    var xg_num = $("#xg_num").val();
                    if (xg_num <= 0 || !regNumber.test(xg_num)) {
                        layer_required("请输入限购数量！");
                    } else {
                        var ids_arr = [];
                        $("#discountLeft li.list_on a").each(function () {
                            ids_arr.push($(this).data("id"));
                        });
                        var ids = ids_arr.join(",");

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var platform = [];
                        if (promotion_platform_pc == 1) {
                            platform.push('pc');
                        }
                        if (promotion_platform_app == 1) {
                            platform.push('app');
                        }
                        if (promotion_platform_wap == 1) {
                            platform.push('wap');
                        }
                        var jsonData = {
                            shop_single: ids,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                            if (!data) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (data.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (data.status == 'success') {
                                $("#discountLeft li.list_on a").each(function () {
                                    var mid = $(this).data("id");
                                    if($("#discountRight li").length > 0){
                                        $("#discountRight li a").each(function () {
                                            if($(this).data('id') == mid){
                                                ipass = 0;
                                            }
                                        });
                                    }
                                    if(ipass == 1){
                                        $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                        $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                            if (res.code == '400') {
                                                layer_required(res.msg);
                                                return false;
                                            }
                                        });
                                        $(this).parent().remove();
                                    }

                                });
                                if(ipass == 0){
                                    layer_required('不能添加同一个商品');
                                    return false;
                                }
                                $("#shop_single").val('');
                                var sid_arr = [];
                                $("#discountRight li a").each(function () {
                                    sid_arr.push($(this).data('id'));
                                });
                                var sids = sid_arr.join(",");
                                $("#shop_more").val(sids);
                            }
                        }, 'json');

                    }
                });

                //删除
                $("#delOption").on("click", function () {
                    $("#discountRight li.list_on a").each(function () {
                        var mid = $(this).data('id');
                        if($("#discountLeft li").length > 0){
                            $("#discountLeft li a").each(function () {
                                if(mid != $(this).data('id')){
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                                }
                            });
                        }else{
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                        }
                        $(this).parent().remove();
                    });
                    var sid_arr = [];
                    $("#discountRight li a").each(function () {
                        sid_arr.push($(this).data('id'));
                    });
                    var sids = sid_arr.join(",");
                    $("#shop_more").val(sids);
                });

                //单品全部添加
                $("#addOptionAll").on("click", function () {
                    var ipass = 1;
                    var xg_num = $("#xg_num").val();
                    if (xg_num <= 0 || !regNumber.test(xg_num)) {
                        layer_required("请输入限购数量！");
                    } else {
                        var ids_arr = [];
                        $("#discountLeft li a").each(function () {
                            ids_arr.push($(this).data("id"));
                        });
                        var ids = ids_arr.join(",");

                        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
                        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
                        var promotion_type = $("#promotion_type").val();
                        var start_time = $('#start_time').val();
                        var end_time = $('#end_time').val();
                        var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
                        var promotion_platform_pc = $("input[name='promotion_platform_pc']").val();
                        var promotion_platform_app = $("input[name='promotion_platform_app']").val();
                        var promotion_platform_wap = $("input[name='promotion_platform_wap']").val();
                        var promotion_platform_wechat = $("input[name='promotion_platform_wechat']").val();
                        var promotion_platform = $(".promotion_platform input:checked").length;
                        //验证活动平台
                        if (promotion_platform == 0) {
                            layer_required('活动平台至少勾选一个！');
                            return false;
                        }

                        var platform = [];
                        if (promotion_platform_pc == 1) {
                            platform.push('pc');
                        }
                        if (promotion_platform_app == 1) {
                            platform.push('app');
                        }
                        if (promotion_platform_wap == 1) {
                            platform.push('wap');
                        }
                        var jsonData = {
                            shop_single: ids,
                            promotion_id: promotion_id,
                            promotion_scope: promotion_scope,
                            promotion_type: promotion_type,
                            promotion_start_time: start_time,
                            promotion_end_time: end_time,
                            promotion_for_users : promotion_for_users,
                            promotion_platform_pc: promotion_platform_pc,
                            promotion_platform_app: promotion_platform_app,
                            promotion_platform_wap: promotion_platform_wap,
                            promotion_platform_wechat: promotion_platform_wechat
                        }

                        $.post('/promotion/verifyTimeRange', jsonData, function (data) {
                            if (!data) {
                                layer_error('操作失败');
                                return false;
                            }
                            if (data.status == 'error') {
                                layer_error(data.info);
                                return false;
                            }
                            if (data.status == 'success') {
                                $("#discountLeft li a").each(function () {
                                    var mid = $(this).data("id");
                                    if($("#discountRight li").length > 0){
                                        $("#discountRight li a").each(function () {
                                            if($(this).data('id') == mid){
                                                ipass = 0;
                                            }
                                        });
                                    }
                                    if(ipass == 1){
                                        $("#discountRight").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" data-xg="' + xg_num + '"><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>&nbsp;---限购：' + xg_num + '件</span></a></li>');

                                        $.post('/goods/isOnShelf', {platform: platform, ids: mid}, function (res) {
                                            if (res.code == '400') {
                                                layer_required(res.msg);
                                                return false;
                                            }
                                        });
                                        $(this).parent().remove();
                                    }

                                });
                                if(ipass == 0){
                                    layer_required('不能添加同一个商品');
                                    return false;
                                }
                                $("#shop_single").val('');
                                var sid_arr = [];
                                $("#discountRight li a").each(function () {
                                    sid_arr.push($(this).data('id'));
                                });
                                var sids = sid_arr.join(",");
                                $("#shop_more").val(sids);
                            }
                        }, 'json');

                    }
                });

                //全部删除
                $("#delOptionAll").on("click", function () {
                    $("#discountRight li a").each(function () {
                        var mid = $(this).data('id');
                        if($("#discountLeft li").length > 0){
                            $("#discountLeft li a").each(function () {
                                if(mid != $(this).data('id')){
                                    $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                                }
                            });
                        }else{
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="' + $(this).data("id") + '" data-pname="' + $(this).data("pname") + '" data-price="' + $(this).data("price") + '" ><span class=pro-name>' + $(this).data("pname") + '</span><span class=pro-price>原价：' + $(this).data("price") + '</span></a></li>');
                        }
                        $(this).parent().remove();
                    });
                    var sid_arr = [];
                    $("#discountRight li a").each(function () {
                        sid_arr.push($(this).data('id'));
                    });
                    var sids = sid_arr.join(",");
                    $("#shop_more").val(sids);
                });
                break;
        }

    });
//左边栏目选中
    $("body").on("click", "#moreLeft li a", function () {
        var $this = $(this);
        $this.parent().toggleClass("list_on");
    });

    //右边栏目选中
    $("body").on("click", "#moreRight li a", function () {
        var $this = $(this);
        $this.parent().toggleClass("list_on");
    });
    //左边栏目选中
    $("body").on("click", "#discountLeft li a", function () {
        var $this = $(this);
        $this.parent().toggleClass("list_on");
    });

    //右边栏目选中
    $("body").on("click", "#discountRight li a", function () {
        var $this = $(this);
        $this.parent().toggleClass("list_on");
    });
    //添加限购活动
    $('#addLimitPromotion').on('click', function () {
        //获取document的值
        var promotion_title = $('#promotion_title').val();
        var promotion_content = $('#promotion_content').val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        var startTime = new Date(start_time).getTime();
        var endTime = new Date(end_time).getTime();
        var promotion_platform = $(".promotion_platform input:checked").length;
        var limit_number = $("input[name='limit_number']").val();
        var limit_unit = $("input[name='limit_unit']").val();
        var nowTime = new Date().getTime();
        if(promotion_scope == '0'){
            layer_required('请选择限购活动的适用范围！');
            return false;
        }
        //验证活动名称
        if (!$.trim(promotion_title)) {
            layer_required('活动名称不能为空！');
            return false;
        }
        //验证时间合法性
        if (!$.trim(start_time) || !$.trim(end_time)) {
            layer_required('活动开始时间或结束时间不能为空！');
            return false;
        }
        if (startTime > endTime) {
            layer_required('活动开始时间不能大于结束时间！');
            return false;
        }
        if(endTime < nowTime){
            layer_required('活动结束时间不能小于当前时间！');return false;
        }
        //验证活动平台
        if (promotion_platform == 0) {
            layer_required('活动平台至少勾选一个！');
            return false;
        }
        //验证活动说明
        if (!$.trim(promotion_content)) {
            layer_required('活动说明不能为空！');
            return false;
        }
        var _ft = 1;
        /****** 验证使用范围start *****/
        switch (promotion_scope) {
            //分类验证
            case 'category':
                if (isCategorySelect() == false) {
                    return false;
                }
                break;
            case 'brand':
                var brand_id = $('input[name="shop_brand"]').val();
                if (brand_id == '' || brand_id == undefined) {
                    layer_required('请添加品牌！');
                    return false;
                }
                var _strJson = '';
                var max_num = 0;
                $(".brand_insert").each(function (i) {
                    var id = $(this).find("td:eq(0)").html();
                    var promotion_num = $(this).find("td:eq(2) input").val();
                    if (promotion_num == "") {
                        _ft = 0;
                        layer_required('限购数量不能为空！');
                        return false;
                    } else {
                        if (promotion_num <= 0 || !regNumber.test(promotion_num)) {
                            _ft = 0;
                            layer_required('限购数量必须大于0');
                            return false;
                        }
                        if (!regNumber.test(promotion_num)) {
                            _ft = 0;
                            layer_required('限购数量请输入正整数！');
                            return false;
                        } else {
                            if (i > 0) {
                                _strJson += ',';
                            }
                            _strJson += '{"id":"' + id + '","promotion_num":"' + promotion_num + '"}';
                            if (parseFloat(max_num) < parseFloat(promotion_num)){
                                max_num = promotion_num;
                            }
                        }
                    }
                });
                if(max_num > limit_number && limit_unit == 1){
                    layer_required('会员限购数量必须大于品牌限购数最大的数量');
                    return false;
                }
                $("#shop_brand_json").val('[' + _strJson + ']');
                break;
            case 'single':
                var goods_id = $('#shop_single').val();
                if (goods_id == '' || goods_id == undefined) {
                    layer_required('请添加单品！');
                    return false;
                }
                var _strJson = '';
                $("#discountRight li a").each(function (i) {
                    if (i > 0) {
                        _strJson += ',';
                    }
                    var id = $(this).data("id");
                    var promotion_num = $(this).data("xg");
                    _strJson += '{"id":"' + id + '","promotion_num":"' + promotion_num + '"}';
                });
                $("#shop_single_json").val('[' + _strJson + ']');
                break;
            case 'more':
                var goods_id = $('#shop_more').val();
                if (goods_id == '' || goods_id == undefined) {
                    layer_required('请添加单品！');
                    return false;
                }
                var _strJson = '';
                var max_num = 0;
                $("#discountRight li a").each(function (i) {
                    if (i > 0) {
                        _strJson += ',';
                    }
                    var id = $(this).data("id");
                    var promotion_num = $(this).data("xg");
                    _strJson += '{"id":"' + id + '","promotion_num":"' + promotion_num + '"}';
                    if (parseFloat(max_num) < parseFloat(promotion_num)){
                        max_num = promotion_num;
                    }
                });
                if(max_num > limit_number && limit_unit == 1){
                    layer_required('会员限购数量必须大于多品限购数最大的数量');
                    return false;
                }
                $("#shop_more_json").val('[' + _strJson + ']');
                break;
        }

        /****** 验证使用范围end *****/
        if (promotion_scope != 'single') {
            //验证限购数量
            if (!$.trim(limit_number)) {
                layer_required('请输入会员限购数');
                return false;
            }
            if (limit_number <= 0 || !regNumber.test(limit_number)) {
                layer_required('会员限购数只能是正整数');
                return false;
            }
        }
        if (_ft == 1) {
            //ajax提交
            ajaxSubmit('addLimitPromotionForm');
        }
    });
});

createValidateNotJoinActivity($("#promotion_scope").val());

switch ($("#promotion_scope").val()) {
    case 'all':
        $("#limit_number").html($("#xg").html());
        if ($("#expextCategoryIds").val() != "") {
            setTimeout(function () {
                $("#category_set").val($("#expextCategoryIds").val());
            }, 1)

        }
        if ($("#expextBrandIds").val() != "") {
            setTimeout(function () {
                $("#brand_set").val($("#expextBrandIds").val());
            }, 1)
        }
        if ($("#expextSingleIds").val() != "") {
            setTimeout(function () {
                $("#goods_set").val($("#expextSingleIds").val());
            }, 1)
        }
        break;
    case 'category':
        if ($("#expextBrandIds").val() != "") {
            setTimeout(function () {
                $("#brand_set").val($("#expextBrandIds").val());
            }, 1)

        }
        if ($("#expextSingleIds").val() != "") {
            setTimeout(function () {
                $("#goods_set").val($("#expextSingleIds").val());
            }, 1)
        }
        break;
    case 'brand':
        if ($("#expextSingleIds").val() != "") {
            setTimeout(function () {
                $("#goods_set").val($("#expextSingleIds").val());
            }, 1)
        }

        break;
    case 'single':

        break;
    case "more":

        break;
}

var category_num = 0;
var brand_num = 0;
var goods_num = 0;
$('#category_set,#brand_set,#goods_set').on('keyup', function () {
    var $this = $(this);
    if ($this[0]['id'] == 'category_set') {
        category_num = $this.val().length;
    }
    if ($this[0]['id'] == 'brand_set') {
        brand_num = $this.val().length;
    }
    if ($this[0]['id'] == 'goods_set') {
        goods_num = $this.val().length;
    }
    if (category_num > 0 || brand_num > 0 || goods_num > 0) {
        $('#validateBtn').addClass('btn-info');
        if ($('#addLimitPromotion')[0]) {
            $('#addLimitPromotion').attr("disabled", "disabled");
        }
    } else {
        if ($('#addLimitPromotion')[0]) {
            //$('#addLimitPromotion').removeAttr("disabled");
        }
    }
});

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
    if (category_num > 0 || brand_num > 0 || goods_num > 0) {
        $('#validateBtn').addClass('btn-info');
        if ($('#addLimitPromotion')[0]) {
            $('#addLimitPromotion').attr("disabled", "disabled");
        }
    } else {
        if ($('#addLimitPromotion')[0]) {
            //$('#addLimitPromotion').removeAttr("disabled");
        }
    }
});

//限购单位
$("#limit_number").on('change', '#limit_unit',function(){
    var promotion_for_users = $("select[name='promotion_for_users'] option:checked").val();
    var limit_unit = $(this).val();
    var limit_number = $("input[name='limit_number']");
    if(promotion_for_users == 20 && limit_unit == 3){
        limit_number.val(1);
        limit_number.attr('readonly','readonly');
    }else {
        limit_number.removeAttr('readonly');
    }
});