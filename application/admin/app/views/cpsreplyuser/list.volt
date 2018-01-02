{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/goodset/list">推广申请用户</a></li>
<li class="active"><a href="#">推广申请用户列表</a></li>
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
            <form class="form-horizontal" role="form" method="get" action="/cpsreplyuser/list">
                <div class="page-header">
                    <h1>推广申请用户列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">

                             <span>会员ID：</span>
                            <input type="text" id="mobile" name="mobile" class="tools-txt" value="{% if channel['mobile'] is defined %}{{channel['mobile']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>身份证号：</span>
                            <input type="text" id="id_cart" name="id_cart" class="tools-txt" value="{% if channel['id_cart'] is defined %}{{channel['id_cart']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>姓名：</span>
                            <input type="text" id="user_name" name="user_name" class="tools-txt" value="{% if channel['user_name'] is defined %}{{channel['user_name']}}{%endif%}">
                    </label>
                     
                    <label>
                        <span>状态：</span>
                        <span>
                            <select name="reply_status" id="reply_status">
                              <option value="" >全部</option>
							  <option value="0" {% if channel['reply_status'] is defined and channel['reply_status'] != "" %}selected{%endif%}>待审核</option>
                              <option value="1"{% if channel['reply_status'] is defined and channel['reply_status'] == 1 %}selected{%endif%}>审核通过</option>
                              <option value="2"{% if channel['reply_status'] is defined and channel['reply_status'] == 2 %}selected{%endif%}>审核未通过</option>
                            </select>
                        </span>
                    </label>
                </div>
                <div class="tools-box">
                    <label>
                    <span>申请时间：</span>
                    <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if channel['start_time'] is defined and channel['start_time']  %}{{ channel['start_time'] }}{%endif%}" readonly/>
                    <span>-</span>
                    <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if channel['end_time'] is defined and channel['end_time']  %}{{ channel['end_time'] }}{%endif%}" readonly/>
                    </label>
                    <label>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>
                   <label>
                     <span>批量：</span>
					<input class="btn btn-sm btn-primary" type="button" id="examine" value="通过">
					<input class="btn btn-sm btn-primary" type="button" id="not_examine"  value="不通过">
                    </label>
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="checkAll">
                            编号
                        </th>
                        <th>姓名</th>
                        <th>用户</th>
                        <th>电话</th>
                        <th>渠道</th>
                        <th>状态</th>
                        <th>申请时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    {% if list['list'] is defined %}
                    {% for v in list['list'] %}
                    <tr>
                        <td style="vertical-align: middle;">
                            {% if v['reply_status']==0 %} 
                                <input type="checkbox" name="f1" value="{{v['reply_id']}}">
                            {% endif %}  
                            {{v['reply_id']}}
                        </td>
                        <td style="vertical-align: middle;">{{v['user_name']}}</td>
                        <td style="vertical-align: middle;">{{v['phone']}}</td>
                        <td style="vertical-align: middle;">{{v['mobile']}}</td>
                        <td style="vertical-align: middle;">{% if v['channel']==90 %}android{% elseif v['channel']==91 %}wap{% else %}ios{% endif %}</td>
                        <td style="vertical-align: middle;">{% if v['reply_status']==1 %}审核通过{% elseif v['reply_status']==2 %}审核未通过{% else %}待审核{% endif %}</td>
                        <td style="vertical-align: middle;">{{v['add_time']}}</td>
                        <td style="vertical-align: middle;">
                            {% if v['reply_status']==0 %}
                                <input class="btn btn-sm btn-primary" type="button" value="通过" onclick="shenhe({{v['reply_id']}},1)">
                                <input class="btn btn-sm btn-primary" type="button" value="不通过"  onclick="shenhe({{v['reply_id']}},2)">
                            {% endif %}
                        </td>
                    </tr>
                    {% endfor %}
                    {% elseif list['res'] is defined and list['res'] == 'success'  %}
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

    //全选/全不选
    $(".checkAll").on("click",function(){
        if($(this).prop("checked")){
            $('input:checkbox[name=f1]').prop("checked",true);
        }else{
            $('input:checkbox[name=f1]').prop("checked",false);
        }
    });

    //单项选择
    $(document).on("click","input:checkbox[name=f1]",function(){
        var inputAllState = true;
        $("input:checkbox[name=f1]").each(function(i,item){
            !$(item).prop("checked") ? inputAllState = false:"";
        });
        if(inputAllState){
            $(".checkAll").prop("checked",true);
        }else{
            $(".checkAll").prop("checked",false);
        }
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
				url:'/cpsreplyuser/examine',
				data:{
					reply_id:reply_id,reply_status:1
				},
				type:'post',
				dataType:'json',
				cache:false,
				success:function(data,i){
					if(data.status == 'success')
					{
						alert('请选择审核对象');
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
				url:'/cpsreplyuser/examine',
				data:{
					reply_id:reply_id,reply_status:2
				},
				type:'post',
				dataType:'json',
				cache:false,
				success:function(data,i){
					if(data.status == 'success')
					{
						alert('请选择审核对象');
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
     //单个审核
	function shenhe(reply_id,reply_status){
	    $.ajax({
            url:'/cpsreplyuser/examine',
            data:{
                reply_id:reply_id,reply_status:reply_status
            },
            type:'post',
            dataType:'json',
            cache:false,
            success:function(data,i){
                if(data.status == 'success')
                {
                    alert('请选择审核对象');
                    return false;
                }else if(data.status =='on'){
                    alert('ID:'+data.id+' 审核失败');
                    return false;
                } else {
                    alert('操作成功');
                    window.location.reload('/cpsreplyuser/list');
                }
            }
        });
	}
    
	
</script>
{% endblock %}