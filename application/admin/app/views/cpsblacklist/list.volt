{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/goodset/list">地推黑名单</a></li>
<li class="active"><a href="#">地推黑名单列表</a></li>
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
          
                <div class="page-header">
                    <h1>地推黑名单列表</h1>
                </div>
	    <form class="form-horizontal" role="form" method="get" action="/cpsblacklist/list">
                <div class="tools-box">
                    <label class="clearfix">

                             <span>手机号：</span>
                            <input type="text" id="phone" name="phone" class="tools-txt" value="{% if channel['phone'] is defined %}{{channel['phone']}}{%endif%}">
                    </label>
					   <label>
                       <a href="#"  onclick="getadd()"> <button class="btn btn-primary" type="button">添加</button></a>
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
	       </form>
                   <label>
                   	  <a href="/cpsblacklist/list?csv=1" style="float:left"><input type="button" value="下载模板" /></a>
                   </label>

                    <span style="float:right;">
                   <form role="form" method="post" action="/cpsblacklist/blacklist" enctype="multipart/form-data" >
                      <input  style="float:left;width:150px" id="File1" runat="server" name="UpLoadFile" type="file" />
                      <input style="float:left" type="submit" class="btn btn-sm btn-primary" value="批量导入">
                   </form>
                     </span>
                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
             

                    </tr>
                    </thead>
                    <tbody>
                
                    <tr>
                      <th class="text-center">编号</th>
                      <th class="text-center">手机号</th>
                      <th class="text-center">添加时间</th>
                      <th class="text-center">操作</th>
					</tr>
                     {% if  list['list'] is defined  %}
                    {% for v in list['list'] %}
                        <tr>
                        <td style="vertical-align: middle;">{{v['list_id']}}</td>
                        <td style="vertical-align: middle;">{{v['phone']}}</td>
                        <td style="vertical-align: middle;">{{v['add_time']}}</td>
                        <td style="vertical-align: middle;"><a class="delete" href="#" onclick="getdel({{v['list_id']}})">删除</a></td>
                       
                        
						
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
  
   function getadd(){
	 var phone =  $("#phone").val();
	 var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
     if (reg.test(phone)) {
        $.getJSON("/cpsblacklist/add?phone="+phone,function(result){
            if(result.on=='ok'){
                alert(result.sts);
                window.location.reload();
            }else{
                alert(result.sts);
            }
        });
     }else{
        alert("请填写正确的手机号");
     }

	}

   function getdel(id){
        if (confirm("确认要进行删除操作吗？")) {
           $.getJSON("/cpsblacklist/del?id="+id,function(result){
                if(result.on=='ok'){
                    alert(result.sts);
                    window.location.reload();
                }else{
                    alert(result.sts);
                }
           });
        }
	} 
	
</script>
{% endblock %}