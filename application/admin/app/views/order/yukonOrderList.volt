{% extends "layout.volt" %}

{% block path %}
<li class="active">订单管理</li>
<li class="active"><a href="/order/orderList">育学园订单结算列表</a></li>
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
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.fancybox-2.1.5.css" class="ace-main-stylesheet" />

<div class="page-content">
    <div class="row">
        <div class="col-xs-12 ">
            <form action="/order/yukonOrderList" method="get" class="form-horizontal" role="form" id="my_form">
                <div class="form-group">
                    <label class="col-sm-1 control-label no-padding-right" for="form-1">订单编号：</label>
                    <div class="col-sm-2">
                        <input type="text" name="order_sn" value="{% if order_sn is defined %}{{ order_sn }}{% endif %}" id="form-1" class="col-xs-12" />
                    </div>
                    <label class="col-sm-1 control-label no-padding-right" for="start_time">下单时间：</label>
                    <div class="col-sm-2">
                        <input type="text" name="start_time" value="{% if start_time is defined %}{{ start_time }}{% endif %}" id="start_time" class="col-xs-12" />
                    </div>
                    <label class="col-sm-1 control-label no-padding-right" for="end_time">至：</label>
                    <div class="col-sm-2">
                        <input type="text" name="end_time" value="{% if end_time is defined %}{{ end_time }}{% endif %}" id="end_time" class="col-xs-12" />
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" id="psize" name="psize" value="{% if psize is defined %}{{ psize }}{% else %}15{% endif %}" />
                    <input type="hidden" id="searchType" name="searchType" value="{% if searchType is defined %}{{ searchType }}{% else %}all{% endif %}" />

                    <label class="col-sm-1 control-label no-padding-right" for="form-4">用户类型：</label>
                    <div class="col-sm-2">
                        <select id="form-4" name="userType" class="search-select">
                            {% if goodsType is defined and goodsType == 'baiyangwang' %}
                                <option value="yukon">育学园用户</option>
                            {% else %}
                                <option value="0">全部</option>
                                <option {% if userType is defined and userType == 'baiyangwang' %}selected{% endif %} value="baiyangwang">百洋用户</option>
                                <option {% if userType is defined and userType == 'yukon' %}selected{% endif %} value="yukon">育学园用户</option>
                            {% endif %}
                        </select>
                    </div>
                    <label class="col-sm-1 control-label no-padding-right" for="form-5">商品类型：</label>
                    <div class="col-sm-2">
                        <select id="form-5" name="goodsType" class="search-select">
                            {% if userType is defined and userType == 'baiyangwang' %}
                                <option value="yukon">育学园商品</option>
                            {% else %}
                                <option value="0">全部</option>
                                <option {% if goodsType is defined and goodsType == 'yukon' %}selected{% endif %} value="yukon">育学园商品</option>
                                <option {% if goodsType is defined and goodsType == 'baiyangwang' %}selected{% endif %} value="baiyangwang">非育学园商品</option>
                            {% endif %}
                        </select>
                    </div>
                    <button class="btn btn-info bor-radius ordre-btn" type="button" name="excel_export" id="excel_export">导出</button>
                    <button id="yukonSubmit" class="btn btn-info bor-radius ordre-btn query" type="submit">查询</button>
                    <div class="col-xs-12">
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
                                       <button class="btn btn-info" style="width: 120px;margin: 0 auto;" id="yukonExportAction" type="submit">确定</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            </div>
        </div>
            <div class="hr hr32 hr-dotted"></div>
            <div class="widget-box transparent">
                <div class="widget-header">
                    <div class="widget-toolbar no-border" style="float: left;">
                        <ul class="nav nav-tabs">
                            <li {% if searchType is defined and searchType == 'all' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('all')">全部订单</a>
                            </li>
                            <!-- <li {% if searchType is defined and searchType == 'toAudit' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('toAudit')">
                                待审核
                                <i class="num">
                                    {% if auditNum is defined and auditNum > 0 %}
                                    {% if auditNum > 999 %}
                                    ...
                                    {% else %}
                                    {{ auditNum }}
                                    {% endif %}
                                    {% else %}
                                    0
                                    {% endif %}
                                </i>
                            </a>
                            </li> -->
                            <li {% if searchType is defined and searchType == 'paying' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('paying')">待付款</a>
                            </li>
                            <li {% if searchType is defined and searchType == 'shipping' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('shipping')">
                                待发货
                                <!-- <i class="num">
                                    {% if shippingNum is defined and shippingNum > 0 %}
                                    {% if shippingNum > 999 %}
                                    ...
                                    {% else %}
                                    {{ shippingNum }}
                                    {% endif %}
                                    {% else %}
                                    0
                                    {% endif %}
                                </i> -->
                            </a>
                            </li>
                            <li {% if searchType is defined and searchType == 'shipped' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('shipped')">已发货</a>
                            </li>
                            <li {% if searchType is defined and searchType == 'finished' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('finished')">交易完成</a>
                            </li>
                            <li {% if searchType is defined and searchType == 'tradingClosed' %}class="active"{% endif %}>
                            <a href="javascript:void(0)" onclick="stateChange('tradingClosed')">交易关闭</a>
                            </li>
                            <!-- <li {% if searchType is defined and searchType == 'aRefundOf' %}class="active"{% endif %}>
                                <a href="javascript:void(0)" onclick="stateChange('aRefundOf')">退款中</a>
                            </li> -->
                        </ul>
                        <script>function stateChange(state){$('#searchType').val(state);$('#yukonSubmit').click();}</script>
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
                        {% if orderList[0] is defined %}
                        {% for v in orderList %}
                        <!-- 订单 -->
                        <tr>
                            <td class="" colspan="7">
                                <div class="order-list-t">
    								<span>
    									<i class="display-max">订单编号：</i>
    									{{ v['order_sn'] }}
    								</span>
    								<span>
    									{% if shops[v['shop_id']] is defined %}
    										{{ shops[v['shop_id']] }}
    									{% else %}
    										{{ config.company_name }}自营
    									{% endif %}
    								</span>
                                    <span>
                                        用户手机：
                                        {{ v['phone'] }}
                                    </span>
    								<span>
    									下单时间：
    									{{ date("Y-m-d H:i:s",v['add_time']) }}
    								</span>
    								<span>
    									付款时间：
    									{% if v['pay_time'] > 0 %}
    										{{ date("Y-m-d H:i:s",v['pay_time']) }}
    									{% else %}
    										未支付
    									{% endif %}
    								</span>
    								<span>
    									<i class="display-max">下单终端：</i>
    									{% if terminal[v['channel_subid']] is defined %}
    										{{ terminal[v['channel_subid']] }}
    									{% else %}
    										未知
    									{% endif %}
    								</span>
                                    <!-- {% if v['audit_state'] == 0 and (v['status'] == 'paying' or v['status'] == 'shipping') %}
                                    <div class="fr">
                                        未审核|
                                        <a href="javascript:;" data-total = "{{ v['total_sn'] }}" class="checkOrder">审核</a>
                                    </div>
                                    {% endif %}
                                    {% if v['audit_state'] == 1 and v['isTotal'] == 0 and v['status'] == 'shipping' and v['isRefund'] == 0 %}
                                    <div class="fr">
                                        未发货|
                                        <a href="javascript:;" data-id = "{{ v['order_sn'] }}" class="deliverGoods">发货</a>
                                    </div>
                                    {% endif %}
                                    {% if v['childOrders'][0] is defined %}
                                    <div class="fr split">订单已拆分</div>
                                    {% endif %} -->
                                </div>
                            </td>
                        </tr>
                        <tr class="order-table-m">
                            <td class="order-list-pro">
                                {% if v['productList'][0] is defined %}
                                {% for product in v['productList'] %}
                                <div class="item">
                                    {% if product['goods_image'] is defined %}
                                    <a href="{{ jumpUrl }}/product/{{ product['goods_id'] }}.html" target="_blank">
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
                                        <span>x{{ product['goods_number'] }}</span>
                                        {% if product['goods_type'] == 1 %}
                                        <a href="">赠品</a>
                                        {% endif %}
                                    </p>
                                </div>
                                {% endfor %}
                                {% endif %}
                            </td>
                            <td class="order-list-money">
                                <p>
                                    ￥{{ v['total'] }}
                                </p>
                                <div>
                                    {% if payment[v['payment_id']] is defined %}
                                    {{ payment[v['payment_id']] }}
                                    {% else %}
                                    未支付
                                    {% endif %}
                                </div>
                            </td>
                            <td >
                                <p>{{ v['consignee'] }}</p>
                                <p>{{ v['telephone'] }}</p>
                                <p>
                                    {{ v['addressInfo'] }}
                                </p>
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
                                {% if v['express_sn'] != "" %}
                                <p>{{ v['express'] }}</p>
                                <p>{{ v['express_sn'] }}</p>
                                {% else %}
                                <p>暂无物流信息</p>
                                {% endif %}
                            </td>
                            <td>
                                <p>{{ v['buyer_message'] }}</p>
                            </td>
                            <td class="order-list-remaek">
                                {% if v['remark'] is defined and v['remark'] is not empty  %}
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
                                    {% if v['isClose'] == 1 or v['status'] == 'canceled' %}
                                        交易关闭
                                    {% elseif v['audit_state'] == 0 and (v['status'] == 'paying' or v['status'] == 'shipping') %}
                                    待审核
                                    {% else %}
                                    {% if orderStat[v['status']] is defined %}
                                    {{ orderStat[v['status']] }}
                                    {% else %}
                                    未知状态
                                    {% endif %}
                                    {% endif %}
                                </p>
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
                                        <span class="highlight">
                                            <a href="/refund/refundDetail?serviceSn={{ item['service_sn'] }}">
                                                {{ item['service_sn'] }}
                                            </a>
                                        </span>
                                    </p>
                                </div>
                                {% endfor %}
                                {% endif %}
                                <div>
                                    <p><a href="/order/orderDetail?orderSn={{ v['order_sn'] }}">查看详情</a></p>
                                    {% if alterRemark is defined and alterRemark == 1 %}
                                    <p><a href="javascript:;" data-id="{{ v['order_sn'] }}" class="addRemark">添加备注</a></p>
                                    {% endif %}
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
<input type="hidden" id="pcUrl" value="{{ jumpUrl }}" />
<div class="pop-frame-content" style="display: none;">
    <i class="ace-icon glyphicon glyphicon-remove"></i>
    <div class="pop-frame">
        <h2>订单信息</h2>
        <p>回拨电话：12345678901</p>
    </div>
    <div class="pop-frame-main-content">
        <div class="table-box">
            <table class="auditOrder">
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
        <div class="pro-frame-img auditOrderPhoto">
            <p>上传照片：</p>
			<div id="checkPrescription" style="width:100%" class="attachments">
                <a href="javascript:;" >
                    <img src="http://demo.lanrenzhijia.com/2014/pic0801/images/4006876523_289a8296ee_m.jpg" alt="">
                </a>
            </div>
        </div>
        <div class="" style="float: left;width:100%">
            <h2>订单审核</h2>
            <div class="">
                审核结果：
                <select id="verifyResult">
                    <option value="0">请选择</option>
                    <option value="1">通过审核</option>
                    <option value="2">不通过审核</option>
                </select>
            </div>
            <div class="reason">
                <span class="tit"><font style="color:#ed4c7a;">*</font>原因：</span>
                <textarea id="reason" placeholder="请输入"></textarea>
                <span class="max-txt"><i>0</i>/200</span>
            </div>
            <div class="pop-frame-button">
                <input type="hidden" id="auditTotalSn" value="" />
                <button type="button" class="btn btn-info bor-radius ordre-btn submitAudit">提交审核</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="orderSn" value="" />
<div class="pop-add-remark">
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
    <div class="pro-add-frame-btn">
        <button class="btn btn-info bor-radius ordre-btn" type="button" id="ensure">确认添加</button>
        <button class="btn btn-danger bor-radius ordre-btn" type="cancel" id="cancel">取消</button>
    </div>
</div>
<!-- 弹框 -->
<div class="pop-express" style="display: none;">
    <i class="ace-icon glyphicon glyphicon-remove"></i>
    <div class="pop-frame">
        <h2>发货操作</h2>
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
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/order/orderList.js?201707182"></script>
{% endblock %}