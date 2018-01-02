{% extends "layout.volt" %}

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
            <form class="form-horizontal" role="form" method="get" action="/cpsmandatary/list">
                <div class="page-header">
                    <h1>委托人信息审核列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">

                             <span>业务代表姓名或身份证号：</span>
                            <input type="text" id="real_name" name="real_name" class="tools-txt" value="{% if channel['real_name'] is defined %}{{channel['real_name']}}{%endif%}">
                    </label>
                    <label class="clearfix">

                             <span>委托人姓名或身份证号：</span>
                            <input type="text" id="mandatary_real_name" name="mandatary_real_name" class="tools-txt" value="{% if channel['mandatary_real_name'] is defined %}{{channel['mandatary_real_name']}}{%endif%}">
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

                    <label>
                        <span>地区：</span>
                        <span>
                            <select name="region_id" id="region_id">
                                <option value="" >全部</option>
                                {% if region_list %}
                                {% for v in region_list %}
                                    <option value="{{v['id']}}" {% if channel['region_id'] is defined and channel['region_id'] == v['id'] %}selected{%endif%}>{{v['region_name']}}</option>
                                {% endfor %}
                                {% endif %}
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
                        <a href="/cpsmandatary/list"><button class="btn btn-primary" type="button">重置</button></a>
                    </label>
                   <label>
                     <span>批量：</span>
					<input class="btn btn-sm btn-primary" type="button" id="examine" value="通过">
					<input class="btn btn-sm btn-primary" type="button" id="not_examine"  value="不通过">
                    </label><br /><br />
                   
                   
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>编号</th>
                        <th>业务代表姓名</th>
                        <th>省份</th>
                        <th>委托人手机号</th>
                        <th>委托人姓名</th>
                        <th>委托人身份证</th>
                        <th>附件</th>
                        <th>提交时间</th>
                        <th>审核状态</th>
                        <th>操作</th>


                    </tr>
                    </thead>
                    <tbody>
                    {% if list['list'] %}
                    {% for v in list['list'] %}
                    <tr>
                   
                        <td style="vertical-align: middle;">{% if  v['status'] == 0 %}<input type="checkbox" name="f1" value="{{v['mandatary_id']}}">{%endif%} {{v['mandatary_id']}}</td>
                        <td style="vertical-align: middle;">{{v['real_name']}}</td>
                        <td style="vertical-align: middle;">{{v['region_name']}}</td>
                        <td style="vertical-align: middle;">{{v['phone']}}</td>
                        <td style="vertical-align: middle;">{{v['bbm_name']}}</td>
                        <td style="vertical-align: middle;">{{v['id_card']}}</td>
                        <td style="vertical-align: middle;"><a href="{{v['id_card_image']}}" target='_blank'><img src="{{v['id_card_image']}}" style="max-width:100px;min-width:50px;"/></a></td>
                        <td style="vertical-align: middle;">{{v['update_time']}}</td>
                        <td style="vertical-align: middle;">
                        	{% if  v['status'] == 0 %}待审核{%endif%}
                            {% if  v['status'] == 1 %}审核通过{%endif%}
                            {% if  v['status'] == 2 %}审核未通过{%endif%}
                        </td>
                         <td style="vertical-align: middle;">
                            {% if  v['status'] == 0 %}
                                <input class="btn btn-sm btn-primary" type="button" value="通过" onclick="shenhe({{v['mandatary_id']}},1)">
                                <input class="btn btn-sm btn-primary" type="button" value="不通过" onclick="shenhe({{v['mandatary_id']}},2)">
                            {%endif%}
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
					if(data.status == 'ok')
					{
						alert('操作成功');
						return false;
					}else if(data.status =='on'){
						alert('ID:'+data.id+' 审核失败');
						return false;
					}else if(data.status =='kong'){
						alert('请勾选业务代表');
						return false;
					}
					else{
						alert('除已操作：'+data.status+'其他操作成功');
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
					if(data.status == 'ok')
					{
						alert('操作成功');
						return false;
					}else if(data.status =='on'){
						alert('ID:'+data.id+' 审核失败');
						return false;
					}else if(data.status =='kong'){
                        alert('请勾选业务代表');
                        return false;
                    }
					else{
						alert('除已操作：'+data.status+'其他操作成功');
					}
					window.location.reload('/cpsreplyuser/list');
				}
			});
		});
	});

    //单个审核
    function shenhe(reply_id,reply_status){

        $.ajax({
            url:'/cpsmandatary/examine',
            data:{
                reply_id:reply_id,reply_status:reply_status
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
                }else if(data.status =='kong'){
                    alert('请勾选业务代表');
                    return false;
                }
                else{
                    alert('操作成功');
                }
                window.location.reload('/cpsreplyuser/list');
            }
        });
    }
	
</script>
{% endblock %}