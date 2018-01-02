{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <form action="/promotion/list" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>活动编号：</span>
                                    <input type="text" id="promotion_code" name="promotion_code" class="tools-txt" value="{% if promotionList['voltValue']['promotion_code'] is defined %}{{promotionList['voltValue']['promotion_code']}}{% endif %}"/>

                                </label>
                                <label class="clearfix">
                                    <span>活动名称：</span>
                                    <input type="text" id="promotion_title" name="promotion_title" class="tools-txt" value="{% if promotionList['voltValue']['promotion_title'] is defined %}{{promotionList['voltValue']['promotion_title']}}{% endif %}"/>
                                </label>
                                <label class="clearfix">
                                    <span>商品搜索：</span>
                                    <input type="text" id="single_search" name="single_search" class="tools-txt" value="{% if promotionList['voltValue']['single_search'] is defined %}{{promotionList['voltValue']['single_search']}}{% endif %}" placeholder="商品ID/名称"/>
                                </label>
                                <!--<label class="clearfix">
                                    <span>活动时间：</span>
                                    <input type="text" id="start_time" name="promotion_start_time" class="tools-txt" />
                                    <span>-</span>
                                    <input type="text" id="end_time" name="promotion_end_time" class="tools-txt" />

                                </label>-->

                            </div>
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>优惠类型：</span>
                                    <select id="promotion_type" name="promotion_type">
                                        <option value="0">全部</option>
                                        {% if promotionEnum['offerType'] is defined %}
                                        {% for k,v in promotionEnum['offerType'] %}
                                        <option value="{{k}}" {% if promotionList['voltValue']['promotion_type'] is defined AND promotionList['voltValue']['promotion_type'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </label>
                                <label class="clearfix">
                                    <span>活动状态：</span>
                                    <select id="promotion_status" name="promotion_status">
                                        <option value="0">全部</option>
                                        {% if promotionEnum['promotionStatus'] is defined %}
                                        {% for k,v in promotionEnum['promotionStatus'] %}
                                        <option value="{{k}}" {% if promotionList['voltValue']['promotion_status'] is defined AND promotionList['voltValue']['promotion_status'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>

                                </label>
                                <label class="clearfix">
                                    <span>适用平台：</span>
                                    <select id="promotion_platform" name="promotion_platform">
                                        <option value="0">全部</option>
                                        {% if promotionEnum['forPlatform'] is defined %}
                                        {% for k,v in promotionEnum['forPlatform'] %}
                                        <option value="{{k}}" {% if promotionList['voltValue']['promotion_platform'] is defined AND promotionList['voltValue']['promotion_platform'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>

                                </label>
                                <label class="clearfix">
                                    <span>使用范围：</span>
                                    <select id="promotion_scope" name="promotion_scope" >
                                        <option value="0">全部</option>
                                        {% if promotionEnum['forScope'] is defined %}
                                        {% for k,v in promotionEnum['forScope'] %}
                                        <option value="{{k}}" {% if promotionList['voltValue']['promotion_scope'] is defined AND promotionList['voltValue']['promotion_scope'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </label>
                                <label class="clearfix" style="height: 20px;">
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>
                                <label class="clearfix" style="float:right;height:30px;">
                                    <a href="/promotion/add"><button class="btn btn-primary btn-sm" type="button" id="add_new_btn">
                                        添加促销活动
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
                                    促销活动列表
                                </div>
                                <!-- div.table-responsive -->
                                <!-- div.dataTables_borderWrap -->
                                <div>
                                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <!--<th class="center">
                                                <label class="pos-rel">
                                                    <input type="checkbox" class="ace" />
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>-->
                                            <th>活动编号</th>
                                            <th>活动名称</th>
                                            <th>优惠类型</th>
                                            <th>活动平台</th>
                                            <th>使用范围</th>

                                            <th>
                                                <i class="ace-icon fa fa-clock-o bigger-110 hidden-480"></i>
                                                开始时间
                                            </th>
                                            <th>
                                                <i class="ace-icon fa fa-clock-o bigger-110 hidden-480"></i>
                                                结束时间
                                            </th>
                                            <th class="hidden-480">活动状态</th>

                                            <th>操作</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <!-- 遍历促销活动信息 -->
                                        {% if promotionList['list'] is defined and promotionList['list'] != 0  %}
                                        {% for k,v in promotionList['list'] %}
                                        <tr>
                                            <!--<td class="center">
                                                <label class="pos-rel">
                                                    <input type="checkbox" class="ace" />
                                                    <span class="lbl"></span>
                                                </label>
                                            </td>-->
                                            <td>
                                                {{ v['promotion_code'] }}
                                            </td>
                                            <td>
                                                {{ v['promotion_title'] }}
                                            </td>
                                            <td>{{ v['promotion_type'] }}</td>
                                            <td>{{ v['promotion_platform'] }}</td>
                                            <td class="hidden-480">{{ v['promotion_scope'] }}</td>
                                            <td>{{ v['promotion_start_time'] }}</td>
                                            <td>{{ v['promotion_end_time'] }}</td>
                                            <td class="{% if v['promotion_status'] == '已结束' %}grey{% elseif v['promotion_status'] == '进行中' %}green{% elseif v['promotion_status'] == '已取消' %}red{% endif %}">
                                                {{ v['promotion_status'] }}
                                                <!--<span class="label label-sm label-warning"></span>-->
                                            </td>
                                            <td>
                                                <div class="hidden-sm hidden-xs action-buttons">
                                                    <a title="查看" {% if v['promotion_status'] == '未开始' %}style="display:none;"{% endif %} class="blue" href="/promotion/edit?promotion_id={{ v['promotion_id'] }}&sign=0">
                                                        <i class="ace-icon fa fa-search-plus bigger-130"></i>
                                                    </a>
                                                    <a title="编辑" {% if v['promotion_status'] != '未开始' %}style="display:none;"{% endif %} class="green" href="/promotion/edit?promotion_id={{ v['promotion_id'] }}&sign=1">
                                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                                    </a>
                                                    <a title="取消" {% if v['promotion_status'] == '已取消' OR v['promotion_status'] == '已结束' %}style="display:none;"{% endif %} class="red cancelPromotion" href="javascript:void(0);" promotion_id="{{ v['promotion_id'] }} promotion_type="{{ v['en_promotion_type'] }}">
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
                                        {% elseif promotionList['res'] is defined and promotionList['res'] == 'error'  %}
                                        <tr>
                                            <td colspan="9" align="center">暂时无数据...</td>
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
            {% if promotionList['page'] is defined and promotionList['list'] != 0 %}
            {{ promotionList['page'] }}
            {% endif %}
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotionList.js"></script>
{% endblock %}