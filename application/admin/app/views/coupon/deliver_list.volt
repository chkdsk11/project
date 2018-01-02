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
                        <h1>优惠券赠送</h1>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-7">
                            <label style="line-height:34px;padding:0 10px;"><a style="text-decoration: none;" href="/coupon/deliver/add">赠券列表</a></label> <label style="line-height:34px;padding:0 10px;border-bottom: 2px solid #2679b5;"><a style="text-decoration: none;" href="javascript:void(0);">已赠券列表</a></label>
                        </div>
                    </div>
                    <div class="col-sm-12">
                    <form class="form-horizontal" role="form" method="get" enctype="multipart/form-data">

                        <div class="tools-box">
                            <label class="clearfix">
                                <span>手机号：</span>
                                <input type="text" id="phone" name="phone" class="tools-txt" value="{% if DeliverList['voltValue']['phone'] is defined %}{{DeliverList['voltValue']['phone']}}{% endif %}"/>
                                <span>用户名：</span>
                                <input type="text" id="user" name="user" class="tools-txt" value="{% if DeliverList['voltValue']['user'] is defined %}{{DeliverList['voltValue']['user']}}{% endif %}"/>
                                <span>活动名称：</span>
                                <input type="text" id="coupon_name" name="coupon_name" class="tools-txt" value="{% if DeliverList['voltValue']['coupon_name'] is defined %}{{DeliverList['voltValue']['coupon_name']}}{% endif %}"/>
                                <span>范围：</span>
                                <select id="coupon_scope" name="coupon_scope" >
                                    <option value="0">全部</option>
                                    {% if couponEnum['forScope'] is defined %}
                                        {% for k,v in couponEnum['forScope'] %}
                                            <option value="{{k}}" {% if DeliverList['voltValue']['coupon_scope'] is defined AND DeliverList['voltValue']['coupon_scope'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                    {% endif %}
                                </select>
                            </label>

                        </div>

                        <div class="tools-box">

                            <label class="clearfix">
                                <span>激活码：</span>
                                <input type="text" id="code_sn" name="code_sn" class="tools-txt" value="{% if DeliverList['voltValue']['code_sn'] is defined %}{{DeliverList['voltValue']['code_sn']}}{% endif %}"/>
                            </label>
                            <label>
                                <span>赠送时间：</span>
                                <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if DeliverList['voltValue']['end_time'] is defined %}{{ DeliverList['voltValue']['start_time'] }}{%endif%}" readonly="">
                                <span>-</span>
                                <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if DeliverList['voltValue']['end_time'] is defined %}{{ DeliverList['voltValue']['end_time'] }}{%endif%}" readonly="">
                            </label>
                            <label class="clearfix" >
                                <button class="btn btn-primary" id='search_dt' type="submit">搜索</button>
                            </label>
                        </div>

                    </form>
                    </div>
                    <form class="form-horizontal" role="form" id="insert_deliver" method="post" action="/coupon/deliver/add" enctype="multipart/form-data" >

                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>赠送时间</th>
                                    <th>手机号</th>
                                    <th>用户名</th>
                                    <th>已赠优惠券范围</th>
                                    <th>活动名称</th>
                                    <th>激活码</th>
                                </tr>
                                </thead>
                                <tbody>
                                {% if DeliverList['list'] is defined and DeliverList['list'] != 0  %}
                                    {% for k,v in DeliverList['list'] %}
                                    <tr>
                                        <td>{{ date('Y-m-d H:i:s',v["add_time"])  }}</td>
                                        <td>{{ v["phone"] }}</td>
                                        <td>{{ v["username"] }}</td>
                                        <td>{{ v["use_range"] }}</td>
                                        <td>{{ v["coupon_name"] }}</td>
                                        <td>{{ v["code_sn"] }}</td>
                                    </tr>
                                    {% endfor %}
                                {% elseif DeliverList['res'] is defined and DeliverList['res'] == 'error'  %}
                                <tr>
                                    <td colspan="4" align="center">暂时无数据...</td>
                                </tr>

                                {% endif %}
                                </tbody>
                              </table>
                        </div>


                    </form>

                    <!-- div.table-responsive -->
                    <!-- div.dataTables_borderWrap -->

                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.page-content -->
{% if DeliverList['page'] is defined and DeliverList['list'] != 0 %}
{{ DeliverList['page'] }}
{% endif %}
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepk').datetimepicker({
            step: 10,
            allowBlank:true
        });
        setInterval(function(){
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        if(start_time.length > 0 && end_time.length > 0){
            var new_start_time = (new Date(start_time)).getTime()/1000;
            var new_end_time = (new Date(end_time)).getTime()/1000;
                if(new_end_time < new_start_time){
                layer_required('开始时间不能晚于结束时间');
                $('#end_time').val('');
                 }
            }
        },100);
    });
    
</script>
{% endblock %}