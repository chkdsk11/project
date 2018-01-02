{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form action="/coupon/list" method="get">
                <div class="tools-box">
                    <label class="clearfix">
                        <span>优惠券编号：</span>
                        <input type="text" id="coupon_sn" name="coupon_sn" class="tools-txt" value="{% if couponList['voltValue']['coupon_sn'] is defined %}{{couponList['voltValue']['coupon_sn']}}{% endif %}"/>

                    </label>
                    <label class="clearfix">
                        <span>优惠券名称：</span>
                        <input type="text" id="coupon_name" name="coupon_name" class="tools-txt" value="{% if couponList['voltValue']['coupon_name'] is defined %}{{couponList['voltValue']['coupon_name']}}{% endif %}"/>
                    </label>


                </div>
                <div class="tools-box">
                    <label class="clearfix">
                        <span>优惠券类型：</span>
                    <select id="coupon_type" name="coupon_type">
                            <option value="0">全部</option>
                            {% if couponEnum['offerType'] is defined %}
                            {% for k,v in couponEnum['offerType'] %}
                            <option value="{{k}}" {% if couponList['voltValue']['coupon_type'] is defined AND couponList['voltValue']['coupon_type'] == k %}selected{% endif %}>{{v}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    </label>
                    <label class="clearfix">
                        <span>活动状态：</span>
                        <select id="coupon_status" name="coupon_status">
                            <option value="0">全部</option>
                            {% if couponEnum['couponStatus'] is defined %}
                            {% for k,v in couponEnum['couponStatus'] %}
                            <option value="{{k}}" {% if couponList['voltValue']['coupon_status'] is defined AND couponList['voltValue']['coupon_status'] == k %}selected{% endif %}>{{v}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>

                    </label>
                    <label class="clearfix">
                        <span>适用平台：</span>
                        <select id="coupon_platform" name="coupon_platform">
                            <option value="0">全部</option>
                            {% if couponEnum['forPlatform'] is defined %}
                            {% for k,v in couponEnum['forPlatform'] %}
                            <option value="{{k}}" {% if couponList['voltValue']['coupon_platform'] is defined AND couponList['voltValue']['coupon_platform'] == k %}selected{% endif %} >{{v}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>

                    </label>
                    <label class="clearfix">
                        <span>使用范围：</span>
                        <select id="coupon_scope" name="coupon_scope" >
                            <option value="0">全部</option>
                            {% if couponEnum['forScope'] is defined %}
                            {% for k,v in couponEnum['forScope'] %}
                            <option value="{{k}}" {% if couponList['voltValue']['coupon_scope'] is defined AND couponList['voltValue']['coupon_scope'] == k %}selected{% endif %}>{{v}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    </label>
                    <label class="clearfix" style="height: 20px;">
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>
                    <label class="clearfix" style="float:right;height:30px;">
                        <a href="/coupon/add"><button class="btn btn-primary btn-sm" type="button" id="add_new_btn">
                            添加优惠券
                        </button>
                        </a>
                    </label>
                </div>
            </form>
            <div class="row">
                <div class="col-xs-12">

                    <div class="clearfix">
                        <div class="pull-right tableTools-container"></div>
                    </div>
                    <div class="table-header">
                        优惠券列表
                    </div>
                    <!-- div.table-responsive -->
                    <!-- div.dataTables_borderWrap -->
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>优惠券编号</th>
                                <th>优惠券名称</th>
                                <th>优惠券类型</th>
                                <th>活动平台</th>
                                <th>使用范围</th>
                                <th>发放时间</th>
                                <th>有效期</th>
                                <th class="hidden-480">活动状态</th>
                                <th>已领/发行量</th>
                                <th>注册发放</th>
                                <th>操作</th>
                            </tr>
                            </thead>

                            <tbody>
                            <!-- 遍历促销活动信息 -->
                            {% if couponList['list'] is defined and couponList['list'] != 0  %}
                            {% for k,v in couponList['list'] %}
                            <tr>
                                <td>
                                    {{ v['coupon_sn'] }}
                                </td>
                                <td>
                                    {{ v['coupon_name'] }}
                                </td>
                                <td>{{ v['coupon_type'] }}</td>
                                <td>{{ v['coupon_platform'] }}</td>
                                <td class="hidden-480">{{ v['use_range'] }}</td>
                                <td>{{ v['start_provide_time'] }}<br/>~<br/>{{ v['end_provide_time'] }}</td>
                                <td>{% if v['validitytype'] == 1 %}{{ date("Y-m-d H:i:s",v['start_use_time']) }}<br/>~<br/>{{ date("Y-m-d H:i:s",v['end_use_time']) }}{% else %}领取起{{  v['relative_validity'] }}天{% endif %}</td>
                                <td class="{% if v['coupon_status'] == '已结束' %}grey{% elseif v['coupon_status'] == '进行中' %}green{% elseif v['coupon_status'] == '已取消' %}red{% endif %}">{{ v['coupon_status'] }}</td>
                                <td>
                                    {{ v['bring_number'] }}/{% if v['coupon_number'] == 0 %}无限制{% else %}{{ v['coupon_number'] }}{% endif %}
                                </td>
                                <td >
                                    <div class="col-sm-5">
                                        <div class="checkbox">
                                            <label>
                                                <input class="register_send" type="checkbox" class="ace" data-mid='{{ v["id"] }}' value='{{ v["register_bonus"] }}' {% if v["register_bonus"] == 1%}checked{% endif %} disabled>
                                            </label>
                                        </div>

                                    </div>
                                </td>
                                <td>
                                    <div class="hidden-sm hidden-xs action-buttons">

                                        <a title="领取详情" class="blue" href="/coupon/detail/{{ v['id'] }}" >

                                        <i class="ace-icon fa fa-search-plus bigger-130"></i>
                                        </a>
                                        <a title="查看" class="green" href="/coupon/edit?id={{ v['id'] }}&isshow=0">
                                            <i class="ace-icon fa fa-bars bigger-130"></i>
                                        </a>
                                        {% if v['coupon_status'] == '进行中' %}
                                         <a title="激活码" {% if v['is_activecode'] == 1 and v['provide_type'] > 1 %} {% else %} style="display:none;" {% endif %}  href="/coupon/actcode/{{ v['id'] }}">
                                        <i class="ace-icon fa fa-send bigger-130"></i>
                                        </a>
                                        {% endif %}
                                        <a title="编辑" {% if v['coupon_status'] != '未开始' %}style="display:none;"{% endif %} class="green" href="/coupon/edit?id={{ v['id'] }}&isshow=1">
                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                        </a>
                                        <a title="取消"  class="red cancelCoupon" href="javascript:void(0);" data-mid="{{ v['id'] }}">
                                        <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                        </a>

                                    </div>

                                    <div class="hidden-md hidden-lg">
                                        <div class="inline pos-rel">
                                            <button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto">
                                                <i class="ace-icon fa fa-caret-down icon-only bigger-120"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close">
                                                <li>
                                                    <a href="#" class="tooltip-info" data-rel="tooltip" title="View">
																				<span class="blue">
																					<i class="ace-icon fa fa-search-plus bigger-120"></i>
																				</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="tooltip-success" data-rel="tooltip" title="Edit">
																				<span class="green">
																					<i class="ace-icon fa fa-pencil-square-o bigger-120"></i>
																				</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" class="tooltip-error" data-rel="tooltip" title="Delete">
																				<span class="red">
																					<i class="ace-icon fa fa-trash-o bigger-120"></i>
																				</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            {% endfor %}
                            {% elseif couponList['res'] is defined and couponList['res'] == 'error'  %}
                            <tr>
                                <td colspan="11" align="center">暂时无数据...</td>
                            </tr>
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
{% if couponList['page'] is defined and couponList['list'] != 0 %}
{{ couponList['page'] }}
{% endif %}
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/coupon/couponList.js"></script>
{% endblock %}