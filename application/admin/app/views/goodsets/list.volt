{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<!--<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />-->
<style>
    .select-discount a{display: inline-block;margin-bottom:0;float: left;margin-right: 10px;}
</style>
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form">
                <div class="page-header">
                    <h1>商品关联组列表</h1>
                </div>
                <!--搜索_开始-->
                <form action="/goodsets/list" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <span>商品组名称：</span>
                        <span>
                            <input type="text" name="goods_info" class="tools-txt" value="{% if goods_info is defined %}{{ goods_info }}{% endif %}" placeholder="输入商品组名称/ID">
                        </span>
                            <span>适用平台：</span>
                        <span>
                            <select name="platform_status">
                                <option value="0" {% if platform is defined and platform == 0 %}selected{%endif%}>全部</option>
                                <option value="1" {% if platform is defined and platform == 1 %}selected{%endif%}>PC</option>
                                <option value="2" {% if platform is defined and platform == 2 %}selected{%endif%}>APP</option>
                                <option value="3" {% if platform is defined and platform == 3 %}selected{%endif%}>WAP</option>
                                <!--<option value="4" {% if platform is defined and platform == 4 %}selected{%endif%}>微商城</option>-->
                            </select>
                        </span>
                            <button class="btn btn-primary" type="submit">搜索</button>

                        </label>
                        <label class="clearfix" style="float:right;height:30px;">
                            <button class="btn btn-primary btn-sm" type="button" id="add_new_btn">
                                添加关联商品组
                            </button>
                        </label>
                    </div>
                </form>
                <div style="float: right;padding: 0 0 10px 0;">

                </div>
                <!--搜索_结束-->
                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead id="table_head">
                    <tr>
                        <th>ID</th>
                        <th>商品组名称</th>
                        <th>相关商品数</th>
                        <th>适用平台</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="JnavList">
                    {% if GoodSetsList['list'] is defined and GoodSetsList['list'] != 0  %}
                    {% for k,v in GoodSetsList['list'] %}
                    <tr class="navCell">
                        <td>{{ v['id'] }}</td>
                        <td>{{ v['name'] }}</td>
                        <td>{{ v['count'] }}</td>
                        <td>{{ v['platform_value'] }}</td>
                        <td>
                            <div class="select-discount" style="padding-top: 0;width: 100%;vertical-align: middle">
                                <a title="编辑" href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_edit green"><i class="ace-icon fa fa-pencil
bigger-130"></i></a>
                                <a href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_del red" title="删除"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                            </div>
                        </td>
                    </tr>
                    {% endfor %}
                    {% elseif GoodSetsList['res'] is defined and GoodSetsList['res'] == 'error'  %}
                    <tr>
                        <td colspan="11" align="center">暂时无数据...</td>
                    </tr>
                    {% endif %}
                    </tbody>
                </table>
            </form>
            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</div>

{% if GoodSetsList['page'] is defined and GoodSetsList['list'] != 0 %}
{{ GoodSetsList['page'] }}
{% endif %}
{% endblock %}
{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.dragsort-0.5.2.js"></script>
<script type="text/javascript">
    Array.prototype.maxnum = function () {

        for (var i = 0, maxValue = Number.MIN_VALUE; i < this.length; i++)parseInt(this[i]) > maxValue && (maxValue = this[i]);

        return maxValue;

    };
    $("#JnavList").dragsort({
        dragSelector: ".navCell",
        dragSelectorExclude: '.do_edit, .do_del',
        dragEnd: function() {
            var _arr=[];
            $("#JnavList tr").find('td:eq(0)').each(function () {
                _arr.push($(this).html());
            });
            var arr={};
            $.each(_arr,function (n,v) {
                arr[n]={};
                arr[n]["id"]=v;
                arr[n]["sort"]=_arr.maxnum()-n;
            });
            $.post("/goodsets/modifysort",arr,function (res) {

            })
        },
        dragBetween: false,
        placeHolderTemplate: ""
    });
    $("#add_new_btn").on("click",function () {
        location.href='/goodsets/add';
        return false;
        $("#table_head").append('<tr class="countCell navCell"><th></th><th><input type="text" class="multi_name form-control"/></th><th><div class="select-discount" style="padding-top: 0;width: 100%;vertical-align: middle"><a href="javascript:void(0);" class="remove_it">删除</a></div></th></tr>');
    });
    
    $("body").on("click",".remove_it",function () {
        $(this).parent().parent().parent().remove();
    });

    $("#update_btn").on("click",function () {
        var _s=1;
        var _s_q=1;
       if($(".countCell").length<1){
           layer_required("获取不到需要添加的数据");
       }else{
           var tmp_arr=[];
           $(".multi_name").each(function () {
              if($(this).val()=="")_s=0;
               var str=$(this).val();
               if(str.indexOf(',')!=-1)_s_q=0;
               tmp_arr.push($(this).val());
           });
           if(_s==0){
               layer_required("请添加商品组名称,不能留空");
           }else{
               if(_s_q==0){
                   layer_required("商品组名称不能存在逗号");
               }else{
                   var _m=tmp_arr.join(",");
                   $.post("/goodsets/addgoodssets",{names:_m},function (res) {
                       location.reload();
                   })
               }
           }
       }
    })

    $(".do_edit").on("click",function () {
        var mid=$(this).data("mid");
        location.href="/goodsets/editgoods?id="+mid;
    });
    $("body").on("click",".do_del",function () {
        var request = location.search;
        var mid=$(this).data("mid");
        layer_confirm('确定要删除吗?','/goodsets/del',{id :mid,request :request});
    });
</script>
{% endblock %}