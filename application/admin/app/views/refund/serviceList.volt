{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="/refund/refundList">售后管理</a></li>
<li class="active">退款/退货单列表</li>

<script>
</script>

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
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/refund/refundList.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.fancybox-2.1.5.css" class="ace-main-stylesheet" />

<div class="page-content">
	<div class="row">
		<div class="col-xs-12 ">
			<form class="form-horizontal" role="form" id="my_form">
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-1">订单编号：</label>
					<div class="col-sm-2">
						<input type="text" name="orderSn" value="{% if orderSn is defined %}{{ orderSn }}{% endif %}" id="form-1" class="col-xs-12" />
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="startTime">申请时间：</label>
					<div class="col-sm-2">
						<input type="text" name="startTime" value="{% if startTime is defined %}{{ startTime }}{% endif %}" id="startTime" class="col-xs-12" />
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="endTime">至：</label>
					<div class="col-sm-2">
						<input type="text" name="endTime" value="{% if endTime is defined %}{{ endTime }}{% endif %}" id="endTime" class="col-xs-12" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-4">退款状态：</label>
					<div class="col-sm-2">
						<select name="refundStatus" id="form-4" style="height: 34px;width: 100%;">
							<option value="0">全部</option>
							{% if refundStateSel is defined %}
								{% for i,v in refundStateSel %}
									<option {% if refundStatus is defined and refundStatus == i %}selected{% endif %} value="{{ i }}">
										{{ i }}
									</option>
								{% endfor %}
							{% endif %}
						</select>
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-5">服务单号：</label>
					<div class="col-sm-2">
						<input type="text" name="serviceSn" value="{% if serviceSn is defined %}{{ serviceSn }}{% endif %}"  id="form-3" class="col-xs-12" />
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-6">所属店铺：</label>
					<div class="col-sm-2">
						<select id="u26_input" name="shopId" style="height: 34px;width: 100%;">
							<option value="0">全部</option>
							{% if shops is defined %}
								{% for k,v in shops %}
									<option value="{{ k }}" {% if shopId is defined and shopId == k %}selected{% endif %}>{{ v }}</option>
								{% endfor %}
							{% endif %}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-7">申请人：</label>
					<div class="col-sm-2">
						<input type="text" name="username" value="{% if username is defined %}{{ username }}{% endif %}" id="form-7" class="col-xs-12" />
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-8">审核人：</label>
					<div class="col-sm-2">
						<input type="text" name="auditor" value="{% if auditor is defined %}{{ auditor }}{% endif %}" id="form-8" class="col-xs-12" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-1 control-label no-padding-right" for="form-10">商品名称：</label>
					<div class="col-sm-2">
						<input type="text" name="goodsName" value="{% if goodsName is defined %}{{ goodsName }}{% endif %}" id="form-10" class="col-xs-12" />
					</div>
					<label class="col-sm-1 control-label no-padding-right" for="form-11">商品编号：</label>
					<div class="col-sm-2">
						<input type="text" name="goodsId" value="{% if goodsId is defined %}{{ goodsId }}{% endif %}" id="form-11" class="col-xs-12"  onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" />
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-offset-7 col-xs-3">
						<input type="hidden" id="psize" name="psize" value="{% if psize is defined %}{{ psize }}{% else %}15{% endif %}" />
						<input type="hidden" id="searchType" name="searchType" value="{% if searchType is defined %}{{ searchType }}{% else %}all{% endif %}" />
						<button id="submit" class="btn btn-info bor-radius ordre-btn" type="submit">查询</button>
						&nbsp;&nbsp;&nbsp;&nbsp;
						 <button class="btn btn-info bor-radius ordre-btn" type="button" name="excel_export" id="excel_export">导出</button>
					</div>
				</div>
				<div class="modal fade" id="export_div" tabindex="-1" role="dialog" aria-labelledby="export_div">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content" style="padding: 10px;height: 400px; width:700px;">
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
                                <button class="btn btn-info" style="width: 120px;margin: 0 auto;" id="exportAction" type="submit">确定</button>
                            </div>
                        </div>
                    </div>
                </div>
			</form>
			<div class="hr hr32 hr-dotted"></div>
			<div class="widget-box transparent">
				<div class="widget-header">
					<div class="widget-toolbar no-border" style="float: left;">
						<ul class="nav nav-tabs">
							<li {% if searchType is defined and searchType == 'all' %}class="active"{% endif %}>
								<a href="javascript:void(0)" onclick="stateChange('all')">
									全部
								</a>
							</li>
							<li {% if searchType is defined and searchType == 'pending' %}class="active"{% endif %}>
								<a href="javascript:void(0)" onclick="stateChange('pending')">
									申请待处理
									<i class="num">
										{% if pendingNum is defined and pendingNum > 0 %}
											{% if pendingNum > 999 %}
												...
											{% else %}
												{{ pendingNum }}
											{% endif %}
										{% else %}
											0
										{% endif %}
									</i>
								</a>
							</li>
							<li {% if searchType is defined and searchType == 'shipping' %}class="active"{% endif %}>
								<a href="javascript:void(0)" onclick="stateChange('shipping')">
									仅退款（未发货）
								</a>
							</li>
							<li {% if searchType is defined and searchType == 'shipped' %}class="active"{% endif %}>
								<a href="javascript:void(0)" onclick="stateChange('shipped')">
									仅退款（已发货）
								</a>
							</li>
							<li {% if searchType is defined and searchType == 'refundReturn' %}class="active"{% endif %}>
								<a href="javascript:void(0)" onclick="stateChange('refundReturn')">
									退款退货（已发货）
								</a>
							</li>
							<li {% if searchType is defined and searchType == 'refuse' %}class="active"{% endif %}>
								<a href="javascript:void(0)" onclick="stateChange('refuse')">
									已拒绝退款
								</a>
							</li>
						</ul>
						<script>function stateChange(state){$('#searchType').val(state);$('#submit').click();}</script>
					</div>
				</div>
				<div class="widget-body">
					<table class="order-list-table col-xs-12">
						<thead>
							<tr>
								<th>订单商品</th>
								<th>退款金额</th>
								<th>退款/退货原因</th>
								<th>原因说明</th>
								<th>备注</th>
								<th>审核人</th>
								<th>订单状态</th>
								<th>退款状态&操作</th>
							</tr>
						</thead>
						<tbody>
							{% if list[0] is defined %}
								{% for val in list %}
									<tr>
										<td class="" colspan="8">
											<div class="order-list-t">
												<span>
													<i class="display-max">服务单号：</i>
													{{ val['service_sn'] }}
												</span>
												<span>
													<i class="display-max">订单编号：</i>
													{{ val['order_sn'] }}
												</span>
												<span>
													{% if shops[val['shop_id']] is defined %}
														{{ shops[val['shop_id']] }}
													{% else %}
														{{ config.company_name }}自营
													{% endif %}
												</span>
												<span>
													申退时间：
													{{ date("Y-m-d H:i:s",val['add_time']) }}
												</span>
												<span>{{ val['username'] }}</span>
												{% if val['refundState'] == 0 %}
													<div class="fr">
														未审核|
														<a href="javascript:;" data-id="{{ val['service_sn'] }}" class="checkOrder">
															审核
														</a>
													</div>
												{% elseif val['refundState'] == 2 or val['refundState'] == 7 %}
													<div class="fr">
														未退款|
														<a href="javascript:;" data-id="{{ val['service_sn'] }}" class="returnMoney">
															退款
														</a>
													</div>
												{% elseif val['refundState'] == 4 or val['refundState'] == 5 %}
													<div class="fr">
														待收货|
														<a href="javascript:;" data-id="{{ val['service_sn'] }}" class="getGoods">
															收货
														</a>
													</div>
												{% endif %}
											</div>
										</td>
									</tr>
									<tr class="order-table-m">
										<td class="order-list-pro">
											{% if val['products'][0] is defined %}
												{% for product in val['products'] %}
													<div class="item">
                                    					<a href="{{ jumpUrl }}/product/{{ product['goods_id'] }}.html" target="_blank">
															<img src="{{ product['goods_image'] }}">
														</a>
														<p class="pro-name">
															{% if product['drug_type'] == 1 %}
																<span class="icon-drug-red">Rx</span>
															{% elseif product['drug_type'] == 2 %}
																<span class="icon-drug-red">OTC</span>
															{% elseif product['drug_type'] == 3 %}
																<span class="icon-drug-green">OTC</span>
															{% endif %}
                                    						<a href="{{ jumpUrl }}/product/{{ product['goods_id'] }}.html" target="_blank">
																{{ product['goods_name'] }}
															</a>
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
															<span>x{{ product['refundNum'] }}</span>
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
										<td style="text-align:left;">
											应退金额：￥{{ val['refund_amount'] }}<br />
											{% if val['refundState'] == 1 or val['refundState'] == 3 or val['refundState'] == 6 %}
												实退金额：￥{{ val['real_amount'] }}
											{% else %}
												{% if val['balance_price'] > 0 and val['pay_fee'] > 0 %}
													{{ val['payment_id'] }}：￥{{ val['pay_fee'] }}<br />
													余额支付：￥{{ val['balance_price'] }}
												{% elseif val['balance_price'] > 0 and val['pay_fee'] == 0 %}
													余额支付：￥{{ val['balance_price'] }}
												{% elseif val['balance_price'] == 0 and val['pay_fee'] > 0 %}
													{{ val['payment_id'] }}：￥{{ val['pay_fee'] }}
												{% endif %}
											{% endif %}
										</td>
										<td>{{ val['reason'] }}</td>
										<td>{{ val['explain'] }}</td>
										<td class="order-list-remaek">
											{% if val['remarkLog'] %}
												<div>
													<p>{{ val['remarkLog']['content'] }}</p>
													<div>
														<p>{{ val['remarkLog']['username'] }}</p>
														<p>{{ date("Y-m-d",val['remarkLog']['add_time']) }}</p>
														<p>{{ date("H:i:s",val['remarkLog']['add_time']) }}</p>
													</div>
													{% if val['remarkCount'] > 1 %}
														<p style="float:right;">
															共
															{{ val['remarkCount'] }}
															条记录
														</p>
													{% endif %}
												</div>
											{% endif %}
										</td>
										<td>
											{{ val['serv_nickname'] }}
										</td>
										<td>
											{% if val['status'] == 'refund' and val['refundState'] == 3 %}
												交易关闭
											{% elseif orderStat[val['status']] is defined %}
												{{ orderStat[val['status']] }}
											{% endif %}
										</td>
										<td class="order-list-status">
											<p>
												{% if refundState[val['refundState']] is defined %}
													{{ refundState[val['refundState']] }}
												{% endif %}
											</p>
											<p><a href="/refund/refundDetail?serviceSn={{ val['service_sn'] }}">查看详情</a></p>
											<p><a href="javascript:;" data-id="{{ val['service_sn'] }}" class="addRemark">添加备注</a></p>
										</td>
									</tr>
								{% endfor %}
							{% else %}
								<tr style="width: 100%;">
									<td class="center" colspan="6">暂无数据</td>
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
<!-- 弹框-阴影 -->
<div class="pop-frame-shadow"></div>
<input type="hidden" id="serviceSn" data-pcUrl="{{ jumpUrl }}" value="" />
<!-- 审核-->
<div class="pop-frame-content pop-verify">
	<i class="ace-icon glyphicon glyphicon-remove"></i>
	<div class="pop-frame">
		<h2>服务单信息</h2>
		<p>
			<span>服务单号：12345678901</span>
			<span>订单编号：951611160000001479</span>
			<span>订单状态：交易完成</span>
		</p>
		<p>
			<span>付款方式：在线付款</span>
			<span>应退金额：￥838.00</span>
		</p>
		<p>用户电话：12345678901</p>
	</div>
	<div class="pop-frame-main-content">
		<div class="table-box">
			<table class="refundGoods">
				<thead>
					<tr>
						<th>商品编号</th>
						<th>商品名称</th>
						<th>规格</th>
						<th>数量</th>
						<th>实付金额（元）</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>8000575</td>
						<td>
							<span class="icon-drug-red">Rx</span>
							同仁堂 六味地黄丸（大蜜丸）9g*10丸
						</td>
						<td>大，红色</td>
						<td>2</td>
						<td>60.00</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="reasonShow">
			<p>退款原因：7天无理由退货</p>
			<p>原因说明：哈哈哈哈哈哈哈哈哈哈哈</p>
		</div>
		<div class="pro-frame-img" style="margin-top:15px;">
			<p>上传图片：</p>
			<div id="checkPrescription" class="attachments">
				<a href="http://demo.lanrenzhijia.com/2014/pic0801/images/4006876523_289a8296ee_m.jpg" class="lightbox" rel="thumbnails">
					<img src="http://demo.lanrenzhijia.com/2014/pic0801/images/4006876523_289a8296ee_m.jpg" >
				</a>
			</div>
		</div>
		<div class="operationAudit">
			<h2>审核</h2>
			<div class="">
				审核结果：
				<select id="verifyResult" >
					<option value="0">请选择</option>
					<option value="goods">退货退款</option>
					<option value="money">仅退款</option>
					<option value="noPass">不通过审核</option>
				</select>
				<!--<span class="retun-way">
					退货方式：
					<select name="" id="">
						<option selected="" value="">请选择</option>
						<option value="">买家自行寄回</option>
						<option value="">买家拒收，快递原路寄回</option>
					</select>
				</span>-->
			</div>
			<div class="reason">
				<span class="tit">备注：</span>
				<textarea id="auditRemark" placeholder="请输入"></textarea>
				<span class="max-txt"><i>0</i>/200</span>
			</div>
			<div class="pop-frame-button">
				<button type="button" class="btn btn-info bor-radius ordre-btn submitAudit">确定</button>
			</div>
		</div>
		<div class="operationConfirm">
			<h2>退款确认</h2>
			<div class="">
				处理结果：<span class="auditResult">退货退款</span>
				<span class="refund-amount">
					退款金额：
					<select id="amountSel">
						<option value="0">请选择</option>
						<option value="all">全额退款</option>
						<option value="part">部分退款</option>
					</select>
					<input type="text" id="partAmount" value="" placeholder="请输入退款金额">
					<span class="moneyInfo" data-refundAmoun="0" data-balancePrice="0" data-paymentName=""></span>
				</span>
			</div>
			<div class="reason">
				<span class="tit">备注：</span>
				<textarea placeholder="请输入" id="confirmRemark"></textarea>
				<span class="max-txt"><i>0</i>/200</span>
			</div>
			<div class="pop-frame-button">
				<button type="button" class="btn btn-info bor-radius ordre-btn agreen">同意退款</button>
				<button type="button" class="btn btn-danger bor-radius ordre-btn disagreen">拒绝退款</button>
			</div>
		</div>
		<div class="receivingGoods">
			<div class="pop-frame-button">
				<button type="button" class="btn btn-info bor-radius ordre-btn receivedGoods">已收到货</button>
				<button type="button" class="btn btn-danger bor-radius ordre-btn" id="cancel">取消</button>
			</div>
		</div>
	</div>
</div>

<!-- 备注 -->
<div class="pop-add-remark pop-remark">
	<i class="ace-icon glyphicon glyphicon-remove"></i>
	<div class="pop-add-frame">
		<h2>添加备注</h2>
		<p class="serviceAccount">当前账号：客服妹妹</p>
		<p style="float: left; margin-bottom: 20px;">
			<span class="title">备注内容：</span>
			<textarea id="remark" placeholder="请输入"></textarea>
			<span class="max-txt"><i>0</i>/200</span>
		</p>
	</div>
	<div class="pro-frame-btn">
		<button class="btn btn-info bor-radius ordre-btn" type="button" id="ensure">确认添加</button>
		<button class="btn btn-danger bor-radius ordre-btn" type="cancel" id="cancel">取消</button>
	</div>
</div>

<script src="http://{{ config.domain.static }}/assets/js/jquery.fancybox-2.1.5.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/lightbox.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/refund/refundList.js?2017062100"></script>
{% endblock %}