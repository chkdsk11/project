{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="/cpsbusiness/order">订单列表</a></li>
<li class="active">
	订单
	{% if  list['order_sn'] is defined  and list['order_sn'] %}
		{{ list['order_sn'] }}
	{% endif %}
</li>

<script>
</script>
{% endblock %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/order/orderDetail.css" class="ace-main-stylesheet" />

<div class="page-content">
						<div class="row bg-gray">
							<div class="base-info-b col-xs-12 bg-white">
								<div class="col-xs-8 base-info" style="border-right: 1px solid #ddd;">
									<h4>基本信息</h4>
									<div class="col-xs-12 padding-l-0">
										<div class="col-xs-6 padding-l-0">
											
											<p>
												订单编号：
												{% if  list['order_sn'] is defined  and list['order_sn'] %}
													{{ list['order_sn'] }}
												{% endif %}
											</p>
											
											<p>
												下单时间：
												{% if list['add_time'] is defined and list['add_time'] != "" %}
													{{list['add_time']}}
												{% endif %}
											</p>
											<p>
												付款时间：
												{% if list['pay_time'] is defined and list['pay_time'] !=""  %}
													{{ date("Y-m-d H:i:s",list['pay_time']) }}
												{% else %}
													未支付
												{% endif %}
											</p>
											<p>
												下单终端：
												{% if list['channel_subid'] is defined %}
													{{ list['channel_subid'] }}
												{% else %}
													未知
												{% endif %}
											</p>
										
										</div>
									
									</div>
								</div>
								
							</div>
							<div class="col-xs-12 bg-white margin-b-20">
								<div class="col-xs-4">
									<h4>配送信息</h4>
									<p>
										配送方式：
										{% if list['express_type'] is defined %}
											{{  list['express_type'] }}
										{% else %}
											未知
										{% endif %}
									</p>
									<p>
										运费金额：￥6.00
										{% if orderInfo['carriage'] is defined %}
											{{ orderInfo['carriage'] }}
										{% else %}
											0.00
										{% endif %}
									</p>
									
								</div>
								
							</div>
							<div class="col-xs-12 bg-white margin-b-20 order-detail-play">
								<div class="col-xs-4">
									<h4>付款信息</h4>
								
									<p>
										商品金额：￥
										{% if list['goods_price'] is defined %}
											{{ list['goods_price'] }}
										{% else %}
											0.00
										{% endif %}
									</p>
									<p>
										运费金额：￥
										{% if list['carriage'] is defined %}
											{{ list['carriage'] }}
										{% else %}
											0.00
										{% endif %}
									</p>
									<p>
										应收金额：￥
										{% if list['order_total_amount'] is defined  %}
											{{ list['order_total_amount']  }}
										{% else %}
											0.00
										{% endif %}
									</p>
									<p>
										实付金额：￥
										{% if list['real_pay'] is defined  %}
											{{ list['real_pay']  }}
										{% else %}
											0.00
										{% endif %}
									</p>
									<p>
										运费税费：￥
										{% if list['yunshui'] is defined  %}
											{{ list['yunshui']  }}
										{% else %}
											0.00
										{% endif %}
									</p>
								</div>
								<div class="col-xs-4">
									<div class="receiver-info">
										<h4>收货人信息</h4>
										
									</div>
									<div class="receivingInfo">
										<p>
											
										</p>
										<p>
											用户名：
											{% if list['username'] is defined %}
												{{ list['username'] }}
											{% endif %}
										</p>
										<p>
											手机号码：
											{% if list['telephone'] is defined %}
												{{ list['telephone'] }}
											{% endif %}
										</p>
										<p>
											昵称：
											{% if list['nickname'] is defined %}
												{{ list['nickname'] }}
											{% endif %}
										</p>
										
										<p>
											收货人：
											{% if list['consignee'] is defined %}
												{{ list['consignee'] }}
											{% endif %}
										</p>
										<div class="more-content">
											<span>收货地址：</span>
											<p>
												{% if list['province'] is defined  %}
													{{ list['province'] }}
												{% endif %}
												{% if list['city'] is defined  %}
													-{{ list['city'] }}
												{% endif %}
												{% if list['county'] is defined  %}
													-{{ list['county'] }}
												{% endif %}
												{% if list['address'] is defined  %}
													{{ list['address'] }}
												{% endif %}
												
											</p>
										</div>
									</div>
								</div>
								
							</div>
							<div class="col-xs-12 bg-white order-detail-list">
								<h4>商品清单</h4>
								<table class="table table-striped table-bordered table-hover">
									<thead>
										<tr>
											 <th>商品编码</th>
											  <th>货品名称</th>
											  <th>商品类型</th>
											  <th>商品单价</th>
											  <th>购买数量</th>
											  <th>商品总额</th>
											  <th>税费</th>
											  <th>实收金额</th>
										</tr>
									</thead>
									<tbody>
										
										{% if list['goods'][0] is defined %}
											{% for product in list['goods'] %}
												
												<tr>
													<td>{{ product['goods_id'] }}</td>
													<td>{{ product['goods_name']}}</td>
													<td>
														{% if product['medicine_type'] == 1 %}
															<span class="icon-drug-red">处方药</span>
														{% elseif product['medicine_type'] == 2 %}
															<span class="icon-drug-red">红色非处方药</span>
														{% elseif product['medicine_type'] == 3 %}
															<span class="icon-drug-green">绿色非处方药</span>
														{% elseif product['medicine_type']==4 %}
															<span class="icon-drug-green">非药物</span>
														{% endif %}
													</td>
													<td>
														{{ product['unit_price'] }}
														
													</td>
													<td>{{ product['goods_number'] }}</td>
													<td>{{ product['price'] }}</td>
													<td>{{ product['goods_tax_amount'] }}</td>
													<td>{{ product['price'] }}+{{product['goods_tax_amount']}}</td>
												</tr>
											{% endfor %}
										{% else %}
											<tr>
												<td>暂时无数据</td>
											</tr>
										{% endif %}
									</tbody>
								</table>
							</div>
							
							
						</div>

					</div>
				</div>
			</div>
{% endblock %}

{% block footer %}
<!-- 弹框 -->
		<div class="pop-frame-shadow" style="display: none;"></div>
		<input type="hidden" id="orderSn" value="{% if orderInfo['order_sn'] is defined %}{{ orderInfo['order_sn'] }}{% endif %}" />
		<div class="pop-modify-address">
			<div class="">
				收货信息修改
				<i class="ace-icon glyphicon glyphicon-remove"></i>
			</div>
			<dl>
				<dt>收&nbsp;&nbsp;货&nbsp;&nbsp;人：</dt>
				<dd><input type="text" id="consignee" value="{% if orderInfo['consignee'] is defined %}{{ orderInfo['consignee'] }}{% endif %}"></dd>
				<dt>手机号码：</dt>
				<dd><input type="text" id="telephone" value="{% if orderInfo['telephone'] is defined %}{{ orderInfo['telephone'] }}{% endif %}"></dd>
				<dt>所在地区：</dt>
				<dd>
					<select id="provinceSel" class="col-xs-3">
						<option value="0">请选择</option>
						{% if orderInfo['provinceVal'] is defined %}
							{% for i,v in orderInfo['provinceVal'] %}
								<option {% if orderInfo['province'] is defined and orderInfo['province'] == i %}selected{% endif %} value="{{ i }}">{{ v }}</option>
							{% endfor %}
						{% endif %}
					</select>
					<select id="citySel" class="col-xs-3">
						<option value="0">请选择</option>
						{% if orderInfo['cityVal'] is defined %}
							{% for i,v in orderInfo['cityVal'] %}
								<option {% if orderInfo['city'] is defined and orderInfo['city'] == i %}selected{% endif %} value="{{ i }}">{{ v }}</option>
							{% endfor %}
						{% endif %}
					</select>
					<select id="countySel" class="col-xs-3">
						<option value="0">请选择</option>
						{% if orderInfo['countyVal'] is defined %}
							{% for i,v in orderInfo['countyVal'] %}
								<option {% if orderInfo['county'] is defined and orderInfo['county'] == i %}selected{% endif %} value="{{ i }}">{{ v }}</option>
							{% endfor %}
						{% endif %}
					</select>
				</dd>
				<dt>收货地址：</dt>
				<dd>
					<textarea id="addressInfo">{% if orderInfo['address'] is defined %}{{ orderInfo['address'] }}{% endif %}</textarea>
					<a href="javascript:;" class="btn btn-info bor-radius ordre-btn addressSubmit">确认修改</a>
				</dd>
			</dl>
		</div>

		<div class="pop-modify-bill">
			<div class="">
				收货信息修改
				<i class="ace-icon glyphicon glyphicon-remove"></i>
			</div>
			<dl>
				<dt>发票类型 ：</dt>
				<dd>
					<select id="invoiceType" class="col-xs-3">
		                <option {% if orderInfo['invoice_type'] is defined and orderInfo['invoice_type'] == 0 %}selected{% endif %} value="0">不开发票</option>
		                <!--<option {% if orderInfo['invoice_type'] is defined and orderInfo['invoice_type'] == 1 %}selected{% endif %} value="1">个人</option>
		                <option {% if orderInfo['invoice_type'] is defined and orderInfo['invoice_type'] == 2 %}selected{% endif %} value="2">单位</option>-->
		                <option {% if orderInfo['invoice_type'] is defined and orderInfo['invoice_type'] == 3 %}selected{% endif %} value="3">电子发票</option>
		            </select>
				</dd>
				<dt>发票抬头 ：</dt>
				<dd>
					<select id="titleType" class="col-xs-3">
						<option {% if orderInfo['invoice_info']['title_type'] is defined and orderInfo['invoice_info']['title_type'] == '个人' %}selected{% endif %} value="个人">个人</option>
		                <option {% if orderInfo['invoice_info']['title_type'] is defined and orderInfo['invoice_info']['title_type'] == '单位' %}selected{% endif %} value="单位">单位</option>
		            </select>
					<input type="text" id="titleName" value="{% if orderInfo['invoice_info']['title_name'] is defined %}{{ orderInfo['invoice_info']['title_name'] }}{% endif %}">
				</dd>
				<dt>发票内容 ：</dt>
				<dd>
					<select id="contentType" class="col-xs-3">
		                <option {% if orderInfo['invoice_info']['content_type'] is defined and orderInfo['invoice_info']['content_type'] == '明细' %}selected{% endif %} value="明细">明细</option>
		                <option {% if orderInfo['invoice_info']['content_type'] is defined and orderInfo['invoice_info']['content_type'] == '药品' %}selected{% endif %} value="药品">药品</option>
	              	</select>
	              	<div style="clear: both;padding-top: 10px;">
	              		<a href="javascript:;" class="btn btn-info bor-radius ordre-btn changBill">确认修改</a>
	              	</div>
	              	
				</dd>
			</dl>
		</div>

<script src="http://{{ config.domain.static }}/assets/admin/js/order/orderDetail.js"></script>
{% endblock %}