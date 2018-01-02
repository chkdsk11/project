{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/cpsbusiness/list">业务代</a></li>
<li class="active">业务代列表</li>
{% endblock %}

{% block content %}
<style type="text/css">
    tr[class^="level-son"]{
        display: none;
    }
    tr.level-son-1{
        display: table-row;
    }
    .toggle-level{
        cursor: pointer;
    }
    td &gt; i{
        display: inline-block;
        width: 20px;
        height: 14px;
    }
    .table-btn-sm{
        border-width: 2px;
        font-size: 12px;
        padding: 2px 5px;
        line-height: 1.39;
    }
    .table-btn-sm + .table-btn-sm{
        margin-left: 20px;
    }
    a.btn:active {
        top: 1px;
    }
    #addFrist{
        position: absolute;
        top: 5px;
        right: 10px;
    }
    #addFrist:active{
        top: 6px;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/cpsbusiness/list" method="get">
                                <div class="tools-box">
                                    <label class="clearfix">
                                        <span>用户名： </span><input type="text" style="width:200px;" placeholder="用户名" name="phone" value="{% if channel['phone'] is defined %}{{channel['phone']}}{%endif%}" class="tools-txt" />
                                    </label>
                                    <label class="clearfix">
                                        <span>身份证： </span><input type="text" style="width:200px;" placeholder="身份证" name="id_card" value="{% if channel['id_card'] is defined %}{{channel['id_card']}}{%endif%}" class="tools-txt" />
                                    </label>
                                 <label class="clearfix">
                                        <span>地区： </span>
                                        <span>
                                         <select name="region_id" id="region_id" onchange="get_address(this.value)">
                            				<option value="0">请选择</option>
                              					{% for date in red %}
                                   					<option value="{{date['id']}}" {% if channel['region_id'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>&nbsp;&nbsp;
                                        <select name="city_id" id="city_id">
                            				<option value="">请选择</option>
                              					{% for date in city %}
                                   					<option value="{{date['id']}}" {% if channel['city_id'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>
                                     </label>
                                    </span>
                                    
                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/cpsbusiness/csv?csv=1">CSV模板</a>
                                    </label>
				                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/cpsbusiness/add">添加业务代表</a>
                                    </label>
                                    <!--
                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/cpsbusiness/csv?phone={{channel['phone']}}&id_card={{channel['phone']}}&region_id={{channel['region_id']}}&city_id={{channel['city_id']}}">导出csv</a>
                                    </label>
                                    -->

                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                    </label>

                                </div>
                            </form>
						    <form role="form" method="post" action="/cpsbusiness/drcsv" enctype="multipart/form-data">
                             <label class="clearfix" style="">
                                  <input id="file_upload" name="file_upload" type="file" multiple="true">
                   				  <input type="hidden" id="user_import_csv" name="user_import_csv">
						  <input style="float:left" type="submit" class="btn btn-sm btn-primary" value="批量导入">
                             </label>
                            </form>
                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-bordered table-hover dataTable no-footer">
                            <thead>
                            <tr role="row">
                                <th class="center">
                                    编号
                                </th>
                                <th class="center">用户名（手机号）</th>
                                <th class="center">姓名</th>
                                <th class="center">身份证</th>
                                <th class="center">省区</th>
                                <th class="center">操作</th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">
                               {% if list['list'] is defined %}
                               {% if list['list'] > 0 %}
                               {% for v in list['list'] %}
                                <tr>
                                    <td class="center">
                                      {{ v['business_id'] }}
                                    </td>
                                    <td class="center">
                                      {{ v['phone'] }}
                                    </td>
                                    <!--<td class="center">-->
                                        <!--{% if v['productRule'] is defined %}{{ v['productRule'] }}{% endif %}-->
                                    <!--</td>-->
                                    <td class="center">
                                      {{ v['real_name'] }}
                                    </td>
                                     <td class="center">
                                      {{ v['id_card'] }}
                                    </td>
                                     <td class="center">
                                      {{ v['region_name'] }}----{{ v['city'] }}
                                    </td>
                                    <td class="center">
                                      <a href="/cpsbusiness/edit?edit={{v['business_id']}}"><input type="button" value=" 修改 "></a>&nbsp;&nbsp;&nbsp;&nbsp;
									  <a href="#" onclick="dele({{v['business_id']}})"  ><input type="button" value=" 删除 "></a>
                                    </td>
                                </tr>
                            {% endfor %}   
                            {% else %}
                                <tr>
                                    <td class="center" colspan="6">
                                        暂无数据……
                                    </td>
                                </tr>
                            {% endif %}
                            {% endif %}
                            <!-- 遍历商品信息 end -->

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
{% if list['page'] is defined %}{{ list['page'] }}{% endif %}
{% endblock %}

{% block footer %}
<script>
   function dele(id){
       //var is_del=confirm('是否删除此功能');
       if(confirm('是否确认要删除？')){
          $.getJSON('/cpsbusiness/dele?id='+id+'&is_del=1', function(msg){
              if (msg.status == 'ok') {
                  layer_success('删除成功');
                  window.location.reload();
              } else {
                  layer_error('删除失败');
              }
          });
       }

  }
     function submit_search(){
        var act = true;
        var one_category = $('#one_category').val();
        if( one_category > 0){
            act = isCategorySelect();
        }
        return act;
    }
    //键盘点击事件
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
	
	  //启用|禁用控制
        $(document).on('click','.is_enable_click',function(){
            var id = $(this).attr('id');
            var is_enable = $(this).attr('name');
            var th = this;
            var html = '';
            if(is_enable == 1){
                html = '<i class="ace-icon fa fa-ban"></i>禁用';
                var name = 2;
            }else{
                html = '<i class="ace-icon fa  fa-circle-o"></i>启用';
                var name = 1;
            }
            $.getJSON('/cpschannel/pause?id='+id+'&dt='+is_enable, function(msg){
                if(msg.status == 'success'){
                    $(th).html(html);
                    $(th).attr('name',name);
                }else{
                    layer.msg(msg.info, {shade: 0.3, time: 300}); return false;
                }
            });
        });
	 //删除功能权限
        $(document).on('click','.is_delet_click',function(){
            var id = $(this).attr('id');
            //var is_del=confirm('是否删除此功能');
            if(confirm('是否删除此功能')){
                $.getJSON('/cpschannel/dele?id='+id, function(msg){
                    if (msg.status == 'success') {
                        layer_success('删除成功');
                        window.location.reload();
                    } else {
                        layer_error('删除失败');
                    }
                });
            }
        });
		
		
	function get_address(id) {
        $("select[name='city_id']").html('<option>请选择</option>');
        if (id > 0) {
            $.ajax({
                url: "/cpsbusiness/list",
                data: { "re": id,"stu":1},
                type: "get",
                dataType : "html",
                success: function (data) {
                    $("select[name='city_id']").html(data);
                }
            });
        }
    }
		
</script>
{% endblock %}