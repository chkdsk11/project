{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="/grouporder/orderList">拼团订单管理</a></li>
<li class="active">订单列表</li>
	<style>
		#export_div ul{
			width:100%;
			float: left;
			list-style: none;
		}
		#export_div ul li{
			float: left;
			width:100px;
		}
	</style>
{% endblock %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/order/order_list.css" class="ace-main-stylesheet" />
<style>
	.pop-frame-content table th{
		height: 0px;
	}
</style>
<div class="page-content">
	<div class="row">
		<div class="col-xs-12 ">
			<form action="/grouporder/orderlist" method="get" class="form-horizontal" role="form" id="my_form">
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-1">订单编号：</label>
					<div class="col-sm-2">
						<input type="text" name="order_sn" value="{% if seaData['order_sn'] is defined %}{{ seaData['order_sn'] }}{% endif %}" id="form-1" class="col-xs-12">
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-2">下单时间：</label>
					<div class="col-sm-2">
						<input type="text" id="startTime" name="startTime" value="{% if seaData['startTime'] is defined %}{{ seaData['startTime'] }}{% endif %}" id="form-2" class="col-xs-12">
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-3">至：</label>
					<div class="col-sm-2">
						<input type="text" id="endTime" name="endTime" value="{% if seaData['endTime'] is defined %}{{ seaData['endTime'] }}{% endif %}" id="form-3" class="col-xs-12">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-4">活动名称：</label>
					<div class="col-sm-2">
						<input type="text" name="gfa_name" value="{% if seaData['gfa_name'] is defined %}{{ seaData['gfa_name'] }}{% endif %}" id="form-4" class="col-xs-12">
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-5">发货商家：</label>
					<div class="col-sm-2">
						<select id="form-5" name="shop_id" class="search-select">
							<option selected="" value="0">全部</option>
							{% if shops is defined %}
                            {% for k,v in shops %}
							<option value="{{ k }}" {% if seaData['shop_id'] is defined and seaData['shop_id'] == k %}selected{% endif %}>{{ v }}</option>
							{% endfor %}
							{% endif %}
						</select>
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-6">下单终端：</label>
					<div class="col-sm-2">
						<select id="form-6" name="channel_subid" class="search-select">
							<option selected="" value="0">全部</option>
							{% if terminal is defined %}
                            {% for k,v in terminal %}
							<option value="{{ k }}" {% if seaData['channel_subid'] is defined and seaData['channel_subid'] == k %}selected{% endif %}>{{ v }}</option>
							{% endfor %}
							{% endif %}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-7">用户名：</label>
					<div class="col-sm-2">
						<input type="text" name="username" value="{% if seaData['username'] is defined %}{{ seaData['username'] }}{% endif %}" id="form-7" class="col-xs-12">
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-8">用户手机：</label>
					<div class="col-sm-2">
						<input type="text" name="phone" value="{% if seaData['phone'] is defined %}{{ seaData['phone'] }}{% endif %}" id="form-8" class="col-xs-12">
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-9">商品名称：</label>
					<div class="col-sm-2">
						<input type="text" name="goods_name" value="{% if seaData['goods_name'] is defined %}{{ seaData['goods_name'] }}{% endif %}" id="form-10" class="col-xs-12">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-10">商品编号：</label>
					<div class="col-sm-2">
						<input type="text" name="goods_id" value="{% if seaData['goods_id'] is defined %}{{ seaData['goods_id'] }}{% endif %}" id="form-11" class="col-xs-12">
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-10">订单状态：</label>
					<div class="col-sm-2">
						<select id="form-11" name="status" class="search-select">
						<option selected="" value="">全部</option>
						{% if filterStat is defined %}
						{% for k,v in filterStat %}
						<option value="{{ k }}" {% if seaData['status'] is defined and seaData['status'] == k %}selected{% endif %}>{{ v }}</option>
						{% endfor %}
						{% endif %}
						</select>
					</div>
				</div>
				<div class="form-group">
					<input type="hidden" id="psize" name="psize" value="{% if psize is defined %}{{ psize }}{% else %}15{% endif %}" />
					<input type="hidden" id="orderType" name="orderType" value="{% if seaData['orderType'] is defined %}{{ seaData['orderType'] }}{% endif %}" />
					<div class="col-md-offset-7 col-xs-3">
						<button id="orderListAction" class="btn btn-info bor-radius ordre-btn" type="submit">查询</button>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<!-- 导出start -->
						<button class="btn btn-info bor-radius ordre-btn" type="button" name="excel_export" id="excel_export">导出</button>
						<div class="modal fade" id="export_div" tabindex="-1" role="dialog" aria-labelledby="export_div">
							<div class="modal-dialog" role="document">
								<div class="modal-content" style="padding: 10px;height: 1030px; width:1000px;">
									<div style="text-align: center;width:300px">
										<label style="font-weight: 500;">导出类型:</label>
										<select name="export_type" id="export_type" class="form-control">
											<option value="select_check">当前搜索结果</option>
											<option value="all">全部</option>
										</select>
									</div>
									<div style="padding-bottom: 2px;"><input type="checkbox" id="check_all_group" >全选/取消全选</div>
									{% for k,v in excelField %}
										<div style="padding-bottom: 2px;"><input type="checkbox" class="check_all" value="{{ k }}">{{ v['text'] }}全选/取消全选</div>
										<div class="clearfix">
											<ul>
												{% for x,y in v['list'] %}
													<li><input class='{{ k }}' type="checkbox" name="export_title[]" value="{{ x }}">{{y}}</li>
												{% endfor %}
											</ul>
										</div>
									{% endfor %}
									<div style="width: 100%;text-align: center;margin-top: 20px;">
										<button class="btn btn-info" style="width: 120px;margin: 0 auto;" id="exportGroupAction" type="submit">确定</button>
									</div>
								</div>
							</div>
						</div>
						<!-- 导出end -->
					</div>
				</div>
			</form>
			<div class="hr hr32 hr-dotted"></div>
			<div class="widget-box transparent">
				<div class="widget-header">
					<div class="widget-toolbar no-border" style="float: left;">
						<ul class="nav nav-tabs">
							<li {% if seaData['orderType'] is defined and seaData['orderType'] == 'all' %}class="active"{% endif %}>
								<a href="/grouporder/orderlist?orderType=all">全部拼团订单</a>
							</li>
							<li {% if seaData['orderType'] is defined and seaData['orderType'] == 'progress' %}class="active"{% endif %}>
								<a href="/grouporder/orderlist?orderType=progress">拼团中订单</a>
							</li>
							<li {% if seaData['orderType'] is defined and seaData['orderType'] == 'success' %}class="active"{% endif %}>
								<a href="/grouporder/orderlist?orderType=success">拼团成功订单</a>
							</li>
							<li {% if seaData['orderType'] is defined and seaData['orderType'] == 'refund' %}class="active"{% endif %}>
								<a href="/grouporder/orderlist?orderType=refund">拼团失败/退款订单</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="widget-body">
					<table class="order-list-table">
						<thead>
							<tr>
								<th>订单商品</th>
								<th>订单金额</th>
								<th>收货人信息</th>
								<th>配送&物流信息</th>
								<th>买家留言</th>
								<th>备注</th>
								<th>订单状态&操作</th>
							</tr>
						</thead>
						<tbody>
						{% if totalOrder is defined and totalOrder is not empty %}
                        {% for v in totalOrder %}
						<!-- 订单 -->
						<tr>
							<td class="" colspan="7">
								<div class="order-list-t">
									<span>订单编号：{{ v['order_sn'] }}</span>
									<span>活动类型：
										{% if v['gfa_type'] == 1 %}
											拼团抽奖
										{% else %}
											普通拼团
										{% endif %}
									</span>
									<span>下单时间：{{ date("Y-m-d H:i:s",v['add_time']) }}</span>
									<span>
										付款时间：
										{% if v['pay_time'] > 0 %}
											{{ date("Y-m-d H:i:s",v['pay_time']) }}
										{% else %}
											未支付
										{% endif %}
									</span>
									<span>
										下单终端：
										{% if terminal[v['channel_subid']] is defined %}
											{{ terminal[v['channel_subid']] }}
										{% else %}
											未知
										{% endif %}
									</span>
									<span>
										付款方式：
										{% if payment[v['payment_id']] is defined %}
											{{ payment[v['payment_id']] }}
										{% else %}
											未支付
										{% endif %}
									</span>
									<span>活动名称：{{ v['gfa_name'] }}</span>
									<span>
										{% if v['is_head'] == 1 %}
											开团人：
										{% else %}
											参团人：
										{% endif %}
										{{ v['phone'] }}
									</span>
									{% if v['is_head'] == 1 and (v['status'] == 'paying' or v['status'] == 'canceled') %}
									<span></span>
									{% else %}
									<span><a href="javascript:;" data-total = "{{ v['gf_id'] }}" class="groupDetail">拼团详情</a></span>
									{% endif %}
								</div>
							</td>
						</tr>
						<!-- 订单详情 -->
						<tr class="order-table-m">
							<td class="order-list-pro">
								<div class="item">
									{% if v['goods_image'] is defined %}
									<a href="javascript:void(0);">
										<img src="{{ v['goods_image'] }}">
									</a>
									{% endif %}
									<p class="pro-name">{{ v['goods_name'] }}</p>
									<p class="pro-info">{{ v['unit_price'] }}<span>x {{ v['goods_number'] }}</span></p>
								</div>
							</td>
							<td class="order-list-money">
								<p>商品金额：{{ v['goods_price'] }}</p>
								<p>运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费：+ {{ v['carriage'] }}</p>
								<div>订单金额：{{ v['total'] }}</div>
							</td>
							<td >
								<p>{{ v['consignee'] }}</p>
								<p>{{ v['telephone'] }}</p>
								<p>{{ v['addressInfo'] }}</p>
							</td>
							<td >
								<p class="express-name">
								{% if v['express_type'] == 1 %}
									顾客自提
								{% elseif v['express_type'] == 2 %}
									两小时达
								{% elseif v['express_type'] == 3 %}
									当日达
								{% else %}
									普通快递
								{% endif %}
								</p>
								{% if v['express_sn'] is defined and v['express_sn'] > 0 %}
								<p>{{ v['express'] }}</p>
								<p>{{ v['express_sn'] }}</p>
								{% else %}
									暂无物流信息
								{% endif %}
							</td>
							<td>
								<p>{{ v['buyer_message'] }}</p>
							</td>
							<td class="order-list-remaek">
								{% if v['remark'] is defined and v['remark'] > 0 %}
								<div>
									<p>{{ v['remark']['content'] }}</p>
									<div>
										<p>{{ v['remark']['username'] }}</p>
										<p>{{ date("Y-m-d",v['remark']['add_time']) }}</p>
										<p>{{ date("H:i:s",v['remark']['add_time']) }}</p>
									</div>
									{% if v['remarkCount'] > 1 %}
										<p style="float:right;">
											共
											{{ v['remarkCount'] }}
											条记录
										</p>
									{% endif %}
								</div>
								{% endif %}
							</td>
							<td class="order-list-status">
								<p>
									{% if v['status'] == 'paying' %}
										待付款
									{% elseif v['gfu_state'] == 1 %}
										待成团
									{% elseif v['status'] == 'draw' %}
										等待抽奖
									{% elseif v['status'] == 'shipping' %}
										待发货
									{% elseif v['status'] == 'shipped' %}
										已发货
									{% elseif v['status'] == 'evaluating' %}
										已收货
									{% elseif v['gfu_state'] == 3 and ((v['status'] == 'await') OR (v['rstatus'] <= 2)) %}
										团未成，退款中
									{% elseif v['gfu_state'] == 3 AND v['rstatus'] == 3 %}
										团未成，已退款
									{% elseif v['status'] == 'canceled' %}
										交易关闭
									{% elseif v['gfu_state'] == 2 AND v['gfa_is_draw'] == 1 AND v['is_win'] == 0 AND (v['status'] == 'await' OR v['rstatus'] <= 2) %}
										未中奖，退款中
									{% elseif v['gfu_state'] == 2 AND v['gfa_is_draw'] == 1 AND v['is_win'] == 0 AND v['rstatus'] == 3 %}
										未中奖，已退款
									{% elseif v['status'] == 'finished' %}
										交易完成
									{% else %}
										未知
									{% endif %}
								</p>
								<div>
									{% if v['serviceInfo'][0] is defined %}
										{% for item in v['serviceInfo'] %}
											<div>
												<p class="red-color">
													退款进度:
													{% if refundState[item['status']] is defined %}
														{{ refundState[item['status']] }}
													{% else %}
														未知进度
													{% endif %}
												</p>
												<p>
													退款单号：<br/>
													<span class="highlight">{{ item['service_sn'] }}</span>
												</p>
											</div>
										{% endfor %}
									{% endif %}
									<p><a href="/grouporder/orderDetail?orderSn={{ v['order_sn'] }}">查看详情</a></p>
									<p><a href="javascript:;" data-id="{{ v['order_sn'] }}" class="addRemark">添加备注</a></p>
								</div>
							</td>
						</tr>
						{% endfor %}
						{% else %}
						<tr>
							<td class="center" colspan="10">
								暂无数据……
							</td>
						</tr>
						{% endif %}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
{% if page is defined %}{{ page }}{% endif %}
{% endblock %}

{% block footer %}
	<div class="pop-frame-shadow" style="display: none;"></div>
	<!-- 添加备注 -->
	<div class="pop-add-remark">
		<i class="ace-icon glyphicon glyphicon-remove"></i>
		<div class="pop-add-frame">
			<h2>添加备注</h2>
			<p>当前账号：客服妹妹</p>
			<p style="float: left; margin-bottom: 20px;">
				<span class="title">备注内容：</span>
				<textarea id="remark" placeholder="请输入"></textarea>
				<span class="max-txt"><i>0</i>/200</span>
			</p>
		</div>
		<div class="pro-add-frame-btn">
			<input type="hidden" id="orderSn" value="" />
			<button class="btn btn-info bor-radius ordre-btn" type="button" id="ensure">确认添加</button>
			<button class="btn btn-danger bor-radius ordre-btn" type="cancel" id="cancel">取消</button>
		</div>
	</div>
	<!-- 拼团详情 -->
	<div class="pop-frame-content" style="display: none;">
		<i class="ace-icon glyphicon glyphicon-remove"></i>
		<div class="pop-frame">
			<h2>拼团信息</h2>
		</div>
		<div class="pop-frame-main-content">
			<p><span>活动类型：</span><span class="group-type"></span></p>
			<p><span>拼团状态：</span><span class="group-status"></span></p>
			<p><span>参与人数：</span><span class="group-joinnum"></span></p>
			<p><span>开团时间：</span><span class="group-starttime"></span></p>
			<p><span>失效时间：</span><span class="group-endtime"></span></p>
			<div class="table-box">
				<table class="group-detail">
					<thead>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
	<script src="http://{{ config.domain.static }}/assets/admin/js/order/orderList.js"></script>
	<script src="http://{{ config.domain.static }}/assets/admin/js/group/groupOrder.js"></script>
{% endblock %}