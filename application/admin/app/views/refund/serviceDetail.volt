{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/refund/serviceDetal.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.fancybox-2.1.5.css" class="ace-main-stylesheet" />

<div class="page-content">
	<div class="row bg-gray">
		<div class="base-info-b col-xs-12 bg-white">
			<input type="hidden" id="serviceSn" value="{% if info['service_sn'] is defined %}{{ info['service_sn'] }}{% endif %}" />
			<div class="col-xs-8 base-info" style="border-right: 1px solid #ddd;">
				<h4>基本信息</h4>
				<div class="col-xs-12 padding-l-0">
					<div class="col-xs-6 padding-l-0">
						<p>
							订单状态：
							{% if info['orderStatus'] == '退款/售后' and info['refundState'] == 3 %}
								交易关闭
							{% elseif info['orderStatus'] is defined %}
								{{ info['orderStatus'] }}
							{% endif %}
						</p>
						<p>
							服务单号：
							{% if info['service_sn'] is defined %}
								{{ info['service_sn'] }}
							{% endif %}
						</p>
						<p>
							退款状态：
							<span>
								{% if refundState[info['refundState']] is defined %}
									{{ refundState[info['refundState']] }}
								{% endif %}
							</span>
							{% if alterState is defined and alterState %}
								<a href="javascript:;" id="changeStatus" data-value="1" style="margin-right:80px;">修改状态</a>
							{% endif %}
							<select data-status="{% if info['refundState'] is defined %}{{ info['refundState'] }}{% endif %}" id="refundState">
								{% for k,v in refundState %}
									<option value="{{ k }}">{{ v }}</option>
								{% endfor %}
							</select>
						</p>
						<p>
							申退时间：
							{% if info['add_time'] is defined %}
								{{ date("Y-m-d H:i:s",info['add_time']) }}
							{% endif %}
						</p>
						{% if info['refundState'] is defined and info['refundState'] == 3 %}
							<p>
								退款时间：
								{% if info['update_time'] is defined %}
									{{ date("Y-m-d H:i:s",info['update_time']) }}
								{% endif %}
							</p>
						{% endif %}
					</div>
					<div class="col-xs-6 pad-r-none">
						<p>
							订单来源 ：
							{% if info['orderSource'] is defined %}
								{{ info['orderSource'] }}
							{% endif %}
						</p>
						<p>
							发货商家：
							{% if shops[info['shop_id']] is defined %}
								{{ shops[info['shop_id']] }}
							{% endif %}
						</p>
						<p>
							退款原因：
							{% if info['reason'] is defined %}
								{{ info['reason'] }}
							{% endif %}
						</p>
						<div class="base-info-explain">
							<span>原因描述：</span>
							<p>
								{% if info['explain'] is defined %}
									{{ info['explain'] }}
								{% endif %}
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-4 pad-r-none">
				<h4>关联订单</h4>
				<p>
					订单编号：
					{% if info['order_sn'] is defined %}
						{{ info['order_sn'] }}
						<a href="/order/orderDetail?orderSn={{ info['order_sn'] }}" class="col-xs-offset-1">查看详情</a>
					{% endif %}
				</p>
			</div>
		</div>
		{% if info['return_type'] is defined and info['return_type'] == 2 %}
			<div class="col-xs-12 bg-white margin-b-20">
				<div class="col-xs-4">
					<h4>退货物流信息</h4>
					{% if info['express_no'] == '' %}
						<p>暂无物流信息</p>
					{% else %}
						<p>
							物流公司：
							{% if info['express'] is defined %}
								{{ info['express'] }}
							{% endif %}
						</p>
						<p>
							物流单号：
							{% if info['express_no'] is defined %}
								{{ info['express_no'] }}
							{% endif %}
						</p>
					{% endif %}
				</div>
				<div class="col-xs-8 logistics-info">
					<!-- 暂无物流信息 -->
					<div class="order-detail-logistics">
						{% if info['logistics'][0] is defined %}
							<ul>
								{% for val in info['logistics'] %}
									<li {% if val['isEnd'] == 1 %}class="active"{% endif %}>
										<span class="date">
											{% if val['isFirst'] == 1 %}
												{{ val['date'] }}
											{% endif %}
										</span>
										<span class="day">
											{% if val['isFirst'] == 1 %}
												{{ val['week'] }}
											{% endif %}
										</span>
										<span class="remark">
											{{ val['hour'] }} {{ val['context'] }}
										</span>
									</li>
								{% endfor %}
							</ul>
						{% else %}
							暂无物流信息
						{% endif %}
					</div>
				</div>
			</div>
		{% endif %}
		<div class="col-xs-12 bg-white order-detail-list margin-b-20">
			<h4>退货商品</h4>
			<p>
				应退金额：￥
				{% if info['refund_amount'] is defined %}
					{{ info['refund_amount'] }}
				{% endif %}
				{% if info['refundState'] is defined and info['refundState'] == 3 %}
					<span class="col-xs-offset-1">
						实退金额：￥
						{% if info['real_amount'] is defined %}
							{{ info['real_amount'] }}
						{% endif %}
					</span>
				{% endif %}
			</p>
			<table class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>商品编号</th>
						<th>商品类型</th>
						<th>商品名称</th>
						<th>规格</th>
						<th>商品单价(元)</th>
						<th>数量</th>
						<th>实付金额（元）</th>
					</tr>
				</thead>
				<tbody>
					{% if info['products'][0] is defined %}
						{% for product in info['products'] %}
							<tr>
								<td>{{ product['goods_id'] }}</td>
								<td>
									{% if product['goods_type'] == 1 %}
										赠品
									{% elseif product['goods_type'] == 2 %}
										赠品
									{% elseif product['goods_type'] == 3 %}
										换购品
									{% else %}
										普通
									{% endif %}
								</td>
								<td>
                                    <a href="{{ config.pc_url[config.environment] }}/product/{{ product['goods_id'] }}.html" target="_blank">
										{% if product['drug_type'] == 1 %}
											<span class="icon-drug-red">Rx</span>
										{% elseif product['drug_type'] == 2 %}
											<span class="icon-drug-red">OTC</span>
										{% elseif product['drug_type'] == 3 %}
											<span class="icon-drug-green">OTC</span>
										{% endif %}
										{{ product['goods_name'] }}
									</a>
								</td>
								<td>
									{{ product['name_id2'] }}
									{% if product['name_id3'] %}
										, 
									{% endif %}
									{{ product['name_id3'] }}
								</td>
								<td>{{ product['unit_price'] }}</td>
								<td>{{ product['refundNum'] }}</td>
								<td>
									{{ product['goodsRefunds'] }}
								</td>
							</tr>
						{% endfor %}
					{% else %}
						<tr>
							<td colspan="7">暂时无数据</td>
						</tr>
					{% endif %}
				</tbody>
			</table>
			<p>
				退款原因：
				{% if info['reason'] is defined %}
					{{ info['reason'] }}
				{% endif %}
			</p>
			<p>
				原因描述：
				{% if info['explain'] is defined %}
					{{ info['explain'] }}
				{% endif %}
			</p>
			<div class="pro-frame-img" style="margin-top: 15px;">
				<p>上传图片：</p>
				<div id="checkPrescription" class="attachments">
					{% if info['images'][0] is defined %}
						{% for val in info['images'] %}
							<a href="{{ val }}" class="lightbox" rel="thumbnails">
								<img src="{{ val }}" alt="">
							</a>
						{% endfor %}
					{% endif %}
				</div>
			</div>
		</div>
		<!--{% if info['refundState'] is defined and info['refundState'] == 0 %}
			<div class="col-xs-12 bg-white margin-b-20" style="padding-bottom:20px;">
				<h4>申请审核</h4>
				<p>
					退款类型：
					<select id="">
						<option selected="" value="请选择">请选择</option>
						<option value="退货退款">退货退款</option>
						<option value="仅退款">仅退款</option>
						<option value="不通过审核">不通过审核</option>
					</select>
					<span class="col-xs-offset-1">
						退款金额：
						<select name="" id="">
							<option selected="" value="请选择">请选择</option>
							<option value="全额退款">全额退款</option>
							<option value="部分退款">部分退款</option>
						</select>
					</span>
				</p>
				<div class="reason">
					<span class="tit">审核备注：</span>
					<textarea name="" placeholder="请输入"></textarea>
					<span class="max-txt"><i>0</i>/200</span>
				</div>
				<div class="pop-frame-button">
					<button class="btn btn-info bor-radius ordre-btn agreen" style="float:left;margin-left: 360px;">确定</button>
				</div>
			</div>
		{% endif %}-->
		<div class="col-xs-12 order-operate-log">
			<div class="operate">
				<h4>操作日志</h4>
				<a href="javascript:;" >展开</a>
			</div>
			<div class="orderLog">
				{% if info['operationLog'][0] is defined %}
					{% for v in info['operationLog'] %}
						<p>	
							<span>{{ v['username'] }}</span>
							{% if v['operation_type'] == 1 %}
								添加了备注<br />
								{{ v['content'] }}<br />
							{% else %}
								{{ v['content'] }}<br />
							{% endif %}
							{{ date('Y-m-d H:i:s',v['add_time']) }}
						</p>
					{% endfor %}
				{% else %}
					<p>暂无操作日志</p>
				{% endif %}
			</div>
		</div>
	</div>
</div>
{% if page is defined %}{{ page }}{% endif %}
{% endblock %}

{% block footer %}

<script src="http://{{ config.domain.static }}/assets/js/jquery.fancybox-2.1.5.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/lightbox.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/refund/refundDetail.js?201706300"></script>
{% endblock %}