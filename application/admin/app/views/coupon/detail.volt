{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
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
                        <h4>
                            优惠券名称:{{ couponName }}
                            <small>
                                <a href="/coupon/list">返回优惠券列表</a>
                            </small>
                        </h4>
                    </div>

                    <div class="table-header">
                        优惠券领取列表
                    </div>
                    <!-- div.table-responsive -->
                    <!-- div.dataTables_borderWrap -->

                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>id</th>
                                <th>用户id</th>
                                <th>优惠券编号</th>
                                <th>使用优惠券订单</th>
                                <th>使用状态</th>
                                <th>使用时间</th>
                                <th>领取时间</th>
                            </tr>
                            </thead>

                            <tbody>
                            <!-- 遍历促销活动信息 -->
                            {% if couponDetailList['list'] is defined and couponDetailList['list'] != 0  %}
                            {% for k,v in couponDetailList['list'] %}
                            <tr>
                                <td>
                                    {{ v['id'] }}
                                </td>
                                <td>
                                    {{ v['username'] }}
                                </td>
                                <td>{{ v['coupon_sn'] }}</td>
                                <td>{{ v['order_sn'] }}</td>
                                <td >{% if v['is_used'] == 1 %}
                                        已使用
                                        {% elseif v['is_used'] == 2 %}
                                        赠送中
                                    {% elseif v['is_used'] == 3 %}
                                        赠送已被领取
                                    {% else %}
                                        未使用
                                    {% endif %}</td>
                                <td>{% if v["used_time"] > 0%}{{ date('Y-m-d H:i:s',v["used_time"]) }}{% endif %}</td>
                                <td>{{ date('Y-m-d H:i:s',v["add_time"]) }}</td>
                            </tr>
                            {% endfor %}
                            {% endif %}
                            <!-- 遍历促销活动信息 end -->
                            </tbody>
                        </table>
                    </div>
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
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/coupon/couponList.js"></script>
{% endblock %}