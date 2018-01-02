{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="/order/orderList">全部订单</a></li>
<li class="active">批量发货</li>

<script>
</script>
{% endblock %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/order/batchShipments.css" class="ace-main-stylesheet" />
	<div class="row">
		<div class="col-xs-12">
			<form id="uploadFiles" action="/order/batchShipments" method="post" enctype="multipart/form-data">
				<input type="hidden" name="step" value="{% if step is defined and step > 0 %}{{ step+1 }}{% else %}1{% endif %}" />
				<div class="batch-ship">
					<div class="course-state state-{% if step is defined and step != 1 %}{{ step }}{% else %}1{% endif %} ">
						<div class="one">导入表格<i>1</i></div>
						<div class="two">预览确认<i>2</i></div>
						<div class="three">完成发货<i>3</i></div>
						<div class="line line-green"></div>
						<div class="line line-gray"></div>
					</div>
					{% if step is defined and step == 1 %}
						<div class="col-xs-12 toLeadFrom">
							<div>
								导入发货订单
								<label >
									<input name="files" class="uploadFiles" value="" type="file" >
									<span>选择文件</span>
								</label>
								<span class="warning">文件格式不对，请重新选择文件！</span>
							</div>
							<p>如还未有表格，请到订单列表导出发货单，填写快递单号后再导入。</p>
							<button class="btn btn-info bor-radius ordre-btn import">导入表格</button>
						</div>
					{% endif %}
					{% if step is defined and step == 3 %}
						<div class="col-xs-12 shippingSuccess">
							<div class="mark">
								<div>!</div>
								<span>发货成功</span>
							</div>
							<div class="backtrack">
								<a href="/order/orderlist">返回全部订单</a>
							</div>
						</div>
					{% endif %}
				</div>
				{% if step is defined and step == 2 %}
					<div class="col-xs-12 batch-ship-m">
						<div class="widget-toolbar no-border" style="float: left;">
							<ul class="nav nav-tabs">
								<li class="normal active">
									<a href="javascript:;">
										正常发货
										<i class="num">
											{% if result['validCount'] is defined %}
												{{ result['validCount'] }}
											{% endif %}
										</i>
									</a>
								</li>
								<li class="abnormality">
									<a href="javascript:;">
										发货信息缺失
										<i class="num">
											{% if result['invalidCount'] is defined %}
												{{ result['invalidCount'] }}
											{% endif %}
										</i>
									</a>
								</li>
							</ul>
						</div>
						<div class="col-xs-offset-10">
							<button class="btn btn-info bor-radius ordre-btn ship confirmShipments">确定发货</button>
						</div>
						<div class="hr hr32 hr-dotted"></div>

						<table class="order-list-table col-xs-12">
							<thead>
								<tr>
									<th style="width:24.5%">订单商品</th>
									<th style="width:13%">订单金额</th>
									<th style="width:26%">收货人信息</th>
									<th style="width:15%">买家留言</th>
									<th style="width:21.5%">发货信息</th>
								</tr>
							</thead>
							<tbody class="validOrder">
								{% if result['valid'][0] is defined %}
									{% for val in result['valid'] %}
										<tr>
											<td colspan="5">
												<div class="order-list-t">
													<span>
														<i class="display-max">订单编号：</i> 
														{{ val['order_sn'] }}
														<input type="hidden" value="{{ val['order_sn'] }}" name="orderSn[]" />
													</span>
													<span>
														下单时间：
														{{ date("Y-m-d H:i:s",val['add_time']) }}
													</span>
													<span>
														订单状态：
														{% if orderStat[val['status']] is defined %}
															{{ orderStat[val['status']] }}
														{% else %}
															未知状态
														{% endif %}
													</span>
												</div>
											</td>
										</tr>
										<tr class="border">
											<td class="order-list-pro">
												{% if val['productList'][0] is defined %}
													{% for product in val['productList'] %}
														<div class="item">
															{% if product['goods_image'] is defined %}
																<a href="">
																	<img src="{{ product['goods_image'] }}">
																</a>
															{% endif %}
															<p class="pro-name">
																{% if product['drug_type'] == 1 %}
																	<span class="icon-drug-red">Rx</span>
																{% elseif product['drug_type'] == 2 %}
																	<span class="icon-drug-red">OTC</span>
																{% elseif product['drug_type'] == 3 %}
																	<span class="icon-drug-green">OTC</span>
																{% endif %}
																{{ product['goods_name'] }}
															</p>
															<p class="pro-spec">
																{{ product['name_id'] }}
																{% if product['name_id'] %}
																	{% if product['name_id2'] %}
																		：
																		{{ product['name_id2'] }}
																		{% if product['name_id3'] %}
																			，
																		{% endif %}
																	{% elseif product['name_id3'] %}
																		：
																	{% endif %}
																{% elseif product['name_id2'] %}
																	{{ product['name_id2'] }}
																	{% if product['name_id3'] %}
																	：
																	{% endif %}
																{% endif %}
																{{ product['name_id3'] }}
															</p>
															<p class="pro-info">
																{{ product['unit_price'] }}
																<span>
																	x{{ product['goods_number'] }}
																</span>
																{% if product['goods_type'] == 1 %}
																	<a href="">赠品</a>
																{% elseif product['goods_type'] == 2 %}
																	<a href="">赠品</a>
																{% elseif product['goods_type'] == 3 %}
																	<a href="">换购品</a>
																{% endif %}
															</p>
														</div>
													{% endfor %}
												{% endif %}
											</td>
											<td>
												<p class="money">
													<i>￥</i>
													{{ val['total'] }}
												</p>
												<p>运费（￥{{ val['carriage'] }}）</p>
											</td>
											<td>
												<p>{{ val['consignee'] }}</p>
												<p>{{ val['telephone'] }}</p>
												<p>
													{{ val['addressInfo'] }}
												</p>
											</td>
											<td>
												<p>{{ val['buyer_message'] }}</p>
											</td>
											<td>
												<p>
													{{ val['express'] }}
													<input type="hidden" value="{{ val['express'] }}" name="express[]" />
												</p>
												<p>
													{{ val['express_sn'] }}
													<input type="hidden" value="{{ val['express_sn'] }}" name="expressSn[]" />
												</p>
											</td>
										</tr>
									{% endfor %}
								{% else %}
									<tr>
										<td colspan="5" style="text-align:center;">没有正常发货数据</td>
									</tr>
								{% endif %}
							</tbody>
							<tbody class="invalidOrder">
								{% if result['invalid'][0] is defined %}
									{% for val in result['invalid'] %}
										<tr>
											<td colspan="5">
												<div class="order-list-t">
													<span>
														<i class="display-max">订单编号：</i> 
														{{ val['order_sn'] }}
													</span>
													<span>
														下单时间：
														{% if val['isNonentity'] == 0 %}
															{{ date("Y-m-d H:i:s",val['add_time']) }}
														{% endif %}
													</span>
													<span>
														订单状态：
														{% if val['isNonentity'] == 0 %}
															{% if orderStat[val['status']] is defined %}
																{{ orderStat[val['status']] }}
															{% else %}
																未知状态
															{% endif %}
														{% endif %}
													</span>
												</div>
											</td>
										</tr>
										<tr class="border">
											{% if val['isNonentity'] == 0 %}
												<td class="order-list-pro">
													{% if val['productList'][0] is defined %}
														{% for product in val['productList'] %}
															<div class="item">
																{% if product['goods_image'] is defined %}
																	<a href="">
																		<img src="{{ product['goods_image'] }}">
																	</a>
																{% endif %}
																<p class="pro-name">
																	{% if product['drug_type'] == 1 %}
																		<span class="icon-drug-red">Rx</span>
																	{% elseif product['drug_type'] == 2 %}
																		<span class="icon-drug-red">OTC</span>
																	{% elseif product['drug_type'] == 3 %}
																		<span class="icon-drug-green">OTC</span>
																	{% endif %}
																	{{ product['goods_name'] }}
																</p>
																<p class="pro-spec">
																	{{ product['name_id'] }}
																	{% if product['name_id'] %}
																		{% if product['name_id2'] %}
																			：
																			{{ product['name_id2'] }}
																			{% if product['name_id3'] %}
																				，
																			{% endif %}
																		{% elseif product['name_id3'] %}
																			：
																		{% endif %}
																	{% elseif product['name_id2'] %}
																		{{ product['name_id2'] }}
																		{% if product['name_id3'] %}
																		：
																		{% endif %}
																	{% endif %}
																	{{ product['name_id3'] }}
																</p>
																<p class="pro-info">
																	{{ product['unit_price'] }}
																	<span>
																		x{{ product['goods_number'] }}
																	</span>
																	{% if product['goods_type'] == 1 %}
																		<a href="">赠品</a>
																	{% elseif product['goods_type'] == 2 %}
																		<a href="">赠品</a>
																	{% elseif product['goods_type'] == 3 %}
																		<a href="">换购品</a>
																	{% endif %}
																</p>
															</div>
														{% endfor %}
													{% endif %}
												</td>
												<td>
													<p class="money">
														<i>￥</i>
														{{ val['total'] }}
													</p>
													<p>运费（￥{{ val['carriage'] }}）</p>
												</td>
												<td>
													<p>{{ val['consignee'] }}</p>
													<p>{{ val['telephone'] }}</p>
													<p>
														{{ val['addressInfo'] }}
													</p>
												</td>
												<td>
													<p>{{ val['buyer_message'] }}</p>
												</td>
											{% else %}
												<td colspan="4">
												</td>
											{% endif %}
											<td>
												<p>{{ val['msg'] }}</p>
											</td>
										</tr>
									{% endfor %}
								{% else %}
									<tr>
										<td colspan="5" style="text-align:center;">没有发货信息缺失数据</td>
									</tr>
								{% endif %}
							</tbody>
						</table>
					</div>
				{% endif %}
			</form>
		</div>
	</div>
</div>
{% endblock %}

{% block footer %}

<script src="http://{{ config.domain.static }}/assets/admin/js/order/batchShipments.js"></script>
{% endblock %}