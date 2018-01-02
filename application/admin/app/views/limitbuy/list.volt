{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <form action="/limitbuy/list" method="get">
                            <input id="promotion_type" type="hidden" name="promotion_type" value="30" />
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>活动名称：</span>
                                    <input type="text" id="promotion_title" name="promotion_title" class="tools-txt" value="{% if promotionList['voltValue']['promotion_title'] is defined %}{{promotionList['voltValue']['promotion_title']}}{% endif %}"/>
                                </label>

                                <label class="clearfix">
                                    <span>活动状态：</span>
                                    <select id="promotion_status" name="promotion_status" class="tools-txt">
                                        <option value="0">全部</option>
                                        {% if limitEnum['promotionStatus'] is defined %}
                                        {% for k,v in limitEnum['promotionStatus'] %}
                                        <option value="{{k}}" {% if promotionList['voltValue']['promotion_status'] is defined AND promotionList['voltValue']['promotion_status'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>

                                </label>

                                <label class="clearfix">
                                    <span>适用平台：</span>
                                    <select id="promotion_platform" name="promotion_platform" class="tools-txt">
                                        <option value="0">全部</option>
                                        {% if limitEnum['forPlatform'] is defined %}
                                        {% for k,v in limitEnum['forPlatform'] %}
                                        <option value="{{k}}" {% if promotionList['voltValue']['promotion_platform'] is defined AND promotionList['voltValue']['promotion_platform'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>

                                </label>
                                <label class="clearfix">
                                    <span>商品搜索：</span>
                                    <input type="text" id="single_search" name="single_search" class="tools-txt" value="{% if promotionList['voltValue']['single_search'] is defined %}{{promotionList['voltValue']['single_search']}}{% endif %}" placeholder="商品ID/名称" />
                                </label>
                                <label class="clearfix">
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>

                            </div>
                        </form>
                        <div class="row">
                            <div class="col-xs-12">

                                <div class="clearfix">
                                    <div class="pull-right tableTools-container"></div>
                                </div>
                                <div class="table-header">
                                    限购活动列表
                                </div>
                                <!-- div.table-responsive -->
                                <!-- div.dataTables_borderWrap -->
                                <div>
                                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>

                                            <th>活动编号</th>
                                            <th>活动名称</th>
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
                                        <!-- 遍历限购活动信息 -->
                                        {% if promotionList['list'] is defined and promotionList['list'] != 0  %}
                                        {% for k,v in promotionList['list'] %}
                                        <tr>
                                            <td>
                                                {{ v['promotion_number'] }}
                                            </td>
                                            <td>
                                                {{ v['promotion_title'] }}
                                            </td>
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
                                                    <a title="查看" {% if v['promotion_status'] == '未开始' %}style="display:none;"{% endif %} class="blue" href="/limitbuy/edit?promotion_id={{ v['promotion_id'] }}&sign=0">
                                                        <i class="ace-icon fa fa-search-plus bigger-130"></i>
                                                    </a>
                                                    <a title="编辑" {% if v['promotion_status'] != '未开始' %}style="display:none;"{% endif %} class="green" href="/limitbuy/edit?promotion_id={{ v['promotion_id'] }}&sign=1">
                                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                                    </a>
                                                    <a title="取消" {% if v['promotion_status'] == '已取消' OR v['promotion_status'] == '已结束' %}style="display:none;"{% endif %} class="red cancelPromotion" href="javascript:void(0);" promotion_id="{{ v['promotion_id'] }}">
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
                                            <td colspan="8" align="center">暂时无数据...</td>
                                        </tr>
                                        {% endif %}
                                        <!-- 遍历限购活动信息 end -->
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
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/limitBuyList.js"></script>
{% endblock %}