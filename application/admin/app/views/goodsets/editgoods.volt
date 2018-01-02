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
            <form class="form-horizontal" role="form" id="goodsets_update" method="post" action="/goodsets/editgoods" enctype="multipart/form-data">
                <div class="page-header">
                    <h1>编辑商品关联组</h1>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label no-padding-left" for="group_name" > 商品组名称 </label>
                    <div class="col-sm-5">
                        <input type="text" id="group_name" name="group_name" placeholder="此处输入商品组名称" class="col-xs-10 col-sm-5" value="{{ edit_info['info']['name'] }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label no-padding-left" > 活动平台</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            <label>
                                <input name="use_platform[]" type="checkbox" class="ace" value="1" {%if edit_info['info']['pc_platform'] == 1 %}checked{% endif %}>
                                <span class="lbl">&nbsp;Pc</span>
                            </label>
                            <label>
                                <input name="use_platform[]" type="checkbox" class="ace" value="2" {%if edit_info['info']['app_platform'] == 1 %}checked{% endif %}>
                                <span class="lbl">&nbsp;App</span>
                            </label>
                            <label>
                                <input name="use_platform[]" type="checkbox" class="ace" value="3" {%if edit_info['info']['wap_platform'] == 1 %}checked{% endif %}>
                                <span class="lbl">&nbsp;Wap</span>
                            </label>
                            <label style="display: none">
                                <input name="use_platform[]" type="checkbox" class="ace" value="4" {%if edit_info['info']['wechat_platform'] == 1 %}checked{% endif %}>
                                <span class="lbl">&nbsp;微商城</span>
                            </label>
                        </div>

                    </div>
                </div>
                <table id="dynamic-table" class="table table-striped table-bordered table-hover" style="margin-bottom: 0;">
                    <thead id="table_head">
                    <tr>
                        <th>关联名称</th>
                        <th>商品编码</th>
                        <th>商品名称</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="JnavList">
                    {% if edit_info['list'] is defined  %}
                    {% for k,v in edit_info['list'] %}
                    <tr class="navCell">
                        <td><input type="text" class="group_name form-control" value="{{ v['name'] }}"/></td>
                        <td><input type="text" class="sku_id form-control" value="{{ v['goods_id'] }}" readonly/></td>
                        <td>{{ v['goods_name'] }}</td>
                        <td>
                            <div class="select-discount" style="padding-top: 0;width: 100%;vertical-align: middle">
                                <a title="删除" href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_del red"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                            </div>
                        </td>
                        <input type="hidden" class="gid" value="{{ v['id'] }}"/>
                    </tr>
                    {% endfor %}
                    {% elseif edit_info['res'] is defined and edit_info['res'] == 'error'  %}
                    <tr>
                        <td colspan="11" align="center">暂时无数据...</td>
                    </tr>
                    {% endif %}
                    </tbody>
                </table>
                <input id="info" name="info" value='' type="hidden">
                <table class="table table-bordered">
                    <tr >
                        <td>
                            <button class="btn btn-info btn-sm" type="button" id="add_new_btn">
                                添加
                            </button>
                        </td>
                    </tr>
                </table>
                <input type="hidden" id="mid" value="{{ mid }}"/>

            </form>
            <button class="btn btn-info" type="button" id="do_ajax_sure_btn">
                <i class="ace-icon fa fa-check bigger-110"></i>
                确认
            </button>
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
        dragEnd: function() {
            var _arr=[];
            $(".gid").each(function () {
                _arr.push($(this).val());
            });
            var arr={};
            $.each(_arr,function (n,v) {
                arr[n]={};
                arr[n]["id"]=v;
                arr[n]["sort"]=_arr.maxnum()-n;
            });
            $.post("/goodsets/modifyeditsort",arr,function (res) {

            })
        },
        dragBetween: false,
        placeHolderTemplate: ""
    });
    $("#add_new_btn").on("click",function () {
        $("#JnavList").append('<tr><td><input type="text" class="group_name form-control"/></td><td><input type="text" class="sku_id form-control"/></td><td></td><td><div class="select-discount" style="padding-top: 0;width: 100%;vertical-align: middle"><a href="javascript:void(0);" title="删除" class="remove_it red"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div></td></tr>');
    });

    $("body").on("click",".remove_it",function () {
        $(this).parent().parent().parent().remove();
    });

    $('body').on("change",".sku_id",function () {
        if(!regNumber.test($(this).val())){
            layer_required("商品编码必须为数字");
            $(this).val("");
        }else{
            var this_ele=$(this);
            var sku_id=$(this).val();
            var _num=[];
            var _s=1;
            $(".sku_id").each(function () {
                if($(this).val() != "" ){
                    if($(this).val() == sku_id){
                        var temp_skuid=$(this).val();
                        if(_num[temp_skuid]){
                            _num[temp_skuid]+=1;
                        }else{
                            _num[temp_skuid]=1;
                        }
                        if(_num[temp_skuid]>1){
                            _s=0;
                            this_ele.val("");
                            this_ele.parent().next().html("");
                        }
                    }
                }
            })
            if(_s==0){
                layer_required("商品编码重复,请重新输入");
                return false;
            }
            $.post("/goodsets/getskuinfobyid",{id:sku_id},function (data) {
                if(data){
                    var goods_name=data["goods_name"];
                    this_ele.parent().next().html(goods_name);
                }else{
                    layer_required("找不到对应商品")
                    this_ele.val("");
                    this_ele.parent().next().html("");
                }
            })
        }
    });

    $("#do_ajax_sure_btn").on("click",function () {
        var group_name=$("input[name='group_name']").val().replace(/(^\s*)|(\s*$)/g, "");
        if(group_name == ""){
            layer_required("商品组名称不能为空");
            return false;
        }
        if($('input[name="use_platform[]"]:checked').length <= 0){
            layer_required("活动平台至少选中一项");
            return false;
        }
       if ($("#JnavList > tr").length < 1) {
           layer_required("请添加关联商品");
           return false;
       }
        var _s1=1;
        var _s2=1;
        var _arr={};
        $(".group_name").each(function (i) {
           if($(this).val()==""){
               _s1=0;
           }else{
               if(_arr[i]){
                   _arr[i]["group_name"]=$(this).val();
               }else{
                   _arr[i]={};
                   _arr[i]["group_name"]=$(this).val();
               }
           }
        });
        if(_s1==0){
            layer_required("关联名称不能为空");
            return false;
        }
        $(".sku_id").each(function (i) {
            if($(this).val()=="") {
                _s2=0;
            }else{
                if(_arr[i]){
                    _arr[i]["sku_id"]=$(this).val();
                }else{
                    _arr[i]={};
                    _arr[i]["sku_id"]=$(this).val();
                }
            }
        });
        if(_s2==0){
            layer_required("商品信息不能为空");
            return false;
        }
        if(_s1==1 && _s2==1){
            var mid=$("#mid").val();
            var obj={};
            obj["info"]=_arr;
            obj["mid"]=mid;
            $("#info").val(JSON.stringify(obj));
            var use_platform_length=$("input[name='use_platform[]']:checked").length;
            if(use_platform_length < 1){
                layer_required("活动平台必须选上");
                return false;
            }
            setTimeout(function () {
                ajaxSubmit('goodsets_update');
            },1);
        }
    });
    $("body").on("click",".do_del",function () {
        var mid=$(this).data("mid");
        layer_confirm('确定要删除吗?','/goodsets/deledit',{id :mid});
    });
</script>
{% endblock %}