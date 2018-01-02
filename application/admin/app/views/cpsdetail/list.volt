{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/goodset/list">注册明细</a></li>
<li class="active"><a href="#">注册明细列表</a></li>
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
            <form class="form-horizontal" role="form" method="get" action="/cpsdetail/list">
                <div class="page-header">
                    <h1>注册明细列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">

                             <span>会员ID：</span>
                            <input type="text" id="user_id" name="user_id" class="tools-txt" value="{% if channel['user_id'] is defined %}{{channel['user_id']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>推广员ID:</span>
                            <input type="text" id="cps_user_id" name="cps_user_id" class="tools-txt" value="{% if channel['cps_user_id'] is defined %}{{channel['cps_user_id']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>推广员姓名：</span>
                            <input type="text" id="user_name" name="user_name" class="tools-txt" value="{% if channel['user_name'] is defined %}{{channel['user_name']}}{%endif%}">
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
                        <a href="/cpsdetail/list">
                            <button class="btn btn-primary" type="button">清空搜索条件</button>
                        </a>
                    </label>

                    <label>
                         <a class="green" href="javascript:;" onclick="showWindow('绑定推广员', '/cpsdetail/bang', 500, 300, true, true);" title="编辑">
                            <button class="btn btn-primary" type="button">绑定推广员</button>
                        </a>
                    </label>
                   
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
             

                    </tr>
                    </thead>
                    <tbody>
                
                    <tr>
                      <th>编号</th>
                      <th>注册会员ID</th>
                      <th>推广会员ID</th>
                      <th>推广人员姓名</th>
                      <th>注册时间</th>
                      <th>绑定时间</th>
                      <th>推广ID</th>
                      <th>注册端口</th>
                      <th>返利金额</th>
					</tr>
                     {% if  list['list'] is defined  %}
                    {% for v in list['list'] %}
                        <tr>
                        <td style="vertical-align: middle;">{{v['cps_id']}}</td>
                        <td style="vertical-align: middle;">{{v['user_id']}}</td>
                        <td style="vertical-align: middle;">{{v['cps_user_id']}}</td>
                        <td style="vertical-align: middle;">{{v['user_name']}}</td>
                        <td style="vertical-align: middle;">{{date('Y-m-d H:i:s',v['add_time'])}}</td>
                        <td style="vertical-align: middle;">{{date('Y-m-d H:i:s',v['bind_time'])}}</td>
                        <td style="vertical-align: middle;">{{v['invite_code']}}</td>
                        <td style="vertical-align: middle;">{{v['channel_name']}}</td>
                        <td style="vertical-align: middle;">{{v['back_amount']}}</td>
                       
						
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