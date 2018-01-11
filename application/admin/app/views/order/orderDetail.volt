{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="#">全部订单</a></li>
<li class="active">
	订单
	{% if orderInfo['order_sn'] %}
		{{ orderInfo['order_sn'] }}
	{% endif %}
</li>

<script>
</script>
{% endblock %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/order/orderDetail.css?201706" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.fancybox-2.1.5.css" class="ace-main-stylesheet" />

<div class="page-content">
	<div class="row bg-gray">
		<div class="base-info-b col-xs-12 bg-white">
			<div class="col-xs-8 base-info" style="border-right: 1px solid #ddd;">
				<h4>基本信息</h4>
				<div class="col-xs-12 padding-l-0">
					<div class="col-xs-6 padding-l-0">
						<p>
							订单状态：
							<span>
								{% if orderInfo['isClose'] == 1 %}
                                        交易关闭
								{% elseif orderStat[orderInfo['status']] is defined %}
									{{ orderStat[orderInfo['status']] }}
								{% else %}
									未知状态
								{% endif %}
							</span>
							{% if alterState is defined and alterState == 1 %}
								<a href="javascript:;" id="changeStatus" style="margin-right:80px;">修改状态</a>
							{% endif %}
							<select data-status="{% if orderInfo['status'] is defined %}{{ orderInfo['status'] }}{% endif %}" id="orderStatus">
								<option value="paying">待付款</option>
								<option value="shipping">待发货</option>
								<option value="shipped">已发货</option>
								<option value="canceled">交易关闭</option>
								<option value="finished">订单完成</option>
							</select>
						</p>
						<p>
							订单编号：
							{% if orderInfo['order_sn'] is defined %} 
								{{ orderInfo['order_sn'] }} 
							{% endif %}
						</p>
						<p>
							订单类型：
							{% if orderInfo['callback_phone'] is defined and orderInfo['callback_phone'] > 0 %}
								处方药订单
							{% elseif orderInfo['order_type'] is defined and orderInfo['order_type'] == 5 %}
								拼团订单
							{% else %}
								普通订单
							{% endif %}
						</p>
						<p>
							用户手机：
							{{ orderInfo['phone'] }}
						</p>
						<p>
							下单时间：
							{% if orderInfo['add_time'] is defined and orderInfo['add_time'] > 0 %}
								{{ date("Y-m-d H:i:s",orderInfo['add_time']) }}
							{% endif %}
						</p>
						<p>
							付款时间：
							{% if orderInfo['pay_time'] is defined and orderInfo['pay_time'] > 0 %}
								{{ date("Y-m-d H:i:s",orderInfo['pay_time']) }}
							{% else %}
								未支付
							{% endif %}
						</p>
						<p>
							下单终端：
							{% if terminal[orderInfo['channel_subid']] is defined %}
								{{ terminal[orderInfo['channel_subid']] }}
							{% else %}
								未知
							{% endif %}
						</p>
						{% if orderInfo['callback_phone'] is defined and orderInfo['callback_phone'] %}
							<p>
								回拨电话：
								{% if orderInfo['callback_phone'] is defined %}
									{{ orderInfo['callback_phone'] }}
								{% endif %}
							</p>
							<p>
								上传照片：
								{% if orderInfo['ordonnance_photo'] is defined and orderInfo['ordonnance_photo'] %}
									<div id="checkPrescription" class="attachments">
										<a href="{{ orderInfo['ordonnance_photo'] }}" class="lightbox" rel="thumbnails">
											<img src="{{ orderInfo['ordonnance_photo'] }}" />
										</a>
									</div>
								{% endif %}
							</p>
						{% endif %}
						{% if orderInfo['serviceInfo'][0] is defined %}
							<p>
								关联服务单：
								{% for val in orderInfo['serviceInfo'] %}
									<span>
										<a href="/refund/refundDetail?serviceSn={{ val['service_sn'] }}">
											{{ val['service_sn'] }}
										</a>
									</span>
								{% endfor %}
							</p>
						{% endif %}
					</div>
					<div class="col-xs-6 pad-r-none">
						<p>
							订单来源：
							{% if orderInfo['orderSource'] is defined %}
								{{ orderInfo['orderSource'] }}
							{% endif %}
						</p>
						<p>
							发货商家：
							{% if shops[orderInfo['shop_id']] is defined %}
								{{ shops[orderInfo['shop_id']] }}
							{% else %}
								诚仁堂自营
							{% endif %}
						</p>
						<div class="base-info-explain">
							<span>客户留言：</span>
							<p>
								{% if orderInfo['buyer_message'] is defined %}
									{{ orderInfo['buyer_message'] }}
								{% endif %}
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-4 pad-r-none">
				<h4>备注及操作</h4>
				<div class="base-info-explain">
					<span>订单备注：</span>
					<p>
						{% if orderInfo['remark']['content'] is defined %}
							{{ orderInfo['remark']['content'] }}
						{% endif %}
					</p>
				</div>
				{% if alterRefund is defined and alterRefund == 1 %}
				{% if orderInfo['status'] is defined and orderInfo['isRefund'] is defined and orderInfo['isRefund'] == 0 %}
					<div class="btn-refund">
						{% if orderInfo['status'] == 'shipping' or orderInfo['status'] == 'shipped' %}
							<a href="/order/applyRefund?orderSn={{ orderInfo['order_sn'] }}" class="btn btn-info bor-radius ordre-btn">
								申请取消/退款
							</a>
						{% elseif orderInfo['status'] == 'evaluating' or orderInfo['status'] == 'finished' %}
							<a href="/order/applyRefund?orderSn={{ orderInfo['order_sn'] }}" class="btn btn-info bor-radius ordre-btn">
								申请售后
							</a>
						{% endif %}
					</div>
				{% endif %}
				{% endif %}
			</div>
		</div>
		<div class="col-xs-12 bg-white margin-b-20 order-detail-play">
			<div class="col-xs-4">
				<div class="express-info">
					<h4>配送信息</h4>
					{% if alterExpress is defined and alterExpress == 1 %}
						<a href="javascript:;" class="">修改</a>
					{% endif %}
				</div>
				<p>
					配送方式：
					{% if delivery[orderInfo['express_type']] is defined %}
						{{ delivery[orderInfo['express_type']] }}
					{% else %}
						未知
					{% endif %}
				</p>
				<p>
					运费金额：￥
					{% if orderInfo['carriage'] is defined %}
						{{ orderInfo['carriage'] }}
					{% else %}
						0.00
					{% endif %}
				</p>
				{% if orderInfo['express_sn'] is defined and orderInfo['express_sn'] != '' %}
				<p>
					物流公司：
					{% if orderInfo['express'] is defined %}
						{{ orderInfo['express'] }}
					{% endif %}
				</p>
				<p>
					物流单号：
					{% if orderInfo['express_sn'] is defined %}
						{{ orderInfo['express_sn'] }}
					{% endif %}
				</p>
				{% else %}
					<p>暂无物流信息</p>
				{%endif%}
			</div>
			<div class="col-xs-8 logistics-info">
				<!-- 物流信息 -->
				<div class="order-detail-logistics">
					{% if orderInfo['logistics'][0] is defined %}
						<ul>
							{% for val in orderInfo['logistics'] %}
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
		<div class="col-xs-12 bg-white margin-b-20 order-detail-play">
			<div class="col-xs-4">
				<h4>付款信息</h4>
				<p>
					付款方式：
					{% if payment[orderInfo['payment_id']] is defined %}
						{{ payment[orderInfo['payment_id']] }}
					{% else %}
						未支付
					{% endif %}
				</p>
				<p>
					商品金额：￥
					{% if orderInfo['goods_price'] is defined %}
						{{ orderInfo['goods_price'] }}
					{% else %}
						0.00
					{% endif %}
				</p>
				<p>
					运费金额：￥
					{% if orderInfo['carriage'] is defined %}
						{{ orderInfo['carriage'] }}
					{% else %}
						0.00
					{% endif %}
				</p>
				<p>
					促销优惠：￥
					{% if orderInfo['youhui_price'] is defined %}
						{{ orderInfo['youhui_price'] }}
					{% else %}
						0.00
					{% endif %}
				</p>
				<p>
					优&nbsp;&nbsp;惠&nbsp;&nbsp;券：￥
					{% if orderInfo['user_coupon_price'] is defined %}
						{{ orderInfo['user_coupon_price'] }}
					{% else %}
						0.00
					{% endif %}
				</p>
				<p>
					应付金额：￥
					{% if orderInfo['orderDue'] is defined %}
						{{ orderInfo['orderDue'] }}
					{% else %}
						0.00
					{% endif %}
				</p>
			</div>
			<div class="col-xs-4">
				<div class="receiver-info">
					<h4>收货人信息</h4>
					{% if alterSite is defined and alterSite == 1 %}
						<a href="javascript:;" class="">修改</a>
					{% endif %}
				</div>
				<div class="receivingInfo">
					<p>
						收  货  人 ：
						{% if orderInfo['consignee'] is defined %}
							{{ orderInfo['consignee'] }}
						{% endif %}
					</p>
					<p>
						手机号码：
						{% if orderInfo['telephone'] is defined %}
							{{ orderInfo['telephone'] }}
						{% endif %}
					</p>
					<div class="more-content">
						<span>收货地址：</span>
						<p>
							{% if orderInfo['addressInfo'] is defined %}
								{{ orderInfo['addressInfo'] }}
							{% endif %}
						</p>
					</div>
				</div>
			</div>
			<div class="col-xs-4">
				<div class="bill-info">
					<h4>发票信息</h4>
					{% if alterBill is defined and alterBill == 1 %}
						<a href="javascript:;" class="">修改</a>
					{% endif %}
				</div>
				<div class="billDetail">
					{% if orderInfo['invoice_type'] is defined and orderInfo['invoice_type'] == 0 %}
						<p>不需要发票</p>
					{% else %}
						<p>
							发票类型：
							{% if orderInfo['invoice_type'] == 1 %}
								个人
							{% elseif orderInfo['invoice_type'] == 2 %}
								单位
							{% else %}
								纸质发票
							{% endif %}
						</p>
						<div class="more-content">
							<span>发票抬头：</span>
							<p>
								{% if orderInfo['invoice_info']['title_name'] is defined %}
									{{ orderInfo['invoice_info']['title_name'] }}
								{% endif %}
								{% if orderInfo['invoice_info']['title_type'] is defined %}
									（{{ orderInfo['invoice_info']['title_type'] }}）
								{% endif %}
							</p>
						</div>
						{% if orderInfo['invoice_info']['taxpayer_number'] is defined and orderInfo['invoice_info']['taxpayer_number'] %}
							<div class="more-content">
								<span>税<font style="margin-left:26px;">号</font>：</span>
								<p>
									{{ orderInfo['invoice_info']['taxpayer_number'] }}
								</p>
							</div>
						{% endif %}
						<div class="more-content">
							<span>发票内容：</span>
							<p>
								{% if orderInfo['invoice_info']['content_type'] is defined %}
									{{ orderInfo['invoice_info']['content_type'] }}
								{% endif %}
							</p>
						</div>
					{% endif %}
				</div>
			</div>
		</div>
		<div class="col-xs-12 bg-white order-detail-list">
			<h4>商品清单</h4>
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
					<?php $goodsCount = 0; ?>
					{% if orderInfo['productList'][0] is defined %}
						{% for product in orderInfo['productList'] %}
							<?php $goodsCount += $product['goods_number']; ?>
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
                                    <a href="{{ jumpUrl }}/product/{{ product['goods_id'] }}.html" target="_blank">
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
								<td>{{ product['goods_number'] }}</td>
								<td>{{ product['promotion_total'] }}</td>
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
		<div class="col-xs-12 order-detail-total-b">
			<div class="order-detail-total">
				<dl>
					<dt>
						<span><?php echo $goodsCount; ?></span>
						件商品，总计：
					</dt>
					<dd>
						￥
						{% if orderInfo['goods_price'] is defined %}
							{{ orderInfo['goods_price'] }}
						{% else %}
							0.00
						{% endif %}
					</dd>
					<dt>运费：</dt>
					<dd>
						+￥
						{% if orderInfo['carriage'] is defined %}
							{{ orderInfo['carriage'] }}
						{% else %}
							0.00
						{% endif %}
					</dd>
					<dt>活动优惠：</dt>
					<dd>
						-￥
						{% if orderInfo['youhui_price'] is defined %}
							{{ orderInfo['youhui_price'] }}
						{% else %}
							0.00
						{% endif %}
					</dd>
					<dt>优惠券抵扣：</dt>
					<dd>
						-￥
						{% if orderInfo['user_coupon_price'] is defined %}
							{{ orderInfo['user_coupon_price'] }}
						{% else %}
							0.00
						{% endif %}
					</dd>
					<dt>余额：</dt>
					<dd>
						-￥
						{% if orderInfo['balance_price'] is defined %}
							{{ orderInfo['balance_price'] }}
						{% else %}
							0.00
						{% endif %}
					</dd>
				</dl>
				<div>
					订单支付金额：
					<span>
						￥
						{% if orderInfo['orderDue'] is defined %}
							{{ orderInfo['orderDue'] }}
						{% else %}
							0.00
						{% endif %}
					</span>
				</div>
			</div>
		</div>
		<div class="col-xs-12 order-operate-log">
			<div class="operate">
				<h4>操作日志</h4>
				<a href="javascript:;" >展开</a>
			</div>
			<div class="orderLog">
				{% if orderInfo['operationLog'][0] is defined %}
					{% for v in orderInfo['operationLog'] %}
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
	                <option {% if orderInfo['invoice_type'] is defined and orderInfo['invoice_type'] == 3 %}selected{% endif %} value="3">纸质发票</option>
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
			{% if orderInfo['invoice_info']['taxpayer_number'] is defined and orderInfo['invoice_info']['taxpayer_number'] %}
				<span id="taxpayerNumber">
			{% else %}
				<span id="taxpayerNumber" style="display:none;">
			{% endif %}
				<dt>税<font style="margin-left:26px;">号</font>：</dt>
				<dd>
					<input type="text" value="{% if orderInfo['invoice_info']['taxpayer_number'] is defined %}{{ orderInfo['invoice_info']['taxpayer_number'] }}{% endif %}">
				</dd>
			</span>
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

	<!-- 弹框 -->
	<div class="pop-express" style="display: none;">
	    <i class="ace-icon glyphicon glyphicon-remove"></i>
	    <div class="pop-frame">
	        <h2>收货信息修改</h2>
	        <p>请填写正确的单号，确保物流跟踪查询信息正确</p>
	    </div>
	    <div class="pop-frame-main-content">
	        <div class="pop-express-m">
	            <input type="text" id="checkExpress" placeholder="请输入物流单号" autocomplete="off">
	            <button class="btn btn-info bor-radius ordre-btn" id="expressInquiry">确定</button>
	            <div class="shotrcut">
	                <p>1231231<span>韵达快递</span></p>
	                <p>1231231<span>韵达快递2</span></p>
	                <p>1231231<span>韵达快递3</span></p>
	                <p><label for="checkExpress">1231231<span>请输入物流公司</span></label></p>
	            </div>
	        </div>
	    </div>
	</div>

<script src="http://{{ config.domain.static }}/assets/js/jquery.fancybox-2.1.5.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/lightbox.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/order/orderDetail.js?201706309"></script>
{% endblock %}