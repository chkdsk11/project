{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <iframe id="export_ifm" name='export_ifm' style="display:none"></iframe>
            <form id="actcode" target="export_ifm" class="form-horizontal" role="form" method="post" action="/coupon/exportactcode" enctype="multipart/form-data" >
                <div class="page-header">
                    <h1>激活码</h1>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"  > 优惠券名称 </label>
                    <div class="col-sm-5" style="margin-left:5px;line-height:33px;font-size:16px;">
                       {{ info["coupon_name"] }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"  > 发放张数 </label>
                    <div class="col-sm-5" style="margin-left:5px;line-height:33px;font-size:16px;">
                        {{ info["coupon_number"] }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"  > 可生成{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}数 </label>
                    <div class="col-sm-5" style="margin-left:5px;line-height:33px;font-size:16px;">
                        {% if info['provide_type'] == 2%}
                            {% if 1  - info["code_sn_list_count"] < 0 %}
                                    0
                                {% else %}
                                    {{ 1  - info["code_sn_list_count"] }}
                            {% endif %}
                        {% else %}
                        {% if info["coupon_number"] - info["bring_number"] - info["code_sn_list_count"] < 0 %}
                            0
                            {% else %}
                                {{ info["coupon_number"] - info["bring_number"] - info["code_sn_list_count"] }}
                            {% endif %}
                        {% endif %}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"  > {% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}库（总数&nbsp;{{ info["code_sn_list_count"]}}&nbsp;） </label>
                    <div class="col-sm-5" >
                        <div class="tags" style="width: auto;height:400px;overflow:auto;">
                        {% if info["code_sn_list"] is defined %}
                        {% for k,v in info["code_sn_list"] %}
                            <span class="tag" >{{ v }}</span>
                        {% endfor %}
                        {% endif %}
                        </div>
                        <a href="javascript:void(0);" id="tags_more_btn" style="display: none;">
                            <i class="ace-icon fa fa-plus-circle bigger-110 btn-info"></i>
                            更多...
                        </a>


                    </div>
                    <button class="btn btn-info" type="button" id="do_export">
                        <i class="ace-icon fa fa-check bigger-110"></i>
                        导出
                    </button>
                </div>
                <input type="hidden" id="actcodelist" name="actcodelist">
                <input type="hidden" id="coupon_sn" name="coupon_sn" value="{% if info['coupon_sn'] is defined  %}{{ info['coupon_sn'] }}{% endif %}">
                {% if info['provide_type'] != 2%}
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"  > 生成{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %} </label>
                    <div class="col-sm-5">
                        <input type="number" id="create_code" placeholder="输入要生成的{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}数量" data-lnum="{% if info['provide_type'] == 2 %}{{ 1  - info["code_sn_list_count"] }}{% else %}{{ info["coupon_number"] - info["bring_number"] - info["code_sn_list_count"]  }}{% endif %}" class="col-xs-10 col-sm-5" min="0" data-mid="{{ info["id"] }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"  >  </label>
                    <div class="col-sm-5">
                        <button class="btn btn-info" type="button" id="do_create">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            生成
                        </button>
                    </div>
                </div>
                {% endif %}
            </form>

            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</div>
{% endblock %}
{% block footer %}
<script type="text/javascript">
    var tags_ajax_page = 1;
    var sid = {{ sid }};

    $('.tags').scroll(function () {
        var $that = $(this);
        if($(this).scrollTop() >= ( $that[0].scrollHeight - $that.outerHeight() )){
            layer_required('正在加载{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}');
            var current_id = tags_ajax_page * 100;
            $.post('/coupon/actcode/' + sid,{current_id:current_id},function (data) {
                if(typeof data == 'object'){
                    $.each(data,function (n,v) {
                        $('.tags').append('<span class="tag" >'+v+'</span>');
                    });
                    tags_ajax_page += 1;
                }else{
                    layer_required('没有更多{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}');
                }
            });
        }
    });
$("#tags_more_btn").on('click',function () {
    var current_id = tags_ajax_page * 100;
    $.post('/coupon/actcode/' + sid,{current_id:current_id},function (data) {
        if(typeof data == 'object'){
            $.each(data,function (n,v) {
                $('.tags').append('<span class="tag" >'+v+'</span>');
            });
            tags_ajax_page += 1;
        }else{
            layer_required('没有更多{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}');
        }
    });
});
$("#create_code").on("change",function () {
    if($(this).val() > 30000){
        layer_required("一次生成{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}不能超过30000");
        $(this).val("");
        return false;
    }
    if($(this).val() > $(this).data("lnum")){
        layer_required("超过{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}剩余张数限制");
        $(this).val("");
    }
});
$("#create_code").on("keyup",function () {
    if($(this).val()>$(this).data("lnum")){
        layer_required("超过{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}剩余张数限制");
        $(this).val("");
    }
    if(!regNumber.test($(this).val())){
        layer_required("必须填写正整数");
        $(this).val("");
        return false;
    }
});
var codeSubmit = false;
$("#do_create").on("click", function () {
    var create_code=$("#create_code");
    if(create_code.val().length < 1 || !regNumber.test(create_code.val())){
        layer_required("请输入正确的{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}");
        create_code.val("");
        return false;
    }
    if(codeSubmit){
        return false;
    }
    if(parseInt(create_code.val()) > parseInt(create_code.data("lnum"))){
        layer_required("超过{% if info['provide_type'] == 2%}统一码{% else %}激活码{% endif %}剩余张数限制");
        create_code.val("");
    }else{
        var mid=create_code.data("mid");
        var num=create_code.val();
        codeSubmit = true;
        $.post("/coupon/addactcode",{id:mid,num:num}, function (data) {
            if(data.code=="200"){
               layer_required(data.info);
                $("#create_code").val('');
                codeSubmit = false;
                location.reload();
            }else{
                layer_required(data.info);
                codeSubmit = false;
            }
        });
    }
});
    $("#do_export").on("click", function () {
        var arr=[];
        $(".tag").each(function () {
            arr.push($(this).html());
        });
        $("#actcodelist").val(arr.join(","));
        $("#actcode").submit();
    })
</script>

{% endblock %}