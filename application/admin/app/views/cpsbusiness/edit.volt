{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        修改业务代表
                    </h1>
                </div><!-- /.page-header -->
                    <div class="">
                        <p>
                            基本信息
                        </p>
                    </div><!-- /.basic -->
                    <form id="form" action="/cpsbusiness/edit?edit={{edit}}" method="post">
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 用户名（电话号码）： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="phone" name="phone" class="col-xs-10 col-sm-5" value = "{% if data['phone'] is defined %}{{data['phone']}}{%endif%}"/>
                                </div>
                            </div>
                            <div class="space-4"></div>
							<div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 省市 </label>

                                <div class="col-sm-9">
                                    <span>地区： </span>
                                         <select name="province" id="province" onchange="get_address(this.value)">
                            				<option value="">请选择</option>
                              					{% for v in red %}
                                   					<option value="{{v['id']}}" {% if data['region_id'] is defined and data['region_id'] == v['id'] %} selected="selected" {% endif %}  > {{v['region_name']}} </option>
                              					{% endfor %} 
                            			</select>&nbsp;&nbsp;
                                        <select name="city" id="city">
                            				<option value="">请选择</option>
                              					{% for date in city %}
                                   					<option value="{{date['id']}}" {% if  data['city_id'] is defined and data['city_id'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>
                                </div>
                            </div>
                            <div class="space-4"></div>
                           

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 身份证： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="id_card" name="id_card" class="col-xs-10 col-sm-5" value = "{% if data['id_card'] is defined %}{{data['id_card']}}{%endif%}" />
                                </div>
                            </div>
							 <div class="space-4"></div>
							 <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 真实姓名： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="real_name" name="real_name" class="col-xs-10 col-sm-5" value = "{% if data['real_name'] is defined %}{{data['real_name']}}{%endif%}" />
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addFullMinues" class="btn btn-info" type="submit">
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
</script> 
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/goodspricetag/globalGoodsPriceTag.js"></script>
{% endblock %}



