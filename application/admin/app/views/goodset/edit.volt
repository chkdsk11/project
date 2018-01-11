{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form" id="edit_good_set" method="post" action="/goodset/edit" enctype="multipart/form-data" >
                <input type="hidden" name="mid" value="{{ edit_info['id'] }}"/>
                <div class="page-header">
                    <h1>编辑商品套餐</h1>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="group_name" > <span  class="text-red">*</span>组合名称 </label>
                    <div class="col-sm-5">
                        <input type="text" id="group_name" name="group_name" placeholder="此处输入组合名称" class="col-xs-10 col-sm-5" value="{{ edit_info['group_name'] }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="group_introduction" > <span  class="text-red">*</span>组合描述 </label>
                    <div class="col-sm-5">
                        <textarea id="group_introduction" name="group_introduction" class="form-control" placeholder="此处输入组合描述">{{ edit_info['group_introduction'] }}</textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="start_time" > <span  class="text-red">*</span>开始时间 </label>
                    <div class="col-sm-5">
                        <input type="text" id="start_time" name="start_time" class="col-xs-10 col-sm-5 datetimepk " value="{{ date('Y-m-d H:i:s',edit_info['start_time']) }}" >
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="end_time" > <span  class="text-red">*</span>结束时间 </label>
                    <div class="col-sm-5">
                        <input type="text" id="end_time" name="end_time" class="col-xs-10 col-sm-5 datetimepk " value="{{ date('Y-m-d H:i:s',edit_info['end_time']) }}" >
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" > <span  class="text-red">*</span>活动平台</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                {% for key,platform in shopPlatform %}
                                    {% set selPlatform = key~'_platform' %}
                                    <label>
                                        <input name="use_platform[]" type="checkbox" class="ace" value="{% if key === 'pc' %}1{% elseif key === 'app' %}2{% elseif key === 'wechat' %}4{% else %}3{% endif %}" {% if edit_info[selPlatform] == "1" %}checked{% endif %}>
                                        <span class="lbl">&nbsp;{{ platform }}</span>
                                    </label>
                                {% endfor %}
                            {% endif %}
                        </div>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 互斥活动 </label>
                    <div class="checkbox mutex">
                        {% if goodSetEnum['mutexPromotion'] is defined %}
                        {% for k,v in goodSetEnum['mutexPromotion'] %}
                        <label>
                            <input name="good_set_mutex[]" type="checkbox" class="ace" value="{{k}}" {% for kk,vv in mutexList %}{% if vv == k %}checked{% endif %}{% endfor %}>
                            <span class="lbl">&nbsp;{{v}}</span>
                        </label>
                        {% endfor %}
                        {% endif %}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">  </label>
                    <div class="col-sm-8 add-production">
                        <div class="search-strat">
                            <input type="text" placeholder="请输入商品名称(可以多个)或者商品ID(可以多个)以英文逗号隔开" class="searchValue" id="search_value">
                            <a class="search" id="searchGoods" href="javascript:;"></a>
                        </div>
                        <div class="discount-left">
                            <ul class="search-item" id="discountLeft">

                            </ul>
                        </div>
                        <div class="select-discount" style="padding-left: 10px;">
                            <div>
                                <label >
                                    <div style="float: left;line-height: 34px;width: 56px;">数量：</div><input type="text" id="p_num" class="col-sm-4"/><div style="line-height: 34px;">个</div>
                                </label>
                            </div>
                            <div>
                                <label >
                                    <div style="float: left;line-height: 34px;width: 56px;">优惠价：</div><input type="text" id="p_cost" class="col-sm-4"/><div style="line-height: 34px;">元</div>
                                </label>
                            </div>
                            <a href="javascript:;" id="addOption">添加&gt;&gt;</a>
                            <a href="javascript:;" id="delOption">&lt;&lt;删除</a>

                        </div>
                        <div class="discount-right" >
                            <ul class="search-item" id='discountRight'>
                                {% if edit_info['group_list'] is defined %}
                                {% for k,v in edit_info['group_list'] %}
                                <li><a href="javascript:;" data-id="{{ v['goods_id'] }}" data-goods_price_pc="{{ v['goods_price_pc'] }}" data-goods_price_app="{{ v['goods_price_app'] }}" data-goods_price_wap="{{ v['goods_price_wap'] }}" data-goods_price_wechat="{{ v['goods_price_wechat'] }}" data-is_unified_price="{{ v['is_unified_price'] }}" data-pname="{{ v['goods_name'] }}" data-price="{{ v['price'] }}" data-pcost="{{ v['favourable_price'] }}" data-pnum="{{ v['goods_number'] }}" data-supplier_id="{{ v['supplier_id'] }}"><span class="pro-name">{{ v['goods_name'] }}</span><span class="pro-price">优惠价：{{ v['favourable_price'] }}元；数量：{{ v['goods_number'] }}个</span></a></li>
                                {% endfor %}
                                {% endif %}
                            </ul>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="single_list" id="single_list" value=''/>

                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        {% if edit_info['status'] >= 1  %}
                            <button class="btn" type="button" id="return_btn" onclick="location.href='/goodset/list'">
                                <i class="ace-icon fa fa-check bigger-110" ></i>
                                返回
                            </button>
                            {% else %}
                                {% if view == 1 %}
                                    <button class="btn" type="button" id="return_btn" onclick="location.href='/goodset/list'">
                                        <i class="ace-icon fa fa-check bigger-110" ></i>
                                        返回
                                    </button>
                                {% else %}
                                    <button class="btn btn-info" type="button" id="do_ajax_sure_btn" >
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认
                                    </button>
                                {% endif %}
                        {% endif %}

                    </div>
                </div>

            </form>

            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</div>
{% endblock %}
{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepk').datetimepicker({
            step: 10,
            allowBlank:true
        });
    });
    //初始化加载单品
    $.post("/limittime/getGoodsList",{is_gift:0}, function (data) {
        if(data!="0"){
            $.each(data, function (n,v) {
                if(v["is_unified_price"] == "0"){
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+v['id']+'" data-pname="'+v["goods_name"]+'" data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'" data-supplier_id="'+v['supplier_id']+'" data-whether_is_gift="'+v['whether_is_gift']+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>原价：'+v['price']+'</span></a></li>');
                }else{
                    $("#discountLeft").append('<li><a href="javascript:;" data-id="'+v['id']+'" data-pname="'+v["goods_name"]+'" data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'" data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'" data-supplier_id="'+v['supplier_id']+'" data-whether_is_gift="'+v['whether_is_gift']+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>pc价格：'+v['goods_price_pc']+'元,app价格:'+v["goods_price_app"]+'元,wap价格:'+v["goods_price_wap"]+'元,微商城价格:'+v["goods_price_wechat"]+'元</span></a></li>');
                }
            });
        }
    });

    //点击搜索 加载商品
    $("#searchGoods").on("click", function () {
        var search_value=$("#search_value").val();
                if($("#discountRight li a").length>0){
                  var discountRightIds='';
                  $("#discountRight li a").each(function () {
                    discountRightIds+=$(this).data('id')+',';
                  })
                  discountRightIds=discountRightIds.substring(0,discountRightIds.length-1)
                }
        if(search_value == ""){
            layer_required("搜索内容不能为空");
        }else{
            $.post("/limittime/getGoodsList",{input:search_value,is_gift:0,not_in:discountRightIds}, function (data) {
                if(data!="0"){
                    $("#discountLeft").html("");
                    $.each(data, function (n,v) {
                        v=v[0];
                        if(v["is_unified_price"] == "0"){
                            $("#discountLeft").append('<li><a href="javascript:;" data-id='+v['id']+' data-pname='+v['goods_name']+' data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'" data-supplier_id="'+v['supplier_id']+'" data-whether_is_gift="'+v['whether_is_gift']+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>原价：'+v['price']+'</span></a></li>');
                        }else{
                            $("#discountLeft").append('<li><a href="javascript:;" data-id='+v['id']+' data-pname='+v['goods_name']+' data-price='+v['price']+' data-is_unified_price="'+v["is_unified_price"]+'"  data-goods_price_pc="'+v["goods_price_pc"]+'" data-goods_price_app="'+v["goods_price_app"]+'" data-goods_price_wap="'+v["goods_price_wap"]+'" data-goods_price_wechat="'+v["goods_price_wechat"]+'" data-supplier_id="'+v['supplier_id']+'" data-whether_is_gift="'+v['whether_is_gift']+'"><span class=pro-name>'+v['goods_name']+'</span><span class=pro-price>pc价格：'+v['goods_price_pc']+'元,app价格:'+v["goods_price_app"]+'元,wap价格:'+v["goods_price_wap"]+'元,微商城价格:'+v["goods_price_wechat"]+'元</span></a></li>');
                        }
                    });
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

    $("body").on("change","#p_num",function () {
        if(!regNumber.test($(this).val()) || $(this).val() <= 0){
            layer_required("格式错误,请重新输入");
            $(this).val("");
        }
        if($(this).val() > 200){
            layer_required("商品数量不能高于200");
            $(this).val("");
        }
    });

    $("body").on("change","#p_cost",function () {
        if(!regMoney.test($(this).val()) || $(this).val() <= 0){
            layer_required("格式错误,请重新输入");
            $(this).val("");
        }
    });
    Array.prototype.min = function() {
        var min = this[0];
        var len = this.length;
        for (var i = 1; i < len; i++){
            if (this[i] < min){
                min = this[i];
            }
        }
        return min;
    }
    $("#addOption").on("click", function () {
        var p_num=$("#p_num").val();
        var p_cost=$("#p_cost").val();
        /*if(p_num == ''){
            layer_required("数量不能为空");
            return false;
        }
        if(p_cost == ''){
            layer_required("优惠价不能为空");
            return false;
        }*/
        if(!usNumber.test(p_num)){
            layer_required("请正确填写数量");
            $("#p_num").val('');
            return false;
        }
        if(!twoDecimal.test(p_cost)){
            layer_required("请正确填写优惠价");
            return false;
        }
        var _f = 1;
        if($("#discountLeft li a").length>0){
            var sid_arr=[];
            if ($("#discountLeft li.list_on").length <=0) {
                layer_required("请选择商品");
                return false;
            }
            $("#discountLeft li.list_on a").each(function () {
                var least_price = 0 ;
                var least_price_arr = [];
                var $that = $(this);
                var is_unified_price = $that.attr('data-is_unified_price'); // 是否统一价
                if(is_unified_price == 0){
                    least_price = $that.attr('data-price');
                }else {
                    $('input[name="use_platform[]"]:checked').each(function () {
                        switch ($(this).val())
                        {
                            case "1":
                                least_price_arr.push($that.data('goods_price_pc')) ;
                                break;
                            case "2":
                                least_price_arr.push($that.data('goods_price_app')) ;
                                break;
                            case "3":
                                least_price_arr.push($that.data('goods_price_wap')) ;
                                break;
                            case "4":
                                least_price_arr.push($that.data('goods_price_wechat')) ;
                                break;
                        }
                    });
                    least_price = least_price_arr.min();
                }

                console.log(least_price);
                if(least_price >= parseFloat(p_cost)){
                    sid_arr.push($(this).data("id"));
                }else{
                    sid_arr = [];
                    _f = 0;
                }
            });

            var _p=1;
            for(var i=0;i<sid_arr.length;i++){
                $("#discountRight li a").each(function () {
                    if(sid_arr[i] == $(this).data("id")){
                        _p=0;
                    }
                });
            }
            if(_p==0){
                layer_required("不能添加同一商品");
                return false;
            }
        }

        if(_f == 0){
            layer_required('套餐价格不能高于商品原价');
            return false;
        }
        var use_platform_arr=[];
        if(_f == 0)return false;
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
        var supplier_obj = {};
        $("#discountLeft li.list_on a").each(function () {
            var supplier_id =  $(this).attr('data-supplier_id'); // 供应商id
            if(!supplier_obj[supplier_id]){
                supplier_obj[supplier_id] = supplier_id;
            }
        });
        if(Object.getOwnPropertyNames(supplier_obj).length > 1){
            layer_required('不同商家的商品不能设置到同一个套餐内');return;
        }
        $("#discountLeft li.list_on a").each(function () {
            // 赠品分端判断
            var whether_is_gift = $(this).data("whether_is_gift");
            if(whether_is_gift == 1){
                $.post('/goods/isGift',{platform:use_platform_arr,ids:$(this).data("id")},function (res) {
                    if (res.code == '400') {
                        layer_required(res.msg);
                        return false;
                    }
                });
            }
            $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:$(this).data("id")},function (res) {
                if (res.code == '400') {
                    layer_required(res.msg);
                    return false;
                }
            });
              if($(this).data('is_unified_price')==0){
                       $("#discountRight").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'" data-pcost="'+p_cost+'" data-pnum="'+p_num+'" data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-supplier_id="'+$(this).data("supplier_id")+'" data-whether_is_gift="'+$(this).data("whether_is_gift")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>优惠价：'+p_cost+'元；数量：'+p_num+'个</span></a></li>');
                   }else{
                       $("#discountRight").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'" data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'" data-pcost="'+p_cost+'" data-pnum="'+p_num+'" data-supplier_id="'+$(this).data("supplier_id")+'" data-whether_is_gift="'+$(this).data("whether_is_gift")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>优惠价：'+p_cost+'元；数量：'+p_num+'个</span></a></li>');
                   }
            $(this).parent().remove();
        });
        $("#p_num").val("");
        $("#p_cost").val("");
    });

    //删除
    $("#delOption").on("click", function () {
        $("#discountRight li.list_on a").each(function () {
            if($(this).data("is_unified_price") == "0"){
                $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-supplier_id="'+$(this).data("supplier_id")+'" data-whether_is_gift="'+$(this).data("whether_is_gift")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>原价：'+$(this).data("price")+'</span></a></li>');
            }else{
                $("#discountLeft").append('<li><a href="javascript:;" data-id="'+$(this).data("id")+'" data-pname="'+$(this).data("pname")+'" data-price="'+$(this).data("price")+'"  data-is_unified_price = "'+$(this).data("is_unified_price")+'" data-goods_price_pc = "'+$(this).data("goods_price_pc")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'" data-goods_price_app = "'+$(this).data("goods_price_app")+'"  data-goods_price_wap = "'+$(this).data("goods_price_wap")+'" data-goods_price_wechat = "'+$(this).data("goods_price_wechat")+'" data-supplier_id="'+$(this).data("supplier_id")+'" data-whether_is_gift="'+$(this).data("whether_is_gift")+'"><span class=pro-name>'+$(this).data("pname")+'</span><span class=pro-price>pc价格：'+$(this).data("goods_price_pc")+'元,app价格:'+$(this).data("goods_price_app")+'元,wap价格:'+$(this).data("goods_price_wap")+'元,微商城价格:'+$(this).data("goods_price_wechat")+'元</span></a></li>');
            }
            $(this).parent().remove();
        });
    });

    //提交添加
    $("#do_ajax_sure_btn").on("click",function () {
        if($("#group_name").val()==""){
            layer_required("请添加组合名称");
            return false;
        }
        if($("#group_introduction").val()==""){
            layer_required("请添加组合描述");
            return false;
        }
        if($("#start_time").val()==""){
            layer_required("开始时间不能为空");
            return false;
        }
        if($("#end_time").val()==""){
            layer_required("结束时间不能为空");
            return false;
        }
        var start_time_timestamp = new Date($("#start_time").val()).getTime();
        var end_time_timestamp = new Date($("#end_time").val()).getTime();
        if(end_time_timestamp<start_time_timestamp){
            layer_required("结束时间不能早于开始时间");
            return false;
        }
        if($("#discountRight li a").length<1){
            layer_required("请添加商品");
            return false;
        }
        var supplier_obj = {};
        $("#discountRight li a").each(function () {
            var supplier_id =  $(this).attr('data-supplier_id'); // 供应商id
            if(!supplier_obj[supplier_id]){
                supplier_obj[supplier_id] = supplier_id;
            }
        });
        if(Object.getOwnPropertyNames(supplier_obj).length > 1){
            layer_required('不同商家的商品不能设置到同一个套餐内');return;
        }
        var single_detail='';
        $("#discountRight li a").each(function (i) {
            if(i>0){
                single_detail+=',{"goods_id":"'+$(this).data("id")+'","favourable_price":"'+$(this).data("pcost")+'","goods_number":"'+$(this).data("pnum")+'"}';
            }else{
                single_detail+='{"goods_id":"'+$(this).data("id")+'","favourable_price":"'+$(this).data("pcost")+'","goods_number":"'+$(this).data("pnum")+'"}';
            }
        });
        single_detail="["+single_detail+"]";
        $("#single_list").val(single_detail);
        var use_platform_length=$("input[name='use_platform[]']:checked").length;
        if(use_platform_length < 1){
            layer_required("活动平台必须选上");
            return false;
        }
        ajaxSubmit("edit_good_set");
    });
</script>
{% endblock %}