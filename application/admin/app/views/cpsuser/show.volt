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
            <form class="form-horizontal" role="form" method="get" action="/cpsuser/show">
                <input type="hidden" name="user" value='{% if channel['user'] is defined  %}{{channel['user']}}{%endif%}' >
                <div class="page-header">
                    <h1>推广会员列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">
                             <span>订单编号：</span>
                            <input type="text" id="order_id" name="order_id" class="tools-txt" value="{% if channel['order_id'] is defined  %}{{channel['order_id']}}{%endif%}">
                    </label>
                    <label class="clearfix">
                             <span>会员ID：</span>
                            <input type="text" id="user_id" name="user_id" class="tools-txt" value="{% if channel['user_id'] is defined %}{{channel['user_id']}}{%endif%}">
                    </label>
                    <label class="clearfix">
                          <span>	推广ID：</span>
                           <span><select name="code">
           							<option value="0">全   部</option>
            						   {% if code is defined %}
            							 {% for date in code %}
            									<option value="{{date}}" {% if channel['code'] is defined and channel['code'] == date %}selected{% endif %}>{{date}}</option>
            						      {% endfor %} 
            						  {%endif%}
							</select>
                            <span>
                    </label>
                   
                    
                    <label>
                        <span>会员渠道：</span>
                        <span>
                            <select name="channel_id" id="channel_id">
                            <option value="">全部</option>
                              {% for date in channel_lset %}
                                   <option value="{{date['channel_id']}}" {% if channel['channel_id'] is defined  and channel['channel_id'] == date['channel_id'] %} selected="selected" {% endif %}  > {{date['channel_name']}} </option>
                              {% endfor %} 
                            </select>
                        </span>
                    </label>
                    
                </div>
                <div class="tools-box">
                    <label>
                        <span>时间：</span>
                        <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if channel['start_time'] is defined and channel['start_time']  %}{{ channel['start_time'] }}{%endif%}" readonly/>
                        <span>-</span>
                        <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if  channel['end_time'] is defined and channel['end_time']  %}{{ channel['end_time'] }}{%endif%}" readonly/>
                    </label>
                    <label>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>
                   
                </div>
            </form>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
					  <th class="text-center">订单编号</th>
					  <th class="text-center">下单时间</th>
					  <th class="text-center">会员ID</th>
					  <th class="text-center">商品金额</th>
					  <th class="text-center">付款方式</th>
					  <th class="text-center">支付时间</th>
					  <th class="text-center">满减</th>
					  <th class="text-center">优惠券名称</th>
					  <th class="text-center">优惠券金额</th>
					  <th class="text-center">运费</th>
					  <th class="text-center">余额</th>
					  <th class="text-center">应付款</th>
					  <th class="text-center">平台</th>
					  <th class="text-center">推广渠道</th>
					  <th class="text-center">推广ID</th>
					  <th class="text-center">推广返利金额</th>
					  <th class="text-center">订单状态</th>
					  <th class="text-center">是否结算</th>
					  <th class="text-center">结算时间</th>
                        
                    </tr>
                    </thead>
                    <tbody>
                    {% if list['list'] is defined and list['list'] != 0  %}
                    {% for v in list['list'] %}
					{% if v['invite_code']!="" %}
                    <tr>
						<td>{{v['order_sn']}}</td>
						<td>{{v['order_time']}}</td>
						<td>{{v['user_id']}}</td>
						<td>{{v['price']}}</td>
						<td>{{v['pay_id']}}</td>
						<td>{{v['pay_time']}}</td>
						<td>{{v['moneyOff']}}</td>
						<td>{{v['coupon_name']}}</td>
						<td style="vertical-align: middle;">{{v['coupon_amount']}}</td>
                        <td style="vertical-align: middle;">{{v['freight']}}</td>
                        <td style="vertical-align: middle;">  {% if v['balance'] is defined  %}{{v['balance']}}{%endif%}</td>
                        <td style="vertical-align: middle;"> {% if v['real_pay'] is defined  %}{{v['real_pay']}}{%endif%}</td>
                        <td style="vertical-align: middle;">  {% if v['m_channel_id'] is defined  %}{{v['m_channel_id']}}{%endif%}</td>
                        <td style="vertical-align: middle;">{% if v['channel_id'] is defined  %}{{v['channel_id']}}{%endif%}</td>
                        <td style="vertical-align: middle;"> {% if v['invite_code'] is defined  %}{{v['invite_code']}}{%endif%}</td>
						<td style="vertical-align: middle;"> {% if v['back_amount'] is defined  %}{{v['back_amount']}}{%endif%}</td>
						<td style="vertical-align: middle;"> {% if v['order_status'] is defined  %}{{v['order_status']}}{%endif%}</td>
						<td style="vertical-align: middle;">  {% if v['clearing'] is defined  %}{{v['clearing']}}{%endif%}</td>
						<td style="vertical-align: middle;">  {% if v['clearing_time'] is defined  %}{{v['clearing_time']}}{%endif%}</td>
                    </tr>
					{% endif %}
                    {% endfor %}
					
                    {% else  %}
                    <tr>
                        <td colspan="19" align="center">暂时无数据...</td>
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
		var user_id  =  $('input[name="user_id"]').val();
		var invite_code  =  $("#invite_code").val()
		var user_name  =  $("#user_name").val();
		var employee_id = $("#employee_id").val();
		var channel_id = $("#channel_id").val();
		var start_time  =  $('input[name="start_time"]').val();
		var end_time  =  $('input[name="end_time"]').val();
		
		
		
		//alert("/cpsuser/list?csv=1&user_id="+user_id+"&invite_code="+invite_code+"&user_name="+user_name+"&employee_id="+employee_id+"&start_time="+start_time+"&end_time="+end_time+"");
		window.open("/cpsuser/list?csv=1&channel_id="+channel_id+"&user_id="+user_id+"&invite_code="+invite_code+"&user_name="+user_name+"&employee_id="+employee_id+"&start_time="+start_time+"&end_time="+end_time+"");
	});
	
	function xiugai(id,p){
			$.get("/cpsuser/list?cps_id="+id+"&stu="+p, function(data){
  				if(data){
					 if(p==2){
					   alert("的会员冻结成功");
					 }else if(p==1){
					  alert("的会员激活成功");
					 }
					 location.reload() ;
				}
			});
	}
 function check() {
        var ids = document.getElementsByName("ids");
        var flag = false;
        var array = new Array();
        for (var i = 0; i < ids.length; i++) {
            if (ids[i].checked) {
                array.push(ids[i].value);
                flag = true;
            }
        }
        if (!flag) {
            alert("请最少选择一项！");
            return false;
        }
        return array;
    }
function clearing() {
		var checked = check();
		if (checked) {
			$.ajax({
				type:"post",
				url:"/cpsuser/clearing",
				data:{"checked":checked},
				dataType:"json",
				success:function(data) {
					if(data.code == 200) {
						alert('结算成功');
						return false;
					} else if (data.code == 300) {
						alert('上月金额已结算');
						return false;
					} else {
						alert('结算失败');
						return false;
					}
				}
			});
		}
    }
</script>
{% endblock %}