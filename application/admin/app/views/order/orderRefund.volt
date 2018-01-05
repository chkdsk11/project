{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="/order/orderList">全部订单</a></li>
<li class="active">
	订单
	{% if info['order_sn'] is defined %}
		{{ info['order_sn'] }}
	{% endif %}
</li>

<script>
</script>
{% endblock %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/order/orderRefund.css" class="ace-main-stylesheet" />

<div class="page-content">
	<div class="row order-return">
		<h4>申请退款</h4>
		<form id="refundForm" action="/order/applyRefund" method="post" class="form-horizontal" onsubmit="ajaxSubmit(refundForm);return false;">
			<div class="col-xs-12" style="padding:0">
				<table class="table-return table table-striped table-bordered table-hover ">
					<thead>
						<tr>
							<th>
								{% if info['status'] is defined %}
									{% if info['status'] == 'shipping' or info['status'] == 'shipped' %}
										<input type="checkbox" checked disabled="true" />
										<input type="checkbox" class="checkAll" checked style="display:none;" />
									{% else %}
										<input type="checkbox" class="checkAll" />
									{% endif %}
								{% endif %}
							</th>
							<th>商品ID</th>
							<th>商品名称</th>
							<th>规格</th>
							<th>附属赠品</th>
							<th>可退数量</th>
							<th>实付金额</th>
							<th style="width:21%">退货数量</th>
						</tr>
					</thead>
					<tbody>
						{% if info['productList'][0] is defined %}
							{% for val in info['productList'] %}
								{% if val['goods_type'] == 0 or val['goods_type'] == 3 %}
									<tr>
										<td>
											{% if info['status'] == 'shipping' or info['status'] == 'shipped' %}
												<input type="checkbox" checked disabled="true" />
												<input type="checkbox" class="option" checked style="display:none; />
											{% else %}
												<input type="checkbox" class="option" />
											{% endif %}
											<input type="hidden" name="goodsId[]" value="{{ val['goods_id'] }}" />
										</td>
										<td>{{ val['goods_id'] }}</td>
										<td>
                                    		<a href="{{ jumpUrl }}/product/{{ val['goods_id'] }}.html" target="_blank">
												{{ val['goods_name'] }}
											</a>
										</td>
										<td>
											{{ val['name_id2'] }}
											{% if val['name_id3'] %}
												，
											{% endif %}
											{{ val['name_id3'] }}
										</td>
										<td>
											{% if val['appendant'][0] is defined %}
												{% for i,v in val['appendant'] %}
													{% if i > 0 %}
														，
													{% endif %}
													{{ v['goods_name'] }}
												{% endfor %}
											{% else	%}
												无
											{% endif %}
										</td>
										<td>
											{{ val['goods_number'] - val['refund_goods_number'] }}
										</td>
										<td>￥{{ val['promotion_total'] }}</td>
										<td>
											{% if info['status'] == 'shipping' or info['status'] == 'shipped' %}
												<input type="text" value="{{ val['goods_number'] }}" disabled="true" class="refundNum" />
												<input type="text" name="num[]" value="{{ val['goods_number'] }}"  class="hide" />
											{% else %}
												<input type="text" name="num[]" data-num="{{ val['goods_number'] - val['refund_goods_number'] }}" data-name="{{ val['goods_name'] }}" value="0" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" class="refundNum hide" />
											{% endif %}
										</td>
									</tr>
								{% endif %}
							{% endfor %}
						{% endif %}
					</tbody>
				</table>
			</div>
			<dl>
				<dt class="box"><i>*</i>退货原因：</dt>
				<dd>
					<select name="reason" id="reason">
						<option value="0">请选择</option>
						{% if reason[0] is defined %}
							{% for val in reason %}
								<option value="{{ val }}">{{ val }}</option>
							{% endfor %}
						{% endif %}
					</select>
					<div class="box">
						<i>*</i>原因说明：
						<input id="explain" name="explain" type="text" placeholder="请输入，200字内">
					</div>
				</dd>
				<!--<dt><i>*</i>处理结果：</dt>
				<dd>
					<select name="" id="">
						<option selected="" value="请选择">请选择</option>
						<option value="退货退款">退货退款</option>
						<option value="仅退款">仅退款</option>
					</select>
					<div class="box">
						<i>*</i>退款金额：
						<select name="" id="">
							<option selected="" value="请选择">请选择</option>
							<option value="全额退款">全额退款</option>
							<option value="部分退款">部分退款</option>
						</select>
						退款金额：￥60.00（余额：￥10，微信支付：￥50）
					</div>
				</dd>
				<dt>审核备注：</dt>
				<dd>
					<div class="textarea-box">
						<textarea name="" id="" placeholder="请输入"></textarea>
						<i class="max-txt"><span>0</span>/200</i>
					</div>
				</dd>-->
				<dd class="refundSubmitButton">
					<input type="hidden" id="orderSn" name="orderSn" value="{% if info['order_sn'] is defined %}{{ info['order_sn'] }}{% endif %}" />
					<a href="javascript:;" class="btn btn-info bor-radius ordre-btn confirmRefund" style="margin: 15px 20px 0 0;">确认提交</a>
					<a href="/order/orderDetail?orderSn={% if info['order_sn'] is defined %}{{ info['order_sn'] }}{% endif %}" class="btn btn-info bor-radius ordre-btn" style="margin: 15px 20px 0 0;">关闭</a>
				</dd>
			</dl>
		</form>
	</div>
</div>
{% endblock %}

{% block footer %}

<script src="http://{{ config.domain.static }}/assets/admin/js/order/orderRefund.js"></script>
{% endblock %}