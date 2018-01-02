{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/goodset/list">推广订单</a></li>
<li class="active"><a href="#">推广订单列表</a></li>
{% endblock %}

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

                             <span>订单编号：</span>
                            <input type="text" id="order_sn" name="order_sn" class="tools-txt" value="{% if channel['order_sn'] is defined %}{{channel['order_sn']}}{%endif%}">
                    </label>
                     <label class="clearfix">

                             <span>用户身份证或用户名：</span>
                            <input type="text" id="id_card" name="id_card" class="tools-txt" value="{% if channel['id_card'] is defined %}{{channel['id_card']}}{%endif%}">
                    </label>
                     <label class="clearfix">

                             <span>代理人手机号/姓名：</span>
                            <input type="text" id="id_daili" name="id_daili" class="tools-txt" value="{% if channel['id_daili'] is defined %}{{channel['id_daili']}}{%endif%}">
                    </label>
                    
                    <label>
                        <span>品牌或名称：</span>
                        <span>
                            <select name="brand" id="brand">
                              <option value=""  >全部</option>
							  <option value="155" {% if channel['brand'] is defined and channel['brand'] == '155' %}selected{%endif%}>迪巧</option>
                              <option value="2988"{% if channel['brand'] is defined and channel['brand'] == '2988' %}selected{%endif%}>纽特舒玛</option>
                            </select>
                        </span>
                    </label>
                    
                </div>
                <div class="tools-box">
                    <label>
                    <span>组合套餐时间：</span>
                    <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if channel['start_time']  is defined %}{{ channel['start_time'] }}{%endif%}" readonly/>
                    <span>-</span>
                    <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if channel['end_time']  is defined %}{{ channel['end_time'] }}{%endif%}" readonly/>
                    </label>
                    <label>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>
					<label>
                        <a href="/cpsbusiness/ordercsv?order_sn={{channel['order_sn']}}&id_card={{channel['id_card']}}&id_daili={{channel['id_daili']}}&brand={{channel['brand']}}&start_time={{channel['start_time']}}&end_time={{channel['end_time']}}" ><button class="btn btn-primary " type="button">导出</button></a>
                    </label>
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                    <th>订单编号</th>
                    <th>身份证</th>
                    <th>用户名</th>
                    <th>用户名真实姓名</th>
                    <th>代理人手机号</th>
                    <th>代理人姓名</th>
                    <th>下单时间</th>
                    <th>商品名称</th>
                    <th>商品金额</th>
                    <th>数量</th>
                    <th>实付总金额</th>
                    <th>操作</th>
                    
                    </tr>
                    </thead>
                    <tbody>
                    {% if list['list'] %}
                    {% for v in list['list'] %}
                    <tr>
                        <td style="vertical-align: middle;">{{v['order_sn']}}</td>
                        <td style="vertical-align: middle;">{{v['id_card']}}</td>
                        <td style="vertical-align: middle;">{{v['phone']}}</td>
                        <td style="vertical-align: middle;">{{v['real_name']}}</td>
                        <td style="vertical-align: middle;">{{v['bu_phone']}}</td>
                        <td style="vertical-align: middle;">{{v['bb_name']}}</td>
                        <td style="vertical-align: middle;">{{v['add_time']}}</td>
                        <td style="vertical-align: middle;">{{v['goods_name']}}</td>
                        <td style="vertical-align: middle;">{{v['price']}}</td>
                      	<td style="vertical-align: middle;">{{v['goods_number']}}</td>
                        <td style="vertical-align: middle;">{{v['c_prie']}}</td>
                        <td style="vertical-align: middle;"> <a href="/cpsbusiness/details?id={{v['order_sn']}}" target="_blank"> 详情</a></td>
                    </tr>
                    {% endfor %}
                    {% elseif list['res'] is defined and list['res'] == 'success'  %}
                    <tr>
                        <td colspan="11" align="center">暂时无数据...</td>
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