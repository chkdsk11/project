{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        添加限时优惠
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addLimitPromotionForm"  class="form-horizontal" action="/limittime/add" method="post" onsubmit="ajaxSubmit(addLimitPromotionForm);return false;">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div>
                            <input id="promotion_type" type="hidden" name="promotion_type" value="35" />
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>活动名称 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="promotion_title" name="promotion_title" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动开始时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="start_time" name="promotion_start_time" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动结束时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="end_time" name="promotion_end_time" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动平台 </label>
                                <div class="checkbox promotion_platform">
                                    <label>
                                        <input name="promotion_platform_pc" type="checkbox" class="ace" value="1" checked>
                                        <span class="lbl">&nbsp;PC</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_app" type="checkbox" class="ace" value="1" checked>
                                        <span class="lbl">&nbsp;APP</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_wap" type="checkbox" class="ace" value="1" checked>
                                        <span class="lbl">&nbsp;WAP</span>
                                    </label>
                                    <label style="display: none">
                                        <input name="promotion_platform_wechat" type="checkbox" class="ace" value="0">
                                        <span class="lbl">&nbsp;微商城</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>活动说明 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_content" class="col-xs-10 col-sm-5" id="promotion_content" placeholder="不可为空" ></textarea>

                                </div>
                            </div>

                            <div class="">
                                <h3>
                                    条件信息
                                </h3>
                            </div><!-- /.condition -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_member_level"> <span  class="text-red">*</span>会员等级 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_member_level" name="promotion_member_level">
                                        {% if limitEnum['memberLevel'] is defined %}
                                        {% for k,v in limitEnum['memberLevel'] %}
                                        <option value="{{k}}">{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_for_users"> <span  class="text-red">*</span>适用人群 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_for_users" name="promotion_for_users">
                                        {% if limitEnum['forPeople'] is defined %}
                                        {% for k,v in limitEnum['forPeople'] %}
                                        <option value="{{k}}">{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 会员标签 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="member_tag" name="member_tag" class="col-sm-2">
                                        <option value="0">不指定</option>
                                        {% if memberTag is defined and memberTag is not empty%}
                                        {% for k,v in memberTag %}
                                        <option value="{{v['tag_id']}}">{{v['tag_name']}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="offer_type"> <span  class="text-red">*</span>优惠类型 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="offer_type" name="offer_type">
                                        {% if limitEnum['limitTimeType'] is defined %}
                                        {% for k,v in limitEnum['limitTimeType'] %}
                                        <option value="{{k}}">{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                    <span class="grey">折扣请填写0.1~9.9之内的数字；优惠价只保留两位小数点</span>
                                </div>
                            </div>
                            <!-- 互斥活动 -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 互斥活动 </label>
                                <div class="checkbox promotion_mutex">
                                    {% if limitEnum['mutexPromotion'] is defined %}
                                    {% for k,v in limitEnum['mutexPromotion'] %}
                                    <label>
                                        <input name="promotion_mutex[]" type="checkbox" class="ace" value="{{k}}">
                                        <span class="lbl">&nbsp;{{v}}</span>
                                    </label>
                                    {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                            <!-- 适用范围start -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_scope"> <span  class="text-red">*</span>适用范围 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_scope" id="promotion_scope">
                                        <option value="single">单品</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">

                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加商品 </label>
                            <div class="col-sm-8 add-production">
                                <div class="search-strat">
                                    <input type="text" placeholder="请输入商品名称(可以多个)或者商品ID(可以多个)以英文逗号隔开" class="searchValue" id="search_value">
                                    <a class="search" id="searchGoods" href="javascript:;"></a>
                                </div>
                                <div class="discount-left">
                                    <ul class="search-item" id="discountLeft">

                                    </ul>
                                </div>
                                <div class="select-discount">
                                    <a href="javascript:;" id="addOptionAll">全部添加&gt;&gt;</a>
                                    <a href="javascript:;" id="addOption">添加&gt;&gt;</a>
                                    <a href="javascript:;" id="delOption">&lt;&lt;删除</a>
                                    <a href="javascript:;" id="delOptionAll">&lt;&lt;全部删除</a>
                                </div>
                                <div class="discount-right" >
                                    <ul class="search-item" id='discountRight'>
                                    </ul>
                                </div>
                            </div>

                            <div style="padding-top: 20px;">
                                <div style="margin-top:20px;" class="col-md-offset-3 col-md-9">
                                    <button id="sure_btn" class="btn btn-info" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认
                                    </button>
                                    &nbsp; &nbsp; &nbsp;
                                    <button id="reset_btn" class="btn" type="button">
                                        <i class="ace-icon fa fa-undo bigger-110"></i>
                                        重置
                                    </button>
                                </div>
                            </div>

                            </div>
                            <div>
                                <table class="discount-tab table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr class="JgoodsTab">
                                            <th width="10%">商品ID</th>
                                            <th width="45%">商品名称</th>
                                            <th width="10%">原价</th>
                                            <th width="20%">折扣&nbsp;<input id="discount_value" type="text" size="1" style="width:75px;" placeholder="统一折扣">&nbsp;<input type="button" value="ok" id="changeAllDiscount"></th>
                                            <th width="10%">优惠价</th>
                                            <th width="5%">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="JgoodsTabTbody">

                                    </tbody>
                                </table>
                            </div>

                            <input type='hidden' name='shop_single' id='shop_single'>
                            <div id="shop_single_temp" style="display: none;"></div>
                            <!-- 适用范围end -->

                            <input type="hidden" id="offer_goods" name="offer_goods" value=''>
                            <div>
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addLimitPromotion" class="btn btn-info" type="button">
                                        提交
                                    </button>
                                    &nbsp; &nbsp; &nbsp;
                                    <!--<button class="btn" type="reset">
                                        <i class="ace-icon fa fa-undo bigger-110"></i>
                                        重置
                                    </button>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.main-content -->
            </form>
        </div>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>

<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/addLimitTime.js"></script>
<script type="text/javascript">
    //初始化加载单品
    $.post("/limittime/getGoodsList", function (data) {
        if(data!="0"){
            $.each(data, function (n,v) {
                if(v["is_unified_price"] == "0"){
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+v['id']+'" data-pname="'+v["goods_name"]+'" data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'" data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>原价：'+v['price']+'</span></a></li>');
                }else{
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+v['id']+'" data-pname="'+v["goods_name"]+'" data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'" data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>pc价格：'+v['goods_price_pc']+'元,app价格:'+v["goods_price_app"]+'元,wap价格:'+v["goods_price_wap"]+'元,微商城价格:'+v["goods_price_wechat"]+'元</span></a></li>');
                }
            });
        }
    });

    //点击搜索 加载商品
    $("#searchGoods").on("click", function () {
        var search_value=$("#search_value").val();
        if(search_value == ""){
            layer_required("搜索内容不能为空");
        }else{
            $.post("/limittime/getGoodsList",{input:search_value}, function (data) {
                if(data!="0"){
                    $("#discountLeft").html("");
                    $.each(data, function (n,v) {
                        v=v[0];
                        if(v["is_unified_price"] == "0"){
                            $("#discountLeft").append('<li><a href="javascript:;" data-id='+v['id']+' data-pname='+v['goods_name']+' data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'"  data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>原价：'+v['price']+'</span></a></li>');
                        }else{
                            $("#discountLeft").append('<li><a href="javascript:;" data-id='+v['id']+' data-pname='+v['goods_name']+' data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'"  data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>pc价格：'+v['goods_price_pc']+'元,app价格:'+v["goods_price_app"]+'元,wap价格:'+v["goods_price_wap"]+'元,微商城价格:'+v["goods_price_wechat"]+'元</span></a></li>');
                        }
                    });
                }else{
                    layer_required('搜索结果为空');
                    $("#discountLeft").html("");
                }
            });
        }
    });

    //左边栏目选中
    $("body").on("click","#discountLeft li a", function () {
        var $this=$(this);
        $this.parent().toggleClass("list_on");
    });

    //右边栏目选中
    $("body").on("click","#discountRight li a", function () {
        var $this=$(this);
        $this.parent().toggleClass("list_on");
    });

    //添加
    $("#addOption").on("click", function () {
        var ids_arr=[];
        $("#discountLeft li.list_on a").each(function () {
            ids_arr.push($(this).data("id"));
        });
        var ids=ids_arr.join(",");

        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var promotion_type = $("#promotion_type").val();
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
            shop_single : ids,
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

        $.post('/limittime/verifyTimeRange',jsonData,function(data) {
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
                    var rid = $(this).data("id");
                    var is_add_re = 0;
                    $("#discountRight li a").each(function () {
                        if(rid == $(this).data("id")) is_add_re = 1;
                    });
                    if(is_add_re == 1){
                        layer_required('不能添加同一商品');
                        return ;
                    }
                    if($(this).data("is_unified_price") == "0"){
                        $("#discountRight").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'" data-is_unified_price = "'+$(this).data("is_unified_price")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
                    }else{
                        $("#discountRight").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'" data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
                    }
                    var mid=$(this).data("id");
                    $.post('/goods/isOnShelf',{platform:platform,ids:mid},function (res) {
                        if(res.code == '400'){
                            layer_required(res.msg);
                            return false;
                        }
                    });
                    $(this).parent().remove();
                });
            }
        },'json');

    });

    //删除
    $("#delOption").on("click", function () {
        $("#discountRight li.list_on a").each(function () {
            if($(this).data("is_unified_price") == "0"){
                var mid = $(this).data('id');
                if($("#discountLeft li").length > 0){
                    $("#discountLeft li a").each(function () {
                        if(mid != $(this).data('id')){
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
                        }
                    });
                }else{
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
                }

            }else{
                var mid = $(this).data('id');
                if($("#discountLeft li").length > 0){
                    $("#discountLeft li a").each(function () {
                        if(mid != $(this).data('id')){
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
                        }
                    });
                }else{
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
                }

            }

            $(this).parent().remove();
        });
    });

    //全部添加
    $("#addOptionAll").on("click", function () {
        var ids_arr=[];
        $("#discountLeft li a").each(function () {
            ids_arr.push($(this).data("id"));
        });
        var ids=ids_arr.join(",");

        var promotion_id = $("input[name='promotion_id']").val() == undefined ? 0 : $("input[name='promotion_id']").val();
        var promotion_scope = $("select[name='promotion_scope'] option:checked").val();
        var promotion_type = $("#promotion_type").val();
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
            shop_single : ids,
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

        $.post('/limittime/verifyTimeRange',jsonData,function(data) {
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
                    var rid = $(this).data("id");
                    var is_add_re = 0;
                    $("#discountRight li a").each(function () {
                        if(rid == $(this).data("id")) is_add_re = 1;
                    });
                    if(is_add_re == 1){
                        layer_required('不能添加同一商品');
                        return ;
                    }
                    if($(this).data("is_unified_price") =="0"){
                        $("#discountRight").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
                    }else{
                        $("#discountRight").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'" data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'" ><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
                    }

                    var mid=$(this).data("id");
                    $.post('/goods/isOnShelf',{platform:platform,ids:mid},function (res) {
                        if(res.code == '400'){
                            layer_required(res.msg);
                            return false;
                        }
                    });
                    $(this).parent().remove();
                });
            }
        },'json');
    })

    //全部删除
    $("#delOptionAll").on("click", function () {
        $("#discountRight li a").each(function () {
            if($(this).data("is_unified_price") == "0"){
                var mid = $(this).data('id');
                if($("#discountLeft li").length > 0){
                    $("#discountLeft li a").each(function () {
                        if(mid != $(this).data('id')){
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
                        }
                    });
                }else{
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
                }

            }else{
                var mid = $(this).data('id');
                if($("#discountLeft li").length > 0){
                    $("#discountLeft li a").each(function () {
                        if(mid != $(this).data('id')){
                            $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
                        }
                    });
                }else{
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
                }
            }
            $(this).parent().remove();
        });
    });

    //确认按钮
    $("#sure_btn").on("click", function () {
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
        if($("#discountRight li").length<1){
            layer_required('还没添加单品！');return false;
        }
        var JgoodsTabTbody=$("#JgoodsTabTbody");
        var discountRight_len=$("#discountRight li").length;
        var offer_type=$("#offer_type").val();
        var _op=1;
        if(discountRight_len > 0){
            if($("#JgoodsTabTbody tr").length >0){
                $("#discountRight li a").each(function () {
                    var id=$(this).data("id");
                    $("#JgoodsTabTbody tr").find("td:eq(0)").each(function () {
                        if(id==$(this).html()){
                            _op=0;
                            layer_required("商品id:"+id+"已经重复,不能添加");
                            return false;
                        }
                    })
                });
            }
            if(_op==1){
                switch (offer_type){
                    case "1":
                        $("#discountRight li a").each(function () {
                            if($(this).data('is_unified_price') == '0'){
                                JgoodsTabTbody.append('<tr data-is_unified_price="0"><td>'+$(this).data('id')+'</td><td>'+$(this).data('pname')+'</td><td>统一价：'+$(this).data('price')+'</td><td><input type="text" data-sid = "'+$(this).data('id')+'" class="discount_num" placeholder="几折"></td><td><input type="text" class="discount_price" placeholder="价格" disabled></td><td><a class="del_js" href="javascript:void(0);" style="text-decoration: none;">删除</a></td></tr>');
                            }else{
                                var $that = $(this);
                                $.each(platform,function (n,v) {
                                    var delStr = '';
                                    var headStr = '<tr><td></td><td></td>';
                                    if(n == 0){
                                        delStr = '<a class="del_js" href="javascript:void(0);" data-is_multi="1" data-ptnum="'+platform.length+'" style="text-decoration: none;">删除</a>';
                                        headStr = '<tr data-is_unified_price="1"><td>'+$that.data('id')+'</td><td>'+$that.data('pname')+'</td>';
                                    }
                                   switch (v){
                                       case 'pc':
                                           JgoodsTabTbody.append(headStr+'<td>pc：'+$that.data('goods_price_pc')+'</td><td><input type="text" class="discount_num" data-platform = "pc" data-sid = "'+$that.data('id')+'" placeholder="几折"></td><td><input type="text" class="discount_price" placeholder="价格" data-platform = "pc" disabled></td><td>'+delStr+'</td></tr>');
                                           break;
                                       case 'app':
                                           JgoodsTabTbody.append(headStr+'<td>app：'+$that.data('goods_price_app')+'</td><td><input type="text" class="discount_num" data-platform = "app" data-sid = "'+$that.data('id')+'" placeholder="几折"></td><td><input type="text" class="discount_price" data-platform = "app" placeholder="价格" disabled></td><td>'+delStr+'</td></tr>');
                                           break;
                                       case 'wap':
                                           JgoodsTabTbody.append(headStr+'<td>wap：'+$that.data('goods_price_wap')+'</td><td><input type="text" class="discount_num" data-platform = "wap" data-sid = "'+$that.data('id')+'" placeholder="几折"></td><td><input type="text" class="discount_price" data-platform = "wap" placeholder="价格" disabled></td><td>'+delStr+'</td></tr>');
                                           break;
                                       case 'wechat':
                                           JgoodsTabTbody.append(headStr+'<td>微商城：'+$that.data('goods_price_wechat')+'</td><td><input type="text" class="discount_num" data-platform = "wechat" data-sid = "'+$that.data('id')+'" placeholder="几折"></td><td><input type="text" class="discount_price" data-platform = "wechat" placeholder="价格" disabled></td><td>'+delStr+'</td></tr>');
                                           break;
                                   }
                                });
                            }

                        });
                        break;
                    case "2":
                        $("#discountRight li a").each(function () {
                            if($(this).data('is_unified_price') == '0'){
                                JgoodsTabTbody.append('<tr data-is_unified_price="0" ><td>'+$(this).data('id')+'</td><td>'+$(this).data('pname')+'</td><td>统一价：'+$(this).data('price')+'</td><td><input type="text" class="discount_num" placeholder="几折" disabled></td><td><input type="text" class="discount_price" placeholder="价格" data-sid = "'+$(this).data('id')+'" ></td><td><a class="del_js" href="javascript:void(0);" style="text-decoration: none;">删除</a></td></tr>');
                            }else{
                                var $that = $(this);
                                $.each(platform,function (n,v) {
                                    var delStr = '';
                                    var headStr = '<tr><td></td><td></td>';
                                    if(n == 0){
                                        delStr = '<a class="del_js" href="javascript:void(0);" data-is_multi="1" data-ptnum="'+platform.length+'" style="text-decoration: none;">删除</a>';
                                        headStr = '<tr data-is_unified_price="1"><td>'+$that.data('id')+'</td><td>'+$that.data('pname')+'</td>';
                                    }
                                    switch (v){
                                        case 'pc':
                                            JgoodsTabTbody.append(headStr+'<td>pc：'+$that.data('goods_price_pc')+'</td><td><input type="text" class="discount_num" placeholder="几折" data-platform = "pc" disabled></td><td><input type="text" class="discount_price" placeholder="价格" data-platform = "pc"  data-sid = "'+$that.data('id')+'" ></td><td>'+delStr+'</td></tr>');
                                            break;
                                        case 'app':
                                            JgoodsTabTbody.append(headStr+'<td>app：'+$that.data('goods_price_app')+'</td><td><input type="text" class="discount_num" data-platform = "app" placeholder="几折" disabled></td><td><input type="text" class="discount_price" placeholder="价格" data-platform = "app"  data-sid = "'+$that.data('id')+'" ></td><td>'+delStr+'</td></tr>');
                                            break;
                                        case 'wap':
                                            JgoodsTabTbody.append(headStr+'<td>wap：'+$that.data('goods_price_wap')+'</td><td><input type="text" class="discount_num" data-platform = "wap" placeholder="几折" disabled></td><td><input type="text" class="discount_price" placeholder="价格" data-platform = "wap"  data-sid = "'+$that.data('id')+'" ></td><td>'+delStr+'</td></tr>');

                                            break;
                                        case 'wechat':
                                            JgoodsTabTbody.append(headStr+'<td>微商城：'+$that.data('goods_price_wechat')+'</td><td><input type="text" class="discount_num" data-platform = "wechat" placeholder="几折" disabled></td><td><input type="text" class="discount_price" placeholder="价格" data-platform = "wechat"  data-sid = "'+$that.data('id')+'" ></td><td>'+delStr+'</td></tr>');

                                            break;
                                    }
                                });


                            }

                        });
                        break;
                }
                var _p=[];
                $("#JgoodsTabTbody tr").find("td:eq(0)").each(function () {
                    if($(this).html() !=''){
                        _p.push($(this).html());
                    }
                });
                $('#shop_single').val(_p.join(","));
            }


        }
    });

    //重置按钮
    $("#reset_btn").on("click", function () {
        $("#discountLeft").html("");
        $("#discountRight").html("");
        $.post("/limittime/getGoodsList", function (data) {
            if(data!="0"){
                $.each(data, function (n,v) {
                    if(v["is_unified_price"] == "0"){
                        $("#discountLeft").append('<li><a href="javascript:;" data-id="'+v['id']+'" data-pname="'+v["goods_name"]+'" data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>原价：'+v['price']+'</span></a></li>');
                    }else{
                        $("#discountLeft").append('<li><a href="javascript:;" data-id="'+v['id']+'" data-pname="'+v["goods_name"]+'" data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'" data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>pc价格：'+v['goods_price_pc']+'元,app价格:'+v["goods_price_app"]+'元,wap价格:'+v["goods_price_wap"]+'元,微商城价格:'+v["goods_price_wechat"]+'元</span></a></li>');
                    }
                });
            }
        });
    });

    //删除对应条
    $("body").on("click",".del_js", function () {
        var $this=$(this);
        var this_line=$this.parent().parent();

        if($this.data('is_multi') == '1'){
            var ptnum = $this.data('ptnum');
            switch (ptnum)
            {
                case 4:
                    this_line.next().next().next().remove();
                    this_line.next().next().remove();
                    this_line.next().remove();
                    this_line.remove();
                    break;
                case 3:
                    this_line.next().next().remove();
                    this_line.next().remove();
                    this_line.remove();
                    break;
                case 2:
                    this_line.next().remove();
                    this_line.remove();
                    break;
                case 1:
                    this_line.remove();
                    break;
                case 0:
                    layer_required('请选择平台');
                    return false;
                    break;
            }

        }else{
            this_line.remove();
        }
        var _p=[];
        $("#JgoodsTabTbody tr").find("td:eq(0)").each(function () {
            if($(this).html() !=''){
                _p.push($(this).html());
            }
        });
        $('#shop_single').val(_p.join(","));
    });

    var offer_type=$("#offer_type").val();
    if(offer_type==1){
        //折扣
        $("#discount_value").show(0);
        $("#changeAllDiscount").show(0);
        $(".discount_num").val("");
        $(".discount_price").val("");
        $(".discount_num").removeAttr("disabled");
        $(".discount_price").attr("disabled",true);
    }else{
        //优惠价
        $("#discount_value").hide(0);
        $("#changeAllDiscount").hide(0);
        $(".discount_num").val("");
        $(".discount_price").val("");
        $(".discount_price").removeAttr("disabled");
        $(".discount_num").attr("disabled",true);
    }
    $("#offer_type").on("change", function () {
        var offer_type=$(this).val();
        if(offer_type==1){
            //折扣
            $("#discount_value").show(0);
            $("#changeAllDiscount").show(0);
            $(".discount_num").val("");
            $(".discount_price").val("");
            $(".discount_num").removeAttr("disabled");
            $(".discount_price").each(function () {
                var sid = $(this).data('sid');
                $(this).parent().parent().find('td:eq(3) .discount_num').attr('data-sid',sid);
            });
            $(".discount_price").attr("disabled",true);
        }else{
            //优惠价
            $("#discount_value").hide(0);
            $("#changeAllDiscount").hide(0);
            $(".discount_num").val("");
            $(".discount_price").val("");
            $(".discount_price").removeAttr("disabled");
            $(".discount_num").each(function () {
                var sid = $(this).data('sid');
                $(this).parent().parent().find('td:eq(4) .discount_price').attr('data-sid',sid);
            });
            $(".discount_num").attr("disabled",true);
        }
    });
    $("#discount_value").on('keyup',function () {
        if(($(this).val() >=10 || $(this).val() <0)){
            $(this).val("");
            layer_required("折扣必须在0~10之间");
        }
    });
    $("#changeAllDiscount").on("click", function () {
        var discount_value=$("#discount_value").val();
        if(discount_value <= 0){
            $("#discount_value").val('');
            layer_required("请输入正确折扣数值");
        }else{

            if((discount_value>=10 || discount_value <0)){
                layer_required("折扣必须在0~10之间");
            }else{
                var p = discount_value.split(".");

                if(!oneDecimal.test(discount_value)){
                    $("#discount_value").val("");
                    layer_required("折扣必须在1位小数之内");
                }else{
                    $(".discount_num").val(discount_value);
                }
            }
        }
    });
    $("body").on("change",".discount_num", function () {
        var discount_num=$(this).val();
        if(discount_num <= 0){
            layer_required("请输入正确折扣数值");
            $(this).val("");
        }else{
            /*var rex = /^[0-9]+.?[0-9]*$/
            if(!rex.test(discount_num)){
                layer_required("必须为数字");
                $(this).val("");
                return false;
            }
            var p = discount_num.split(".");
            if((discount_num>=10 || discount_num <0)){
                $(this).val("");
                layer_required("折扣必须在0~10之间");
            }*/
           if(!oneDecimal.test(discount_num)){
               $(this).val("");
               layer_required("折扣必须在1位小数之内");
           }
        }
    });
    $("body").on("change",".discount_price", function () {
        var discount_price=$(this).val();
        if(discount_price==""){
            layer_required("优惠价不能输入空字符");
        }else{
            var rex = /^[0-9]+.?[0-9]*$/
            if(!rex.test(discount_price)){
                layer_required("必须为数字");
                $(this).val("");
                return false;
            }
                if($(this).val()<=0){$(this).val("");layer_required("优惠价不能少于等于0");return false;}
            if(discount_price<0 || !regMoney.test(discount_price)){
                $(this).val("");
                layer_required("优惠价必须在2位小数之内");
            }
        }
    });
</script>
{% endblock %}