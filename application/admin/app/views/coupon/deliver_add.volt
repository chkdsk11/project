{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->

            <div class="row">
                <div class="col-xs-12">

                    <div class="clearfix">
                        <div class="pull-right tableTools-container"></div>
                    </div>
                    <div class="page-header">
                        <h1>赠送优惠券</h1>
                    </div>

                    <form class="form-horizontal" role="form" id="insert_deliver" method="post" action="/coupon/deliver/add" enctype="multipart/form-data" >
                        <div class="form-group">
                            <div class="col-sm-7">
                            <label style="line-height:34px;padding:0 10px;border-bottom: 2px solid #2679b5;"><a style="text-decoration: none;" href="javascript:void(0);">赠券列表</a></label> <label style="line-height:34px;padding:0 10px;"><a style="text-decoration: none;" href="/coupon/deliver/list">已赠券列表</a></label>
                            </div>
                        </div>
                        <div id="show_tel_area">
                            <div class="form-group">
                                <label class="col-sm-2 control-label no-padding-right" >
                                    <select class="form-control" id="orderType">
                                        <option value="1">按用户</option>
                                        <option value="2">按订单</option>
                                    </select></label>
                                <div class="col-sm-7" style="padding: 7px 2px;">
                                    <div class="input-group">
                                        <input class="form-control" id="search_tels_input" type="text" placeholder="请输入手机或邮箱（多用户搜索时请用英文逗号,隔开）">
                                    <span class="input-group-btn">
                                        <button id="do_search_btn" class="btn btn-sm btn-default" type="button">
                                            <i class="ace-icon fa fa-search bigger-110"></i>
                                        </button>
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-9">
                                    <div class="lister">
                                        <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                                            <thead>
                                            <tr>
                                                <th>用户账号</th>
                                                <th>昵称</th>
                                            </tr>
                                            </thead>
                                        </table>
                                        <ul class="left" id="tel_left">
                                            <!-- <li>
                                                 <a href="javascript:void(0);" data-mid="4">rrr  原价:0.00</a>
                                             </li>-->
                                        </ul>
                                    </div>
                                    <div class="mid_panel">
                                        <div class="add_all" id="add_all_user_btn">
                                            <a href="javascript:void(0);">全部添加&gt;&gt;</a>
                                        </div>
                                        <div class="add" id="add_user_btn">
                                            <a href="javascript:void(0);">添&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;加&gt;&gt;</a>
                                        </div>
                                        <div class="del_all" id="all_del_user_btn">
                                            <a href="javascript:void(0);">&lt;&lt; 全部删除</a>
                                        </div>
                                        <div class="del " id="del_user_btn">
                                            <a href="javascript:void(0);">&lt;&lt; 删&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;除</a>
                                        </div>
                                    </div>
                                    <div class="lister ">
                                        <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                                            <thead>
                                            <tr>
                                                <th>用户账号</th>
                                                <th>昵称</th>
                                            </tr>
                                            </thead>
                                        </table>
                                        <ul class="right " id="tel_right">

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">

                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>编号：</span>
                                    <input type="text" id="sid" class="tools-txt" value=""/>
                                </label>
                                <label class="clearfix">
                                    <span>活动名称：</span>
                                    <input type="text" id="coupon_name" class="tools-txt" value=""/>
                                </label>

                            </div>
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>类型：</span>
                                    <select id="coupon_scope" >
                                        <option value="0">全部</option>
                                        {% if couponEnum['forScope'] is defined %}
                                        {% for k,v in couponEnum['forScope'] %}
                                        <option value="{{k}}" >{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </label>
                                <label class="clearfix" style="height: 20px;">
                                    <button class="btn btn-primary" type="button" id="search_activities_btn">搜索</button>
                                </label>
                            </div>
                        </div>



                        <div class="form-group">
                            <div class="col-sm-9">
                                <div class="lister">
                                    <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                                        <thead>
                                        <tr>
                                            <th>编号</th>
                                            <th>活动名称</th>
                                            <th class="hidden-480">数量</th>
                                            <th>使用范围</th>
                                        </tr>
                                        </thead>
                                    </table>
                                    <ul class="left" id="left_act">
                                        <!-- <li>
                                             <a href="javascript:void(0);" data-mid="4">rrr  原价:0.00</a>
                                         </li>-->
                                    </ul>
                                </div>
                                <div class="mid_panel">
                                    <div>
                                        赠送数量<input type="text" id="limit_buy" style="width: 40px;">
                                    </div>
                                    <div id="add_hd" class="add">
                                        <a href="javascript:void(0);">添&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;加&gt;&gt;</a>
                                    </div>
                                    <div id="del_hd" class="del">
                                        <a href="javascript:void(0);">&lt;&lt; 删&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;除</a>
                                    </div>
                                </div>
                                <div class="lister ">
                                    <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                                        <thead>
                                        <tr>
                                            <th>编号</th>
                                            <th>活动名称</th>
                                            <th class="hidden-480">使用范围</th>
                                            <th>赠送数量</th>
                                        </tr>
                                        </thead>
                                    </table>
                                    <ul class="right " id="right_act">

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix form-actions">
                            <div class="col-md-offset-3 col-md-9">
                                <button class="btn btn-info" type="button" id="do_submit_deliver_btn">
                                    <i class="ace-icon fa fa-check bigger-110"></i>
                                    提交
                                </button>
                            </div>
                        </div>


                     </form>

                    <!-- div.table-responsive -->
                    <!-- div.dataTables_borderWrap -->

                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.page-content -->
{% if couponDetailList['page'] is defined and couponDetailList['list'] != 0 %}
{{ couponDetailList['page'] }}
{% endif %}
{% endblock %}

{% block footer %}
<script type="text/html" id="getTelByOrderId">
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right" >
            <select class="form-control" id="orderType">
                <option value="1">按用户</option>
                <option value="2" selected>按订单</option>
            </select></label>
        <div class="col-sm-7" style="padding: 7px 2px;">
            <div class="input-group">
                <input class="form-control" id="search_tels_input" type="text" placeholder="请输入订单号（多订单搜索时请用英文逗号,隔开）">
                                    <span class="input-group-btn">
                                        <button id="do_search_btn" class="btn btn-sm btn-default" type="button">
                                            <i class="ace-icon fa fa-search bigger-110"></i>
                                        </button>
                                    </span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-9">
            <div class="lister">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                    <thead>
                    <tr>
                        <td>订单号</td>
                        <th>用户账号</th>
                        <th>昵称</th>
                    </tr>
                    </thead>
                </table>
                <ul class="left" id="tel_left">
                    <!-- <li>
                         <a href="javascript:void(0);" data-mid="4">rrr  原价:0.00</a>
                     </li>-->
                </ul>
            </div>
            <div class="mid_panel">
                <div class="add_all" id="add_all_user_btn">
                    <a href="javascript:void(0);">全部添加&gt;&gt;</a>
                </div>
                <div class="add" id="add_user_btn">
                    <a href="javascript:void(0);">添&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;加&gt;&gt;</a>
                </div>
                <div class="del_all" id="all_del_user_btn">
                    <a href="javascript:void(0);">&lt;&lt; 全部删除</a>
                </div>
                <div class="del " id="del_user_btn">
                    <a href="javascript:void(0);">&lt;&lt; 删&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;除</a>
                </div>
            </div>
            <div class="lister ">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                    <thead>
                    <tr>
                        <td>订单号</td>
                        <th>用户账号</th>
                        <th>昵称</th>
                    </tr>
                    </thead>
                </table>
                <ul class="right " id="tel_right">

                </ul>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="getTelByUser">
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right" >
            <select class="form-control" id="orderType">
                <option value="1" selected>按用户</option>
                <option value="2">按订单</option>
            </select></label>
        <div class="col-sm-7" style="padding: 7px 2px;">
            <div class="input-group">
                <input class="form-control" id="search_tels_input" type="text" placeholder="请输入手机或者邮箱（多用户搜索时请用英文逗号,隔开）">
                                    <span class="input-group-btn">
                                        <button id="do_search_btn" class="btn btn-sm btn-default" type="button">
                                            <i class="ace-icon fa fa-search bigger-110"></i>
                                        </button>
                                    </span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-9">
            <div class="lister">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                    <thead>
                    <tr>
                        <th>用户账号</th>
                        <th>昵称</th>
                    </tr>
                    </thead>
                </table>
                <ul class="left" id="tel_left">
                    <!-- <li>
                         <a href="javascript:void(0);" data-mid="4">rrr  原价:0.00</a>
                     </li>-->
                </ul>
            </div>
            <div class="mid_panel">
                <div class="add_all" id="add_all_user_btn">
                    <a href="javascript:void(0);">全部添加&gt;&gt;</a>
                </div>
                <div class="add" id="add_user_btn">
                    <a href="javascript:void(0);">添&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;加&gt;&gt;</a>
                </div>
                <div class="del_all" id="all_del_user_btn">
                    <a href="javascript:void(0);">&lt;&lt; 全部删除</a>
                </div>
                <div class="del " id="del_user_btn">
                    <a href="javascript:void(0);">&lt;&lt; 删&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;除</a>
                </div>
            </div>
            <div class="lister ">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;">
                    <thead>
                    <tr>
                        <th>用户账号</th>
                        <th>昵称</th>
                    </tr>
                    </thead>
                </table>
                <ul class="right " id="tel_right">

                </ul>
            </div>
        </div>
    </div>
</script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script type="text/javascript">
    //电话去重
    String.prototype.PhoneUnique= function (obj) {
        var _arr=this.split(',');
        var n = {},r=[];
        for(var i = 0; i < _arr.length; i++)
        {
            if (!n[_arr[i]])
            {
                n[_arr[i]] = true;
                r.push(_arr[i]);
            }else{
                layer_required("不能存在重复号码,已经为你去除重复");
            }
        }
        var ids=r.join(",");
        document.getElementById(obj).value=ids;
        return ids;
    }
    //订单去重
    String.prototype.OrderUnique=function (obj) {
        var _arr=this.split(',');
        var n = {},r=[];
        for(var i = 0; i < _arr.length; i++)
        {
            if (!n[_arr[i]])
            {
                n[_arr[i]] = true;
                r.push(_arr[i]);
            }else{
                layer_required("不能存在重复订单号,已经为你去除重复");
            }
        }
        var ids=r.join(",");
        document.getElementById(obj).value=ids;
        return ids;
    }
    //Upon Start

    //点击搜索
    $("body").on("click", "#do_search_btn",function () {
        $("#tel_left").html("");

       var search_tels_input=$("#search_tels_input").val();
        var type_id=$("#orderType").val();
        switch (type_id){
            case "1":
                search_tels_input=search_tels_input.PhoneUnique("search_tels_input");
                if(search_tels_input==""){
                    layer_required("必须输入手机号码或者邮箱地址");
                }else{
                    var search_tels_input_arr = search_tels_input.split(',');

                   var format_type = 1;
                    $.each(search_tels_input_arr,function (n,v) {
                        if(!regPhone.test(v)){
                            if(!regEmail.test(v)){
                                format_type = 0;
                            }
                        }
                    });
                    if(format_type == 0){
                        layer_required("手机号码或者邮箱格式不正确");
                        return false;
                    }
                    $.post("/coupon/getUserByInfo",{tels:search_tels_input}, function (data) {
                        $.each(Array.prototype.slice.call(data), function (i,Value) {
                            if(Value != null){
                                var is_exist_phone=0;
                                $("#tel_left li a").each(function () {
                                    if($(this).data("phone") == Value['phone'])is_exist_phone=1;
                                })
                                if(is_exist_phone==0){
                                if(regMobliePhone.test(Value['phone']) || regEmail.test(Value['phone'])){
                                    $("#tel_left").append('<li><a href="javascript:void(0);" data-mid="'+Value['id']+'" data-phone="'+Value['phone']+'">'+Value['phone']+' | '+(Value['nickname']==""?"没有昵称":Value['nickname'])+'</a> </li>');
                                }else{
                                    layer_required(Value['phone']+'格式错误');
                                }
                                }else{
                                    layer_required("不能重复输入");
                                }
                            }else{
                                var number_arr=search_tels_input.split(",");
                                $("#tel_left").html("");
                                layer_required(number_arr[i]+"在用户体系找不到");
                                return false;
                            }
                        });
                    });
                }
                break;
            case "2":
                    search_tels_input=search_tels_input.OrderUnique("search_tels_input");
                    if(search_tels_input == ""){
                        layer_required("必须输入订单号");
                    }else{
                        if(!/^[a-zA-Z0-9,]*$/.test(search_tels_input)){
                            layer_required("订单格式不正确,请重新输入");
                            return false;
                        }
                        $.post("/coupon/getUserByOrderId",{order_ids:search_tels_input},function (data) {
                            $.each(Array.prototype.slice.call(data), function (i,Value) {
                                if(Value != null){
                                    var is_exist_phone=0;
                                    $("#tel_left li a").each(function () {
                                        if($(this).data("phone") == Value['phone'])is_exist_phone=1;
                                    })
                                    if(is_exist_phone==0){
                                        if(regMobliePhone.test(Value['phone']) || regEmail.test(Value['phone'])){
                                            $("#tel_left").append('<li><a href="javascript:void(0);" data-mid="'+Value['id']+'" data-phone="'+Value['phone']+'">'+Value['order_sn']+' | '+Value['phone']+' | '+(Value['nickname']==""?"没有昵称":Value['nickname'])+'</a> </li>');
                                        }else{
                                            layer_required(Value['phone']+'格式错误');
                                        }
                                    }else{
                                        layer_required("不能重复输入");
                                    }
                                }else{
                                    layer_required("订单号不存在");
                                }
                            });
                        })
                    }
                break;
        }

    });

    //左边点击存入队列池
   $("body").on("click","#tel_left li a", function () {
       var $this=$(this);
       $this.parent().toggleClass("list_on");
   });

    //右边点击排出队列池
    $("body").on("click","#tel_right li a", function () {
        var $this=$(this);
        $this.parent().toggleClass("list_on");
    });

    //添加
    $("body").on("click","#add_user_btn", function () {
        $("#tel_left li.list_on a").each(function () {
            var phone = $(this).data("phone");
            var _m = 1;
            $("#tel_right li a").each(function () {
                if(phone == $(this).data("phone") && _m == 1){
                    _m = 0;
                    layer_required('不能添加重复用户');
                }
            });
            if(_m == 1){
                $("#tel_right").append('<li><a data-mid="'+$(this).data("mid")+'" data-phone="'+$(this).data("phone")+'" >'+$(this).text()+'</a></li>');
                $(this).parent().remove();
            }
        });
    });

    //添加全部
    $("body").on("click","#add_all_user_btn", function () {
        $("#tel_left li a").each(function () {
            var phone = $(this).data("phone");
            var _m = 1;
            $("#tel_right li a").each(function () {
                if(phone == $(this).data("phone") && _m == 1){
                    _m = 0;
                    layer_required('不能添加重复用户');
                }
            });
            if(_m == 1){
                $("#tel_right").append('<li><a data-mid="'+$(this).data("mid")+'" data-phone="'+$(this).data("phone")+'" >'+$(this).text()+'</a></li>');
                $(this).parent().remove();
            }
        });
    });

    //删除
    $("body").on("click","#del_user_btn", function () {
        $("#tel_right li.list_on a").each(function () {
            $("#tel_left").append('<li><a data-mid="'+$(this).data("mid")+'" data-phone="'+$(this).data("phone")+'" >'+$(this).text()+'</a></li>');
            $(this).parent().remove();
        });
    });

    //全部删除
    $("body").on("click","#all_del_user_btn", function () {
        $("#tel_right li a").each(function () {
            $("#tel_left").append('<li><a data-mid="'+$(this).data("mid")+'" data-phone="'+$(this).data("phone")+'" >'+$(this).text()+'</a></li>');
            $(this).parent().remove();
        });
    });

    //change switch
    $("body").on("change","#orderType",function () {
       var typeId=$(this).val();
        switch (typeId){
            case "1":
                    $("#show_tel_area").html($("#getTelByUser").html());
                break;
            case "2":
                    $("#show_tel_area").html($("#getTelByOrderId").html());
                break;
        }
    });
    //Upon End

    //搜索结果放进左下
    $("#search_activities_btn").on("click", function () {
        var sid=$("#sid").val();
        if (!regNumber.test(sid) && sid.length > 0){
            layer_required('编号必须为数字');
            return false;
        }
        var coupon_name=$("#coupon_name").val();
        var coupon_scope=$("#coupon_scope").val();
        $.post("/coupon/getScopeActivities",{sid:sid,coupon_name:coupon_name,coupon_scope:coupon_scope}, function (res) {
            if(res == false){
                layer_required('找不到相应优惠券');
                return false;
            }
            $("#left_act").html("");
            $.each(res, function (n,v) {
                var use_range="";
                switch (v["use_range"]){
                    case "all":
                        use_range="全场";
                        break;
                    case "category":
                        use_range="品类";
                        break;
                    case "brand":
                        use_range="品牌";
                        break;
                    case "single":
                            use_range="单品";
                        break;
                }
                $("#left_act").append('<li><a data-mid="'+v["id"]+'" data-limitNum="'+v["coupon_number"]+'" data-couponsn="'+v["coupon_sn"]+'" data-pname="'+v["coupon_name"]+'" data-userange="'+use_range+'">'+v["coupon_sn"]+'&nbsp;|&nbsp;'+v["coupon_name"]+'&nbsp;|&nbsp;'+v["coupon_number"]+'&nbsp;|&nbsp;'+use_range+'</a></li>');
            });
        });
    });
    $("#limit_buy").change(function () {
       var limit_buy = $("#limit_buy").val();
        if(!regNumber.test(limit_buy) || limit_buy <= 0){
            layer_required("请填写正确的赠送数量");
            $("#limit_buy").val("");
            return ;
        }
    });
    //左下一进一出
    $("body").on("click","#left_act li a", function () {
        $("#left_act li").removeClass("list_on");
        var $this=$(this);
        $this.parent().toggleClass("list_on");
    });

    //右下一进一出
    $("body").on("click","#right_act li a", function () {
        $("#right_act li").removeClass("list_on");
        var $this=$(this);
        $this.parent().toggleClass("list_on");
    });

    //添加
    $("#add_hd").on("click", function () {
        var limit_buy=$("#limit_buy").val();
        if($("#left_act li.list_on a").length == 0){
            layer_required("请选择需要赠送的优惠券");
            return false;
        }
        if(limit_buy!="" && regNumber.test(limit_buy)){
            var tel_right_len=$("#tel_right li a").length;
           if(tel_right_len>0){
                var limitNum=$("#left_act li.list_on a").data("limitnum");
               if(limit_buy>limitNum && limitNum > 0 ){
                   layer_required("赠送数量不能超过优惠券数量");
               }else{
                   var hd_info=$("#left_act li.list_on a");
                   if(hd_info.length>0){
                       var _s = 1;
                       $("#right_act li a").each(function () {
                           if($(this).data("mid") == hd_info.data("mid")){
                               _s = 0;
                               layer_required('不能设置重复优惠券');
                           }
                       });
                       if(_s == 1){
                           $("#right_act").append('<li><a data-mid="'+hd_info.data("mid")+'" data-limitNum="'+hd_info.data("limitnum")+'" data-couponsn="'+hd_info.data("couponsn")+'" data-pname="'+hd_info.data("pname")+'" data-userange="'+hd_info.data("userange")+'" data-lbuy="'+limit_buy+'">'+hd_info.data("couponsn")+'&nbsp;|&nbsp;'+hd_info.data("pname")+'&nbsp;|&nbsp;'+hd_info.data("userange")+'&nbsp;|&nbsp;'+limit_buy+'</a></li>');
                           $("#left_act li.list_on a").parent().remove();
                           $("#limit_buy").val("");
                       }
                   }

               }
           }else{
               layer_required("请先添加手机号码");
           }
        }else{
            layer_required("必须填写赠送数量");
        }
    });

    //删除
    $("#del_hd").on("click", function () {
        if($("#right_act li.list_on a").length == 0){
            layer_required("请选择需要删除的优惠券");
            return false;
        }
        var hd_info=$("#right_act li.list_on a");
        if(hd_info.length>0){
            var _s = 1;
            $("#left_act li a").each(function () {
                if($(this).data("mid") == hd_info.data("mid")){
                    _s = 0;
                    $("#right_act li.list_on a").parent().remove();
                }
            });
            if(_s == 1){
                $("#left_act").append('<li><a data-mid="'+hd_info.data("mid")+'" data-limitNum="'+hd_info.data("limitnum")+'" data-couponsn="'+hd_info.data("couponsn")+'" data-pname="'+hd_info.data("pname")+'" data-userange="'+hd_info.data("userange")+'">'+hd_info.data("couponsn")+'&nbsp;|&nbsp;'+hd_info.data("pname")+'&nbsp;|&nbsp;'+hd_info.data("limitnum")+'&nbsp;|&nbsp;'+hd_info.data("userange")+'</a></li>');
                $("#right_act li.list_on a").parent().remove();
            }
        }
    });
    
    //提交赠送优惠券
    $("#do_submit_deliver_btn").on("click", function () {
        var tel_right_len=$("#tel_right li a").length;
        var act_right_len=$("#right_act li a").length;
        if(tel_right_len < 1){
            layer_required("请先添加手机号码");
        }else{
            if(act_right_len < 1){
                layer_required("请添加要赠送的优惠券信息");
            }else{
                var uids_arr=[];
                $("#tel_right li a").each(function () {
                    var mid=$(this).data("mid");
                    uids_arr.push(mid);
                });
                var user_id=uids_arr.join(",");
                var coupon_info="[";
                $("#right_act li a").each(function (i) {
                    if(i>0)coupon_info+=',';
                    coupon_info+='{"id":"';
                    coupon_info+=$(this).data("mid");
                    coupon_info+='","num":"';
                    coupon_info+=$(this).data("lbuy");
                    coupon_info+='"}'
                });
                coupon_info+=']';
                $.post("/coupon/treatedCouponData",{user_id:user_id,coupon_info:coupon_info}, function (res) {
                    layer_required(res.info);
                    if(res.code == 200){
                        setTimeout(function () {
                            location.reload();
                        },3000);
                    }
                });
            }
        }
    })
</script>
{% endblock %}