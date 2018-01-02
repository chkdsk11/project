{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<style>
    .select-discount a{display: inline-block;margin-bottom:0;float: left;margin-right: 10px;}
</style>
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form" method="get" action="/cpsorder/list">
                <div class="page-header">
                    <h1>推广订单列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">
                             <span>订单号：</span>
                            <input type="text" id="order_id" name="order_id" class="tools-txt" value="{% if channel['order_id'] is defined %}{{channel['order_id']}}{%endif%}">
                    </label>
                    <label>
                        <span>订单状态：</span>
                        <span>
                            <select name="order_status" id="order_status">
                              <option value="0"  {% if channel['order_status'] is defined and channel['order_status'] == 0 %}selected{%endif%}>全部</option>
							  <option value="paying" {% if channel['order_status'] is defined and channel['order_status'] == 'paying' %}selected{%endif%}>待付款</option>
                              <option value="shipping"{% if channel['order_status'] is defined and channel['order_status'] == 'shipping' %}selected{%endif%}>待发货</option>
                              <option value="shipped"{% if channel['order_status'] is defined and channel['order_status'] == 'shipped' %}selected{%endif%}>待收货</option>
                              <option value="evaluating"{% if channel['order_status'] is defined and channel['order_status'] == 'evaluating' %}selected{%endif%}>待评价</option>
                              <option value="refund"{% if channel['order_status'] is defined and channel['order_status'] == 'refund' %}selected{%endif%}>退款/售后</option>
                              <option value="canceled"{% if channel['order_status'] is defined and channel['order_status'] == 'canceled' %}selected{%endif%}>取消</option>
                              <option value="finished"{% if channel['order_status'] is defined and channel['order_status'] == 'finished' %}selected{%endif%}>订单完成</option>
                            </select>
                        </span>
                    </label>
                    <label>
                        <span>	渠道：</span>
                        <span>
                            <select name="channel" id="channel" >
							      <option value=""   > 全选</option>
                                {% for date in channel_lset %}
                                   <option value="{{date['channel_id']}}" {% if channel['channel'] == date['channel_id'] %} selected="selected" {% endif %}  > {{date['channel_name']}} </option>
                                {% endfor %}   
                                <!--<option value="4" {% if platform is defined and platform == 4 %}selected{%endif%}>微商城</option>-->
                            </select>
                        </span>
                    </label>
                </div>
                <div class="tools-box">
                    <label>
                    <span>时间：</span>
                    <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if channel['start_time']  %}{{ channel['start_time'] }}{%endif%}" readonly/>
                    <span>-</span>
                    <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if channel['end_time']  %}{{ channel['end_time'] }}{%endif%}" readonly/>
                    </label>
                    <label>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>
					<label>
                        <button class="btn btn-primary daochu" type="button">导出</button>
                    </label>
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>订单编号</th>
                        <th>下单时间</th>
                        <th>用户名</th>
                        <th>商品金额</th>
                        <th>付款方式</th>
                        <th>支付时间</th>
                        <th>满减</th>
                        <th>优惠券名称</th>
                        <th>优惠券金额</th>
                        <th>运费</th>
                        <th>余额</th>
                        <th>应付款</th>
                        <th>推广平台</th>
                        <th>推广渠道</th>
                        <th>推广ID</th>
                        <th>推广返利金额</th>
                        <th>订单状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    {% if list['list'] %}
                    {% for v in list['list'] %}
                    <tr>
                        <td style="vertical-align: middle;">{{v['order_sn']}}</td>
                        <td style="vertical-align: middle;">{{v['order_time']}}</td>
                        <td style="vertical-align: middle;">{{v['user_id']}}</td>
                        <td style="vertical-align: middle;">{{v['price']}}</td>
                        <td style="vertical-align: middle;">{{v['pay_id']}}</td>
                        <td style="vertical-align: middle;">{{v['pay_time']}}</td>
                        <td style="vertical-align: middle;">{{v['moneyOff']}}</td>
                        <td style="vertical-align: middle;">{{v['coupon_name']}}</td>
                        <td style="vertical-align: middle;">{{v['coupon_amount']}}</td>
                        <td style="vertical-align: middle;">{{v['freight']}}</td>
                        <td style="vertical-align: middle;">{{v['balance']}}</td>
                        <td style="vertical-align: middle;">{{v['real_pay']}}</td>
                        <td style="vertical-align: middle;">{{v['m_channel_id']}}</td>
                        <td style="vertical-align: middle;">{{v['channel_id']}}</td>
                        <td style="vertical-align: middle;">{{v['invite_code']}}</td>
                        <td style="vertical-align: middle;">{{v['back_amount']}}</td>
                        <td style="vertical-align: middle;">{{v['order_status']}}</td>
                    </tr>
                    {% endfor %}
                    {% elseif list['res'] is defined and list['res'] == 'success'  %}
                    <tr>
                        <td colspan="17" align="center">暂时无数据...</td>
                    </tr>
                    {% endif %}
                    </tbody>
                </table>
            </form>
            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</div>
{% if list['page'] is defined and list['list'] != 0 %}
{{ list['page'] }}
{% endif %}
{% endblock %}
{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepk').datetimepicker({
            step: 10,
            allowBlank:true
        });
    });
    $(".do_edit").on("click",function () {
        var mid=$(this).data("mid");
        location.href="/goodset/edit?id="+mid;
    })
    $(".do_edit_view").on("click",function () {
        var mid=$(this).data("mid");
        location.href="/goodset/edit?view=1&id="+mid;
    })
    $(".do_del").on("click",function () {
        var request = location.search;
        var mid=$(this).data("mid");
        layer_confirm('确定要删除这个套餐吗?','/goodset/del',{mid :mid,request :request});
        //location.reload();
    });
    $(".daochu").on("click",function(){
		var order_id  =  $('input[name="order_id"]').val();
		var order_status  =  $("#order_status").val()
		var channel  =  $("#channel").val();
		var start_time  =  $('input[name="start_time"]').val();
		var end_time  =  $('input[name="end_time"]').val();
		//alert("/cpsorder/list?order_id="+order_id+"&order_status="+order_status+"&channel="+channel+"&start_time="+start_time+"&end_time="+end_time+"");
		window.open("/cpsorder/list?exel=1&order_id="+order_id+"&order_status="+order_status+"&channel="+channel+"&start_time="+start_time+"&end_time="+end_time+"");
	});
</script>
{% endblock %}