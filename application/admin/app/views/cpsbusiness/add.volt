{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        添加业务代表
                    </h1>
                </div><!-- /.page-header -->
                    <div class="">
                        <p>
                            基本信息
                        </p>
                    </div><!-- /.basic -->
                    <form id="form" action="/cpsbusiness/add" method="post">
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 用户名（电话号码）： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="phone" name="phone" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                    <span id="phone_span" style="color:red;"></span>
                                </div>
                            </div>
                            <div class="space-4"></div>
							<div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 省市 </label>

                                <div class="col-sm-9">
                                    <span>地区： </span>
                                         <select name="province" id="province" onchange="get_address(this.value)">
                            				<option value="">请选择</option>
                              					{% for date in address %}
                                   					<option value="{{date['id']}}" {% if channel['province'] is defined and channel['province'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>&nbsp;&nbsp;
                                        <select name="city" id="city">
                            				<option value="">请选择</option>
                              					{% for date in city %}
                                   					<option value="{{date['id']}}" {% if  channel['city'] is defined and channel['city'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>
                            			<span id="area_span" style="color:red;"></span>
                                </div>
                            </div>
                            <div class="space-4"></div>
                           

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 身份证： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="id_card" name="id_card" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                    <span id="id_span" style="color:red;"></span>
                                </div>
                            </div>
							 <div class="space-4"></div>
							 <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 真实姓名： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="real_name" name="real_name" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                    <span id="name_span" style="color:red;"></span>
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addFullMinues" class="btn btn-info" type="button" onclick="sub()">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认添加
                                    </button>
                                </div>
                            </div>
                            </div>
                    </div>
                    </div><!-- /.main-content -->
                    </form>
            </div>
            </div>
        </div>
</div><!-- /.main-container -->
<script type="text/javascript">
 function get_address(id) {
    $("select[name='city']").html('<option>--请选择--</option>');
        $.ajax({
            url: "/cpsbusiness/add",
            data: { "province": id,"stu":1},
            type: "get",
            dataType : "html",
            success: function (data) {
                $("select[name='city']").html(data);
            }
        });
    }
    function check(){
        var phone = $("#phone").val();
        var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
        if (!reg.test(phone)) {
            $("#phone_span").html(" * 手机号不正确");
            return false;
        }else{
            $("#phone_span").html("");
        }

        var province = $("#province").val();
        var city = $("#city").val();
        var real_name = $("#real_name").val();
        var id_card = $("#id_card").val();

        if(!province || (!city || city<1)){
             $("#area_span").html(" * 省市不能为空");
             return false;
         }else{
             $("#area_span").html("");
         }
         if(!id_card){
             $("#id_span").html(" * 身份证不能为空");
             return false;
         }else{
             $("#id_span").html("");
         }
         if(!real_name){
              $("#name_span").html(" * 真实姓名不能为空");
              return false;
          }else{
              $("#name_span").html("");
          }

        return true;
    }

    function sub(){
        if(check()==true){
            $("#form").submit();
        }
    }
</script> 
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/goodspricetag/globalGoodsPriceTag.js"></script>
{% endblock %}



