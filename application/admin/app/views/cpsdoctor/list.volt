{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/goodset/list">业务代表</a></li>
<li class="active"><a href="#">医生订单明细</a></li>
{% endblock %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<style>
    .select-discount a{display: inline-block;margin-bottom:0;float: left;margin-right: 10px;}
	.table-btn-sm {
    border-width: 2px;
    font-size: 12px;
    line-height: 1.39;
    padding: 2px 5px;
}
</style>
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form" method="get" action="/cpsdoctor/list">
                <div class="page-header">
                    <h1>委托人信息审核列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">

                             <span>订单编号：</span>
                            <input type="text" id="order_id" name="order_id" class="tools-txt" value="{% if channel['order_id'] is defined %}{{channel['order_id']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>订单用户名：</span>
                            <input type="text" id="user_name" name="user_name" class="tools-txt" value="{% if channel['user_name'] is defined %}{{channel['user_name']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>医生用户名：</span>
                            <input type="text" id="doctor_name" name="doctor_name" class="tools-txt" value="{% if channel['doctor_name'] is defined %}{{channel['doctor_name']}}{%endif%}">
                    </label>
                     
                    <label>
                        <span>渠道：</span>
                        <span>
                           <select name="channel_id" id="channel_id" onchange="get_activity(this.value)">
                            <option value="">全部</option>
                                {% for date in channel_lset %}
                            <option value="{{date['channel_id']}}" {% if channel['channel_id'] == date['channel_id'] %} selected="selected" {% endif %}  > {{date['channel_name']}} </option>
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
                    <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if channel['end_time'] is defined and channel['end_time']  %}{{ channel['end_time'] }}{%endif%}" readonly/>
                    </label>
                    <label>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>
                   
                    <label>
                        <a href="/cpsdoctor/csv?order_id={{channel['order_id']}}&user_name={{channel['user_name']}}&doctor_name={{channel['doctor_name']}}&start_time={{channel['start_time']}}&end_time={{channel['end_time']}}"><button class="btn btn-primary" type="button">导出</button></a>
                    </label>
                   
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
             

                    </tr>
                    </thead>
                    <tbody>
                
                    <tr>
                  		<th>订单编号</th>
                        <th>用户名</th>
                        <th>姓名</th>
                        <th>推广ID</th>
                        <th>医生用户名</th>
                        <th>医生姓名</th>
                        <th>下单时间</th>
                        <th>商品名称</th>
                        <th>商品金额</th>
                        <th>税费</th>
                        <th>折扣</th>
                        <th>数量</th>
                        <th>实付总金额</th>
                        <th>操作</th>
						</tr>
                     {% if  list['list'] is defined  %}
                    {% for v in list['list'] %}
                        <tr>
                        <td style="vertical-align: middle;">{{v['order_sn']}}</td>
                        <td style="vertical-align: middle;">{{v['user_name']}}</td>
                        <td style="vertical-align: middle;">{{v['real_name']}}</td>
                        <td style="vertical-align: middle;">{{v['cps_id']}}</td>
                        <td style="vertical-align: middle;">{{v['doctor_name']}}</td>
                        <td style="vertical-align: middle;">{{v['id_card']}}</td>
                        <td style="vertical-align: middle;">{{v['doctor_real_name']}}</a></td>
                        <td style="vertical-align: middle;">{{v['order_time']}}</td>
                        <td style="vertical-align: middle;">{{v['goods_name']}}</td>
						<td style="vertical-align: middle;">{{v['c_price']}}</td>
                        <td style="vertical-align: middle;">{{v['tax']}}</td>
                        <td style="vertical-align: middle;">{{v['discount']}}</td>
                        <td style="vertical-align: middle;">{{v['qty']}}</td>
                        <td style="vertical-align: middle;">{{v['total_price']}}</td>
                        <td style="vertical-align: middle;">详情</td>
						
                    </tr>
                    {% endfor %}
                    {% else  %}
                    <tr>
                        <td colspan="8" align="center">暂时无数据...</td>
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
  
    $(document).ready(function(){
		$("#examine").click(function(){
			var reply_id = '';
			$('input:checkbox[name=f1]:checked').each(function(i){
				if(i == 0)
				{
					reply_id += $(this).val();
				}else{
					reply_id += (':'+$(this).val());
				}
			});
			
			$.ajax({
				url:'/cpsmandatary/examine',
				data:{
					reply_id:reply_id,reply_status:1
				},
				type:'post',
				dataType:'json',
				cache:false,
				success:function(data,i){
					if(data.status == 'success')
					{
						alert('操作失败');
						return false;
					}else if(data.status =='on'){
						alert('ID:'+data.id+' 审核失败');
						return false;
					}
					else{
						alert('操作成功');
					}
					window.location.reload('/cpsreplyuser/list');
				}
			});
		});
		$("#not_examine").click(function(){
			var reply_id = '';
			$('input:checkbox[name=f1]:checked').each(function(i){
				if(i == 0)
				{
					reply_id += $(this).val();
				}else{
					reply_id += (':'+$(this).val());
				}
			});
			
			$.ajax({
				url:'/cpsmandatary/examine',
				data:{
					reply_id:reply_id,reply_status:2
				},
				type:'post',
				dataType:'json',
				cache:false,
				success:function(data,i){
					if(data.status == 'success')
					{
						alert('操作失败');
						return false;
					}else if(data.status =='on'){
						alert('ID:'+data.id+' 审核失败');
						return false;
					}
					else{
						alert('操作成功');
					}
					window.location.reload('/cpsreplyuser/list');
				}
			});
		});
	});
    
	
</script>
{% endblock %}