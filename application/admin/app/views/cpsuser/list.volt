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
            <form class="form-horizontal" role="form" method="get" action="/cpsuser/list">
                <div class="page-header">
                    <h1>推广会员列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">
                             <span>会员ID：</span>
                            <input type="text" id="user_id" name="user_id" class="tools-txt" value="{% if channel['user_id']  %}{{channel['user_id']}}{%endif%}">
                    </label>
                    <label class="clearfix">
                             <span>推广ID：</span>
                            <input type="text" id="invite_code" name="invite_code" class="tools-txt" value="{% if channel['invite_code'] is defined %}{{channel['invite_code']}}{%endif%}">
                    </label>
                    <label class="clearfix">
                             <span>姓名：</span>
                            <input type="text" id="user_name" name="user_name" class="tools-txt" value="{% if channel['user_name'] is defined %}{{channel['user_name']}}{%endif%}">
                    </label>
                    <label class="clearfix">
                             <span>员工号：</span>
                            <input type="text" id="employee_id" name="employee_id" class="tools-txt" value="{% if channel['employee_id'] is defined %}{{channel['employee_id']}}{%endif%}">
                    </label>
                    
                    <label>
                        <span>会员渠道：</span>
                        <span>
                            <select name="channel_id" id="channel_id" onchange="change_drmb(this.value)">
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
					<label>
                    <a href="/cpsuser/add"> <button class="btn btn-primary " type="button">添加推广会员</button></a>
                    </label>
					<label>
                    <a href="/cpsuser/daoru?lzi=1"> <button class="btn btn-primary " type="button">导入模板</button></a>
                    </label>
                    </form>
                    <form role="form" method="post" action="/cpsuser/daoru" enctype="multipart/form-data">
                        <input type="hidden" name="channel" value="{% if channel['channel_id'] is defined %}{{channel['channel_id']}}{%endif%}" >
                        <input style="float:right;width: 136px; height: 30px; margin-top: 0px;" type="file" name="Filedata">
                        <input style="float:right;" class="btn btn-primary" type="submit" onclick="return checkFrom()" value="导入">
                        <span name="channel_name" style="float:right">
                            {% for date in channel_lset %}
                                    {% if channel['channel_id'] == date['channel_id'] %}
                                        {{date['channel_name']}}
                                    {% endif %}
                            {% endfor %} 
                        </span>
                    </form>
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th></th>
                        <th>编号</th>
                        <th>会员渠道</th>
                        <th>会员ID</th>
                        <th>姓名</th>
                        <th>员工号</th>
                        <th>推广ID</th>
                        <th>注册会员总数</th>
                        <th>有效订单总数</th>
                        <th>返利总金额</th>
                        <th>上月注册会员数</th>
                        <th>上月订单有效数</th>
                        <th>上月应结算金额</th>
                        <th>本月预估结算金额</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    {% if list['list'] is defined and list['list'] != 0  %}
                    {% for v in list['list'] %}
                    <tr>
                    	<td><input name="ids" value="{{v['user_id']}}" type="checkbox"></td>
                        <td style="vertical-align: middle;">{{v['cps_id']}}</td>
                        <td style="vertical-align: middle;">{{v['channel_id']}}</td>
                        <td style="vertical-align: middle;">{{v['user_id']}}</td>
                        <td style="vertical-align: middle;">{{v['user_name']}}</td>
                        <td style="vertical-align: middle;">{{v['employee_id']}}</td>
                        <td style="vertical-align: middle;">{{v['invite_code']}}</td>
                        <td style="vertical-align: middle;">{{v['reg_num']}}</td>
                        <td style="vertical-align: middle;">{{v['order_num']}}</td>
                        <td style="vertical-align: middle;">{{v['count_back_amount']}}</td>
                        <td style="vertical-align: middle;">{{v['last_reg_num']}}</td>
                        <td style="vertical-align: middle;">{{v['last_order_num']}}</td>
                        <td style="vertical-align: middle;">{{v['last_mny']}}</td>
                        <td style="vertical-align: middle;">{{v['curr_mny']}}</td>
			<td style="vertical-align: middle;">
                        	<a href="/cpsuser/show?user={{v['user_id']}}">查看会员情况</a>
							</br>
                            {% if v['cps_status']==1 %}
                            <a href="#" onclick="xiugai({{v['cps_id']}},2)">冻结</a>
                            {% else %}
                            <a href="#" onclick="xiugai({{v['cps_id']}},1)">激活</a>
                            {% endif %} 
                        </td>
                    </tr>
                    {% endfor %}
                    {% elseif list['res'] is defined and list['res'] == 'success'  %}
                    <tr>
                        <td colspan="14" align="center">暂时无数据...</td>
                    </tr>
                    {% endif %}
                    <!-- <tr>
                        <td colspan="14" align=""> <a class="btn-sm btn-primary" href="javascript:clearing();">结算</a></td>
                        <tr> -->
                   
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
    function change_drmb(val){
        $("input[name='channel']").val(val);
        var obj=document.getElementById('channel_id');
        var text=obj.options[obj.selectedIndex].text;//获取文本
        if (val) {
            $("span[name='channel_name']").html(text);
        } else {
            $("span[name='channel_name']").html('');
        }
    }
    function checkFrom() {
        if (!$("input[name='Filedata']").val()) {
            alert('请选择文件');
            return false;
        }
        if (!$("input[name='channel']").val()) {
            alert('请选择渠道再导入');
            return false;
        }
        return true;
    }
</script>
{% endblock %}